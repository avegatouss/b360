<?php

declare(strict_types=1);

namespace Modules\Menuiserie360\Http\Controllers\Alertes;

use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Modules\Core\Support\CurrentInstance;
use Modules\Menuiserie360\Domain\Chantier\Enums\StatutChantier;
use Modules\Menuiserie360\Domain\Chantier\Models\Chantier;
use Modules\Menuiserie360\Domain\Commercial\Enums\StatutDevis;
use Modules\Menuiserie360\Domain\Commercial\Models\Devis;
use Modules\Menuiserie360\Domain\Finance\Enums\StatutFacture;
use Modules\Menuiserie360\Domain\Finance\Models\MenuiserieInvoice;

/**
 * M-UI-8 — Page d'alertes opérationnelles Menuiserie360.
 *
 * Agrège en temps réel les signaux faibles du module :
 *  - Matières en stock critique (quantité disponible < seuil d'alerte).
 *  - Devis expirés (created_at + validite_jours < now ET statut brouillon/soumis).
 *  - Chantiers en retard (date_fin_prevue < now ET statut non terminal).
 *  - Factures impayées avec issued_at > 30j.
 *
 * Pas de table dédiée : les alertes sont calculées à la volée à chaque
 * affichage. C'est cohérent avec le pattern Dashboard Operations (P3-6).
 */
final class AlerteController extends Controller
{
    public function index(): View
    {
        $instance = CurrentInstance::get();
        abort_if($instance === null, 503, 'No instance context.');

        $now = Carbon::now();

        // 1. Stocks bas via JOIN matière premiere ↔ stock (cf. P3-6 pattern).
        $stocksBas = DB::table('mnu_matieres_premieres as m')
            ->leftJoin('mnu_stocks_matieres as s', 's.matiere_id', '=', 'm.id')
            ->where('m.instance_id', $instance->id)
            ->where('m.is_active', true)
            ->whereRaw('COALESCE(s.quantite_actuelle, 0) - COALESCE(s.quantite_reservee, 0) < m.seuil_alerte')
            ->orderBy('m.designation')
            ->select([
                'm.id', 'm.code', 'm.designation', 'm.unite', 'm.seuil_alerte',
                's.quantite_actuelle', 's.quantite_reservee',
            ])
            ->limit(50)
            ->get();

        // 2. Devis expirés (validite dépassée sans acceptation).
        // Calcul en PHP plutôt que via DATE_ADD pour rester cross-DB (MySQL + SQLite tests).
        $devisExpires = Devis::query()
            ->where('instance_id', $instance->id)
            ->whereIn('statut', [StatutDevis::BROUILLON->value, StatutDevis::SOUMIS->value, StatutDevis::VALIDE->value])
            ->orderBy('created_at')
            ->limit(200)
            ->get(['id', 'numero', 'client_id', 'statut', 'validite_jours', 'montant_ttc', 'created_at'])
            ->filter(fn ($d) => Carbon::parse($d->getAttribute('created_at'))->addDays((int) $d->getAttribute('validite_jours'))->isPast())
            ->take(50)
            ->values();

        // 3. Chantiers en retard.
        $chantiersEnRetard = Chantier::query()
            ->where('instance_id', $instance->id)
            ->whereNotNull('date_fin_prevue')
            ->where('date_fin_prevue', '<', $now->toDateString())
            ->whereNotIn('statut', [
                StatutChantier::TERMINE->value,
                StatutChantier::LIVRE->value,
                StatutChantier::ANNULE->value,
            ])
            ->orderBy('date_fin_prevue')
            ->limit(50)
            ->get(['id', 'numero', 'client_id', 'statut', 'date_fin_prevue']);

        // 4. Factures impayées en retard (> 30j depuis émission).
        $facturesImpayees = MenuiserieInvoice::query()
            ->where('instance_id', $instance->id)
            ->whereIn('status', [StatutFacture::ISSUED->value, StatutFacture::PAID_PARTIAL->value])
            ->where('issued_at', '<', $now->copy()->subDays(30))
            ->orderBy('issued_at')
            ->limit(50)
            ->get(['id', 'invoice_number', 'client_id', 'status', 'amount_ttc', 'paid_amount', 'issued_at']);

        return view('menuiserie360::alertes.index', [
            'stocksBas' => $stocksBas,
            'devisExpires' => $devisExpires,
            'chantiersEnRetard' => $chantiersEnRetard,
            'facturesImpayees' => $facturesImpayees,
            'totalAlertes' => $stocksBas->count() + $devisExpires->count() + $chantiersEnRetard->count() + $facturesImpayees->count(),
        ]);
    }
}
