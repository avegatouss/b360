@extends('eshop360::portal._print-layout')

@section('doc-title'){{ __('Bon de commande') }} {{ $onlineOrder->reference }}@endsection

@section('doc-info')
    <tr><td class="text-muted">{{ __('Reference') }}</td><td class="fw-bold">{{ $onlineOrder->reference }}</td></tr>
    <tr><td class="text-muted">{{ __('Date') }}</td><td>{{ $onlineOrder->created_at->format('d/m/Y H:i') }}</td></tr>
    <tr><td class="text-muted">{{ __('Statut') }}</td><td>{{ ucfirst(str_replace('_', ' ', $onlineOrder->status)) }}</td></tr>
    @if($onlineOrder->channel)<tr><td class="text-muted">{{ __('Canal') }}</td><td>{{ $onlineOrder->channel->name }}</td></tr>@endif
    @if($onlineOrder->delivery_address)<tr><td class="text-muted">{{ __('Livraison') }}</td><td>{{ $onlineOrder->delivery_address }}</td></tr>@endif
    @if($onlineOrder->confirmed_at)<tr><td class="text-muted">{{ __('Confirme le') }}</td><td>{{ $onlineOrder->confirmed_at->format('d/m/Y') }}</td></tr>@endif
    @if($onlineOrder->delivered_at)<tr><td class="text-muted">{{ __('Livre le') }}</td><td>{{ $onlineOrder->delivered_at->format('d/m/Y') }}</td></tr>@endif
    @if($onlineOrder->received_at)<tr><td class="text-muted">{{ __('Recu le') }}</td><td>{{ $onlineOrder->received_at->format('d/m/Y') }}</td></tr>@endif
@endsection

@section('doc-items')
    @foreach($onlineOrder->items as $item)
        <tr>
            <td>{{ $item->product?->name ?? 'Produit' }}<br><small class="text-muted">{{ $item->product?->sku ?? '' }}</small></td>
            <td class="text-center">{{ $item->quantity }}</td>
            <td class="text-end">{{ number_format((float)$item->unit_price, 0, ',', ' ') }}</td>
            <td class="text-end">—</td>
            <td class="text-end">—</td>
            <td class="text-end fw-bold">{{ number_format((float)$item->total, 0, ',', ' ') }}</td>
        </tr>
    @endforeach
@endsection

@section('doc-totals')
    <tr><td class="text-muted">{{ __('Sous-total') }}</td><td class="text-end">{{ number_format((float)$onlineOrder->subtotal, 0, ',', ' ') }}</td></tr>
    @if($onlineOrder->tax_amount > 0)<tr><td class="text-muted">{{ __('Taxes') }}</td><td class="text-end">{{ number_format((float)$onlineOrder->tax_amount, 0, ',', ' ') }}</td></tr>@endif
    <tr class="border-top"><td class="fw-bold fs-5">{{ __('Total') }}</td><td class="text-end fw-bold fs-5">{{ number_format((float)$onlineOrder->total, 0, ',', ' ') }} {{ $settings['currency_symbol'] ?? 'FCFA' }}</td></tr>
@endsection
