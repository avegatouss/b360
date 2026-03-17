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
        <h4 class="fw-bold mb-1">Commande {{ $order->order_number ?? ('ORD-' . $order->id) }}</h4>
        <p class="text-muted mb-0">
            <span class="badge bg-{{ $statusColors[$order->status] ?? 'secondary' }}">
                {{ $statusLabels[$order->status] ?? $order->status }}
            </span>
            &mdash; {{ $order->created_at->format('d/m/Y H:i') }}
        </p>
    </div>
    <div class="d-flex gap-2">
        @if($order->status === 'pending' || $order->status === 'processing')
        <form method="POST" action="{{ route('eshop360.channel-portal.orders.confirm-reception', [$slug, $channel->slug ?? $channel->id, $order->id]) }}">
            @csrf
            <button type="submit" class="btn btn-success" onclick='return confirm(@js(__('Confirmer la reception de cette commande ?')))'>
                <i class="ti ti-check me-1"></i> Confirmer la reception
            </button>
        </form>
        @endif
        <a href="{{ route('eshop360.channel-portal.orders.index', [$slug, $channel->slug ?? $channel->id]) }}" class="btn btn-outline-secondary">
            <i class="ti ti-arrow-left me-1"></i> Retour
        </a>
    </div>
</div>

<div class="row g-4">
    {{-- Order Info --}}
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h6 class="card-title mb-0">{{ __('Articles de la commande') }}</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>{{ __('Produit') }}</th>
                                <th class="text-end">{{ __('Prix unitaire') }}</th>
                                <th class="text-center">{{ __('Quantite') }}</th>
                                <th class="text-end">{{ __('Total') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($order->items as $item)
                            <tr>
                                <td>
                                    <div class="fw-medium">{{ $item->product->name ?? __('Produit supprime') }}</div>
                                    <small class="text-muted">{{ $item->product->sku ?? '' }}</small>
                                </td>
                                <td class="text-end">{{ number_format($item->price, 0, ',', ' ') }} XAF</td>
                                <td class="text-center">{{ $item->quantity }}</td>
                                <td class="text-end fw-bold">{{ number_format($item->total, 0, ',', ' ') }} XAF</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <td colspan="3" class="text-end fw-bold">{{ __('Sous-total') }}</td>
                                <td class="text-end fw-bold">{{ number_format($order->subtotal, 0, ',', ' ') }} XAF</td>
                            </tr>
                            @if($order->tax_amount > 0)
                            <tr>
                                <td colspan="3" class="text-end">{{ __('Taxes') }}</td>
                                <td class="text-end">{{ number_format($order->tax_amount, 0, ',', ' ') }} XAF</td>
                            </tr>
                            @endif
                            @if($order->discount_amount > 0)
                            <tr>
                                <td colspan="3" class="text-end">{{ __('Remise') }}</td>
                                <td class="text-end text-danger">-{{ number_format($order->discount_amount, 0, ',', ' ') }} XAF</td>
                            </tr>
                            @endif
                            <tr>
                                <td colspan="3" class="text-end fw-bold fs-5">{{ __('Total') }}</td>
                                <td class="text-end fw-bold fs-5">{{ number_format($order->total, 0, ',', ' ') }} XAF</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Side Info --}}
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="card-title mb-0">{{ __('Informations') }}</h6>
            </div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-5 text-muted">{{ __('Client') }}</dt>
                    <dd class="col-7">{{ $order->customer->name ?? __('Non renseigne') }}</dd>

                    <dt class="col-5 text-muted">{{ __('Paiement') }}</dt>
                    <dd class="col-7">
                        @php
                            $payColors = ['paid' => 'success', 'partial' => 'warning', 'unpaid' => 'danger'];
                            $payLabels = ['paid' => 'Paye', 'partial' => 'Partiel', 'unpaid' => 'Non paye'];
                        @endphp
                        <span class="badge bg-{{ $payColors[$order->payment_status] ?? 'secondary' }}">
                            {{ $payLabels[$order->payment_status] ?? $order->payment_status }}
                        </span>
                    </dd>

                    <dt class="col-5 text-muted">{{ __('Montant paye') }}</dt>
                    <dd class="col-7">{{ number_format($order->paid_amount, 0, ',', ' ') }} XAF</dd>

                    <dt class="col-5 text-muted">{{ __('Reste du') }}</dt>
                    <dd class="col-7">{{ number_format($order->due_amount, 0, ',', ' ') }} XAF</dd>

                    <dt class="col-5 text-muted">{{ __('Source') }}</dt>
                    <dd class="col-7">{{ $order->source ?? '—' }}</dd>
                </dl>
            </div>
        </div>

        @if($order->notes)
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h6 class="card-title mb-0">{{ __('Notes') }}</h6>
            </div>
            <div class="card-body">
                <p class="mb-0">{{ $order->notes }}</p>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
