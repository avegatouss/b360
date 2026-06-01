@extends('eshop360::portal._print-layout')

@section('doc-title'){{ __('Facture') }} {{ $order->order_number ?? 'ORD-' . $order->id }}@endsection

@section('doc-info')
    <tr><td class="text-muted">{{ __('Reference') }}</td><td class="fw-bold">{{ $order->order_number ?? 'ORD-' . $order->id }}</td></tr>
    <tr><td class="text-muted">{{ __('Date') }}</td><td>{{ $order->created_at->format('d/m/Y H:i') }}</td></tr>
    <tr><td class="text-muted">{{ __('Statut') }}</td><td>{{ ucfirst($order->status) }}</td></tr>
    <tr><td class="text-muted">{{ __('Paiement') }}</td><td>{{ ucfirst($order->payment_status) }} ({{ ucfirst(str_replace('_', ' ', $order->payment_method ?? '—')) }})</td></tr>
    @if($order->store)<tr><td class="text-muted">{{ __('Magasin') }}</td><td>{{ $order->store->name }}</td></tr>@endif
@endsection

@section('doc-items')
    @foreach($order->items as $item)
        <tr>
            <td>{{ $item->product_name ?? $item->product?->name ?? '—' }}<br><small class="text-muted">{{ $item->sku ?? '' }}</small></td>
            <td class="text-center">{{ $item->quantity }}</td>
            <td class="text-end">{{ number_format((float)$item->unit_price, 0, ',', ' ') }}</td>
            <td class="text-end">{{ $item->discount > 0 ? number_format((float)$item->discount, 0, ',', ' ') : '—' }}</td>
            <td class="text-end">{{ number_format((float)$item->tax, 0, ',', ' ') }}</td>
            <td class="text-end fw-bold">{{ number_format((float)$item->total, 0, ',', ' ') }}</td>
        </tr>
    @endforeach
@endsection

@section('doc-totals')
    <tr><td class="text-muted">{{ __('Sous-total') }}</td><td class="text-end">{{ number_format((float)$order->subtotal, 0, ',', ' ') }}</td></tr>
    @if($order->tax_amount > 0)<tr><td class="text-muted">{{ __('Taxes') }}</td><td class="text-end">{{ number_format((float)$order->tax_amount, 0, ',', ' ') }}</td></tr>@endif
    @if($order->discount_amount > 0)<tr><td class="text-muted">{{ __('Remise') }}</td><td class="text-end text-danger">-{{ number_format((float)$order->discount_amount, 0, ',', ' ') }}</td></tr>@endif
    <tr class="border-top"><td class="fw-bold fs-5">{{ __('Total') }}</td><td class="text-end fw-bold fs-5">{{ number_format((float)$order->total, 0, ',', ' ') }} {{ $settings['currency_symbol'] ?? 'FCFA' }}</td></tr>
    <tr><td class="text-muted">{{ __('Paye') }}</td><td class="text-end text-success fw-bold">{{ number_format((float)$order->paid_amount, 0, ',', ' ') }}</td></tr>
    @if($order->due_amount > 0)<tr><td class="text-muted">{{ __('Restant du') }}</td><td class="text-end text-danger fw-bold">{{ number_format((float)$order->due_amount, 0, ',', ' ') }}</td></tr>@endif
@endsection
