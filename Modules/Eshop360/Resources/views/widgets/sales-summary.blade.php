{{-- Widget: Sales Summary --}}
@php
    $instance = \Modules\Core\Support\CurrentInstance::get();
    $instanceId = $instance?->id ?? 0;
    $from = now()->startOfMonth()->toDateString();
    $to = now()->toDateString();
    $slug = $instance->slug ?? '';

    $orders = \Modules\Eshop360\Domain\Sales\Models\Order::where('instance_id', $instanceId)
        ->whereBetween('created_at', [$from . ' 00:00:00', $to . ' 23:59:59'])
        ->where('status', '!=', 'cancelled');

    $totalSales = round((float) (clone $orders)->sum('total'), 2);
    $orderCount = (clone $orders)->count();
    $totalDue = round((float) (clone $orders)->sum('due_amount'), 2);
    $todaySales = round((float) \Modules\Eshop360\Domain\Sales\Models\Order::where('instance_id', $instanceId)
        ->whereDate('created_at', today())
        ->where('status', '!=', 'cancelled')
        ->sum('total'), 2);
@endphp
<div class="card border-0 shadow-sm h-100">
    <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-bold"><i class="ti ti-chart-bar me-2 text-primary"></i>Ventes du mois</h6>
        <a href="{{ route('eshop360.sales.dashboard', $slug) }}" class="btn btn-sm btn-outline-primary">Voir tout</a>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-6">
                <div class="text-muted">CA Mois</div>
                <div class="fw-bold fs-4 text-primary">{{ number_format($totalSales, 0, ',', ' ') }}</div>
            </div>
            <div class="col-6">
                <div class="text-muted">Aujourd'hui</div>
                <div class="fw-bold fs-4 text-success">{{ number_format($todaySales, 0, ',', ' ') }}</div>
            </div>
            <div class="col-6">
                <div class="text-muted">Commandes</div>
                <div class="fw-bold fs-4">{{ $orderCount }}</div>
            </div>
            <div class="col-6">
                <div class="text-muted">Impayés</div>
                <div class="fw-bold fs-4 {{ $totalDue > 0 ? 'text-danger' : 'text-muted' }}">{{ number_format($totalDue, 0, ',', ' ') }}</div>
            </div>
        </div>
    </div>
</div>
