{{-- Widget: Customer Personal Stats --}}
@php
    $user = auth()->user();
    $instance = \Modules\Core\Support\CurrentInstance::get();
    $customer = \Modules\Eshop360\Models\Customer::where('instance_id', $instance?->id)
        ->where(fn($q) => $q->where('user_id', $user->id)->orWhere('email', $user->email))
        ->first();

    if (!$customer) return;

    $slug = $instance->slug ?? '';
    $instanceId = $instance->id;

    // Stats from regular orders
    $orderStats = \Illuminate\Support\Facades\DB::table('eshop_orders')
        ->where('instance_id', $instanceId)
        ->where('customer_id', $customer->id)
        ->where('status', 'completed')
        ->selectRaw('
            COUNT(*) as total_orders,
            COALESCE(SUM(total), 0) as total_spent,
            COALESCE(SUM(paid_amount), 0) as total_paid,
            COALESCE(SUM(due_amount), 0) as total_due,
            COALESCE(AVG(total), 0) as avg_order
        ')
        ->first();

    // Online orders stats
    $onlineStats = \Illuminate\Support\Facades\DB::table('eshop_online_orders')
        ->where('instance_id', $instanceId)
        ->where('customer_id', $customer->id)
        ->selectRaw('
            COUNT(*) as total,
            SUM(CASE WHEN status IN ("pending_validation","validated","preparing") THEN 1 ELSE 0 END) as en_cours,
            SUM(CASE WHEN status IN ("shipping","delivered") THEN 1 ELSE 0 END) as livraison,
            SUM(CASE WHEN status = "received" THEN 1 ELSE 0 END) as recues,
            SUM(CASE WHEN status = "cancelled" THEN 1 ELSE 0 END) as annulees
        ')
        ->first();

    // Monthly purchases (last 6 months)
    $monthlyPurchases = \Illuminate\Support\Facades\DB::table('eshop_orders')
        ->where('instance_id', $instanceId)
        ->where('customer_id', $customer->id)
        ->where('status', 'completed')
        ->where('created_at', '>=', now()->subMonths(6)->startOfMonth())
        ->selectRaw('YEAR(created_at) as year, MONTH(created_at) as month, SUM(total) as total, COUNT(*) as orders')
        ->groupByRaw('YEAR(created_at), MONTH(created_at)')
        ->orderByRaw('YEAR(created_at), MONTH(created_at)')
        ->get();

    // Top products bought
    $topProducts = \Illuminate\Support\Facades\DB::table('eshop_order_items')
        ->join('eshop_orders', 'eshop_order_items.order_id', '=', 'eshop_orders.id')
        ->where('eshop_orders.instance_id', $instanceId)
        ->where('eshop_orders.customer_id', $customer->id)
        ->where('eshop_orders.status', 'completed')
        ->selectRaw('COALESCE(eshop_order_items.product_name, "Produit") as name, SUM(eshop_order_items.quantity) as qty, SUM(eshop_order_items.total) as total')
        ->groupBy('eshop_order_items.product_id', 'eshop_order_items.product_name')
        ->orderByDesc('total')
        ->limit(5)
        ->get();

    $fmt = fn($n) => number_format((float)$n, 0, ',', ' ');
@endphp
<div class="card border-0 shadow-sm h-100">
    <div class="card-header bg-transparent">
        <h6 class="mb-0 fw-bold"><i class="ti ti-chart-bar me-2 text-success"></i>{{ __('Mes statistiques') }}</h6>
    </div>
    <div class="card-body">
        {{-- KPI row --}}
        <div class="row g-2 mb-3">
            <div class="col-6">
                <div class="text-center p-2 bg-primary bg-opacity-10 rounded">
                    <div class="text-muted">{{ __('Total depense') }}</div>
                    <div class="fs-4 fw-bold text-primary">{{ $fmt($orderStats->total_spent) }}</div>
                </div>
            </div>
            <div class="col-6">
                <div class="text-center p-2 bg-success bg-opacity-10 rounded">
                    <div class="text-muted">{{ __('Commandes') }}</div>
                    <div class="fs-4 fw-bold text-success">{{ $orderStats->total_orders }}</div>
                </div>
            </div>
            <div class="col-6">
                <div class="text-center p-2 {{ $orderStats->total_due > 0 ? 'bg-danger' : 'bg-secondary' }} bg-opacity-10 rounded">
                    <div class="text-muted">{{ __('Solde du') }}</div>
                    <div class="fs-4 fw-bold {{ $orderStats->total_due > 0 ? 'text-danger' : 'text-muted' }}">{{ $fmt($orderStats->total_due) }}</div>
                </div>
            </div>
            <div class="col-6">
                <div class="text-center p-2 bg-info bg-opacity-10 rounded">
                    <div class="text-muted">{{ __('Panier moyen') }}</div>
                    <div class="fs-4 fw-bold text-info">{{ $fmt($orderStats->avg_order) }}</div>
                </div>
            </div>
        </div>

        {{-- Online orders status --}}
        @if($onlineStats && $onlineStats->total > 0)
        <h6 class="fw-bold mb-2">{{ __('Commandes en ligne') }}</h6>
        <div class="d-flex gap-2 flex-wrap mb-3">
            @if($onlineStats->en_cours > 0)<span class="badge bg-warning">{{ $onlineStats->en_cours }} {{ __('en cours') }}</span>@endif
            @if($onlineStats->livraison > 0)<span class="badge bg-primary">{{ $onlineStats->livraison }} {{ __('en livraison') }}</span>@endif
            @if($onlineStats->recues > 0)<span class="badge bg-success">{{ $onlineStats->recues }} {{ __('recues') }}</span>@endif
            @if($onlineStats->annulees > 0)<span class="badge bg-danger">{{ $onlineStats->annulees }} {{ __('annulees') }}</span>@endif
            <span class="badge bg-secondary">{{ $onlineStats->total }} {{ __('total') }}</span>
        </div>
        @endif

        {{-- Monthly chart --}}
        @if($monthlyPurchases->count() > 0)
        <h6 class="fw-bold mb-2">{{ __('Achats (6 mois)') }}</h6>
        <div class="row g-1 mb-3">
            @foreach($monthlyPurchases as $mp)
                @php $max = $monthlyPurchases->max('total'); $pct = $max > 0 ? round($mp->total / $max * 100) : 0; @endphp
                <div class="col">
                    <div class="text-center">
                        <div class="d-flex flex-column align-items-center" style="height:80px; justify-content:flex-end;">
                            <small class="fw-bold text-primary" style="font-size:.55rem;">{{ $fmt($mp->total) }}</small>
                            <div class="bg-primary bg-opacity-75 rounded-top" style="width:20px; height:{{ max($pct, 8) }}%; min-height:4px;"></div>
                        </div>
                        <div class="text-muted" style="font-size:.6rem;">{{ \Carbon\Carbon::createFromDate($mp->year, $mp->month)->translatedFormat('M') }}</div>
                    </div>
                </div>
            @endforeach
        </div>
        @endif

        {{-- Top products --}}
        @if($topProducts->count() > 0)
        <h6 class="fw-bold mb-2">{{ __('Produits les plus achetes') }}</h6>
        <div class="list-group list-group-flush">
            @foreach($topProducts as $p)
                <div class="list-group-item px-0 d-flex justify-content-between align-items-center py-1">
                    <span>{{ $p->name }}</span>
                    <div class="text-end">
                        <span class="fw-bold">{{ $fmt($p->total) }}</span>
                        <small class="text-muted ms-1">x{{ $p->qty }}</small>
                    </div>
                </div>
            @endforeach
        </div>
        @endif
    </div>
</div>
