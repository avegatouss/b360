<?php

declare(strict_types=1);

namespace Modules\Menuiserie360\Http\Controllers\Reporting;

use Carbon\CarbonImmutable;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\Core\Support\CurrentInstance;
use Modules\Menuiserie360\Domain\Chantier\Models\Chantier;
use Modules\Menuiserie360\Domain\Commercial\Models\Devis;
use Modules\Menuiserie360\Domain\Finance\Models\MenuiserieInvoice;
use Modules\Menuiserie360\Domain\Finance\Models\MenuiseriePayment;
use Modules\Menuiserie360\Domain\Production\Models\OrdreFabrication;
use Modules\Menuiserie360\Domain\Reporting\Services\ExportComptableService;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * P2 — Dashboard menuiserie (KPIs direction simples MVP).
 */
final class DashboardMenuiserieController extends Controller
{
    public function index(Request $request): View
    {
        $instance = CurrentInstance::get();
        abort_if($instance === null, 503, 'No instance context.');

        $kpis = [
            'devis_brouillons' => Devis::query()->where('instance_id', $instance->id)->where('statut', 'brouillon')->count(),
            'devis_acceptes_30j' => Devis::query()->where('instance_id', $instance->id)->where('statut', 'accepte')->where('updated_at', '>=', now()->subDays(30))->count(),
            'of_en_cours' => OrdreFabrication::query()->where('instance_id', $instance->id)->where('statut', 'en_cours')->count(),
            'chantiers_en_cours' => Chantier::query()->where('instance_id', $instance->id)->where('statut', 'en_cours')->count(),
            'ca_mois' => (float) MenuiserieInvoice::query()->where('instance_id', $instance->id)->where('issued_at', '>=', now()->startOfMonth())->sum('amount_ttc'),
            'creances' => (float) MenuiserieInvoice::query()
                ->where('instance_id', $instance->id)
                ->whereIn('status', ['issued', 'paid_partial'])
                ->selectRaw('SUM(amount_ttc - paid_amount) as creances_total')
                ->value('creances_total'),
            'encaisse_mois' => (float) MenuiseriePayment::query()
                ->where('instance_id', $instance->id)
                ->where('status', 'succeeded')
                ->where('paid_at', '>=', now()->startOfMonth())
                ->sum('amount'),
            'taux_conversion_devis_90j' => $this->tauxConversionDevis90j($instance->id),
        ];

        $caMensuel12m = $this->caMensuel12m($instance->id);
        $topClients = $this->topClientsParCa($instance->id, limit: 5);
        $mixPaiements = $this->mixPaiementsMois($instance->id);

        return view('menuiserie360::reporting.dashboard', compact('kpis', 'caMensuel12m', 'topClients', 'mixPaiements'));
    }

    /**
     * @return array<int, array{mois: string, ttc: float}>
     */
    private function caMensuel12m(int $instanceId): array
    {
        $rows = [];
        for ($offset = 11; $offset >= 0; $offset--) {
            $start = now()->subMonths($offset)->startOfMonth();
            $end = now()->subMonths($offset)->endOfMonth();
            $sum = (float) MenuiserieInvoice::query()
                ->where('instance_id', $instanceId)
                ->whereBetween('issued_at', [$start, $end])
                ->sum('amount_ttc');
            $rows[] = ['mois' => $start->format('Y-m'), 'ttc' => $sum];
        }

        return $rows;
    }

    /**
     * @return array<int, array{client_id: int|string, ttc: float, count: int}>
     */
    private function topClientsParCa(int $instanceId, int $limit): array
    {
        return MenuiserieInvoice::query()
            ->where('instance_id', $instanceId)
            ->where('issued_at', '>=', now()->subDays(180))
            ->selectRaw('client_id, SUM(amount_ttc) as ttc, COUNT(*) as count')
            ->groupBy('client_id')
            ->orderByDesc('ttc')
            ->limit($limit)
            ->get()
            ->map(fn ($r): array => [
                'client_id' => $r->getAttribute('client_id'),
                'ttc' => (float) $r->getAttribute('ttc'),
                'count' => (int) $r->getAttribute('count'),
            ])
            ->all();
    }

    /**
     * @return array<int, array{method: string, total: float, share: float}>
     */
    private function mixPaiementsMois(int $instanceId): array
    {
        $rows = MenuiseriePayment::query()
            ->where('instance_id', $instanceId)
            ->where('status', 'succeeded')
            ->where('paid_at', '>=', now()->startOfMonth())
            ->selectRaw('method, SUM(amount) as total')
            ->groupBy('method')
            ->get();

        $totalMois = (float) $rows->sum('total');

        return $rows->map(fn ($r): array => [
            'method' => (string) $r->getAttribute('method'),
            'total' => (float) $r->getAttribute('total'),
            'share' => $totalMois > 0 ? round((float) $r->getAttribute('total') / $totalMois * 100, 1) : 0.0,
        ])->all();
    }

    private function tauxConversionDevis90j(int $instanceId): float
    {
        $base = Devis::query()
            ->where('instance_id', $instanceId)
            ->where('updated_at', '>=', now()->subDays(90));

        $total = (int) (clone $base)->count();
        if ($total === 0) {
            return 0.0;
        }

        $accepted = (int) (clone $base)->where('statut', 'accepte')->count();

        return round($accepted / $total * 100, 1);
    }

    /**
     * P3-6 — Dashboard opérationnel (atelier + chantier + stock).
     */
    public function operations(): View
    {
        $instance = CurrentInstance::get();
        abort_if($instance === null, 503, 'No instance context.');

        $ofsEnAttente = OrdreFabrication::query()
            ->where('instance_id', $instance->id)
            ->where('statut', 'en_attente')
            ->orderBy('created_at')
            ->limit(20)
            ->get();

        $ofsEnCours = OrdreFabrication::query()
            ->where('instance_id', $instance->id)
            ->where('statut', 'en_cours')
            ->orderBy('date_demarrage')
            ->limit(20)
            ->get();

        $chantiersEnRetard = Chantier::query()
            ->where('instance_id', $instance->id)
            ->whereNotIn('statut', ['termine', 'livre', 'annule'])
            ->whereNotNull('date_fin_prevue')
            ->where('date_fin_prevue', '<', now()->toDateString())
            ->orderBy('date_fin_prevue')
            ->limit(20)
            ->get();

        $stocksBas = DB::table('mnu_stocks_matieres')
            ->join('mnu_matieres_premieres as mp', 'mp.id', '=', 'mnu_stocks_matieres.matiere_id')
            ->where('mnu_stocks_matieres.instance_id', $instance->id)
            ->whereColumn('mnu_stocks_matieres.quantite_actuelle', '<=', 'mp.seuil_alerte')
            ->select('mnu_stocks_matieres.id', 'mnu_stocks_matieres.quantite_actuelle', 'mp.code as matiere_code', 'mp.designation as matiere_designation', 'mp.seuil_alerte', 'mp.unite')
            ->orderBy('mnu_stocks_matieres.quantite_actuelle')
            ->limit(20)
            ->get();

        return view('menuiserie360::reporting.operations', compact(
            'ofsEnAttente',
            'ofsEnCours',
            'chantiersEnRetard',
            'stocksBas',
        ));
    }

    /**
     * P3-4 — Journal des ventes (factures) + paiements pour la période.
     */
    public function journal(Request $request): View
    {
        $instance = CurrentInstance::get();
        abort_if($instance === null, 503, 'No instance context.');

        $data = $request->validate([
            'from' => 'nullable|date',
            'to' => 'nullable|date|after_or_equal:from',
        ]);

        $from = isset($data['from'])
            ? CarbonImmutable::parse($data['from'])->startOfDay()
            : CarbonImmutable::now()->startOfMonth();
        $to = isset($data['to'])
            ? CarbonImmutable::parse($data['to'])->endOfDay()
            : CarbonImmutable::now()->endOfMonth();

        $invoices = MenuiserieInvoice::query()
            ->where('instance_id', $instance->id)
            ->whereBetween('issued_at', [$from, $to])
            ->orderByDesc('issued_at')
            ->orderBy('id')
            ->paginate(50)
            ->withQueryString();

        $payments = MenuiseriePayment::query()
            ->where('instance_id', $instance->id)
            ->whereBetween('paid_at', [$from, $to])
            ->orderByDesc('paid_at')
            ->orderBy('id')
            ->paginate(50, ['*'], 'pay_page')
            ->withQueryString();

        $totals = [
            'ventes_ttc' => (float) MenuiserieInvoice::query()
                ->where('instance_id', $instance->id)
                ->whereBetween('issued_at', [$from, $to])
                ->sum('amount_ttc'),
            'encaisse' => (float) MenuiseriePayment::query()
                ->where('instance_id', $instance->id)
                ->whereBetween('paid_at', [$from, $to])
                ->where('status', 'succeeded')
                ->sum('amount'),
            'impaye' => (float) MenuiserieInvoice::query()
                ->where('instance_id', $instance->id)
                ->whereBetween('issued_at', [$from, $to])
                ->selectRaw('SUM(amount_ttc - paid_amount) as impaye')
                ->value('impaye'),
        ];

        return view('menuiserie360::reporting.journal', compact('invoices', 'payments', 'totals', 'from', 'to'));
    }

    /**
     * P3-3 — Export comptable CSV des factures sur la période demandée.
     * Format URL : ?from=YYYY-MM-DD&to=YYYY-MM-DD (défaut = mois courant).
     */
    public function exportComptable(Request $request, ExportComptableService $service): StreamedResponse
    {
        $instance = CurrentInstance::get();
        abort_if($instance === null, 503, 'No instance context.');

        $data = $request->validate([
            'from' => 'nullable|date',
            'to' => 'nullable|date|after_or_equal:from',
        ]);

        $from = isset($data['from'])
            ? CarbonImmutable::parse($data['from'])
            : CarbonImmutable::now()->startOfMonth();
        $to = isset($data['to'])
            ? CarbonImmutable::parse($data['to'])
            : CarbonImmutable::now()->endOfMonth();

        return $service->streamCsv($instance->id, $from, $to);
    }
}
