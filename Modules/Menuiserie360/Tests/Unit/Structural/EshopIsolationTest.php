<?php

declare(strict_types=1);

namespace Modules\Menuiserie360\Tests\Unit\Structural;

use Modules\Menuiserie360\Tests\TestCase;

/**
 * Tests structurels d'isolation Menuiserie360 ↔ Eshop360 (spec v1.3 §6.2).
 *
 * 5 invariants ADR-021 + ADR-020 §1.4ter à activer dès le premier commit
 * (P0 — squelette). Toute violation = build broken.
 *
 * Ces tests scannent statiquement le code source de `Modules/Menuiserie360/`.
 * Ils sont délibérément simples (regex sur fichiers) — la défense en
 * profondeur est assurée par le ruleset deptrac (architecture gate au
 * niveau symbolique) et la PHPStan custom rule
 * `NoDirectCrossModuleTableAccess` (qui couvre `DB::table('eshop_*')`).
 *
 * Pas de baseline qui absorberait silencieusement de futures violations
 * (cf. spec v1.3 §6.2 « Pas de baseline »).
 */
final class EshopIsolationTest extends TestCase
{
    private const MENUISERIE_ROOT = __DIR__.'/../../../';

    /**
     * Invariant 1 — Aucun fichier Menuiserie360 n'importe `Modules\Eshop360\Models\*`
     * (ancien namespace legacy pré-R-101 / R-101 stubs S12).
     */
    public function test_no_file_imports_eshop360_legacy_models_namespace(): void
    {
        $matches = $this->grepInModule('use Modules\\\\Eshop360\\\\Models\\\\');

        $this->assertSame(
            [],
            $matches,
            'Menuiserie360 ne doit jamais importer Modules\\Eshop360\\Models\\* (legacy R-101). '.
            'Utiliser un contrat ADR-021 (Modules\\Eshop360\\Contracts\\*).'
        );
    }

    /**
     * Invariant 2 — Aucun fichier Menuiserie360 n'importe
     * `Modules\Eshop360\Domain\<Sub>\Models\*` (canonique post-R-101).
     */
    public function test_no_file_imports_eshop360_domain_models(): void
    {
        $matches = $this->grepInModule('use Modules\\\\Eshop360\\\\Domain\\\\[A-Z][A-Za-z0-9]+\\\\Models\\\\');

        $this->assertSame(
            [],
            $matches,
            'Menuiserie360 ne doit jamais importer un modèle Eloquent de '.
            'Modules\\Eshop360\\Domain\\<Sub>\\Models\\* (ADR-021 §1). '.
            'Passer par un contrat (Modules\\Eshop360\\Contracts\\<Domain>\\<Reader|Resolver>).'
        );
    }

    /**
     * Invariant 3 — Aucun fichier Menuiserie360 n'importe
     * `Modules\Eshop360\Services\*` (services internes Eshop360).
     *
     * Exception : la spec v1.3 §5.2 a marqué le pattern ACL comme RETIRED v1.3
     * (BC-Finance autonome). Donc cet invariant doit être strict en P0.
     */
    public function test_no_file_imports_eshop360_internal_services(): void
    {
        $matches = $this->grepInModule('use Modules\\\\Eshop360\\\\Services\\\\');

        $this->assertSame(
            [],
            $matches,
            'Menuiserie360 ne doit pas importer Modules\\Eshop360\\Services\\* '.
            '(ADR-021 + spec v1.3 §5.2 RETIRED v1.3 — BC-Finance autonome).'
        );
    }

    /**
     * Invariant 4 — Aucun fichier Menuiserie360 n'écrit `DB::table('eshop_*')`
     * (accès direct aux tables Eshop360, contournement ADR-021).
     *
     * Note : la PHPStan custom rule `NoDirectCrossModuleTableAccess` couvre déjà
     * cet invariant globalement. Ce test fournit une vérification indépendante
     * (defense in depth, et exécutable même si PHPStan est désactivé).
     */
    public function test_no_file_calls_db_table_on_eshop_tables(): void
    {
        $matches = $this->grepInModule("DB::table\\(['\"]eshop_");

        $this->assertSame(
            [],
            $matches,
            'Menuiserie360 ne doit pas appeler DB::table(\'eshop_*\') '.
            '(ADR-021 + règle PHPStan NoDirectCrossModuleTableAccess).'
        );
    }

    /**
     * Invariant 5 — Aucun nom de classe Menuiserie360 n'apparaît dans le morph
     * map central d'Eshop360ServiceProvider (Cas A — spec v1.3 §1.4ter).
     *
     * Menuiserie360 doit poser SON PROPRE morph map dans
     * Menuiserie360ServiceProvider::boot(), avec short keys `mnu.*`.
     */
    public function test_no_menuiserie_class_in_eshop360_morph_map(): void
    {
        $eshopProviderPath = __DIR__.'/../../../../Eshop360/Providers/Eshop360ServiceProvider.php';

        if (! file_exists($eshopProviderPath)) {
            // Si le module Eshop360 n'est pas présent, l'invariant est trivialement vérifié.
            // Marqué skip plutôt que pass silencieux pour révéler clairement la situation.
            $this->markTestSkipped('Eshop360ServiceProvider not found — invariant trivially holds.');
        }

        $content = (string) file_get_contents($eshopProviderPath);

        // On cherche toute mention d'une classe Menuiserie360 dans le morph map central.
        $hasMenuiserieEntry = (bool) preg_match('/Modules\\\\Menuiserie360\\\\/', $content);

        $this->assertFalse(
            $hasMenuiserieEntry,
            'Aucune classe Modules\\Menuiserie360\\* ne doit apparaître dans '.
            'Eshop360ServiceProvider (morph map central). Cas A décidé v1.3 §1.4ter — '.
            'Menuiserie360 a son propre morph map dans Menuiserie360ServiceProvider::boot().'
        );
    }

    // ─── Helper ──────────────────────────────────────────────────

    /**
     * Grep récursif (regex) sur tous les fichiers `.php` sous `Modules/Menuiserie360/`,
     * excluant le répertoire Tests (où les invariants ne s'appliquent pas — un
     * test STRUCTURAL peut référencer la chaîne interdite dans son code).
     *
     * @return array<int, string> chemins des fichiers contenant un match
     */
    private function grepInModule(string $regex): array
    {
        $root = realpath(self::MENUISERIE_ROOT);
        if ($root === false) {
            return [];
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($root, \RecursiveDirectoryIterator::SKIP_DOTS)
        );

        $matches = [];
        foreach ($iterator as $file) {
            if (! $file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }

            $path = (string) $file->getRealPath();

            // Exclure le répertoire Tests (où les invariants peuvent légitimement
            // être référencés dans des assertions ou messages d'erreur).
            if (str_contains($path, DIRECTORY_SEPARATOR.'Tests'.DIRECTORY_SEPARATOR)) {
                continue;
            }

            $content = (string) file_get_contents($path);
            if (preg_match('#'.$regex.'#', $content) === 1) {
                $matches[] = $path;
            }
        }

        return $matches;
    }
}
