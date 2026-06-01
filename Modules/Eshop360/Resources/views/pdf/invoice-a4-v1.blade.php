<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Facture {{ $invoice->reference }}</title>
    <style>
        __BLADE_BLOCK_82__
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 12px; color: #333; background: #fff; }
        .page { padding: 40px; max-width: 800px; margin: 0 auto; }

        .header { display: table; width: 100%; margin-bottom: 30px; border-bottom: 3px solid __BLADE_BLOCK_1__; padding-bottom: 20px; }
        .header-left { display: table-cell; width: 50%; vertical-align: top; }
        .header-right { display: table-cell; width: 50%; vertical-align: top; text-align: right; color: #555; line-height: 1.6; }
        .header-left img { max-height: 70px; max-width: 180px; }
        .header-left .company-name { font-size: 22px; font-weight: 700; color: __BLADE_BLOCK_2__; }

        .doc-title { text-align: center; margin-bottom: 25px; }
        .doc-title h1 { font-size: 26px; font-weight: 800; letter-spacing: 4px; color: __BLADE_BLOCK_3__; }
        .doc-title .reference { font-size: 13px; color: #666; margin-top: 4px; }

        .info-row { display: table; width: 100%; margin-bottom: 25px; }
        .info-block { display: table-cell; width: 48%; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 4px; padding: 14px; line-height: 1.7; vertical-align: top; }
        .info-spacer { display: table-cell; width: 4%; }
        .info-block h4 { font-size: 11px; text-transform: uppercase; letter-spacing: 1px; color: #64748b; margin-bottom: 6px; border-bottom: 1px solid #e2e8f0; padding-bottom: 4px; }

        table.items { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        table.items thead tr { background: __BLADE_BLOCK_4__; color: #fff; }
        table.items thead th { padding: 10px 12px; text-align: left; font-size: 11px; font-weight: 600; }
        table.items tbody tr { border-bottom: 1px solid #e2e8f0; }
        table.items tbody tr:nth-child(even) { background: #f8fafc; }
        table.items tbody td { padding: 8px 12px; vertical-align: top; }
        table.items tfoot td { padding: 8px 12px; }

        .text-right { text-align: right; }
        .text-center { text-align: center; }

        .total-row { background: __BLADE_BLOCK_5__ !important; color: #fff; font-size: 13px; }
        .total-row td { padding: 10px 12px; font-weight: 700; }

        .badge { display: inline-block; padding: 3px 10px; border-radius: 12px; font-size: 11px; font-weight: 600; }
        .badge-paid { background: #dcfce7; color: #166534; }
        .badge-pending { background: #fef9c3; color: #854d0e; }
        .badge-overdue { background: #fee2e2; color: #991b1b; }
        .badge-partial { background: #dbeafe; color: #1e40af; }

        .notes { margin-bottom: 20px; padding: 12px; background: #fffbeb; border-left: 4px solid #f59e0b; }
        .notes h4 { font-size: 11px; text-transform: uppercase; color: #92400e; margin-bottom: 4px; }

        .terms { margin-bottom: 20px; padding: 12px; background: #f0fdf4; border-left: 4px solid #22c55e; }
        .terms h4 { font-size: 11px; text-transform: uppercase; color: #166534; margin-bottom: 4px; }

        .signatures { display: table; width: 100%; margin: 40px 0 20px; }
        .sig-block { display: table-cell; width: 45%; border-top: 2px solid #333; padding-top: 8px; text-align: center; font-size: 11px; color: #555; }
        .sig-spacer { display: table-cell; width: 10%; }

        .footer { border-top: 2px solid #e2e8f0; padding-top: 12px; text-align: center; color: #64748b; font-size: 10px; line-height: 1.8; }
        .footer strong { color: __BLADE_BLOCK_6__; }

        @media print {
            body { print-color-adjust: exact; -webkit-print-color-adjust: exact; }
            .page { padding: 20px; }
        }
    </style>
</head>
<body>
<div class="page">
    {{-- Header --}}
    <div class="header">
        <div class="header-left">
            @if(!empty($settings['logo_url']))
                <img src="{{ $settings['logo_url'] }}" alt="{{ $company['name'] ?? '' }}">
            @elseif(!empty($company['name']))
                <div class="company-name">{{ $company['name'] }}</div>
            @endif
        </div>
        <div class="header-right">
            <p><strong>{{ $company['name'] ?? '' }}</strong></p>
            @if(!empty($company['address']))<p>{{ $company['address'] }}</p>@endif
            @if(!empty($company['phone']))<p>Tel : {{ $company['phone'] }}</p>@endif
            @if(!empty($company['email']))<p>{{ $company['email'] }}</p>@endif
            @if(!empty($company['tax_number']))<p>N&deg; Taxe : {{ $company['tax_number'] }}</p>@endif
        </div>
    </div>

    {{-- Title --}}
    <div class="doc-title">
        <h1>{{ __('FACTURE') }}</h1>
        <div class="reference">Ref. : {{ $invoice->reference }}</div>
    </div>

    {{-- Info blocks --}}
    <div class="info-row">
        <div class="info-block">
            <h4>{{ __('Facture a') }}</h4>
            <strong>{{ $invoice->customer->name ?? __('Client comptoir') }}</strong><br>
            @if($invoice->customer?->address){{ $invoice->customer->address }}<br>@endif
            @if($invoice->customer?->city){{ $invoice->customer->city }}<br>@endif
            @if($invoice->customer?->phone)Tel : {{ $invoice->customer->phone }}<br>@endif
            @if($invoice->customer?->email){{ $invoice->customer->email }}@endif
        </div>
        <div class="info-spacer"></div>
        <div class="info-block">
            <h4>{{ __('Details de la facture') }}</h4>
            <table style="width:100%;border:none;margin:0;">
                <tr><td style="padding:2px 0;color:#64748b;">{{ __('N° Facture :') }}</td><td style="padding:2px 0;text-align:right;font-weight:600;">{{ $invoice->reference }}</td></tr>
                <tr><td style="padding:2px 0;color:#64748b;">{{ __('Date :') }}</td><td style="padding:2px 0;text-align:right;">{{ $invoice->created_at?->format('d/m/Y') }}</td></tr>
                @if($invoice->due_date)
                <tr><td style="padding:2px 0;color:#64748b;">{{ __('Echeance :') }}</td><td style="padding:2px 0;text-align:right;">{{ $invoice->due_date->format('d/m/Y') }}</td></tr>
                @endif
                <tr>
                    <td style="padding:2px 0;color:#64748b;">{{ __('Statut :') }}</td>
                    <td style="padding:2px 0;text-align:right;">
                        @php $sc = match($invoice->status ?? 'pending') { 'paid' => 'badge-paid', 'overdue' => 'badge-overdue', 'partial' => 'badge-partial', default => 'badge-pending' }; @endphp
                        <span class="badge {{ $sc }}">{{ ucfirst($invoice->status ?? 'pending') }}</span>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    {{-- Items --}}
    <table class="items">
        <thead>
            <tr>
                <th style="background-color:#1e40af; color:#fff; width:30px;">#</th>
                <th style="background-color:#1e40af; color:#fff; padding:10px 12px;">{{ __('Designation') }}</th>
                <th class="text-center" style="background-color:#1e40af; color:#fff; width:50px;">{{ __('Qte') }}</th>
                <th class="text-right" style="background-color:#1e40af; color:#fff; width:90px;">{{ __('Prix Unit.') }}</th>
                <th class="text-right" style="background-color:#1e40af; color:#fff; width:60px;">{{ __('TVA') }}</th>
                <th class="text-right" style="background-color:#1e40af; color:#fff; width:90px;">{{ __('Remise') }}</th>
                <th class="text-right" style="background-color:#1e40af; color:#fff; width:100px;">{{ __('Total HT') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($items as $i => $item)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>
                    <strong>{{ $item->product?->name ?? $item->description ?? '-' }}</strong>
                    @if(!empty($item->description) && $item->product)
                        <br><small style="color:#64748b;">{{ $item->description }}</small>
                    @endif
                </td>
                <td class="text-center">{{ $item->quantity }}</td>
                <td class="text-right">{{ number_format($item->unit_price, 2, ',', ' ') }}</td>
                <td class="text-right">{{ $item->tax_rate ?? 0 }}%</td>
                <td class="text-right">{{ ($item->discount ?? 0) > 0 ? number_format($item->discount, 2, ',', ' ') : '-' }}</td>
                <td class="text-right">{{ number_format($item->total, 2, ',', ' ') }}</td>
            </tr>
            @empty
            <tr><td colspan="7" class="text-center" style="color:#999;padding:20px;">{{ __('Aucun article') }}</td></tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr style="background:#f1f5f9;">
                <td colspan="6" class="text-right"><strong>{{ __('Sous-total HT') }}</strong></td>
                <td class="text-right">{{ number_format($invoice->subtotal, 2, ',', ' ') }}</td>
            </tr>
            @if(($invoice->tax_amount ?? 0) > 0)
            <tr style="background:#fef9c3;">
                <td colspan="6" class="text-right"><strong>{{ __('TVA') }}</strong></td>
                <td class="text-right">{{ number_format($invoice->tax_amount, 2, ',', ' ') }}</td>
            </tr>
            @endif
            @if(($invoice->discount_amount ?? 0) > 0)
            <tr style="background:#fef2f2;">
                <td colspan="6" class="text-right" style="color:#dc2626;"><strong>{{ __('Remise') }}</strong></td>
                <td class="text-right" style="color:#dc2626;">-{{ number_format($invoice->discount_amount, 2, ',', ' ') }}</td>
            </tr>
            @endif
            <tr class="total-row" style="background-color:#1e40af; color:#fff; font-size:14px;">
                <td colspan="6" class="text-right">{{ __('TOTAL TTC') }}</td>
                <td class="text-right">{{ number_format($invoice->total, 2, ',', ' ') }} {{ $settings['currency'] ?? __('FCFA') }}</td>
            </tr>
            @if(($invoice->paid_amount ?? 0) > 0)
            <tr style="background:#dcfce7;">
                <td colspan="6" class="text-right" style="color:#166534;"><strong>{{ __('Paye') }}</strong></td>
                <td class="text-right" style="color:#166534;">{{ number_format($invoice->paid_amount, 2, ',', ' ') }}</td>
            </tr>
            <tr style="background:#fee2e2;">
                <td colspan="6" class="text-right" style="color:#991b1b;"><strong>{{ __('Reste a payer') }}</strong></td>
                <td class="text-right" style="color:#991b1b;">{{ number_format(max(0, $invoice->total - ($invoice->paid_amount ?? 0)), 2, ',', ' ') }}</td>
            </tr>
            @endif
        </tfoot>
    </table>

    {{-- Notes --}}
    @if(!empty($invoice->notes))
    <div class="notes"><h4>{{ __('Notes') }}</h4><p>{{ $invoice->notes }}</p></div>
    @endif

    {{-- Terms --}}
    @if(!empty($invoice->terms) || !empty($settings['invoice_terms']))
    <div class="terms"><h4>{{ __('Conditions de paiement') }}</h4><p>{{ $invoice->terms ?? $settings['invoice_terms'] }}</p></div>
    @endif

    {{-- Signatures --}}
    <div class="signatures">
        <div class="sig-block"><p>{{ __('Signature du vendeur') }}</p><br><br><br><p>{{ $company['name'] ?? '' }}</p></div>
        <div class="sig-spacer"></div>
        <div class="sig-block"><p>{{ __('Signature du client') }}</p><br><br><br><p>{{ $invoice->customer->name ?? __('Client') }}</p></div>
    </div>

    {{-- Footer --}}
    <div class="footer">
        <strong>{{ $company['name'] ?? '' }}</strong>
        @if(!empty($company['address'])) &bull; {{ $company['address'] }} @endif
        @if(!empty($company['phone'])) &bull; Tel : {{ $company['phone'] }} @endif
        @if(!empty($company['email'])) &bull; {{ $company['email'] }} @endif
        <br><span style="color:#94a3b8;">Facture generee le {{ now()->format('d/m/Y H:i') }}</span>
    </div>
</div>
</body>
</html>
