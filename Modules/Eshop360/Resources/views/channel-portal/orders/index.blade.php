@extends('eshop360::channel-portal.layouts.master')

@section('content')
@php
    $instance = \Modules\Core\Support\CurrentInstance::get();
    $slug = $instance->slug ?? '';
    $statusColors = ['pending' => 'warning', 'processing' => 'info', 'completed' => 'success', 'cancelled' => 'danger'];
    $statusLabels = ['pending' => 'En attente', 'processing' => 'En cours', 'completed' => 'Terminee', 'cancelled' => 'Annulee'];
@endphp

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="fw-bold mb-1">{{ __('Commandes') }}</h4>
        <p class="text-muted mb-0">{{ __('Gestion des commandes du canal') }}</p>
    </div>
    <a href="{{ route('eshop360.channel-portal.orders.create', [$slug, $channel->slug ?? $channel->id]) }}" class="btn btn-primary">
        <i class="ti ti-plus me-1"></i> Nouvelle commande
    </a>
</div>

{{-- Filters --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label">{{ __('Statut') }}</label>
                <select name="status" class="form-select">
                    <option value="">{{ __('Tous') }}</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>{{ __('En attente') }}</option>
                    <option value="processing" {{ request('status') === 'processing' ? 'selected' : '' }}>{{ __('En cours') }}</option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>{{ __('Terminee') }}</option>
                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>{{ __('Annulee') }}</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">{{ __('Du') }}</label>
                <input type="date" name="from" class="form-control" value="{{ request('from') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">{{ __('Au') }}</label>
                <input type="date" name="to" class="form-control" value="{{ request('to') }}">
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-outline-primary w-100"><i class="ti ti-filter me-1"></i> {{ __('Filtrer') }}</button>
            </div>
        </form>
    </div>
</div>

{{-- Orders Table --}}
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>{{ __('N° commande') }}</th>
                        <th>{{ __('Client') }}</th>
                        <th>{{ __('Articles') }}</th>
                        <th>{{ __('Total') }}</th>
                        <th>{{ __('Statut') }}</th>
                        <th>{{ __('Date') }}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                    <tr>
                        <td class="fw-medium">{{ $order->order_number ?? ('ORD-' . $order->id) }}</td>
                        <td>{{ $order->customer->name ?? '—' }}</td>
                        <td>{{ $order->items->count() }}</td>
                        <td class="fw-bold">{{ number_format($order->total, 0, ',', ' ') }} XAF</td>
                        <td>
                            <span class="badge bg-{{ $statusColors[$order->status] ?? 'secondary' }}">
                                {{ $statusLabels[$order->status] ?? $order->status }}
                            </span>
                        </td>
                        <td>{{ $order->created_at->format('d/m/Y H:i') }}</td>
                        <td>
                            <a href="{{ route('eshop360.channel-portal.orders.show', [$slug, $channel->slug ?? $channel->id, $order->id]) }}" class="btn btn-sm btn-outline-primary">
                                <i class="ti ti-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">{{ __('Aucune commande trouvee.') }}</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="mt-3">
    {{ $orders->links() }}
</div>
@endsection
