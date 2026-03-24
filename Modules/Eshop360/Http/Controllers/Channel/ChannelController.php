<?php

namespace Modules\Eshop360\Http\Controllers\Channel;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Eshop360\Models\DistributionChannel;
use Modules\Eshop360\Models\ChannelMarginLog;
use Modules\Eshop360\Models\Order;
use Modules\Eshop360\Services\MarginService;
use Modules\Eshop360\Services\CostCalculatorService;
use Modules\Eshop360\Services\ChannelB2BService;
use Modules\Eshop360\Services\EshopSettingsService;
use Modules\Core\Support\CurrentInstance;

class ChannelController extends Controller
{
    public function __construct(
        private MarginService $marginService,
        private CostCalculatorService $costCalculator,
        private ChannelB2BService $channelB2BService,
        private EshopSettingsService $settingsService,
    ) {}

    /**
     * List all distribution channels.
     */
    public function index()
    {
        $instance = CurrentInstance::get();
        $channels = DistributionChannel::where('instance_id', $instance->id)
            ->withCount('orders', 'marginLogs')
            ->get();
        return view('eshop360::channels.index', compact('channels'));
    }

    /**
     * Show channel dashboard with margin summary.
     */
    public function show(string $slug, DistributionChannel $channel)
    {
        $summary = $this->marginService->getMarginSummary(
            $channel->instance_id,
            now()->startOfMonth()->toDateTimeString(),
            now()->toDateTimeString(),
            $channel->id
        );
        $recentLogs = ChannelMarginLog::where('channel_id', $channel->id)
            ->with('order.customer')
            ->latest()
            ->limit(20)
            ->get();
        return view('eshop360::channels.show', compact('channel', 'summary', 'recentLogs'));
    }

    /**
     * Create a new distribution channel.
     */
    public function create()
    {
        return view('eshop360::channels.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:100|regex:/^[a-z0-9\-]+$/',
            'code' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'margin_rate' => 'required|numeric|min:0|max:1',
            'buy_rate' => 'required|numeric|min:0|max:1',
            'debt_share' => 'required|numeric|min:0|max:1',
            'channel_share' => 'required|numeric|min:0|max:1',
            'owner_share' => 'required|numeric|min:0|max:1',
        ]);

        $instance = CurrentInstance::get();
        $validated['instance_id'] = $instance->id;

        $channel = DistributionChannel::create($validated);
        $this->channelB2BService->syncHubCustomer($channel);

        return redirect()->route('eshop360.channels.index', $instance->slug)
            ->with('success', __('Canal de distribution créé.'));
    }

    /**
     * Edit channel configuration.
     */
    public function edit(string $slug, DistributionChannel $channel)
    {
        $featureSettings = $this->settingsService->getForChannel('features', $channel->id);
        $featureDefinitions = $this->featureDefinitions();

        return view('eshop360::channels.edit', compact('channel', 'featureSettings', 'featureDefinitions'));
    }

    public function update(Request $request, string $slug, DistributionChannel $channel)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'margin_rate' => 'required|numeric|min:0|max:1',
            'buy_rate' => 'required|numeric|min:0|max:1',
            'debt_share' => 'required|numeric|min:0|max:1',
            'channel_share' => 'required|numeric|min:0|max:1',
            'owner_share' => 'required|numeric|min:0|max:1',
        ]);

        $channel->update($validated);
        $this->channelB2BService->syncHubCustomer($channel);
        $features = [];

        foreach (array_keys($this->settingsService->defaults('features')) as $feature) {
            $features[$feature] = $request->boolean("features.{$feature}");
        }

        $this->settingsService->setChannelFeatures($features, $channel->id);

        return redirect()->back()->with('success', __('Configuration mise à jour.'));
    }

    /**
     * View channel margins with date filtering.
     */
    public function margins(Request $request, string $slug, DistributionChannel $channel)
    {
        $from = $request->get('from', now()->startOfMonth()->toDateTimeString());
        $to = $request->get('to', now()->toDateTimeString());
        $summary = $this->marginService->getMarginSummary($channel->instance_id, $from, $to, $channel->id);
        $logs = ChannelMarginLog::where('channel_id', $channel->id)
            ->whereBetween('created_at', [$from, $to])
            ->with('order')
            ->latest()
            ->paginate(20);
        return view('eshop360::channels.margins', compact('channel', 'summary', 'logs', 'from', 'to'));
    }

    /**
     * View orders assigned to this channel.
     */
    public function orders(string $slug, DistributionChannel $channel)
    {
        $orders = Order::where('instance_id', $channel->instance_id)
            ->where('channel_id', $channel->id)
            ->with('customer', 'items')
            ->latest()
            ->paginate(20);
        return view('eshop360::channels.orders', compact('channel', 'orders'));
    }

    public function destroy(string $slug, DistributionChannel $channel)
    {
        $instance = CurrentInstance::get();
        $channel->delete();
        return redirect()->route('eshop360.channels.index', $instance->slug)
            ->with('success', __('Canal supprimé.'));
    }

    /**
     * @return array<string, string>
     */
    private function featureDefinitions(): array
    {
        return [
            'portal' => 'Portail channel',
            'shop' => 'Espace client du channel',
            'orders' => 'Commandes B2B du channel',
            'online_orders' => 'Commandes clients en ligne',
            'stock' => 'Consultation du stock',
            'stock_adjustments' => 'Ajustements de stock',
            'sales' => 'Ventes du channel',
            'customers' => 'Clients du channel',
            'pos' => 'POS du channel',
            'promotions' => 'Promotions et coupons',
            'settings' => 'Parametres du channel',
            'reports' => 'Rapports du channel',
            'margins' => 'Marge et rentabilite',
            'finance' => 'Finance',
            'hr' => 'Ressources humaines',
            'support' => 'Support',
        ];
    }
}
