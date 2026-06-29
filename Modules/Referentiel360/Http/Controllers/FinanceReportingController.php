<?php

declare(strict_types=1);

namespace Modules\Referentiel360\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\Core\Support\CurrentInstance;
use Modules\Referentiel360\Contracts\Finance\FinanceReader;

/**
 * Reporting 360° — tableau de bord LECTURE SEULE du registre financier miroir
 * (ADR-031 / Lot 3). Agrège le CA / encaissement / reste-dû consolidé d'une
 * instance, PAR DEVISE et PAR TIERS (golden record `ref_parties`).
 *
 * Source unique : tables `ref_*`. Aucune lecture des tables métier `mnu_*` /
 * `eshop_*` (les modules L3 POUSSENT déjà leur état financier vers le miroir).
 *
 * Toutes les requêtes filtrent explicitement `instance_id` et bypassent le
 * global scope (cohérent avec EloquentFinanceReader) pour rester déterministes.
 */
final class FinanceReportingController extends Controller
{
    public function index(FinanceReader $finance): View
    {
        $instance = CurrentInstance::get();
        abort_if($instance === null, 503, 'No instance context.');

        abort_unless(
            Auth::user()?->can('referentiel.finance.view') ?? false,
            403,
        );

        $instanceId = (int) $instance->id;

        // 1. Totaux PAR DEVISE (avoirs soustraits, annulés exclus) — bcmath échelle 2.
        $totalsByCurrency = $finance->totalsForInstance($instanceId);

        // 2. CA PAR TIERS × DEVISE : on agrège le registre miroir (non annulé)
        //    groupé par party_id + currency, joint à ref_parties pour le nom.
        //    Les sommes SQL sur colonnes decimal(14,2) restent exactes (pas de
        //    float intermédiaire) ; on les normalise ensuite en string 2 décimales.
        //    Query Builder brut (stdClass) : ce sont des lignes agrégées, pas des
        //    modèles Eloquent. Scope `instance_id` explicite (table miroir).
        $byParty = DB::table('ref_documents_finance as f')
            ->leftJoin('ref_parties as p', 'p.id', '=', 'f.party_id')
            ->where('f.instance_id', $instanceId)
            ->where('f.is_cancelled', false)
            ->groupBy('f.party_id', 'f.currency', 'p.display_name', 'p.is_customer', 'p.is_supplier')
            ->orderByRaw('p.display_name IS NULL')      // tiers nommés d'abord, "(non rattaché)" en bas
            ->orderBy('p.display_name')
            ->orderBy('f.currency')
            ->selectRaw('f.party_id as party_id')
            ->selectRaw('f.currency as currency')
            ->selectRaw('p.display_name as display_name')
            ->selectRaw('p.is_customer as is_customer')
            ->selectRaw('p.is_supplier as is_supplier')
            ->selectRaw('SUM(f.amount_ttc) as sum_ttc')
            ->selectRaw('SUM(f.paid_amount) as sum_paid')
            ->selectRaw('SUM(f.due_amount) as sum_due')
            ->selectRaw('COUNT(*) as nb_documents')
            ->get()
            ->map(static fn (object $row): array => [
                'party_id' => $row->party_id !== null ? (int) $row->party_id : null,
                'display_name' => $row->display_name !== null
                    ? (string) $row->display_name
                    : '(non rattaché)',
                'is_customer' => (bool) $row->is_customer,
                'is_supplier' => (bool) $row->is_supplier,
                'currency' => (string) ($row->currency !== '' ? $row->currency : 'XOF'),
                'ttc' => number_format((float) $row->sum_ttc, 2, '.', ''),
                'paid' => number_format((float) $row->sum_paid, 2, '.', ''),
                'due' => number_format((float) $row->sum_due, 2, '.', ''),
                'nb_documents' => (int) $row->nb_documents,
            ])
            ->values()
            ->all();

        // 3. Répartition par module source (compteurs + nb documents).
        $byModule = DB::table('ref_documents_finance')
            ->where('instance_id', $instanceId)
            ->where('is_cancelled', false)
            ->groupBy('source_module')
            ->orderBy('source_module')
            ->selectRaw('source_module as source_module')
            ->selectRaw('COUNT(*) as nb_documents')
            ->get()
            ->map(static fn (object $row): array => [
                'source_module' => (string) ($row->source_module !== '' ? $row->source_module : '—'),
                'nb_documents' => (int) $row->nb_documents,
            ])
            ->all();

        // 4. Répartition par statut normalisé (compteurs).
        $byStatus = DB::table('ref_documents_finance')
            ->where('instance_id', $instanceId)
            ->where('is_cancelled', false)
            ->groupBy('status_normalized')
            ->orderBy('status_normalized')
            ->selectRaw('status_normalized as status_normalized')
            ->selectRaw('COUNT(*) as nb_documents')
            ->get()
            ->map(static fn (object $row): array => [
                'status_normalized' => (string) ($row->status_normalized !== '' ? $row->status_normalized : '—'),
                'nb_documents' => (int) $row->nb_documents,
            ])
            ->all();

        return view('referentiel360::finance.reporting', [
            'totalsByCurrency' => $totalsByCurrency,
            'byParty' => $byParty,
            'byModule' => $byModule,
            'byStatus' => $byStatus,
        ]);
    }
}
