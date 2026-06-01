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
        <h4 class="fw-bold mb-1">{{ $customer->name }}</h4>
        <p class="text-muted mb-0">Fiche client &mdash; Canal {{ $channel->name }}</p>
    </div>
    <a href="{{ route('eshop360.channel-portal.customers.index', [$slug, $channel->slug ?? $channel->id]) }}" class="btn btn-outline-secondary">
        <i class="ti ti-arrow-left me-1"></i> Retour
    </a>
</div>

<div class="row g-4">
    {{-- Customer Info --}}
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h6 class="card-title mb-0">{{ __('Informations') }}</h6>
            </div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-5 text-muted">{{ __('Nom') }}</dt>
                    <dd class="col-7">{{ $customer->name }}</dd>

                    <dt class="col-5 text-muted">{{ __('Email') }}</dt>
                    <dd class="col-7">{{ $customer->email ?? '—' }}</dd>

                    <dt class="col-5 text-muted">{{ __('Telephone') }}</dt>
                    <dd class="col-7">{{ $customer->phone ?? '—' }}</dd>

                    <dt class="col-5 text-muted">{{ __('Adresse') }}</dt>
                    <dd class="col-7">{{ $customer->address ?? '—' }}</dd>

                    <dt class="col-5 text-muted">{{ __('Ville') }}</dt>
                    <dd class="col-7">{{ $customer->city ?? '—' }}</dd>
                </dl>
            </div>
        </div>

        <div class="card border-0 shadow-sm mt-3">
            <div class="card-body text-center">
                <h6 class="text-muted small">{{ __('Total depense sur ce canal') }}</h6>
                <h3 class="fw-bold text-primary mb-0">{{ number_format($totalSpent ?? 0, 0, ',', ' ') }} {{ $eshopCurrency ?? 'FCFA' }}</h3>
            </div>
        </div>
    </div>

    {{-- Order History --}}
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h6 class="card-title mb-0">{{ __('Historique des commandes') }}</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>{{ __('N° commande') }}</th>
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
                                <td class="fw-bold">{{ number_format($order->total, 0, ',', ' ') }} {{ $eshopCurrency ?? 'FCFA' }}</td>
                                <td>
                                    <span class="badge bg-{{ $statusColors[$order->status] ?? 'secondary' }}">
                                        {{ $statusLabels[$order->status] ?? $order->status }}
                                    </span>
                                </td>
                                <td>{{ $order->created_at->format('d/m/Y') }}</td>
                                <td>
                                    <a href="{{ route('eshop360.channel-portal.orders.show', [$slug, $channel->slug ?? $channel->id, $order->id]) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="ti ti-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">{{ __('Aucune commande pour ce client.') }}</td>
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
    </div>
</div>
@endsection
