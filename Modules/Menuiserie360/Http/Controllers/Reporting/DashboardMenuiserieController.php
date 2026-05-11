<?php

declare(strict_types=1);

namespace Modules\Menuiserie360\Http\Controllers\Reporting;

use Carbon\CarbonImmutable;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
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
        ];

        return view('menuiserie360::reporting.dashboard', compact('kpis'));
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
