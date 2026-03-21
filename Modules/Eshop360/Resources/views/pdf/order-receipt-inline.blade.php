<style>
    .receipt-inline { width: 300px; margin: 0 auto; padding: 16px 12px; font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 12px; color: #111; }
    .receipt-inline .receipt-header { text-align: center; margin-bottom: 12px; border-bottom: 2px dashed #333; padding-bottom: 10px; }
    .receipt-inline .receipt-header h2 { font-size: 16px; font-weight: 800; letter-spacing: 1px; margin: 0; }
    .receipt-inline .receipt-header p { font-size: 10px; color: #555; margin: 2px 0 0; }
    .receipt-inline .receipt-title { text-align: center; margin: 10px 0; }
    .receipt-inline .receipt-title h3 { font-size: 14px; font-weight: 700; letter-spacing: 3px; text-transform: uppercase; border: 2px solid #111; display: inline-block; padding: 3px 14px; margin: 0; }
    .receipt-inline .receipt-meta { margin-bottom: 10px; border-bottom: 1px dashed #888; padding-bottom: 8px; }
    .receipt-inline .receipt-meta table { width: 100%; border: none; border-collapse: collapse; }
    .receipt-inline .receipt-meta td { padding: 2px 0; font-size: 11px; }
    .receipt-inline .receipt-meta td:last-child { text-align: right; font-weight: 600; }
    .receipt-inline .items-header { display: flex; justify-content: space-between; font-weight: 700; font-size: 11px; border-bottom: 1px solid #333; padding-bottom: 4px; margin-bottom: 4px; }
    .receipt-inline .item-row { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 5px; font-size: 11px; }
    .receipt-inline .item-name { flex: 1; padding-right: 6px; }
    .receipt-inline .item-qty { width: 40px; text-align: center; }
    .receipt-inline .item-price { width: 60px; text-align: right; }
    .receipt-inline .item-total { width: 65px; text-align: right; font-weight: 600; }
    .receipt-inline .items-section { border-bottom: 1px dashed #888; padding-bottom: 8px; margin-bottom: 8px; }
    .receipt-inline .totals-section { margin-bottom: 10px; border-bottom: 1px dashed #888; padding-bottom: 8px; }
    .receipt-inline .totals-section table { width: 100%; border: none; border-collapse: collapse; }
    .receipt-inline .totals-section td { padding: 2px 0; font-size: 11px; }
    .receipt-inline .totals-section td:last-child { text-align: right; font-weight: 600; }
    .receipt-inline .grand-total-row td { font-size: 14px; font-weight: 800; border-top: 2px solid #111; padding-top: 4px; }
    .receipt-inline .payment-section { margin-bottom: 10px; font-size: 11px; }
    .receipt-inline .payment-label { color: #555; }
    .receipt-inline .payment-value { font-weight: 600; }
    .receipt-inline .receipt-footer { text-align: center; font-size: 10px; color: #555; line-height: 1.7; border-top: 2px dashed #333; padding-top: 10px; }
    .receipt-inline .receipt-footer .thank-you { font-size: 13px; font-weight: 700; color: #111; margin-bottom: 4px; letter-spacing: 1px; }
</style>

<div class="receipt-inline">
    {{-- Header --}}
    <div class="receipt-header">
        @if(!empty($instance->settings['logo']))
            <div style="margin-bottom:6px;">
                <img src="{{ asset('storage/' . $instance->settings['logo']) }}" alt="{{ $instance->name }}" style="max-height:55px;max-width:200px;object-fit:contain;">
            </div>
        @endif
        <h2>{{ $instance->name }}</h2>
        @if(!empty($instance->settings['address']))
            <p>{{ $instance->settings['address'] }}</p>
        @endif
        @if(!empty($instance->settings['phone']))
            <p>{{ __('Tel :') }} {{ $instance->settings['phone'] }}</p>
        @endif
    </div>

    {{-- Title --}}
    <div class="receipt-title">
        <h3>{{ __('Recu') }}</h3>
    </div>

    {{-- Meta info --}}
    <div class="receipt-meta">
        <table>
            <tr>
                <td>{{ __('N Commande :') }}</td>
                <td>{{ $order->order_number ?? $order->reference ?? '—' }}</td>
            </tr>
            <tr>
                <td>{{ __('Date :') }}</td>
                <td>{{ $order->created_at?->format('d/m/Y H:i') }}</td>
            </tr>
            @if($order->customer)
            <tr>
                <td>{{ __('Client :') }}</td>
                <td>{{ $order->customer->name }}</td>
            </tr>
            @endif
            @if(!empty($order->store))
            <tr>
                <td>{{ __('Point de vente :') }}</td>
                <td>{{ $order->store->name }}</td>
            </tr>
            @endif
        </table>
    </div>

    {{-- Items --}}
    <div class="items-section">
        <div class="items-header">
            <span style="flex:1;">{{ __('Article') }}</span>
            <span style="width:40px;text-align:center;">{{ __('Qte') }}</span>
            <span style="width:60px;text-align:right;">{{ __('P.U.') }}</span>
            <span style="width:65px;text-align:right;">{{ __('Total') }}</span>
        </div>
        @forelse($order->items as $item)
        <div class="item-row">
            <div class="item-name">{{ $item->product?->name ?? $item->product_name ?? '—' }}</div>
            <div class="item-qty">{{ $item->quantity }}</div>
            <div class="item-price">{{ number_format($item->unit_price, 0, ',', ' ') }}</div>
            <div class="item-total">{{ number_format($item->total, 0, ',', ' ') }}</div>
        </div>
        @empty
        <p style="text-align:center;color:#888;padding:8px 0;">{{ __('Aucun article') }}</p>
        @endforelse
    </div>

    {{-- Totals --}}
    <div class="totals-section">
        <table>
            <tr>
                <td>{{ __('Sous-total :') }}</td>
                <td>{{ number_format($order->subtotal, 0, ',', ' ') }}</td>
            </tr>
            @if(($order->tax_amount ?? 0) > 0)
            <tr>
                <td>{{ __('TVA :') }}</td>
                <td>{{ number_format($order->tax_amount, 0, ',', ' ') }}</td>
            </tr>
            @endif
            @if(($order->discount_amount ?? 0) > 0)
            <tr>
                <td>{{ __('Remise :') }}</td>
                <td style="color:#dc2626;">-{{ number_format($order->discount_amount, 0, ',', ' ') }}</td>
            </tr>
            @endif
            <tr class="grand-total-row">
                <td>{{ __('TOTAL :') }}</td>
                <td>{{ number_format($order->total, 0, ',', ' ') }} {{ $instance->settings['currency'] ?? 'FCFA' }}</td>
            </tr>
            @if(($order->paid_amount ?? 0) > 0)
            <tr>
                <td>{{ __('Montant recu :') }}</td>
                <td>{{ number_format($order->paid_amount, 0, ',', ' ') }}</td>
            </tr>
            @if($order->paid_amount > $order->total)
            <tr>
                <td>{{ __('Monnaie rendue :') }}</td>
                <td>{{ number_format($order->paid_amount - $order->total, 0, ',', ' ') }}</td>
            </tr>
            @endif
            @endif
        </table>
    </div>

    {{-- Payment method --}}
    <div class="payment-section">
        <span class="payment-label">{{ __('Mode de paiement :') }}</span>
        <span class="payment-value">{{ ucfirst($order->payment_method ?? 'Especes') }}</span>
    </div>

    {{-- Footer --}}
    <div class="receipt-footer">
        <div class="thank-you">{{ __('Merci de votre visite !') }}</div>
        @if(!empty($instance->settings['website']))
            <p>{{ $instance->settings['website'] }}</p>
        @endif
        @if(!empty($instance->settings['email']))
            <p>{{ $instance->settings['email'] }}</p>
        @endif
        <p style="margin-top:6px;color:#888;">{{ __('Ce recu tient lieu de preuve d\'achat.') }}</p>
        <p style="margin-top:2px;color:#aaa;font-size:9px;">{{ now()->format('d/m/Y H:i:s') }}</p>
    </div>
</div>
