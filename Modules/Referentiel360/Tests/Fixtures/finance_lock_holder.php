<?php

declare(strict_types=1);

/**
 * ADR-031 / Lot 3 (ZONE L1) — Fixture de concurrence RÉELLE (MySQL/InnoDB).
 *
 * Process « adverse » exécuté en SOUS-PROCESSUS par
 * {@see \Modules\Referentiel360\Tests\Feature\FinanceWriterConcurrencyMysqlTest}.
 *
 * Il ouvre une connexion PDO DISTINCTE, démarre une transaction et pose un verrou
 * exclusif (`SELECT ... FOR UPDATE`) sur la ligne `ref_documents_finance` cible,
 * puis le CONSERVE pendant `hold_ms` avant d'appliquer `paid=60` et de committer.
 *
 * Pendant ce hold, le process principal appelle le VRAI
 * `EloquentFinanceWriter::upsertFromModule` (paid=120) : son `lockForUpdate()`
 * doit BLOQUER jusqu'au commit de ce fixture, prouvant la sérialisation réelle.
 *
 * Ce script n'amorce PAS Laravel (PDO brut) : il n'écrit que sur la ligne déjà
 * créée par le test et ne touche AUCUNE logique applicative (périmètre tests).
 *
 * argv[1] = chemin d'un fichier JSON (passer par fichier, pas par argv : le
 * quoting Windows mange les guillemets d'un JSON inline) contenant :
 *   { host, port, database, username, password, document_id, paid, hold_ms, ready_file }
 *
 * Écrit le fichier sentinelle `ready_file` UNE FOIS le verrou acquis, pour que le
 * process principal sache quand lancer son upsert concurrent (pas de sleep aveugle).
 */
$configPath = (string) ($argv[1] ?? '');
$config = json_decode((string) file_get_contents($configPath), true, 512, JSON_THROW_ON_ERROR);

$dsn = sprintf(
    'mysql:host=%s;port=%s;dbname=%s',
    $config['host'],
    $config['port'],
    $config['database'],
);

$pdo = new PDO($dsn, $config['username'], (string) $config['password'], [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
]);

$documentId = (int) $config['document_id'];
$paid = (string) $config['paid'];
$holdMs = (int) $config['hold_ms'];
$readyFile = (string) $config['ready_file'];

$pdo->beginTransaction();

// Verrou exclusif sur la ligne ciblée (même ligne que le Writer concurrent).
$lock = $pdo->prepare('SELECT id, amount_ttc FROM ref_documents_finance WHERE id = :id FOR UPDATE');
$lock->execute(['id' => $documentId]);
$row = $lock->fetch(PDO::FETCH_ASSOC);

if ($row === false) {
    fwrite(STDERR, "lock_holder: document {$documentId} introuvable\n");
    exit(2);
}

// Signale au process principal que le verrou est ACQUIS et tenu.
file_put_contents($readyFile, (string) time());

// Conserve le verrou : le Writer concurrent doit attendre ici.
usleep($holdMs * 1000);

$ttc = (string) $row['amount_ttc'];
$due = bcsub($ttc, $paid, 2);
$status = bccomp($paid, '0', 2) === 0
    ? 'issued'
    : (bccomp($paid, $ttc, 2) >= 0 ? 'paid' : 'partially_paid');

$update = $pdo->prepare(
    'UPDATE ref_documents_finance
        SET paid_amount = :paid, due_amount = :due, status_normalized = :status, updated_at = NOW()
      WHERE id = :id'
);
$update->execute([
    'paid' => $paid,
    'due' => $due,
    'status' => $status,
    'id' => $documentId,
]);

$pdo->commit();

fwrite(STDOUT, "lock_holder: committed paid={$paid}\n");
exit(0);
