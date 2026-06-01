@extends('eshop360::channel-portal.layouts.master')

@section('content')
@php
    $instance = \Modules\Core\Support\CurrentInstance::get();
    $slug = $instance->slug ?? '';
    $statuses = [
        'pending_validation' => ['En attente', 'warning'],
        'validated'          => ['Validee', 'info'],
        'preparing'          => ['Preparation', 'info'],
        'prepared'           => ['Preparee', 'primary'],
        'shipping'           => ['Expediee', 'primary'],
        'delivered'          => ['Livree', 'success'],
        'received'           => ['Recue', 'success'],
        'invoiced'           => ['Facturee', 'success'],
        'cancelled'          => ['Annulee', 'danger'],
    ];
@endphp

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="fw-bold mb-1"><i class="ti ti-world me-2"></i>{{ __('Commandes en ligne') }}</h4>
        <p class="text-muted mb-0">{{ __('Commandes passees par vos clients depuis le portail') }}</p>
    </div>
</div>

{{-- Filters --}}
<div class="card mb-3 border-0 shadow-sm">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <input type="text" name="search" class="form-control form-control-sm" value="{{ request('search') }}" placeholder="{{ __('Ref ou client...') }}">
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">{{ __('Tous les statuts') }}</option>
                    @foreach($statuses as $key => [$label, $color])
                        <option value="{{ $key }}" @selected(request('status') === $key)>{{ __($label) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-sm btn-primary"><i class="ti ti-search"></i></button>
            </div>
            @if(request()->hasAny(['search', 'status']))
                <div class="col-auto">
                    <a href="{{ route('eshop360.channel-portal.online-orders.index', [$slug, $channel->slug ?? $channel->id]) }}" class="btn btn-sm btn-outline-secondary"><i class="ti ti-x"></i></a>
                </div>
            @endif
        </form>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show"><i class="ti ti-check me-1"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>{{ __('Reference') }}</th>
                        <th>{{ __('Client') }}</th>
                        <th class="text-end">{{ __('Total') }}</th>
                        <th class="text-center">{{ __('Statut') }}</th>
                        <th>{{ __('Date') }}</th>
                        <th class="text-end">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                        @php [$label, $color] = $statuses[$order->status] ?? ['?', 'secondary']; @endphp
                        <tr>
                            <td class="fw-medium">{{ $order->reference }}</td>
                            <td>{{ $order->customer?->name ?? '—' }}</td>
                            <td class="text-end fw-bold">{{ number_format($order->total, 0, ',', ' ') }}</td>
                            <td class="text-center"><span class="badge bg-{{ $color }}">{{ __($label) }}</span></td>
                            <td class="small text-muted">{{ $order->created_at?->format('d/m/Y H:i') }}</td>
                            <td class="text-end">
                                <a href="{{ route('eshop360.channel-portal.online-orders.show', [$slug, $channel->slug ?? $channel->id, $order]) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="ti ti-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-4"><i class="ti ti-inbox fs-1 d-block mb-2"></i>{{ __('Aucune commande en ligne') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($orders->hasPages())
            <div class="p-3">{{ $orders->links() }}</div>
        @endif
    </div>
</div>
@endsection
