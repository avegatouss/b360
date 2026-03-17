<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Preview: {{ $template->name }}</title>
    <style>
        __BLADE_BLOCK_41__

        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Courier New', monospace; font-size: __BLADE_BLOCK_1__; color: #111; background: #eee; padding: 20px; }
        .preview-wrapper { text-align: center; }
        .preview-label { font-family: Arial, sans-serif; font-size: 14px; color: #666; margin-bottom: 10px; }

        .receipt { width: __BLADE_BLOCK_2__; margin: 0 auto; padding: 14px 10px; background: #fff; box-shadow: 0 2px 10px rgba(0,0,0,0.15); }

        .receipt-header { text-align: center; margin-bottom: 10px; border-bottom: 2px dashed #333; padding-bottom: 8px; }
        .receipt-header h2 { font-size: 16px; font-weight: 800; }
        .receipt-header p { font-size: 10px; color: #555; margin-top: 2px; }

        .receipt-title { text-align: center; margin: 8px 0; }
        .receipt-title h3 { font-size: 13px; font-weight: 700; border: 1px solid #111; display: inline-block; padding: 2px 12px; letter-spacing: 2px; text-transform: uppercase; }

        .meta td { padding: 2px 0; font-size: 11px; }
        .meta td:last-child { text-align: right; font-weight: 600; }

        .items-section { border-top: 1px dashed #888; border-bottom: 1px dashed #888; padding: 6px 0; margin: 6px 0; }
        .item-row { display: flex; justify-content: space-between; font-size: 11px; margin-bottom: 3px; }
        .item-row .name { flex: 1; }
        .item-row .qty { width: 35px; text-align: center; }
        .item-row .total { width: 55px; text-align: right; font-weight: 600; }

        .totals td { padding: 2px 0; font-size: 11px; }
        .totals td:last-child { text-align: right; font-weight: 600; }
        .grand td { font-size: 14px; font-weight: 800; border-top: 2px solid #111; padding-top: 4px; }

        .receipt-footer { text-align: center; font-size: 10px; color: #555; border-top: 2px dashed #333; padding-top: 8px; margin-top: 8px; }
        .receipt-footer .thanks { font-size: 12px; font-weight: 700; color: #111; }

        .no-print { margin-top: 15px; }
        @media print { .no-print { display: none; } body { background: #fff; padding: 0; } .receipt { box-shadow: none; } }
    </style>
</head>
<body>
<div class="preview-wrapper">
    <div class="preview-label no-print">
        Preview: <strong>{{ $template->name }}</strong> ({{ $template->paper_width }}, {{ $template->font_size }})
    </div>

    <div class="receipt">
        {{-- Header --}}
        <div class="receipt-header">
            @if($template->show_logo && !empty($instance->settings['logo']))
                <img src="{{ asset('storage/' . $instance->settings['logo']) }}" alt="" style="max-height:50px;max-width:180px;margin-bottom:4px;">
            @endif
            <h2>{{ $instance->name ?? __('Ma Boutique') }}</h2>
            @if($template->show_address && !empty($instance->settings['address']))
                <p>{{ $instance->settings['address'] }}</p>
            @endif
            @if($template->show_phone && !empty($instance->settings['phone']))
                <p>Tel : {{ $instance->settings['phone'] }}</p>
            @endif
            @if($template->header_text)
                <p style="margin-top:4px;">{{ $template->header_text }}</p>
            @endif
        </div>

        <div class="receipt-title"><h3>{{ __('Recu') }}</h3></div>

        {{-- Meta --}}
        <table class="meta" style="width:100%;border:none;margin-bottom:6px;">
            <tr><td>{{ __('N° Commande :') }}</td><td>{{ $sampleOrder->reference }}</td></tr>
            <tr><td>{{ __('Date :') }}</td><td>{{ $sampleOrder->created_at->format('d/m/Y H:i') }}</td></tr>
            <tr><td>{{ __('Client :') }}</td><td>{{ $sampleOrder->customer->name }}</td></tr>
        </table>

        {{-- Items --}}
        <div class="items-section">
            @foreach($sampleOrder->items as $item)
            <div class="item-row">
                <span class="name">{{ $item->product->name }}</span>
                <span class="qty">x{{ $item->quantity }}</span>
                <span class="total">{{ number_format($item->total, 0, ',', ' ') }}</span>
            </div>
            @endforeach
        </div>

        {{-- Totals --}}
        <table class="totals" style="width:100%;border:none;">
            <tr><td>{{ __('Sous-total :') }}</td><td>{{ number_format($sampleOrder->subtotal, 0, ',', ' ') }}</td></tr>
            @if($sampleOrder->discount_amount > 0)
            <tr><td>{{ __('Remise :') }}</td><td style="color:#c00;">-{{ number_format($sampleOrder->discount_amount, 0, ',', ' ') }}</td></tr>
            @endif
            <tr class="grand"><td>{{ __('TOTAL :') }}</td><td>{{ number_format($sampleOrder->total, 0, ',', ' ') }} {{ $instance->settings['currency'] ?? __('FCFA') }}</td></tr>
            <tr><td>{{ __('Paye :') }}</td><td>{{ number_format($sampleOrder->paid_amount, 0, ',', ' ') }}</td></tr>
        </table>

        <p style="font-size:11px;margin:4px 0;">Mode : {{ $sampleOrder->payment_method }}</p>

        {{-- Footer --}}
        <div class="receipt-footer">
            @if($template->footer_text)
                <p>{{ $template->footer_text }}</p>
            @else
                <div class="thanks">{{ __('Merci de votre visite !') }}</div>
            @endif
            <p style="margin-top:4px;color:#aaa;font-size:9px;">{{ now()->format('d/m/Y H:i:s') }}</p>
        </div>
    </div>

    <div class="no-print" style="margin-top:15px;">
        <button onclick="window.print()" style="padding:8px 20px;background:#4f46e5;color:#fff;border:none;border-radius:4px;cursor:pointer;">{{ __('Print Preview') }}</button>
        <button onclick="window.close()" style="padding:8px 20px;background:#6b7280;color:#fff;border:none;border-radius:4px;cursor:pointer;margin-left:8px;">{{ __('Close') }}</button>
    </div>
</div>
</body>
</html>
