<?php

declare(strict_types=1);

namespace Modules\Menuiserie360\Http\Controllers\Reporting;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Core\Support\CurrentInstance;
use Modules\Menuiserie360\Domain\Chantier\Models\Chantier;
use Modules\Menuiserie360\Domain\Commercial\Models\Devis;
use Modules\Menuiserie360\Domain\Finance\Models\MenuiserieInvoice;
use Modules\Menuiserie360\Domain\Production\Models\OrdreFabrication;

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
}
