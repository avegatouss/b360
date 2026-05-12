<?php

declare(strict_types=1);

namespace Modules\Menuiserie360\Domain\Commercial\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Modules\Eshop360\Domain\CRM\Models\Customer;
use Modules\Menuiserie360\Domain\Commercial\Enums\StatutDevis;
use Modules\Menuiserie360\Domain\Commercial\Models\Devis;
use Modules\Menuiserie360\Domain\Commercial\Models\LigneDevis;
use Modules\Menuiserie360\Domain\Stock\Models\MatierePremiere;

/**
 * V1.2-3 — Import CSV batch de devis menuiserie.
 *
 * Format attendu (séparateur `,`, BOM UTF-8 toléré, 1re ligne = en-tête) :
 *   devis_ref,client_code,designation,quantite,prix_unitaire_ht,cout_revient,
 *   largeur_mm,hauteur_mm,matiere_code,taux_tva,validite_jours,marge_minimum
 *
 * Une ligne = une ligne de devis. Les lignes partageant le même `devis_ref`
 * sont regroupées dans le même devis. Le 1er row du groupe fixe les
 * paramètres devis (taux_tva, validite_jours, marge_minimum).
 *
 * Comportement :
 *   - Devis ré-importés (devis_ref existant pour l'instance) : SKIP avec
 *     erreur "devis déjà importé". Pas d'écrasement (ce sont des données
 *     business potentiellement actées).
 *   - client_code → résolu via Customer Eshop360 (ADR-021 §exception
 *     seeders/imports : autorisé pour les services de migration).
 *   - matiere_code → résolu via MatierePremiere (nullable, peut être vide).
 *   - Numérotation devis : IMPORT-<devis_ref> pour traçabilité.
 *   - Transaction unique : toute exception fatale rollback tout le batch.
 *
 * Note ADR-021 : cette dépendance directe au modèle Customer Eshop360 est
 * l'extension du même pattern que MenuiserieDemoSeeder (cf. exclusion
 * Database/Seeders/ dans EshopIsolationTest, étendue aux services Import*Csv).
 */
final class ImportDevisCsvService
{
    /**
     * @return array{
     *   created: int,
     *   skipped: int,
     *   lignes_created: int,
     *   errors: list<array{ref: string|null, message: string}>,
     * }
     */
    public function import(UploadedFile $file, int $instanceId): array
    {
        $handle = fopen($file->getRealPath(), 'rb');
        if ($handle === false) {
            return ['created' => 0, 'skipped' => 0, 'lignes_created' => 0, 'errors' => [['ref' => null, 'message' => 'Impossible d\'ouvrir le fichier.']]];
        }

        $created = 0;
        $skipped = 0;
        $lignesCreated = 0;
        $errors = [];

        try {
            $header = fgetcsv($handle);
            if ($header === false) {
                return ['created' => 0, 'skipped' => 0, 'lignes_created' => 0, 'errors' => [['ref' => null, 'message' => 'Fichier vide.']]];
            }
            if (isset($header[0])) {
                $header[0] = preg_replace('/^\xEF\xBB\xBF/', '', $header[0]) ?? $header[0];
            }
            $header = array_map(static fn ($h) => is_string($h) ? trim(strtolower($h)) : $h, $header);

            $required = ['devis_ref', 'client_code', 'designation', 'quantite', 'prix_unitaire_ht'];
            foreach ($required as $col) {
                if (! in_array($col, $header, true)) {
                    return ['created' => 0, 'skipped' => 0, 'lignes_created' => 0, 'errors' => [['ref' => null, 'message' => "Colonne requise manquante : {$col}"]]];
                }
            }

            // Lire tout le CSV en mémoire pour grouper par devis_ref.
            /** @var array<string, list<array<string, mixed>>> $byRef */
            $byRef = [];
            while (($row = fgetcsv($handle)) !== false) {
                if (count($row) === 1 && trim((string) $row[0]) === '') {
                    continue;
                }
                $mapped = $this->mapRow($header, $row);
                $ref = trim((string) ($mapped['devis_ref'] ?? ''));
                if ($ref === '') {
                    $errors[] = ['ref' => null, 'message' => 'devis_ref vide sur une ligne.'];

                    continue;
                }
                $byRef[$ref] ??= [];
                $byRef[$ref][] = $mapped;
            }

            DB::transaction(function () use ($byRef, $instanceId, &$created, &$skipped, &$lignesCreated, &$errors): void {
                foreach ($byRef as $ref => $lignes) {
                    $error = $this->processGroup((string) $ref, $lignes, $instanceId, $created, $skipped, $lignesCreated);
                    if ($error !== null) {
                        $errors[] = ['ref' => (string) $ref, 'message' => $error];
                    }
                }
            });
        } finally {
            fclose($handle);
        }

        return ['created' => $created, 'skipped' => $skipped, 'lignes_created' => $lignesCreated, 'errors' => $errors];
    }

    /**
     * @param  list<array<string, mixed>>  $lignes
     */
    private function processGroup(string $ref, array $lignes, int $instanceId, int &$created, int &$skipped, int &$lignesCreated): ?string
    {
        $numero = 'IMPORT-'.$ref;

        // Idempotence : on n'écrase pas un devis déjà importé (business data).
        if (Devis::query()->where('instance_id', $instanceId)->where('numero', $numero)->exists()) {
            $skipped++;

            return 'devis déjà importé (numero existant).';
        }

        $first = $lignes[0];
        $clientCode = trim((string) ($first['client_code'] ?? ''));
        if ($clientCode === '') {
            return 'client_code vide.';
        }

        $customer = Customer::withoutGlobalScopes()
            ->where('instance_id', $instanceId)
            ->where('code', $clientCode)
            ->first();
        if ($customer === null) {
            return "client_code '{$clientCode}' introuvable côté Eshop360.";
        }

        $tauxTva = (float) ($first['taux_tva'] ?? 0.18);
        $validiteJours = (int) ($first['validite_jours'] ?? 30);
        $margeMinimum = (float) ($first['marge_minimum'] ?? 0.15);

        $devis = Devis::create([
            'instance_id' => $instanceId,
            'numero' => $numero,
            'client_id' => $customer->getKey(),
            'statut' => StatutDevis::BROUILLON->value,
            'taux_tva' => $tauxTva,
            'validite_jours' => $validiteJours,
            'marge_minimum' => $margeMinimum,
            'montant_ht' => 0,
            'montant_tva' => 0,
            'montant_ttc' => 0,
        ]);

        $totalHt = 0.0;
        foreach ($lignes as $i => $l) {
            $designation = trim((string) ($l['designation'] ?? ''));
            if ($designation === '') {
                continue;
            }
            $qte = (int) ($l['quantite'] ?? 1);
            $pu = (float) str_replace(',', '.', (string) ($l['prix_unitaire_ht'] ?? 0));
            if ($qte < 1 || $pu < 0) {
                continue;
            }

            $matiereId = null;
            $matiereCode = trim((string) ($l['matiere_code'] ?? ''));
            if ($matiereCode !== '') {
                $matiere = MatierePremiere::query()
                    ->where('instance_id', $instanceId)
                    ->where('code', $matiereCode)
                    ->first();
                $matiereId = $matiere?->getKey();
            }

            $montant = $pu * $qte;
            LigneDevis::create([
                'instance_id' => $instanceId,
                'devis_id' => $devis->getKey(),
                'designation' => $designation,
                'quantite' => $qte,
                'prix_unitaire_ht' => $pu,
                'cout_revient' => (float) str_replace(',', '.', (string) ($l['cout_revient'] ?? 0)),
                'largeur_mm' => ! empty($l['largeur_mm']) ? (int) $l['largeur_mm'] : null,
                'hauteur_mm' => ! empty($l['hauteur_mm']) ? (int) $l['hauteur_mm'] : null,
                'matiere_id' => $matiereId,
                'montant_ht' => $montant,
                'ordre' => $i + 1,
            ]);
            $totalHt += $montant;
            $lignesCreated++;
        }

        $tva = round($totalHt * $tauxTva, 2);
        $devis->fill([
            'montant_ht' => $totalHt,
            'montant_tva' => $tva,
            'montant_ttc' => round($totalHt + $tva, 2),
        ])->save();

        $created++;

        return null;
    }

    /**
     * @param  list<string>  $header
     * @param  list<string|null>  $row
     * @return array<string, mixed>
     */
    private function mapRow(array $header, array $row): array
    {
        $assoc = [];
        foreach ($header as $i => $col) {
            $assoc[$col] = $row[$i] ?? null;
        }

        return $assoc;
    }
}
