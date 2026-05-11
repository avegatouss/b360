<?php

declare(strict_types=1);

namespace Modules\Menuiserie360\Domain\Stock\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Modules\Menuiserie360\Domain\Stock\Enums\CategorieMatiere;
use Modules\Menuiserie360\Domain\Stock\Enums\UniteMesure;
use Modules\Menuiserie360\Domain\Stock\Models\MatierePremiere;

/**
 * M-UI-9 — Import CSV de matières premières.
 *
 * Format attendu (séparateur `,`, BOM UTF-8 toléré, 1re ligne = en-tête) :
 *   code,designation,categorie,unite,prix_unitaire,seuil_alerte,fournisseur_principal,is_active
 *   ALU-001,Profil 40x60,profile_alu,m_lineaire,2500,50,Fournisseur A,1
 *
 * Comportement :
 *   - `updateOrCreate` sur (instance_id, code) — idempotent.
 *   - Validation ligne par ligne : code unique, designation requise, enums
 *     valides, prix/seuil numériques positifs.
 *   - Rejet de ligne = saute la ligne et accumule l'erreur dans le rapport.
 *   - Transaction unique pour atomicité : si une ligne lève une exception
 *     fatale (pas une erreur de validation), tout le batch rollback.
 */
final class ImportMatieresCsvService
{
    /**
     * @return array{
     *   created: int,
     *   updated: int,
     *   errors: list<array{line: int, message: string}>,
     * }
     */
    public function import(UploadedFile $file, int $instanceId): array
    {
        $handle = fopen($file->getRealPath(), 'rb');
        if ($handle === false) {
            return ['created' => 0, 'updated' => 0, 'errors' => [['line' => 0, 'message' => 'Impossible d\'ouvrir le fichier.']]];
        }

        $created = 0;
        $updated = 0;
        $errors = [];

        try {
            $header = fgetcsv($handle);
            if ($header === false) {
                return ['created' => 0, 'updated' => 0, 'errors' => [['line' => 0, 'message' => 'Fichier vide.']]];
            }
            // Strip BOM UTF-8 de la première colonne si présent.
            if (isset($header[0])) {
                $header[0] = preg_replace('/^\xEF\xBB\xBF/', '', $header[0]) ?? $header[0];
            }
            $header = array_map(static fn ($h) => is_string($h) ? trim(strtolower($h)) : $h, $header);

            $required = ['code', 'designation', 'categorie', 'unite', 'prix_unitaire', 'seuil_alerte'];
            foreach ($required as $col) {
                if (! in_array($col, $header, true)) {
                    return ['created' => 0, 'updated' => 0, 'errors' => [['line' => 1, 'message' => "Colonne requise manquante : {$col}"]]];
                }
            }

            DB::transaction(function () use ($handle, $header, $instanceId, &$created, &$updated, &$errors): void {
                $lineNumber = 1;
                while (($row = fgetcsv($handle)) !== false) {
                    $lineNumber++;
                    if (count($row) === 1 && trim((string) $row[0]) === '') {
                        continue; // ligne vide
                    }

                    $data = $this->mapRow($header, $row);
                    $error = $this->validate($data);
                    if ($error !== null) {
                        $errors[] = ['line' => $lineNumber, 'message' => $error];

                        continue;
                    }

                    $existing = MatierePremiere::query()
                        ->where('instance_id', $instanceId)
                        ->where('code', $data['code'])
                        ->first();

                    if ($existing === null) {
                        MatierePremiere::create(['instance_id' => $instanceId, ...$data]);
                        $created++;
                    } else {
                        $existing->fill($data)->save();
                        $updated++;
                    }
                }
            });
        } finally {
            fclose($handle);
        }

        return ['created' => $created, 'updated' => $updated, 'errors' => $errors];
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

        return [
            'code' => isset($assoc['code']) ? trim((string) $assoc['code']) : '',
            'designation' => isset($assoc['designation']) ? trim((string) $assoc['designation']) : '',
            'categorie' => isset($assoc['categorie']) ? trim((string) $assoc['categorie']) : '',
            'unite' => isset($assoc['unite']) ? trim((string) $assoc['unite']) : '',
            'prix_unitaire' => isset($assoc['prix_unitaire']) ? (float) str_replace(',', '.', (string) $assoc['prix_unitaire']) : 0.0,
            'seuil_alerte' => isset($assoc['seuil_alerte']) ? (float) str_replace(',', '.', (string) $assoc['seuil_alerte']) : 0.0,
            'fournisseur_principal' => isset($assoc['fournisseur_principal']) && $assoc['fournisseur_principal'] !== ''
                ? trim((string) $assoc['fournisseur_principal'])
                : null,
            'is_active' => isset($assoc['is_active'])
                ? in_array(strtolower(trim((string) $assoc['is_active'])), ['1', 'true', 'oui', 'yes'], true)
                : true,
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function validate(array $data): ?string
    {
        if ($data['code'] === '') {
            return 'code vide.';
        }
        if (strlen($data['code']) > 50) {
            return 'code > 50 caractères.';
        }
        if ($data['designation'] === '') {
            return 'designation vide.';
        }
        $categories = array_map(static fn (CategorieMatiere $c) => $c->value, CategorieMatiere::cases());
        if (! in_array($data['categorie'], $categories, true)) {
            return 'categorie invalide (attendu : '.implode(', ', $categories).').';
        }
        $unites = array_map(static fn (UniteMesure $u) => $u->value, UniteMesure::cases());
        if (! in_array($data['unite'], $unites, true)) {
            return 'unite invalide (attendu : '.implode(', ', $unites).').';
        }
        if ((float) $data['prix_unitaire'] < 0) {
            return 'prix_unitaire négatif.';
        }
        if ((float) $data['seuil_alerte'] < 0) {
            return 'seuil_alerte négatif.';
        }

        return null;
    }
}
