<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Facture {{ $invoice->reference }}</title>
    <style>
        __BLADE_BLOCK_54__
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 10px; color: #333; background: #fff; }
        .page { padding: 25px; max-width: 800px; margin: 0 auto; }

        .header { display: table; width: 100%; margin-bottom: 15px; }
        .header-left { display: table-cell; width: 50%; vertical-align: top; }
        .header-right { display: table-cell; width: 50%; vertical-align: top; text-align: right; }
        .header-left img { max-height: 45px; }
        .header-left .name { font-size: 16px; font-weight: 700; color: __BLADE_BLOCK_1__; }

        .title-line { background: __BLADE_BLOCK_2__; color: #fff; padding: 6px 12px; margin-bottom: 12px; display: table; width: 100%; }
        .title-line h1 { display: table-cell; font-size: 16px; letter-spacing: 2px; }
        .title-line .ref { display: table-cell; text-align: right; font-size: 11px; padding-top: 3px; }

        .meta { display: table; width: 100%; margin-bottom: 12px; }
        .meta-col { display: table-cell; width: 33%; vertical-align: top; padding: 6px; font-size: 10px; line-height: 1.5; }
        .meta-col h4 { font-size: 9px; text-transform: uppercase; color: __BLADE_BLOCK_3__; letter-spacing: 0.5px; margin-bottom: 3px; }

        table.items { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        table.items thead th { background: __BLADE_BLOCK_4__; color: #fff; padding: 5px 6px; font-size: 9px; text-align: left; }
        table.items tbody td { padding: 4px 6px; border-bottom: 1px solid #eee; font-size: 10px; }
        table.items tbody tr:nth-child(even) { background: #f8fafc; }

        .text-right { text-align: right; }
        .text-center { text-align: center; }

        .totals { float: right; width: 220px; margin-bottom: 10px; }
        .totals td { padding: 3px 6px; font-size: 10px; border-bottom: 1px solid #eee; }
        .totals .grand { background: __BLADE_BLOCK_5__; color: #fff; font-weight: 700; }

        .clearfix::after { content: ''; display: table; clear: both; }
        .footer { clear: both; border-top: 1px solid #ddd; padding-top: 8px; text-align: center; color: #888; font-size: 9px; line-height: 1.6; }

        @media print { body { print-color-adjust: exact; -webkit-print-color-adjust: exact; } }
    </style>
</head>
<body>
<div class="page">
    <div class="header">
        <div class="header-left">
            @if(!empty($settings['logo_url']))
                <img src="{{ $settings['logo_url'] }}" alt="">
            @else
                <div class="name">{{ $company['name'] ?? '' }}</div>
            @endif
        </div>
        <div class="header-right" style="font-size:10px;line-height:1.4;color:#555;">
            {{ $company['name'] ?? '' }}<br>
            {{ $company['address'] ?? '' }}<br>
            {{ $company['phone'] ?? '' }} {{ !empty($company['email']) ? '| ' . $company['email'] : '' }}
        </div>
    </div>

    <div class="title-line">
        <h1>{{ __('FACTURE') }}</h1>
        <div class="ref">{{ $invoice->reference }} | {{ $invoice->created_at?->format('d/m/Y') }}@if($invoice->due_date) | Ech. {{ $invoice->due_date->format('d/m/Y') }}@endif</div>
    </div>

    <div class="meta">
        <div class="meta-col">
            <h4>{{ __('Client') }}</h4>
            <strong>{{ $invoice->customer->name ?? __('Comptoir') }}</strong><br>
            {{ $invoice->customer?->address ?? '' }}<br>
            {{ $invoice->customer?->phone ?? '' }}
        </div>
        <div class="meta-col">
            <h4>{{ __('Statut') }}</h4>
            {{ ucfirst($invoice->status ?? 'pending') }}
        </div>
        <div class="meta-col text-right">
            <h4>{{ __('Montant Total') }}</h4>
            <span style="font-size:14px;font-weight:700;color:{{ $primaryColor }};">{{ number_format($invoice->total, 2, ',', ' ') }} {{ $settings['currency'] ?? __('FCFA') }}</span>
        </div>
    </div>

    <table class="items">
        <thead>
            <tr>
                <th style="background-color:#1e40af; color:#fff; padding:10px 12px;">#</th><th style="background-color:#1e40af; color:#fff; padding:10px 12px;">{{ __('Designation') }}</th><th class="text-center">{{ __('Qte') }}</th><th class="text-right">{{ __('P.U.') }}</th><th class="text-right">{{ __('TVA') }}</th><th class="text-right">{{ __('Total') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($items as $i => $item)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $item->product?->name ?? $item->description ?? '-' }}</td>
                <td class="text-center">{{ $item->quantity }}</td>
                <td class="text-right">{{ number_format($item->unit_price, 2, ',', ' ') }}</td>
                <td class="text-right">{{ $item->tax_rate ?? 0 }}%</td>
                <td class="text-right">{{ number_format($item->total, 2, ',', ' ') }}</td>
            </tr>
            @empty
            <tr><td colspan="6" class="text-center" style="padding:10px;color:#999;">{{ __('Aucun article') }}</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="clearfix">
        <div class="totals">
            <table style="width:100%;border-collapse:collapse;">
                <tr><td>{{ __('Sous-total') }}</td><td class="text-right">{{ number_format($invoice->subtotal, 2, ',', ' ') }}</td></tr>
                @if(($invoice->tax_amount ?? 0) > 0)<tr><td>{{ __('TVA') }}</td><td class="text-right">{{ number_format($invoice->tax_amount, 2, ',', ' ') }}</td></tr>@endif
                @if(($invoice->discount_amount ?? 0) > 0)<tr><td>{{ __('Remise') }}</td><td class="text-right">-{{ number_format($invoice->discount_amount, 2, ',', ' ') }}</td></tr>@endif
                <tr class="grand"><td>{{ __('TOTAL') }}</td><td class="text-right">{{ number_format($invoice->total, 2, ',', ' ') }}</td></tr>
            </table>
        </div>
    </div>

    @if(!empty($invoice->notes))<p style="clear:both;margin:8px 0;font-size:10px;color:#666;"><strong>{{ __('Notes :') }}</strong> {{ $invoice->notes }}</p>@endif

    <div class="footer">
        {{ $company['name'] ?? '' }} @if(!empty($company['address'])) | {{ $company['address'] }} @endif @if(!empty($company['phone'])) | {{ $company['phone'] }} @endif
    </div>
</div>
</body>
</html>
