<x-dashboard::layouts.master
    :title="__('Commandes') . ' — ' . ($instance->name ?? 'B360')"
    :instance="$instance"
    :pageTitle="__('Commandes')">

@php $slug = $instance->slug ?? ''; @endphp

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="fw-bold mb-1"><i class="ti ti-shopping-cart me-2"></i>{{ __('Commandes') }}</h4>
        <p class="text-muted mb-0">{{ __('Toutes les commandes de l\'instance') }}</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('eshop360.export.sales', $slug) }}" class="btn btn-outline-info btn-sm"><i class="ti ti-download me-1"></i>{{ __('Exporter') }}</a>
        <a href="{{ route('eshop360.sales.dashboard', $slug) }}" class="btn btn-outline-secondary btn-sm"><i class="ti ti-chart-bar me-1"></i>{{ __('Tableau de bord') }}</a>
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
                        <span class="text-muted">{{ __('Commandes') }}</span>
                    </div>
                </div>
                <div class="mt-2">
                    <span class="badge bg-success-subtle text-success">{{ $kpi->completed }} {{ __('terminees') }}</span>
                    @if($kpi->pending > 0)<span class="badge bg-warning-subtle text-warning ms-1">{{ $kpi->pending }} {{ __('en attente') }}</span>@endif
                    @if($kpi->cancelled > 0)<span class="badge bg-danger-subtle text-danger ms-1">{{ $kpi->cancelled }} {{ __('annulees') }}</span>@endif
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl col-sm-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body py-3">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle bg-success-subtle d-flex align-items-center justify-content-center" style="width:44px;height:44px;">
                        <i class="ti ti-chart-bar text-success fs-4"></i>
                    </div>
                    <div class="ms-3">
                        <h3 class="fw-bold mb-0">{{ number_format($kpi->revenue, 0, ',', ' ') }}</h3>
                        <span class="text-muted">{{ __('Chiffre d\'affaires') }}</span>
                    </div>
                </div>
                <div class="mt-2 text-muted">{{ __('Panier moyen') }}: <span class="fw-bold">{{ number_format($kpi->avg, 0, ',', ' ') }}</span></div>
            </div>
        </div>
    </div>
    <div class="col-xl col-sm-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body py-3">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle bg-info-subtle d-flex align-items-center justify-content-center" style="width:44px;height:44px;">
                        <i class="ti ti-cash text-info fs-4"></i>
                    </div>
                    <div class="ms-3">
                        <h3 class="fw-bold mb-0 text-success">{{ number_format($kpi->paid, 0, ',', ' ') }}</h3>
                        <span class="text-muted">{{ __('Encaisse') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl col-sm-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body py-3">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle bg-{{ $kpi->due > 0 ? 'danger' : 'secondary' }}-subtle d-flex align-items-center justify-content-center" style="width:44px;height:44px;">
                        <i class="ti ti-alert-triangle text-{{ $kpi->due > 0 ? 'danger' : 'secondary' }} fs-4"></i>
                    </div>
                    <div class="ms-3">
                        <h3 class="fw-bold mb-0 {{ $kpi->due > 0 ? 'text-danger' : '' }}">{{ number_format($kpi->due, 0, ',', ' ') }}</h3>
                        <span class="text-muted">{{ __('Impayes') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Filters --}}
<div class="card mb-3 border-0 shadow-sm">
    <div class="card-body py-2">
        <form method="GET" action="{{ route('eshop360.orders.index', $slug) }}" class="row g-2 align-items-end">
            <div class="col-md-2">
                <label class="form-label mb-1">{{ __('Recherche') }}</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text"><i class="ti ti-search"></i></span>
                    <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="{{ __('N, client...') }}">
                </div>
            </div>
            <div class="col-md-2">
                <label class="form-label mb-1">{{ __('Client') }}</label>
                <select name="customer_id" class="form-select form-select-sm orders-select2" data-placeholder="{{ __('Tous les clients') }}">
                    <option value=""></option>
                    @foreach($customers as $c)
                        <option value="{{ $c->id }}" @selected(request('customer_id') == $c->id)>{{ $c->name }}{{ $c->code ? " ({$c->code})" : '' }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label mb-1">{{ __('Statut') }}</label>
                <select name="status" class="form-select form-select-sm orders-select2" data-placeholder="{{ __('Tous') }}">
                    <option value=""></option>
                    @foreach(['pending' => 'En attente', 'processing' => 'En cours', 'completed' => 'Terminee', 'cancelled' => 'Annulee', 'refunded' => 'Remboursee'] as $val => $label)
                        <option value="{{ $val }}" @selected(request('status') === $val)>{{ __($label) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label mb-1">{{ __('Paiement') }}</label>
                <select name="payment_status" class="form-select form-select-sm orders-select2" data-placeholder="{{ __('Tous') }}">
                    <option value=""></option>
                    <option value="paid" @selected(request('payment_status') === 'paid')>{{ __('Paye') }}</option>
                    <option value="partial" @selected(request('payment_status') === 'partial')>{{ __('Partiel') }}</option>
                    <option value="unpaid" @selected(request('payment_status') === 'unpaid')>{{ __('Impaye') }}</option>
                    <option value="overdue" @selected(request('payment_status') === 'overdue')>{{ __('En retard') }}</option>
                </select>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-sm btn-primary"><i class="ti ti-filter me-1"></i>{{ __('Filtrer') }}</button>
            </div>
            @if(request()->hasAny(['search','status','payment_status','source','customer_id','channel_id','payment_method','date_from','date_to','min_total','max_total']))
                <div class="col-auto">
                    <a href="{{ route('eshop360.orders.index', $slug) }}" class="btn btn-sm btn-outline-secondary"><i class="ti ti-x me-1"></i>{{ __('Reset') }}</a>
                </div>
            @endif
            <div class="col-auto ms-auto">
                <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="collapse" data-bs-target="#advanced-filters">
                    <i class="ti ti-adjustments me-1"></i>{{ __('+ Filtres') }}
                </button>
            </div>
        </form>

        {{-- Advanced filters --}}
        <div class="collapse {{ request()->hasAny(['source','channel_id','payment_method','date_from','date_to','min_total','max_total']) ? 'show' : '' }}" id="advanced-filters">
            <form method="GET" action="{{ route('eshop360.orders.index', $slug) }}" class="row g-2 align-items-end mt-2 pt-2 border-top">
                {{-- Preserve main filters --}}
                @if(request('search'))<input type="hidden" name="search" value="{{ request('search') }}">@endif
                @if(request('status'))<input type="hidden" name="status" value="{{ request('status') }}">@endif
                @if(request('payment_status'))<input type="hidden" name="payment_status" value="{{ request('payment_status') }}">@endif
                @if(request('customer_id'))<input type="hidden" name="customer_id" value="{{ request('customer_id') }}">@endif

                <div class="col-md-2">
                    <label class="form-label mb-1">{{ __('Source') }}</label>
                    <select name="source" class="form-select form-select-sm orders-select2-adv" data-placeholder="{{ __('Toutes') }}">
                        <option value=""></option>
                        <option value="pos" @selected(request('source') === 'pos')>POS</option>
                        <option value="online" @selected(request('source') === 'online')>{{ __('En ligne') }}</option>
                        <option value="manual" @selected(request('source') === 'manual')>{{ __('Manuel') }}</option>
                        <option value="channel_portal" @selected(request('source') === 'channel_portal')>{{ __('Canal') }}</option>
                    </select>
                </div>
                @if($channels->isNotEmpty())
                <div class="col-md-2">
                    <label class="form-label mb-1">{{ __('Canal') }}</label>
                    <select name="channel_id" class="form-select form-select-sm orders-select2-adv" data-placeholder="{{ __('Tous') }}">
                        <option value=""></option>
                        @foreach($channels as $ch)
                            <option value="{{ $ch->id }}" @selected(request('channel_id') == $ch->id)>{{ $ch->name }}</option>
                        @endforeach
                    </select>
                </div>
                @endif
                <div class="col-md-2">
                    <label class="form-label mb-1">{{ __('Methode paiement') }}</label>
                    <select name="payment_method" class="form-select form-select-sm orders-select2-adv" data-placeholder="{{ __('Toutes') }}">
                        <option value=""></option>
                        @foreach($paymentMethods as $pm)
                            <option value="{{ $pm }}" @selected(request('payment_method') === $pm)>{{ ucfirst(__($pm)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-auto">
                    <label class="form-label mb-1">{{ __('Periode') }}</label>
                    <div class="input-group input-group-sm">
                        <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                        <span class="input-group-text">-</span>
                        <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                    </div>
                </div>
                <div class="col-auto">
                    <label class="form-label mb-1">{{ __('Montant') }}</label>
                    <div class="input-group input-group-sm">
                        <input type="number" name="min_total" class="form-control" value="{{ request('min_total') }}" placeholder="{{ __('Min') }}" style="max-width:90px;">
                        <span class="input-group-text">-</span>
                        <input type="number" name="max_total" class="form-control" value="{{ request('max_total') }}" placeholder="{{ __('Max') }}" style="max-width:90px;">
                    </div>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-sm btn-primary"><i class="ti ti-filter me-1"></i>{{ __('Appliquer') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show"><i class="ti ti-check me-1"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif

{{-- Quick status tabs --}}
<div class="d-flex gap-2 mb-3 flex-wrap">
    <a href="{{ route('eshop360.orders.index', array_merge(request()->except('status', 'page'), ['slug' => $slug])) }}" class="btn btn-sm {{ !request('status') ? 'btn-primary' : 'btn-outline-secondary' }}">
        {{ __('Toutes') }} <span class="badge bg-white text-dark ms-1">{{ $kpi->total }}</span>
    </a>
    <a href="{{ route('eshop360.orders.index', array_merge(request()->except('status', 'page'), ['slug' => $slug, 'status' => 'pending'])) }}" class="btn btn-sm {{ request('status') === 'pending' ? 'btn-warning' : 'btn-outline-warning' }}">
        <i class="ti ti-clock me-1"></i>{{ __('En attente') }} @if($kpi->pending)<span class="badge bg-white text-dark ms-1">{{ $kpi->pending }}</span>@endif
    </a>
    <a href="{{ route('eshop360.orders.index', array_merge(request()->except('status', 'page'), ['slug' => $slug, 'status' => 'completed'])) }}" class="btn btn-sm {{ request('status') === 'completed' ? 'btn-success' : 'btn-outline-success' }}">
        <i class="ti ti-check me-1"></i>{{ __('Terminees') }} @if($kpi->completed)<span class="badge bg-white text-dark ms-1">{{ $kpi->completed }}</span>@endif
    </a>
    <a href="{{ route('eshop360.orders.index', array_merge(request()->except('status', 'page'), ['slug' => $slug, 'status' => 'cancelled'])) }}" class="btn btn-sm {{ request('status') === 'cancelled' ? 'btn-danger' : 'btn-outline-danger' }}">
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
                        <th>{{ __('Source') }}</th>
                        <th>{{ __('Methode') }}</th>
                        <th class="text-end">{{ __('Total') }}</th>
                        <th class="text-end">{{ __('Paye') }}</th>
                        <th class="text-center">{{ __('Statut') }}</th>
                        <th class="text-center">{{ __('Paiement') }}</th>
                        <th>{{ __('Date') }}</th>
                        <th class="text-end" style="width:100px;">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                        @php
                            $sc = match($order->status) { 'completed' => 'bg-success', 'pending' => 'bg-warning text-dark', 'processing' => 'bg-info', 'cancelled','refunded' => 'bg-danger', default => 'bg-secondary' };
                            $pc = match($order->payment_status) { 'paid' => 'bg-success', 'partial' => 'bg-warning text-dark', 'overdue' => 'bg-danger', default => 'bg-secondary' };
                            $statusLabels = ['completed' => 'Terminee', 'pending' => 'En attente', 'processing' => 'En cours', 'cancelled' => 'Annulee', 'refunded' => 'Remboursee'];
                            $payLabels = ['paid' => 'Paye', 'partial' => 'Partiel', 'unpaid' => 'Impaye', 'overdue' => 'En retard'];
                        @endphp
                        <tr>
                            <td class="fw-medium"><a href="{{ route('eshop360.orders.show', [$slug, $order]) }}" class="text-decoration-none">{{ $order->reference }}</a></td>
                            <td>{{ $order->customer?->name ?? '—' }}</td>
                            <td><span class="badge bg-light text-dark">{{ ucfirst($order->source ?? '—') }}</span></td>
                            <td class="text-capitalize">{{ $order->payment_method ?? '—' }}</td>
                            <td class="text-end fw-bold">{{ number_format($order->total, 0, ',', ' ') }}</td>
                            <td class="text-end {{ $order->due_amount > 0 ? 'text-danger' : 'text-success' }}">{{ number_format($order->paid_amount, 0, ',', ' ') }}</td>
                            <td class="text-center"><span class="badge {{ $sc }}">{{ __($statusLabels[$order->status] ?? ucfirst($order->status)) }}</span></td>
                            <td class="text-center"><span class="badge {{ $pc }}">{{ __($payLabels[$order->payment_status] ?? ucfirst($order->payment_status)) }}</span></td>
                            <td class="text-muted">{{ $order->created_at?->format('d/m/Y H:i') }}</td>
                            <td class="text-end">
                                <div class="d-flex gap-1 justify-content-end">
                                    <a href="{{ route('eshop360.orders.show', [$slug, $order]) }}" class="btn btn-sm btn-outline-info" title="{{ __('Detail') }}"><i class="ti ti-eye"></i></a>
                                    <form action="{{ route('eshop360.orders.destroy', [$slug, $order]) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('Supprimer cette commande ?') }}')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger"><i class="ti ti-trash"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="10" class="text-center text-muted py-4"><i class="ti ti-shopping-cart-off fs-1 d-block mb-2"></i>{{ __('Aucune commande trouvee.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($orders->hasPages())<div class="p-3">{{ $orders->links() }}</div>@endif
    </div>
</div>

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet">
@endpush

@push('scripts')
<script>
jQuery(function ($) {
    $('.orders-select2, .orders-select2-adv').each(function () {
        $(this).select2({
            theme: 'bootstrap-5',
            allowClear: true,
            width: '100%',
            placeholder: $(this).data('placeholder') || ''
        });
    });
    $('.orders-select2').on('select2:select select2:clear', function () {
        $(this).closest('form')[0].submit();
    });
});
</script>
@endpush

</x-dashboard::layouts.master>
