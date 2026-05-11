<?php

declare(strict_types=1);

namespace Modules\Menuiserie360\Database\Seeders;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Modules\Eshop360\Domain\CRM\Models\Customer;
use Modules\Menuiserie360\Domain\Chantier\Enums\StatutChantier;
use Modules\Menuiserie360\Domain\Chantier\Models\Chantier;
use Modules\Menuiserie360\Domain\Chantier\Models\EtapeChantier;
use Modules\Menuiserie360\Domain\Client\Models\ClientMenuiserie;
use Modules\Menuiserie360\Domain\Commercial\Enums\StatutDevis;
use Modules\Menuiserie360\Domain\Commercial\Models\Devis;
use Modules\Menuiserie360\Domain\Commercial\Models\LigneDevis;
use Modules\Menuiserie360\Domain\Finance\Enums\StatutFacture;
use Modules\Menuiserie360\Domain\Finance\Enums\TypeFacture;
use Modules\Menuiserie360\Domain\Finance\Models\MenuiserieInvoice;
use Modules\Menuiserie360\Domain\Production\Enums\StatutOrdreFabrication;
use Modules\Menuiserie360\Domain\Production\Models\OrdreFabrication;
use Modules\Menuiserie360\Domain\Sales\Enums\StatutBonCommande;
use Modules\Menuiserie360\Domain\Sales\Models\BonCommande;
use Modules\Menuiserie360\Domain\Sales\Models\BonCommandeItem;
use Modules\Menuiserie360\Domain\Stock\Enums\CategorieMatiere;
use Modules\Menuiserie360\Domain\Stock\Enums\UniteMesure;
use Modules\Menuiserie360\Domain\Stock\Models\MatierePremiere;
use Modules\Menuiserie360\Domain\Stock\Models\StockMatiere;

/**
 * M-UI-1 — Demo seeder Menuiserie360.
 *
 * Crée un scénario démo cohérent pour l'instance donnée :
 *   - 5 matières premières + leurs stocks initiaux
 *   - 3 customers Eshop360 (DEMO-MNU-CL-*) avec extensions menuiserie
 *   - 1 devis brouillon (2 lignes)
 *   - 1 devis accepté + son BC + facture acompte + OF planifié + chantier
 *     (4 étapes : préparation, fabrication, transport, pose)
 *
 * Idempotent via `updateOrCreate` / `firstOrCreate` sur les codes uniques.
 * Réversible via `reset()` : supprime toutes les rows DEMO-MNU-*.
 *
 * Invocable :
 *   - Directement : `php artisan tinker` puis `app(MenuiserieDemoSeeder::class)->run($instanceId)`
 *   - Via UI Demo : enregistré comme `DemoDataProvider` dans Menuiserie360HooksProvider
 *     (lot M-UI-1 — exposé sous /demo).
 */
final class MenuiserieDemoSeeder
{
    private const CUSTOMER_CODE_PREFIX = 'DEMO-MNU-CL-';

    private const MATIERE_CODE_PREFIX = 'DEMO-MNU-MAT-';

    private const DEVIS_NUMERO_PREFIX = 'DEMO-DEV-';

    private const BC_NUMERO_PREFIX = 'DEMO-BC-';

    private const INVOICE_NUMBER_PREFIX = 'DEMO-FAC-';

    private const OF_NUMERO_PREFIX = 'DEMO-OF-';

    private const CHANTIER_NUMERO_PREFIX = 'DEMO-CH-';

    public function run(int $instanceId): void
    {
        DB::transaction(function () use ($instanceId): void {
            $matieres = $this->seedMatieres($instanceId);
            $this->seedStocks($instanceId, $matieres);

            $customers = $this->seedCustomers($instanceId);
            $this->seedClientExtensions($instanceId, $customers);

            // Devis brouillon (libre)
            $this->seedDevisBrouillon($instanceId, $customers[0], $matieres);

            // Devis accepté → workflow complet (BC + facture acompte + OF + chantier)
            $this->seedDevisAccepteWorkflow($instanceId, $customers[1], $matieres);
        });
    }

    public function reset(int $instanceId): void
    {
        DB::transaction(function () use ($instanceId): void {
            // Supprimer en ordre inverse des dépendances FK.
            $bcIds = BonCommande::query()
                ->where('instance_id', $instanceId)
                ->where('numero', 'like', self::BC_NUMERO_PREFIX.'%')
                ->pluck('id');

            $chantierIds = Chantier::query()
                ->where('instance_id', $instanceId)
                ->where('numero', 'like', self::CHANTIER_NUMERO_PREFIX.'%')
                ->pluck('id');

            EtapeChantier::query()->whereIn('chantier_id', $chantierIds)->forceDelete();
            Chantier::query()->whereIn('id', $chantierIds)->forceDelete();

            OrdreFabrication::query()
                ->where('instance_id', $instanceId)
                ->where('numero', 'like', self::OF_NUMERO_PREFIX.'%')
                ->forceDelete();

            MenuiserieInvoice::query()
                ->where('instance_id', $instanceId)
                ->where('invoice_number', 'like', self::INVOICE_NUMBER_PREFIX.'%')
                ->forceDelete();

            BonCommandeItem::query()->whereIn('bc_id', $bcIds)->forceDelete();
            BonCommande::query()->whereIn('id', $bcIds)->forceDelete();

            $devisIds = Devis::query()
                ->where('instance_id', $instanceId)
                ->where('numero', 'like', self::DEVIS_NUMERO_PREFIX.'%')
                ->pluck('id');
            LigneDevis::query()->whereIn('devis_id', $devisIds)->forceDelete();
            Devis::query()->whereIn('id', $devisIds)->forceDelete();

            $customerIds = Customer::withoutGlobalScopes()
                ->where('instance_id', $instanceId)
                ->where('code', 'like', self::CUSTOMER_CODE_PREFIX.'%')
                ->pluck('id');
            ClientMenuiserie::query()->whereIn('customer_id', $customerIds)->forceDelete();

            // Stocks puis matières (FK matiere_id sur stocks).
            $matiereIds = MatierePremiere::query()
                ->where('instance_id', $instanceId)
                ->where('code', 'like', self::MATIERE_CODE_PREFIX.'%')
                ->pluck('id');
            StockMatiere::query()->whereIn('matiere_id', $matiereIds)->forceDelete();
            MatierePremiere::query()->whereIn('id', $matiereIds)->forceDelete();

            // Customers Eshop360 conservés par défaut (impact potentiel sur d'autres
            // modules). Décommenter si tu veux les nettoyer aussi.
            // Customer::withoutGlobalScopes()->whereIn('id', $customerIds)->forceDelete();
        });
    }

    /**
     * @return array<int, MatierePremiere> (réindexé 0..N)
     */
    private function seedMatieres(int $instanceId): array
    {
        $data = [
            ['code' => self::MATIERE_CODE_PREFIX.'001', 'designation' => 'Profil aluminium 40×60 mm', 'categorie' => CategorieMatiere::PROFILE_ALU->value, 'unite' => UniteMesure::METRE_LINEAIRE->value, 'prix_unitaire' => 2500, 'seuil_alerte' => 50],
            ['code' => self::MATIERE_CODE_PREFIX.'002', 'designation' => 'Verre clair 4 mm', 'categorie' => CategorieMatiere::VITRAGE->value, 'unite' => UniteMesure::METRE_CARRE->value, 'prix_unitaire' => 8000, 'seuil_alerte' => 10],
            ['code' => self::MATIERE_CODE_PREFIX.'003', 'designation' => 'Verre dépoli 4 mm', 'categorie' => CategorieMatiere::VITRAGE->value, 'unite' => UniteMesure::METRE_CARRE->value, 'prix_unitaire' => 12000, 'seuil_alerte' => 5],
            ['code' => self::MATIERE_CODE_PREFIX.'004', 'designation' => 'Visserie inox M6', 'categorie' => CategorieMatiere::ACCESSOIRE->value, 'unite' => UniteMesure::PIECE->value, 'prix_unitaire' => 100, 'seuil_alerte' => 200],
            ['code' => self::MATIERE_CODE_PREFIX.'005', 'designation' => 'Joint EPDM 8 mm (rouleau 50 m)', 'categorie' => CategorieMatiere::ACCESSOIRE->value, 'unite' => UniteMesure::METRE_LINEAIRE->value, 'prix_unitaire' => 800, 'seuil_alerte' => 100],
        ];

        return array_map(
            fn (array $d) => MatierePremiere::updateOrCreate(
                ['instance_id' => $instanceId, 'code' => $d['code']],
                [
                    ...$d,
                    'instance_id' => $instanceId,
                    'fournisseur_principal' => '[DEMO] Fournisseur Alu Côte d\'Ivoire',
                    'is_active' => true,
                ]
            ),
            $data
        );
    }

    /**
     * @param  array<int, MatierePremiere>  $matieres
     */
    private function seedStocks(int $instanceId, array $matieres): void
    {
        $quantitesInitiales = [120.0, 30.0, 20.0, 500.0, 100.0];

        foreach ($matieres as $i => $matiere) {
            StockMatiere::updateOrCreate(
                ['instance_id' => $instanceId, 'matiere_id' => $matiere->getKey()],
                [
                    'instance_id' => $instanceId,
                    'matiere_id' => $matiere->getKey(),
                    'quantite_actuelle' => $quantitesInitiales[$i],
                    'quantite_reservee' => 0,
                ]
            );
        }
    }

    /**
     * @return array<int, Customer> (réindexé 0..2)
     */
    private function seedCustomers(int $instanceId): array
    {
        $data = [
            ['code' => self::CUSTOMER_CODE_PREFIX.'001', 'name' => 'Résidence Les Palmiers', 'email' => 'gestion@palmiers-abj.ci', 'phone' => '+225 27 22 100 200', 'city' => 'Abidjan', 'country' => 'CI'],
            ['code' => self::CUSTOMER_CODE_PREFIX.'002', 'name' => 'Bureau Karim Coulibaly', 'email' => 'karim.c@example.ci', 'phone' => '+225 07 50 80 90 00', 'city' => 'Yamoussoukro', 'country' => 'CI'],
            ['code' => self::CUSTOMER_CODE_PREFIX.'003', 'name' => 'Hôtel Tropicana', 'email' => 'reception@tropicana.ci', 'phone' => '+225 27 21 60 30 30', 'city' => 'Grand-Bassam', 'country' => 'CI'],
        ];

        return array_map(
            fn (array $d) => Customer::withoutGlobalScopes()->updateOrCreate(
                ['instance_id' => $instanceId, 'code' => $d['code']],
                [
                    ...$d,
                    'instance_id' => $instanceId,
                    'is_active' => true,
                ]
            ),
            $data
        );
    }

    /**
     * @param  array<int, Customer>  $customers
     */
    private function seedClientExtensions(int $instanceId, array $customers): void
    {
        $extensions = [
            ['preferred_contact_method' => 'whatsapp', 'total_chantiers_count' => 0, 'total_revenue_xof' => 0],
            ['preferred_contact_method' => 'email', 'total_chantiers_count' => 1, 'total_revenue_xof' => 850000],
            ['preferred_contact_method' => 'phone', 'total_chantiers_count' => 3, 'total_revenue_xof' => 4200000],
        ];

        foreach ($customers as $i => $customer) {
            ClientMenuiserie::updateOrCreate(
                ['instance_id' => $instanceId, 'customer_id' => $customer->getKey()],
                [
                    'instance_id' => $instanceId,
                    'customer_id' => $customer->getKey(),
                    ...$extensions[$i],
                ]
            );
        }
    }

    /**
     * @param  array<int, MatierePremiere>  $matieres
     */
    private function seedDevisBrouillon(int $instanceId, Customer $customer, array $matieres): Devis
    {
        $devis = Devis::updateOrCreate(
            ['instance_id' => $instanceId, 'numero' => self::DEVIS_NUMERO_PREFIX.'2026-0001'],
            [
                'instance_id' => $instanceId,
                'numero' => self::DEVIS_NUMERO_PREFIX.'2026-0001',
                'client_id' => $customer->getKey(),
                'statut' => StatutDevis::BROUILLON->value,
                'taux_tva' => 0.18,
                'validite_jours' => 30,
                'marge_minimum' => 0.20,
                'montant_ht' => 0,
                'montant_tva' => 0,
                'montant_ttc' => 0,
            ]
        );

        LigneDevis::query()->where('devis_id', $devis->getKey())->forceDelete();

        $lignes = [
            ['designation' => 'Fenêtre coulissante 1500×1200 mm — verre clair', 'quantite' => 4, 'pu' => 220000, 'cout' => 140000, 'l' => 1500, 'h' => 1200, 'matiere' => $matieres[0]],
            ['designation' => 'Porte d\'entrée alu 900×2100 mm — verre dépoli', 'quantite' => 1, 'pu' => 480000, 'cout' => 320000, 'l' => 900, 'h' => 2100, 'matiere' => $matieres[2]],
        ];

        $totalHt = 0.0;
        foreach ($lignes as $i => $l) {
            $montant = $l['pu'] * $l['quantite'];
            LigneDevis::create([
                'instance_id' => $instanceId,
                'devis_id' => $devis->getKey(),
                'designation' => $l['designation'],
                'quantite' => $l['quantite'],
                'prix_unitaire_ht' => $l['pu'],
                'cout_revient' => $l['cout'],
                'largeur_mm' => $l['l'],
                'hauteur_mm' => $l['h'],
                'matiere_id' => $l['matiere']->getKey(),
                'montant_ht' => $montant,
                'ordre' => $i + 1,
            ]);
            $totalHt += $montant;
        }

        $tva = round($totalHt * 0.18, 2);
        $devis->fill([
            'montant_ht' => $totalHt,
            'montant_tva' => $tva,
            'montant_ttc' => round($totalHt + $tva, 2),
        ])->save();

        return $devis;
    }

    /**
     * @param  array<int, MatierePremiere>  $matieres
     */
    private function seedDevisAccepteWorkflow(int $instanceId, Customer $customer, array $matieres): void
    {
        $now = Carbon::now();

        // 1. Devis accepté
        $devis = Devis::updateOrCreate(
            ['instance_id' => $instanceId, 'numero' => self::DEVIS_NUMERO_PREFIX.'2026-0002'],
            [
                'instance_id' => $instanceId,
                'numero' => self::DEVIS_NUMERO_PREFIX.'2026-0002',
                'client_id' => $customer->getKey(),
                'statut' => StatutDevis::ACCEPTE->value,
                'taux_tva' => 0.18,
                'validite_jours' => 30,
                'marge_minimum' => 0.20,
            ]
        );

        LigneDevis::query()->where('devis_id', $devis->getKey())->forceDelete();

        $lignesData = [
            ['designation' => 'Vitrine alu 2000×1800 mm', 'quantite' => 1, 'pu' => 650000, 'cout' => 420000, 'l' => 2000, 'h' => 1800, 'matiere' => $matieres[0]],
            ['designation' => 'Fenêtre fixe 800×1200 mm', 'quantite' => 3, 'pu' => 180000, 'cout' => 120000, 'l' => 800, 'h' => 1200, 'matiere' => $matieres[1]],
        ];

        $totalHt = 0.0;
        $lignes = [];
        foreach ($lignesData as $i => $l) {
            $montant = $l['pu'] * $l['quantite'];
            $lignes[] = LigneDevis::create([
                'instance_id' => $instanceId,
                'devis_id' => $devis->getKey(),
                'designation' => $l['designation'],
                'quantite' => $l['quantite'],
                'prix_unitaire_ht' => $l['pu'],
                'cout_revient' => $l['cout'],
                'largeur_mm' => $l['l'],
                'hauteur_mm' => $l['h'],
                'matiere_id' => $l['matiere']->getKey(),
                'montant_ht' => $montant,
                'ordre' => $i + 1,
            ]);
            $totalHt += $montant;
        }

        $tva = round($totalHt * 0.18, 2);
        $ttc = round($totalHt + $tva, 2);
        $devis->fill([
            'montant_ht' => $totalHt,
            'montant_tva' => $tva,
            'montant_ttc' => $ttc,
        ])->save();

        // 2. Bon de commande dérivé
        $acomptePct = 30.0;
        $bc = BonCommande::updateOrCreate(
            ['instance_id' => $instanceId, 'numero' => self::BC_NUMERO_PREFIX.'2026-0001'],
            [
                'instance_id' => $instanceId,
                'numero' => self::BC_NUMERO_PREFIX.'2026-0001',
                'devis_id' => $devis->getKey(),
                'client_id' => $customer->getKey(),
                'statut' => StatutBonCommande::CREE->value,
                'taux_tva' => 0.18,
                'montant_ht' => $totalHt,
                'montant_tva' => $tva,
                'montant_ttc' => $ttc,
                'acompte_pct' => $acomptePct,
                'date_livraison_prevue' => $now->copy()->addWeeks(6)->toDateString(),
            ]
        );

        BonCommandeItem::query()->where('bc_id', $bc->getKey())->forceDelete();
        foreach ($lignes as $i => $ligne) {
            BonCommandeItem::create([
                'instance_id' => $instanceId,
                'bc_id' => $bc->getKey(),
                'ligne_devis_id' => $ligne->getKey(),
                'designation' => $ligne->getAttribute('designation'),
                'quantite' => $ligne->getAttribute('quantite'),
                'prix_unitaire_ht' => $ligne->getAttribute('prix_unitaire_ht'),
                'largeur_mm' => $ligne->getAttribute('largeur_mm'),
                'hauteur_mm' => $ligne->getAttribute('hauteur_mm'),
                'matiere_id' => $ligne->getAttribute('matiere_id'),
                'montant_ht' => $ligne->getAttribute('montant_ht'),
                'ordre' => $i + 1,
            ]);
        }

        // 3. Facture acompte
        $acompteTtc = round($ttc * $acomptePct / 100, 2);
        $acompteHt = round($acompteTtc / 1.18, 2);
        $acompteTva = round($acompteTtc - $acompteHt, 2);
        $facture = MenuiserieInvoice::updateOrCreate(
            ['instance_id' => $instanceId, 'invoice_number' => self::INVOICE_NUMBER_PREFIX.'2026-A-0001'],
            [
                'instance_id' => $instanceId,
                'invoice_number' => self::INVOICE_NUMBER_PREFIX.'2026-A-0001',
                'client_id' => $customer->getKey(),
                'bc_id' => $bc->getKey(),
                'type' => TypeFacture::ACOMPTE->value,
                'amount_ht' => $acompteHt,
                'tax_rate' => 0.18,
                'amount_tva' => $acompteTva,
                'amount_ttc' => $acompteTtc,
                'paid_amount' => 0,
                'status' => StatutFacture::ISSUED->value,
                'issued_at' => $now,
            ]
        );

        $bc->fill(['facture_acompte_id' => $facture->getKey()])->save();

        // 4. Ordre de fabrication planifié
        OrdreFabrication::updateOrCreate(
            ['instance_id' => $instanceId, 'numero' => self::OF_NUMERO_PREFIX.'2026-0001'],
            [
                'instance_id' => $instanceId,
                'numero' => self::OF_NUMERO_PREFIX.'2026-0001',
                'bc_id' => $bc->getKey(),
                'statut' => StatutOrdreFabrication::EN_ATTENTE->value,
                'date_planifiee' => $now->copy()->addWeeks(2)->toDateString(),
            ]
        );

        // 5. Chantier dérivé + étapes
        $chantier = Chantier::updateOrCreate(
            ['instance_id' => $instanceId, 'numero' => self::CHANTIER_NUMERO_PREFIX.'2026-0001'],
            [
                'instance_id' => $instanceId,
                'numero' => self::CHANTIER_NUMERO_PREFIX.'2026-0001',
                'bc_id' => $bc->getKey(),
                'client_id' => $customer->getKey(),
                'statut' => StatutChantier::EN_ATTENTE->value,
                'adresse_pose' => 'Quartier Cocody, rue des Jardins, Abidjan',
                'contact_chantier' => '+225 07 11 22 33 44',
                'date_debut_prevue' => $now->copy()->addWeeks(4)->toDateString(),
                'date_fin_prevue' => $now->copy()->addWeeks(6)->toDateString(),
            ]
        );

        EtapeChantier::query()->where('chantier_id', $chantier->getKey())->forceDelete();
        $etapes = [
            ['ordre' => 1, 'nom' => 'Préparation atelier', 'avancement_pct' => 0, 'statut' => 'a_faire'],
            ['ordre' => 2, 'nom' => 'Fabrication menuiseries', 'avancement_pct' => 0, 'statut' => 'a_faire'],
            ['ordre' => 3, 'nom' => 'Transport sur site', 'avancement_pct' => 0, 'statut' => 'a_faire'],
            ['ordre' => 4, 'nom' => 'Pose et finitions', 'avancement_pct' => 0, 'statut' => 'a_faire'],
        ];
        foreach ($etapes as $e) {
            EtapeChantier::create([
                'instance_id' => $instanceId,
                'chantier_id' => $chantier->getKey(),
                ...$e,
            ]);
        }
    }
}
