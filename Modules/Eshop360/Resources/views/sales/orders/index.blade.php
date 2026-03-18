<x-dashboard::layouts.master
    :title="__('Commandes') . ' — ' . ($instance->name ?? 'B360')"
    :instance="$instance"
    :pageTitle="__('Commandes')">

@php $slug = $instance->slug ?? ''; @endphp

<div class="page-header">
    <div class="add-item d-flex">
        <div class="page-title">
            <h4 class="fw-bold">{{ __('Commandes') }}</h4>
            <h6>{{ __('Liste de toutes les commandes') }}</h6>
        </div>
    </div>
    <div class="page-btn d-flex gap-2">
        <a href="{{ route('eshop360.export.sales', $slug) }}" class="btn btn-outline-info btn-sm"><i class="ti ti-download me-1"></i>{{ __('Exporter') }}</a>
        <a href="{{ route('eshop360.sales.dashboard', $slug) }}" class="btn btn-outline-secondary btn-sm"><i class="ti ti-chart-bar me-1"></i>{{ __('Tableau de bord') }}</a>
    </div>
</div>

{{-- Filtres --}}
<div class="card mb-3 border-0 shadow-sm">
    <div class="card-body py-2">
        <form method="GET" action="{{ route('eshop360.orders.index', $slug) }}" class="row g-2 align-items-end">
            <div class="col-md-2">
                <label class="form-label small mb-1">{{ __('Recherche') }}</label>
                <input type="text" name="search" class="form-control form-control-sm" value="{{ request('search') }}" placeholder="{{ __('N° ou client...') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-1">{{ __('Statut') }}</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">{{ __('Tous') }}</option>
                    @foreach(['pending' => 'En attente', 'processing' => 'En cours', 'completed' => 'Terminee', 'cancelled' => 'Annulee', 'refunded' => 'Remboursee'] as $val => $label)
                        <option value="{{ $val }}" {{ request('status') === $val ? 'selected' : '' }}>{{ __($label) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-1">{{ __('Paiement') }}</label>
                <select name="payment_status" class="form-select form-select-sm">
                    <option value="">{{ __('Tous') }}</option>
                    <option value="paid" {{ request('payment_status') === 'paid' ? 'selected' : '' }}>{{ __('Paye') }}</option>
                    <option value="partial" {{ request('payment_status') === 'partial' ? 'selected' : '' }}>{{ __('Partiel') }}</option>
                    <option value="unpaid" {{ request('payment_status') === 'unpaid' ? 'selected' : '' }}>{{ __('Impaye') }}</option>
                </select>
            </div>
            <div class="col-md-1">
                <label class="form-label small mb-1">{{ __('Source') }}</label>
                <select name="source" class="form-select form-select-sm">
                    <option value="">{{ __('Toutes') }}</option>
                    <option value="pos" {{ request('source') === 'pos' ? 'selected' : '' }}>POS</option>
                    <option value="online" {{ request('source') === 'online' ? 'selected' : '' }}>{{ __('En ligne') }}</option>
                    <option value="manual" {{ request('source') === 'manual' ? 'selected' : '' }}>{{ __('Manuel') }}</option>
                </select>
            </div>
            <div class="col-md-1">
                <label class="form-label small mb-1">{{ __('Du') }}</label>
                <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}">
            </div>
            <div class="col-md-1">
                <label class="form-label small mb-1">{{ __('Au') }}</label>
                <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}">
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-sm btn-primary"><i class="ti ti-search"></i></button>
            </div>
            @if(request()->hasAny(['search','status','payment_status','source','date_from','date_to']))
                <div class="col-auto">
                    <a href="{{ route('eshop360.orders.index', $slug) }}" class="btn btn-sm btn-outline-secondary"><i class="ti ti-x"></i></a>
                </div>
            @endif
        </form>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show"><i class="ti ti-check me-1"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif

<div class="card border-0 shadow-sm">
    <div class="card-header bg-transparent">
        <h6 class="mb-0 fw-bold"><i class="ti ti-shopping-cart me-2"></i>{{ __('Commandes') }} <span class="badge bg-primary ms-1">{{ $orders->total() }}</span></h6>
    </div>
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
                            $pc = match($order->payment_status) { 'paid' => 'bg-success', 'partial' => 'bg-warning text-dark', default => 'bg-danger' };
                        @endphp
                        <tr>
                            <td class="fw-medium"><a href="{{ route('eshop360.orders.show', [$slug, $order]) }}" class="text-decoration-none">{{ $order->reference }}</a></td>
                            <td class="small">{{ $order->customer?->name ?? '—' }}</td>
                            <td><span class="badge bg-light text-dark" style="font-size:.6rem;">{{ $order->source }}</span></td>
                            <td class="small text-capitalize">{{ $order->payment_method ?? '—' }}</td>
                            <td class="text-end fw-bold">{{ number_format($order->total, 0, ',', ' ') }}</td>
                            <td class="text-center"><span class="badge {{ $sc }} rounded-pill" style="font-size:.6rem;">{{ ucfirst($order->status) }}</span></td>
                            <td class="text-center"><span class="badge {{ $pc }} rounded-pill" style="font-size:.6rem;">{{ ucfirst($order->payment_status) }}</span></td>
                            <td class="small text-muted">{{ $order->created_at?->format('d/m/Y H:i') }}</td>
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
                        <tr><td colspan="9" class="text-center text-muted py-4"><i class="ti ti-shopping-cart-off fs-1 d-block mb-2"></i>{{ __('Aucune commande trouvee.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($orders->hasPages())<div class="p-3">{{ $orders->links() }}</div>@endif
    </div>
</div>

</x-dashboard::layouts.master>
