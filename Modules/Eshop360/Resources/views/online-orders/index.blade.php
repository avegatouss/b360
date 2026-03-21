<x-dashboard::layouts.master
    :title="__('Commandes en ligne') . ' — ' . ($instance->name ?? 'B360')"
    :instance="$instance"
    :pageTitle="__('Commandes en ligne')">

@php
    $slug = $instance->slug ?? '';
    $statusMap = [
        'pending_validation' => ['En attente', 'warning', 'ti-clock'],
        'validated'          => ['Validee', 'info', 'ti-circle-check'],
        'preparing'          => ['Preparation', 'info', 'ti-package'],
        'prepared'           => ['Preparee', 'primary', 'ti-packages'],
        'shipping'           => ['Expediee', 'primary', 'ti-truck'],
        'delivered'          => ['Livree', 'success', 'ti-truck-delivery'],
        'received'           => ['Recue', 'success', 'ti-package-import'],
        'invoiced'           => ['Facturee', 'success', 'ti-file-invoice'],
        'cancelled'          => ['Annulee', 'danger', 'ti-x'],
    ];
@endphp

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="fw-bold mb-1"><i class="ti ti-world me-2"></i>{{ __('Commandes en ligne') }}</h4>
        <p class="text-muted mb-0">{{ __('Suivi et traitement des commandes clients en ligne') }}</p>
    </div>
</div>

{{-- KPI Cards --}}
<div class="row g-3 mb-3">
    <div class="col-xl col-sm-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body py-3">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle bg-primary-subtle d-flex align-items-center justify-content-center" style="width:44px;height:44px;">
                        <i class="ti ti-shopping-cart text-primary fs-4"></i>
                    </div>
                    <div class="ms-3">
                        <h3 class="fw-bold mb-0">{{ $kpi->total }}</h3>
                        <span class="text-muted small">{{ __('Total') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl col-sm-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body py-3">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle bg-warning-subtle d-flex align-items-center justify-content-center" style="width:44px;height:44px;">
                        <i class="ti ti-clock text-warning fs-4"></i>
                    </div>
                    <div class="ms-3">
                        <h3 class="fw-bold mb-0 text-warning">{{ $kpi->pending }}</h3>
                        <span class="text-muted small">{{ __('En attente') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl col-sm-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body py-3">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle bg-info-subtle d-flex align-items-center justify-content-center" style="width:44px;height:44px;">
                        <i class="ti ti-package text-info fs-4"></i>
                    </div>
                    <div class="ms-3">
                        <h3 class="fw-bold mb-0 text-info">{{ $kpi->validated }}</h3>
                        <span class="text-muted small">{{ __('En cours') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl col-sm-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body py-3">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle bg-success-subtle d-flex align-items-center justify-content-center" style="width:44px;height:44px;">
                        <i class="ti ti-truck text-success fs-4"></i>
                    </div>
                    <div class="ms-3">
                        <h3 class="fw-bold mb-0 text-success">{{ $kpi->shipping }}</h3>
                        <span class="text-muted small">{{ __('Expedition') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl col-sm-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body py-3">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle bg-success-subtle d-flex align-items-center justify-content-center" style="width:44px;height:44px;">
                        <i class="ti ti-check text-success fs-4"></i>
                    </div>
                    <div class="ms-3">
                        <h3 class="fw-bold mb-0">{{ $kpi->completed }}</h3>
                        <span class="text-muted small">{{ __('Terminees') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl col-sm-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body py-3">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle bg-primary-subtle d-flex align-items-center justify-content-center" style="width:44px;height:44px;">
                        <i class="ti ti-chart-bar text-primary fs-4"></i>
                    </div>
                    <div class="ms-3">
                        <h3 class="fw-bold mb-0">{{ number_format($kpi->revenue, 0, ',', ' ') }}</h3>
                        <span class="text-muted small">{{ __('CA') }}</span>
                    </div>
                </div>
                <div class="mt-1 small text-muted">{{ __('Moy.') }} {{ number_format($kpi->avg, 0, ',', ' ') }} / cmd</div>
            </div>
        </div>
    </div>
</div>

{{-- Filters --}}
<div class="card mb-3 border-0 shadow-sm">
    <div class="card-body py-2">
        <form method="GET" action="{{ route('eshop360.online-orders.index', $slug) }}" class="row g-2 align-items-end">
            <div class="col-md-2">
                <label class="form-label small mb-1">{{ __('Recherche') }}</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text"><i class="ti ti-search"></i></span>
                    <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="{{ __('Ref, client...') }}">
                </div>
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-1">{{ __('Statut') }}</label>
                <select name="status" class="form-select form-select-sm online-select2" data-placeholder="{{ __('Tous') }}">
                    <option value=""></option>
                    @foreach($statusMap as $key => [$label, $color, $icon])
                        <option value="{{ $key }}" @selected(request('status') === $key)>{{ __($label) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-1">{{ __('Client') }}</label>
                <select name="customer_id" class="form-select form-select-sm online-select2" data-placeholder="{{ __('Tous les clients') }}">
                    <option value=""></option>
                    @foreach($customers as $c)
                        <option value="{{ $c->id }}" @selected(request('customer_id') == $c->id)>{{ $c->name }}{{ $c->code ? " ({$c->code})" : '' }}</option>
                    @endforeach
                </select>
            </div>
            @if($channels->isNotEmpty())
            <div class="col-md-2">
                <label class="form-label small mb-1">{{ __('Canal') }}</label>
                <select name="channel_id" class="form-select form-select-sm online-select2" data-placeholder="{{ __('Tous') }}">
                    <option value=""></option>
                    @foreach($channels as $ch)
                        <option value="{{ $ch->id }}" @selected(request('channel_id') == $ch->id)>{{ $ch->name }}</option>
                    @endforeach
                </select>
            </div>
            @endif
            <div class="col-auto">
                <label class="form-label small mb-1">{{ __('Periode') }}</label>
                <div class="input-group input-group-sm">
                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                    <span class="input-group-text">-</span>
                    <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                </div>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-sm btn-primary"><i class="ti ti-filter me-1"></i>{{ __('Filtrer') }}</button>
            </div>
            @if(request()->hasAny(['search', 'status', 'customer_id', 'channel_id', 'date_from', 'date_to']))
                <div class="col-auto">
                    <a href="{{ route('eshop360.online-orders.index', $slug) }}" class="btn btn-sm btn-outline-secondary"><i class="ti ti-x me-1"></i>{{ __('Reset') }}</a>
                </div>
            @endif
        </form>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show"><i class="ti ti-check me-1"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show"><i class="ti ti-x me-1"></i>{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif

{{-- Quick status tabs --}}
<div class="d-flex gap-2 mb-3 flex-wrap">
    <a href="{{ route('eshop360.online-orders.index', $slug) }}" class="btn btn-sm {{ !request('status') ? 'btn-primary' : 'btn-outline-secondary' }}">
        {{ __('Toutes') }} <span class="badge bg-white text-dark ms-1">{{ $kpi->total }}</span>
    </a>
    <a href="{{ route('eshop360.online-orders.index', array_merge(request()->except('status', 'page'), ['slug' => $slug, 'status' => 'pending_validation'])) }}" class="btn btn-sm {{ request('status') === 'pending_validation' ? 'btn-warning' : 'btn-outline-warning' }}">
        <i class="ti ti-clock me-1"></i>{{ __('En attente') }} <span class="badge bg-white text-dark ms-1">{{ $kpi->pending }}</span>
    </a>
    <a href="{{ route('eshop360.online-orders.index', array_merge(request()->except('status', 'page'), ['slug' => $slug, 'status' => 'shipping'])) }}" class="btn btn-sm {{ request('status') === 'shipping' ? 'btn-primary' : 'btn-outline-primary' }}">
        <i class="ti ti-truck me-1"></i>{{ __('Expediees') }}
    </a>
    <a href="{{ route('eshop360.online-orders.index', array_merge(request()->except('status', 'page'), ['slug' => $slug, 'status' => 'delivered'])) }}" class="btn btn-sm {{ request('status') === 'delivered' ? 'btn-success' : 'btn-outline-success' }}">
        <i class="ti ti-truck-delivery me-1"></i>{{ __('Livrees') }}
    </a>
    <a href="{{ route('eshop360.online-orders.index', array_merge(request()->except('status', 'page'), ['slug' => $slug, 'status' => 'cancelled'])) }}" class="btn btn-sm {{ request('status') === 'cancelled' ? 'btn-danger' : 'btn-outline-danger' }}">
        <i class="ti ti-x me-1"></i>{{ __('Annulees') }} @if($kpi->cancelled)<span class="badge bg-white text-dark ms-1">{{ $kpi->cancelled }}</span>@endif
    </a>
</div>

{{-- Orders Table --}}
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>{{ __('Reference') }}</th>
                        <th>{{ __('Client') }}</th>
                        <th>{{ __('Canal') }}</th>
                        <th class="text-end">{{ __('Total') }}</th>
                        <th class="text-center">{{ __('Statut') }}</th>
                        <th>{{ __('Date') }}</th>
                        <th class="text-end" style="width:80px;">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                        @php [$label, $color, $icon] = $statusMap[$order->status] ?? ['?', 'secondary', 'ti-help']; @endphp
                        <tr>
                            <td>
                                <a href="{{ route('eshop360.online-orders.show', [$slug, $order]) }}" class="fw-medium text-decoration-none">
                                    {{ $order->reference }}
                                </a>
                            </td>
                            <td>
                                <div class="fw-medium small">{{ $order->customer?->name ?? '—' }}</div>
                                @if($order->customer?->phone)
                                    <div class="text-muted" style="font-size:.7rem;">{{ $order->customer->phone }}</div>
                                @endif
                            </td>
                            <td>
                                @if($order->channel)
                                    <span class="badge bg-info-subtle text-info">{{ $order->channel->name }}</span>
                                @else
                                    <span class="text-muted small">{{ __('Direct') }}</span>
                                @endif
                            </td>
                            <td class="text-end fw-bold">{{ number_format($order->total, 0, ',', ' ') }}</td>
                            <td class="text-center">
                                <span class="badge bg-{{ $color }}"><i class="ti {{ $icon }} me-1" style="font-size:.7rem;"></i>{{ __($label) }}</span>
                            </td>
                            <td class="small text-muted">{{ $order->created_at?->format('d/m/Y H:i') }}</td>
                            <td class="text-end">
                                <a href="{{ route('eshop360.online-orders.show', [$slug, $order]) }}" class="btn btn-sm btn-outline-primary" title="{{ __('Voir') }}"><i class="ti ti-eye"></i></a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                <i class="ti ti-inbox fs-1 d-block mb-2 opacity-50"></i>
                                {{ __('Aucune commande en ligne trouvee.') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($orders->hasPages())
            <div class="p-3">{{ $orders->links() }}</div>
        @endif
    </div>
</div>

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet">
@endpush

@push('scripts')
<script>
jQuery(function ($) {
    $('.online-select2').each(function () {
        $(this).select2({
            theme: 'bootstrap-5',
            allowClear: true,
            width: '100%',
            placeholder: $(this).data('placeholder') || ''
        }).on('select2:select select2:clear', function () {
            $(this).closest('form')[0].submit();
        });
    });
});
</script>
@endpush

</x-dashboard::layouts.master>
