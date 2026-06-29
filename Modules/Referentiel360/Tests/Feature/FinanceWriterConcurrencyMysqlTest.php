<?php

declare(strict_types=1);

namespace Modules\Referentiel360\Tests\Feature;

use App\Instances\Instance;
use Illuminate\Support\Facades\DB;
use Modules\Core\Support\CurrentInstance;
use Modules\Referentiel360\Contracts\Finance\FinanceAttributesDto;
use Modules\Referentiel360\Contracts\Finance\FinanceWriter;
use Modules\Referentiel360\Domain\Finance\Models\FinanceDocument;
use Modules\Referentiel360\Domain\Finance\Models\FinanceDocumentLink;
use PDO;
use PHPUnit\Framework\Attributes\Group;
use RuntimeException;
use Tests\TestCase;
use Throwable;

/**
 * ADR-031 / Lot 3 (ZONE L1) — Concurrence RÉELLE sur MySQL/InnoDB.
 *
 * Les tests SQLite de {@see FinanceWriterUpsertTest} sont SÉQUENTIELS : SQLite
 * verrouille toute la base, il ne peut donc pas prouver que `lockForUpdate()`
 * SÉRIALISE deux push concurrents (pas de vraie contention ligne à ligne).
 *
 * Ce test ouvre une contention RÉELLE :
 *  - un SOUS-PROCESSUS PDO distinct ({@see Fixtures/finance_lock_holder.php}) pose
 *    un `SELECT ... FOR UPDATE` sur la ligne `ref_documents_finance` et le tient ;
 *  - pendant ce hold, le process principal appelle le VRAI
 *    `EloquentFinanceWriter::upsertFromModule` (paid=120) : son `lockForUpdate()`
 *    doit BLOQUER jusqu'au commit du sous-process (paid=60), puis appliquer 120.
 *
 * On prouve ainsi : (1) blocage effectif (durée mesurée), (2) convergence
 * last-write-wins (paid=120, status `paid`, due=0), (3) AUCUNE perte de mise à
 * jour, (4) 0 doublon de lien — donc la sérialisation L1 fonctionne réellement.
 *
 * Le test N'UTILISE PAS RefreshDatabase : il tourne sur la base MySQL réelle
 * (dev `b360`), crée ses propres lignes scopées à une instance dédiée et les
 * NETTOIE en tearDown. Il est SKIPPÉ proprement si MySQL n'est pas joignable
 * (CI SQLite) pour ne pas casser la suite.
 *
 * @group concurrency
 * @group mysql
 */
#[Group('concurrency')]
#[Group('mysql')]
final class FinanceWriterConcurrencyMysqlTest extends TestCase
{
    private const CONNECTION = 'mysql_concurrency';

    private int $instanceId = 0;

    private int $documentId = 0;

    /** @var array<string, mixed> */
    private array $db = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->db = $this->mysqlConfig();

        if (! $this->mysqlReachable($this->db)) {
            $this->markTestSkipped(
                'MySQL injoignable ('.$this->db['host'].':'.$this->db['port'].'/'.$this->db['database']
                .') — test de concurrence réelle skippé (env SQLite/CI).'
            );
        }

        // Connexion Eloquent dédiée pointant la base MySQL réelle, AVEC les tables
        // ref_*finance* déjà migrées (DB de dev). Aucune dépendance à RefreshDatabase.
        config()->set('database.connections.'.self::CONNECTION, [
            'driver' => 'mysql',
            'host' => $this->db['host'],
            'port' => $this->db['port'],
            'database' => $this->db['database'],
            'username' => $this->db['username'],
            'password' => $this->db['password'],
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => true,
            'options' => [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION],
        ]);

        // Le modèle miroir et l'Instance résolvent sur la connexion par défaut :
        // on bascule default + system vers MySQL pour toute la durée du test.
        config()->set('database.default', self::CONNECTION);
        config()->set('database.connections.system', config('database.connections.'.self::CONNECTION));
        DB::purge(self::CONNECTION);
        DB::purge('system');
        DB::setDefaultConnection(self::CONNECTION);

        $this->ensureTablesPresent();

        // Instance dédiée (jamais root) — isolation des données de test.
        $instance = Instance::create([
            'name' => 'Concurrency Test',
            'slug' => 'concurrency-test-'.uniqid(),
            'is_active' => true,
            'meta' => ['is_root' => false, 'concurrency_test' => true],
        ]);
        $this->instanceId = (int) $instance->id;
        CurrentInstance::set($instance);
    }

    protected function tearDown(): void
    {
        // Nettoyage explicite : on tourne sur la base de dev persistante.
        try {
            if ($this->instanceId > 0) {
                FinanceDocumentLink::withoutInstanceScope()->where('instance_id', $this->instanceId)->forceDelete();
                FinanceDocument::withoutInstanceScope()->where('instance_id', $this->instanceId)->forceDelete();
                DB::connection(self::CONNECTION)->table('instances')->where('id', $this->instanceId)->delete();
            }
        } catch (Throwable) {
            // best-effort cleanup
        } finally {
            CurrentInstance::clear();
            parent::tearDown();
        }
    }

    public function test_two_concurrent_pushes_serialize_via_lock_for_update_and_converge(): void
    {
        $writer = app(FinanceWriter::class);

        // 1) État initial : document miroir créé via le VRAI Writer (paid=0).
        $writer->upsertFromModule($this->instanceId, 'mnu.invoice', new FinanceAttributesDto(
            localId: 9001,
            documentNumber: 'MNU-CONC-0001',
            amountTtc: '120.00',
            paidAmount: '0.00',
            sourceModule: 'menuiserie',
        ));

        $this->documentId = (int) FinanceDocument::withoutInstanceScope()
            ->where('instance_id', $this->instanceId)
            ->value('id');

        $this->assertGreaterThan(0, $this->documentId);

        // 2) Sous-process adverse : pose FOR UPDATE sur la ligne et le tient ~2s,
        //    puis applique paid=60. Il signale l'acquisition via un fichier ready.
        $readyFile = tempnam(sys_get_temp_dir(), 'finlock_ready_');
        @unlink($readyFile); // recréé par le fixture une fois le verrou pris
        $holdMs = 2000;

        $proc = $this->startLockHolder($readyFile, $holdMs);

        try {
            // Attend que le verrou soit RÉELLEMENT acquis par le sous-process.
            $this->waitForFile($readyFile, timeoutMs: 5000);

            // 3) Le VRAI Writer tente paid=120 : son lockForUpdate doit BLOQUER
            //    jusqu'au commit du sous-process.
            $start = microtime(true);
            $final = $writer->upsertFromModule($this->instanceId, 'mnu.invoice', new FinanceAttributesDto(
                localId: 9001,
                documentNumber: 'MNU-CONC-0001',
                amountTtc: '120.00',
                paidAmount: '120.00',
                sourceModule: 'menuiserie',
            ));
            $elapsedMs = (microtime(true) - $start) * 1000;
        } finally {
            $this->finishProcess($proc);
            @unlink($readyFile);
        }

        // PREUVE 1 — blocage réel : le Writer a attendu la libération du verrou.
        // (hold=2000ms, ready écrit quasi immédiatement ; marge généreuse 1200ms.)
        $this->assertGreaterThan(
            1200,
            $elapsedMs,
            'Le upsert concurrent aurait dû BLOQUER sur lockForUpdate (sérialisation L1).'
        );

        // PREUVE 2 — convergence last-write-wins : le dernier writer (120) gagne.
        $this->assertSame('120.00', $final->paidAmount, 'paid final = dernier writer (120)');
        $this->assertSame('0.00', $final->dueAmount);
        $this->assertSame('paid', $final->statusNormalized);

        // PREUVE 3 — état persistant cohérent, pas de perte de mise à jour
        // (lecture colonne par colonne : pas de dépendance au typage du modèle).
        $persisted = FinanceDocument::withoutInstanceScope()
            ->where('instance_id', $this->instanceId)
            ->whereKey($this->documentId);
        $this->assertSame('120.00', (string) $persisted->clone()->value('paid_amount'));
        $this->assertSame('0.00', (string) $persisted->clone()->value('due_amount'));
        $this->assertSame('paid', (string) $persisted->clone()->value('status_normalized'));

        // PREUVE 4 — aucun doublon : 1 document, 1 lien pour ce localId.
        $this->assertSame(1, FinanceDocument::withoutInstanceScope()
            ->where('instance_id', $this->instanceId)->count());
        $this->assertSame(1, FinanceDocumentLink::withoutInstanceScope()
            ->where('instance_id', $this->instanceId)
            ->where('linkable_type', 'mnu.invoice')
            ->where('linkable_id', 9001)
            ->count());
    }

    /**
     * Démarre le fixture lock-holder en sous-process (connexion PDO distincte).
     *
     * @return resource
     */
    private function startLockHolder(string $readyFile, int $holdMs)
    {
        $payload = json_encode([
            'host' => $this->db['host'],
            'port' => $this->db['port'],
            'database' => $this->db['database'],
            'username' => $this->db['username'],
            'password' => $this->db['password'],
            'document_id' => $this->documentId,
            'paid' => '60.00',
            'hold_ms' => $holdMs,
            'ready_file' => $readyFile,
        ], JSON_THROW_ON_ERROR);

        // Payload passé par FICHIER (le quoting Windows corrompt un JSON inline).
        $configFile = tempnam(sys_get_temp_dir(), 'finlock_cfg_');
        file_put_contents($configFile, $payload);
        $this->configFile = $configFile;

        $script = __DIR__.'/../Fixtures/finance_lock_holder.php';
        $php = PHP_BINARY;

        $command = escapeshellarg($php).' '.escapeshellarg($script).' '.escapeshellarg($configFile);

        $descriptors = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];

        $proc = proc_open($command, $descriptors, $pipes);
        if (! is_resource($proc)) {
            throw new RuntimeException('Impossible de démarrer le sous-process lock-holder.');
        }

        // Ferme stdin ; laisse stdout/stderr lisibles à la fin.
        fclose($pipes[0]);
        $this->pipes = $pipes;

        return $proc;
    }

    /** @var array<int, resource> */
    private array $pipes = [];

    private ?string $configFile = null;

    /**
     * @param  resource  $proc
     */
    private function finishProcess($proc): void
    {
        $stdout = is_resource($this->pipes[1] ?? null) ? (string) stream_get_contents($this->pipes[1]) : '';
        $stderr = is_resource($this->pipes[2] ?? null) ? (string) stream_get_contents($this->pipes[2]) : '';

        foreach ($this->pipes as $pipe) {
            if (is_resource($pipe)) {
                fclose($pipe);
            }
        }

        $exit = proc_close($proc);

        if ($this->configFile !== null) {
            @unlink($this->configFile);
            $this->configFile = null;
        }

        if ($exit !== 0) {
            throw new RuntimeException(
                "lock-holder a échoué (exit {$exit}). stdout={$stdout} stderr={$stderr}"
            );
        }
    }

    private function waitForFile(string $path, int $timeoutMs): void
    {
        $deadline = microtime(true) + ($timeoutMs / 1000);
        while (microtime(true) < $deadline) {
            clearstatcache(true, $path);
            if (is_file($path)) {
                return;
            }
            usleep(20_000);
        }

        throw new RuntimeException("Le sous-process lock-holder n'a pas acquis le verrou dans le délai imparti.");
    }

    private function ensureTablesPresent(): void
    {
        $schema = DB::connection(self::CONNECTION)->getSchemaBuilder();
        foreach (['ref_documents_finance', 'ref_finance_links', 'instances'] as $table) {
            if (! $schema->hasTable($table)) {
                $this->markTestSkipped("Table {$table} absente de la base MySQL — migrations non appliquées.");
            }
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function mysqlConfig(): array
    {
        // Valeurs de la base de dev `b360` par défaut. On NE retombe PAS sur les
        // DB_* : phpunit.xml les force vers SQLite (`DB_DATABASE=:memory:`), ce qui
        // fausserait la cible. Override possible via les CONCURRENCY_DB_* (CI/local).
        $env = static fn (string $key, string $default): string => is_string($v = getenv($key)) && $v !== '' ? $v : $default;

        return [
            'host' => $env('CONCURRENCY_DB_HOST', '127.0.0.1'),
            'port' => $env('CONCURRENCY_DB_PORT', '3306'),
            'database' => $env('CONCURRENCY_DB_DATABASE', 'b360'),
            'username' => $env('CONCURRENCY_DB_USERNAME', 'root'),
            'password' => $env('CONCURRENCY_DB_PASSWORD', ''),
        ];
    }

    /**
     * @param  array<string, mixed>  $db
     */
    private function mysqlReachable(array $db): bool
    {
        $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s', $db['host'], $db['port'], $db['database']);

        try {
            new PDO($dsn, (string) $db['username'], (string) $db['password'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_TIMEOUT => 2,
            ]);

            return true;
        } catch (Throwable) {
            return false;
        }
    }
}
