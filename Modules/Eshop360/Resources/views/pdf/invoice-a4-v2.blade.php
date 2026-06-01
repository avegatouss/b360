<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Facture {{ $invoice->reference }}</title>
    <style>
        __BLADE_BLOCK_58__
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 12px; color: #333; background: #fff; }
        .page { padding: 40px; max-width: 800px; margin: 0 auto; }

        .header-bar { background: __BLADE_BLOCK_1__; color: #fff; padding: 20px 30px; margin: -40px -40px 30px; }
        .header-bar .row { display: table; width: 100%; }
        .header-bar .col { display: table-cell; vertical-align: middle; }
        .header-bar .col-right { text-align: right; }
        .header-bar img { max-height: 50px; }
        .header-bar h1 { font-size: 24px; letter-spacing: 3px; margin: 0; }
        .header-bar .ref { font-size: 13px; opacity: 0.85; }

        .meta-row { display: table; width: 100%; margin-bottom: 25px; }
        .meta-col { display: table-cell; width: 50%; vertical-align: top; padding: 12px; background: #f0fdfa; border: 1px solid #99f6e4; }
        .meta-col:first-child { border-right: none; }
        .meta-col h4 { font-size: 10px; text-transform: uppercase; color: __BLADE_BLOCK_2__; letter-spacing: 1px; margin-bottom: 6px; }
        .meta-col p { margin: 2px 0; line-height: 1.6; }

        table.items { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        table.items thead th { background: __BLADE_BLOCK_3__; color: #fff; padding: 9px 10px; text-align: left; font-size: 11px; }
        table.items tbody td { padding: 8px 10px; border-bottom: 1px solid #e2e8f0; }
        table.items tbody tr:nth-child(even) { background: #f0fdfa; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }

        .totals-box { float: right; width: 280px; margin-bottom: 20px; }
        .totals-box table { width: 100%; border-collapse: collapse; }
        .totals-box td { padding: 6px 10px; border-bottom: 1px solid #e2e8f0; }
        .totals-box .grand { background: __BLADE_BLOCK_4__; color: #fff; font-weight: 700; font-size: 13px; }

        .clearfix::after { content: ''; display: table; clear: both; }

        .notes { clear: both; margin-bottom: 15px; padding: 10px; background: #fffbeb; border-left: 3px solid #f59e0b; font-size: 11px; }
        .terms { margin-bottom: 15px; padding: 10px; background: #f0fdf4; border-left: 3px solid #22c55e; font-size: 11px; }

        .footer { border-top: 2px solid __BLADE_BLOCK_5__; padding-top: 12px; text-align: center; color: #64748b; font-size: 10px; line-height: 1.8; margin-top: 30px; }

        .badge { display: inline-block; padding: 2px 8px; border-radius: 10px; font-size: 10px; font-weight: 600; }
        .badge-paid { background: #dcfce7; color: #166534; }
        .badge-pending { background: #fef9c3; color: #854d0e; }
        .badge-overdue { background: #fee2e2; color: #991b1b; }
        .badge-partial { background: #dbeafe; color: #1e40af; }

        @media print { body { print-color-adjust: exact; -webkit-print-color-adjust: exact; } .page { padding: 0; } .header-bar { margin: 0 0 20px; } }
    </style>
</head>
<body>
<div class="page">
    <div class="header-bar">
        <div class="row">
            <div class="col">
                @if(!empty($settings['logo_url']))
                    <img src="{{ $settings['logo_url'] }}" alt="">
                @else
                    <h1>{{ $company['name'] ?? __('Entreprise') }}</h1>
                @endif
            </div>
            <div class="col col-right">
                <h1>{{ __('FACTURE') }}</h1>
                <div class="ref">{{ $invoice->reference }} | {{ $invoice->created_at?->format('d/m/Y') }}</div>
            </div>
        </div>
    </div>

    <div class="meta-row">
        <div class="meta-col">
            <h4>{{ __('De') }}</h4>
            <p><strong>{{ $company['name'] ?? '' }}</strong></p>
            @if(!empty($company['address']))<p>{{ $company['address'] }}</p>@endif
            @if(!empty($company['phone']))<p>Tel : {{ $company['phone'] }}</p>@endif
            @if(!empty($company['email']))<p>{{ $company['email'] }}</p>@endif
        </div>
        <div class="meta-col">
            <h4>{{ __('Facture a') }}</h4>
            <p><strong>{{ $invoice->customer->name ?? __('Client comptoir') }}</strong></p>
            @if($invoice->customer?->address)<p>{{ $invoice->customer->address }}</p>@endif
            @if($invoice->customer?->phone)<p>Tel : {{ $invoice->customer->phone }}</p>@endif
            @if($invoice->customer?->email)<p>{{ $invoice->customer->email }}</p>@endif
        </div>
    </div>

    <table class="items">
        <thead>
            <tr>
                <th style="background-color:#1e40af; color:#fff; padding:10px 12px;">#</th>
                <th style="background-color:#1e40af; color:#fff; padding:10px 12px;">{{ __('Designation') }}</th>
                <th class="text-center">{{ __('Qte') }}</th>
                <th class="text-right">{{ __('P.U.') }}</th>
                <th class="text-right">{{ __('TVA') }}</th>
                <th class="text-right">{{ __('Total') }}</th>
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
            <tr><td colspan="6" class="text-center" style="padding:20px;color:#999;">{{ __('Aucun article') }}</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="clearfix">
        <div class="totals-box">
            <table>
                <tr><td>{{ __('Sous-total HT') }}</td><td class="text-right">{{ number_format($invoice->subtotal, 2, ',', ' ') }}</td></tr>
                @if(($invoice->tax_amount ?? 0) > 0)
                <tr><td>{{ __('TVA') }}</td><td class="text-right">{{ number_format($invoice->tax_amount, 2, ',', ' ') }}</td></tr>
                @endif
                @if(($invoice->discount_amount ?? 0) > 0)
                <tr><td style="color:#dc2626;">{{ __('Remise') }}</td><td class="text-right" style="color:#dc2626;">-{{ number_format($invoice->discount_amount, 2, ',', ' ') }}</td></tr>
                @endif
                <tr class="grand"><td>{{ __('TOTAL TTC') }}</td><td class="text-right">{{ number_format($invoice->total, 2, ',', ' ') }} {{ $settings['currency'] ?? __('FCFA') }}</td></tr>
                @if(($invoice->paid_amount ?? 0) > 0)
                <tr><td>{{ __('Paye') }}</td><td class="text-right" style="color:#166534;">{{ number_format($invoice->paid_amount, 2, ',', ' ') }}</td></tr>
                <tr><td><strong>{{ __('Reste') }}</strong></td><td class="text-right" style="color:#991b1b;font-weight:700;">{{ number_format(max(0, $invoice->total - ($invoice->paid_amount ?? 0)), 2, ',', ' ') }}</td></tr>
                @endif
            </table>
        </div>
    </div>

    @if(!empty($invoice->notes))
    <div class="notes"><strong>{{ __('Notes :') }}</strong> {{ $invoice->notes }}</div>
    @endif

    @if(!empty($invoice->terms) || !empty($settings['invoice_terms']))
    <div class="terms"><strong>{{ __('Conditions :') }}</strong> {{ $invoice->terms ?? $settings['invoice_terms'] }}</div>
    @endif

    <div class="footer">
        <strong>{{ $company['name'] ?? '' }}</strong>
        @if(!empty($company['address'])) &bull; {{ $company['address'] }} @endif
        @if(!empty($company['phone'])) &bull; {{ $company['phone'] }} @endif
        @if(!empty($company['email'])) &bull; {{ $company['email'] }} @endif
        <br><span style="color:#94a3b8;">{{ now()->format('d/m/Y H:i</span>
    </div>
</div>
</body>
</html>
