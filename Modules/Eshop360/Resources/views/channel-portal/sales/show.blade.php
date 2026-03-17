@extends('eshop360::channel-portal.layouts.master')

@section('content')
@php
    $instance = \Modules\Core\Support\CurrentInstance::get();
    $slug = $instance->slug ?? '';
@endphp

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="fw-bold mb-1">Vente {{ $order->order_number ?? ('ORD-' . $order->id) }}</h4>
        <p class="text-muted mb-0">
            <span class="badge bg-success">{{ __('Terminee') }}</span>
            &mdash; {{ $order->created_at->format('d/m/Y H:i') }}
        </p>
    </div>
    <a href="{{ route('eshop360.channel-portal.sales.index', [$slug, $channel->slug ?? $channel->id]) }}" class="btn btn-outline-secondary">
        <i class="ti ti-arrow-left me-1"></i> Retour
    </a>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        {{-- Items --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h6 class="card-title mb-0">{{ __('Articles vendus') }}</h6>
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
                                <td colspan="3" class="text-end fw-bold fs-5">{{ __('Total') }}</td>
                                <td class="text-end fw-bold fs-5">{{ number_format($order->total, 0, ',', ' ') }} XAF</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        {{-- Client Info --}}
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="card-title mb-0">{{ __('Client') }}</h6>
            </div>
            <div class="card-body">
                @if($order->customer)
                    <p class="fw-medium mb-1">{{ $order->customer->name }}</p>
                    @if($order->customer->phone)
                        <p class="text-muted mb-1"><i class="ti ti-phone me-1"></i> {{ $order->customer->phone }}</p>
                    @endif
                    @if($order->customer->email)
                        <p class="text-muted mb-0"><i class="ti ti-mail me-1"></i> {{ $order->customer->email }}</p>
                    @endif
                @else
                    <p class="text-muted mb-0">{{ __('Client non renseigne') }}</p>
                @endif
            </div>
        </div>

        {{-- Margin Info --}}
        @if($order->channelMarginLogs->count() > 0)
        @php $marginLog = $order->channelMarginLogs->first(); @endphp
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h6 class="card-title mb-0">{{ __('Repartition des marges') }}</h6>
            </div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-6 text-muted">{{ __('Marge totale') }}</dt>
                    <dd class="col-6 fw-bold text-primary">{{ number_format($marginLog->total_margin, 0, ',', ' ') }} XAF</dd>

                    <dt class="col-6 text-muted">{{ __('Part canal') }}</dt>
                    <dd class="col-6 fw-bold text-success">{{ number_format($marginLog->channel_part, 0, ',', ' ') }} XAF</dd>

                    <dt class="col-6 text-muted">{{ __('Part proprietaire') }}</dt>
                    <dd class="col-6 fw-bold text-info">{{ number_format($marginLog->owner_part, 0, ',', ' ') }} XAF</dd>

                    <dt class="col-6 text-muted">{{ __('Part dette') }}</dt>
                    <dd class="col-6 fw-bold text-danger">{{ number_format($marginLog->debt_part, 0, ',', ' ') }} XAF</dd>
                </dl>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
