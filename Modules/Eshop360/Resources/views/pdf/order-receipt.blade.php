<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reçu {{ $order->reference }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 12px; color: #111; background: #fff; }

        /* Compact receipt — 80mm thermal printer friendly */
        .receipt { width: 300px; margin: 0 auto; padding: 16px 12px; }

        .receipt-header { text-align: center; margin-bottom: 12px; border-bottom: 2px dashed #333; padding-bottom: 10px; }
        .receipt-header .logo img { max-height: 55px; max-width: 200px; object-fit: contain; margin-bottom: 6px; }
        .receipt-header h2 { font-size: 16px; font-weight: 800; letter-spacing: 1px; }
        .receipt-header p { font-size: 10px; color: #555; margin-top: 2px; }

        .receipt-title { text-align: center; margin: 10px 0; }
        .receipt-title h3 { font-size: 14px; font-weight: 700; letter-spacing: 3px; text-transform: uppercase; border: 2px solid #111; display: inline-block; padding: 3px 14px; }

        .receipt-meta { margin-bottom: 10px; border-bottom: 1px dashed #888; padding-bottom: 8px; }
        .receipt-meta table { width: 100%; border: none; }
        .receipt-meta td { padding: 2px 0; font-size: 11px; }
        .receipt-meta td:last-child { text-align: right; font-weight: 600; }

        .items-header { display: flex; justify-content: space-between; font-weight: 700; font-size: 11px; border-bottom: 1px solid #333; padding-bottom: 4px; margin-bottom: 4px; }
        .item-row { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 5px; font-size: 11px; }
        .item-row .item-name { flex: 1; padding-right: 6px; }
        .item-row .item-qty { width: 40px; text-align: center; }
        .item-row .item-price { width: 60px; text-align: right; }
        .item-row .item-total { width: 65px; text-align: right; font-weight: 600; }

        .items-section { border-bottom: 1px dashed #888; padding-bottom: 8px; margin-bottom: 8px; }

        .totals-section { margin-bottom: 10px; border-bottom: 1px dashed #888; padding-bottom: 8px; }
        .totals-section table { width: 100%; border: none; }
        .totals-section td { padding: 2px 0; font-size: 11px; }
        .totals-section td:last-child { text-align: right; font-weight: 600; }
        .grand-total-row td { font-size: 14px; font-weight: 800; border-top: 2px solid #111; padding-top: 4px; }

        .payment-section { margin-bottom: 10px; font-size: 11px; }
        .payment-section .payment-label { color: #555; }
        .payment-section .payment-value { font-weight: 600; }

        .receipt-footer { text-align: center; font-size: 10px; color: #555; line-height: 1.7; border-top: 2px dashed #333; padding-top: 10px; }
        .receipt-footer .thank-you { font-size: 13px; font-weight: 700; color: #111; margin-bottom: 4px; letter-spacing: 1px; }

        @media print {
            body { print-color-adjust: exact; -webkit-print-color-adjust: exact; }
            .receipt { width: 100%; padding: 10px 6px; }
        }
    </style>
</head>
<body>
<div class="receipt">

    {{-- Header --}}
    <div class="receipt-header">
        @if(!empty($instance->settings['logo']))
            <div class="logo">
                <img src="{{ asset('storage/' . $instance->settings['logo']) }}" alt="{{ $instance->name }}">
            </div>
        @endif
        <h2>{{ $instance->name }}</h2>
        @if(!empty($instance->settings['address']))
            <p>{{ $instance->settings['address'] }}</p>
        @endif
        @if(!empty($instance->settings['phone']))
            <p>Tél : {{ $instance->settings['phone'] }}</p>
        @endif
    </div>

    {{-- Title --}}
    <div class="receipt-title">
        <h3>Reçu</h3>
    </div>

    {{-- Meta info --}}
    <div class="receipt-meta">
        <table>
            <tr>
                <td>N° Commande :</td>
                <td>{{ $order->reference }}</td>
            </tr>
            <tr>
                <td>Date :</td>
                <td>{{ $order->created_at?->format('d/m/Y H:i') }}</td>
            </tr>
            @if(!empty($order->cashier) || !empty($order->creator))
            <tr>
                <td>Caissier :</td>
                <td>{{ $order->cashier?->name ?? $order->creator?->name ?? '—' }}</td>
            </tr>
            @endif
            @if($order->customer)
            <tr>
                <td>Client :</td>
                <td>{{ $order->customer->name }}</td>
            </tr>
            @endif
            @if(!empty($order->store))
            <tr>
                <td>Point de vente :</td>
                <td>{{ $order->store->name }}</td>
            </tr>
            @endif
        </table>
    </div>

    {{-- Items --}}
    <div class="items-section">
        <div class="items-header">
            <span style="flex:1;">Article</span>
            <span style="width:40px;text-align:center;">Qté</span>
            <span style="width:60px;text-align:right;">P.U.</span>
            <span style="width:65px;text-align:right;">Total</span>
        </div>
        @forelse($order->items as $item)
        <div class="item-row">
            <div class="item-name">{{ $item->product?->name ?? $item->description ?? '—' }}</div>
            <div class="item-qty">{{ $item->quantity }}</div>
            <div class="item-price">{{ number_format($item->unit_price, 0, ',', ' ') }}</div>
            <div class="item-total">{{ number_format($item->total, 0, ',', ' ') }}</div>
        </div>
        @empty
        <p style="text-align:center;color:#888;padding:8px 0;">Aucun article</p>
        @endforelse
    </div>

    {{-- Totals --}}
    <div class="totals-section">
        <table>
            <tr>
                <td>Sous-total :</td>
                <td>{{ number_format($order->subtotal, 0, ',', ' ') }}</td>
            </tr>
            @if(($order->tax_amount ?? 0) > 0)
            <tr>
                <td>TVA :</td>
                <td>{{ number_format($order->tax_amount, 0, ',', ' ') }}</td>
            </tr>
            @endif
            @if(($order->discount_amount ?? 0) > 0)
            <tr>
                <td>Remise :</td>
                <td style="color:#dc2626;">-{{ number_format($order->discount_amount, 0, ',', ' ') }}</td>
            </tr>
            @endif
            <tr class="grand-total-row">
                <td>TOTAL :</td>
                <td>{{ number_format($order->total, 0, ',', ' ') }} {{ $instance->settings['currency'] ?? 'FCFA' }}</td>
            </tr>
            @if(($order->paid_amount ?? 0) > 0)
            <tr>
                <td>Montant reçu :</td>
                <td>{{ number_format($order->paid_amount, 0, ',', ' ') }}</td>
            </tr>
            @if($order->paid_amount > $order->total)
            <tr>
                <td>Monnaie rendue :</td>
                <td>{{ number_format($order->paid_amount - $order->total, 0, ',', ' ') }}</td>
            </tr>
            @endif
            @endif
        </table>
    </div>

    {{-- Payment method --}}
    <div class="payment-section">
        <span class="payment-label">Mode de paiement : </span>
        <span class="payment-value">{{ ucfirst($order->payment_method ?? 'Espèces') }}</span>
    </div>

    {{-- Footer --}}
    <div class="receipt-footer">
        <div class="thank-you">Merci de votre visite !</div>
        @if(!empty($instance->settings['website']))
            <p>{{ $instance->settings['website'] }}</p>
        @endif
        @if(!empty($instance->settings['email']))
            <p>{{ $instance->settings['email'] }}</p>
        @endif
        <p style="margin-top:6px;color:#888;">Ce reçu tient lieu de preuve d'achat.</p>
        <p style="margin-top:2px;color:#aaa;font-size:9px;">{{ now()->format('d/m/Y H:i:s') }}</p>
    </div>

</div>
</body>
</html>
