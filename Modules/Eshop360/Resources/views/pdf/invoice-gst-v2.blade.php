<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Facture GST {{ $invoice->reference }}</title>
    <style>
        __BLADE_BLOCK_77__
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 11px; color: #333; background: #fff; }
        .page { padding: 30px; max-width: 800px; margin: 0 auto; border: 2px solid __BLADE_BLOCK_1__; }

        .header { display: table; width: 100%; margin-bottom: 15px; padding-bottom: 12px; border-bottom: 2px double __BLADE_BLOCK_2__; }
        .header-left { display: table-cell; width: 60%; vertical-align: top; }
        .header-right { display: table-cell; width: 40%; vertical-align: top; text-align: right; }
        .header-left img { max-height: 55px; margin-bottom: 5px; }
        .header-left .name { font-size: 18px; font-weight: 700; color: __BLADE_BLOCK_3__; }
        .header-left p { font-size: 10px; color: #555; line-height: 1.5; }

        .gst-title { text-align: center; padding: 8px; background: __BLADE_BLOCK_4__; color: #fff; font-size: 16px; font-weight: 700; letter-spacing: 2px; margin-bottom: 15px; }

        .info-grid { display: table; width: 100%; margin-bottom: 15px; border: 1px solid #ddd; }
        .info-cell { display: table-cell; width: 25%; padding: 8px; border-right: 1px solid #ddd; font-size: 10px; vertical-align: top; }
        .info-cell:last-child { border-right: none; }
        .info-cell label { display: block; font-size: 9px; text-transform: uppercase; color: #888; margin-bottom: 2px; }
        .info-cell strong { color: #111; }

        table.items { width: 100%; border-collapse: collapse; margin-bottom: 10px; font-size: 10px; }
        table.items th { background: __BLADE_BLOCK_5__; color: #fff; padding: 6px 5px; text-align: center; font-size: 9px; border: 1px solid __BLADE_BLOCK_6__; }
        table.items td { padding: 5px; border: 1px solid #ddd; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }

        .tax-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; font-size: 10px; }
        .tax-table th { background: #f3f4f6; padding: 5px; border: 1px solid #ddd; font-size: 9px; }
        .tax-table td { padding: 5px; border: 1px solid #ddd; }

        .totals-row { display: table; width: 100%; margin-bottom: 15px; }
        .amount-words { display: table-cell; width: 55%; vertical-align: top; font-size: 11px; padding: 8px; background: #faf5ff; border: 1px solid #ddd6fe; }
        .totals-col { display: table-cell; width: 45%; vertical-align: top; }
        .totals-col table { width: 100%; border-collapse: collapse; }
        .totals-col td { padding: 4px 8px; border: 1px solid #ddd; font-size: 10px; }
        .totals-col .grand { background: __BLADE_BLOCK_7__; color: #fff; font-weight: 700; font-size: 12px; }

        .sig-row { display: table; width: 100%; margin-top: 30px; }
        .sig-col { display: table-cell; width: 50%; text-align: center; font-size: 10px; color: #555; vertical-align: bottom; }

        .footer { border-top: 1px solid #ddd; padding-top: 8px; text-align: center; color: #888; font-size: 9px; margin-top: 15px; }

        @media print { body { print-color-adjust: exact; -webkit-print-color-adjust: exact; } .page { border: none; } }
    </style>
</head>
<body>
<div class="page">
    <div class="header">
        <div class="header-left">
            @if(!empty($settings['logo_url']))<img src="{{ $settings['logo_url'] }}" alt=""><br>@endif
            <div class="name">{{ $company['name'] ?? '' }}</div>
            <p>{{ $company['address'] ?? '' }}</p>
            @if(!empty($company['phone']))<p>Tel : {{ $company['phone'] }}</p>@endif
            @if(!empty($company['tax_number']))<p>N&deg; Taxe : {{ $company['tax_number'] }}</p>@endif
            @if(!empty($company['gst_number']))<p>GSTIN : {{ $company['gst_number'] }}</p>@endif
        </div>
        <div class="header-right">
            <strong style="font-size:14px;color:{{ $primaryColor }};">{{ __('FACTURE FISCALE') }}</strong><br><br>
            <span style="font-size:10px;color:#555;">{{ __('Document original') }}</span>
        </div>
    </div>

    <div class="gst-title">{{ __('TAX INVOICE / FACTURE GST') }}</div>

    <div class="info-grid">
        <div class="info-cell"><label>{{ __('N° Facture') }}</label><strong>{{ $invoice->reference }}</strong></div>
        <div class="info-cell"><label>{{ __('Date') }}</label><strong>{{ $invoice->created_at?->format('d/m/Y') }}</strong></div>
        <div class="info-cell"><label>{{ __('Echeance') }}</label><strong>{{ $invoice->due_date?->format('d/m/Y') ?? '-' }}</strong></div>
        <div class="info-cell"><label>{{ __('Statut') }}</label><strong>{{ ucfirst($invoice->status ?? 'pending') }}</strong></div>
    </div>

    <div class="info-grid">
        <div class="info-cell" style="width:50%;"><label>{{ __('Client') }}</label><strong>{{ $invoice->customer->name ?? __('Comptoir') }}</strong><br>{{ $invoice->customer?->address ?? '' }}<br>{{ $invoice->customer?->phone ?? '' }}@if($invoice->customer?->tax_number)<br>Tax: {{ $invoice->customer->tax_number }}@endif</div>
        <div class="info-cell" style="width:50%;"><label>{{ __('Livraison a') }}</label><strong>{{ $invoice->customer->name ?? __('Comptoir') }}</strong><br>{{ $invoice->customer?->address ?? '' }}</div>
    </div>

    <table class="items">
        <thead>
            <tr>
                <th>#</th>
                <th>{{ __('Description') }}</th>
                <th>{{ __('HSN/SAC') }}</th>
                <th>{{ __('Qte') }}</th>
                <th>{{ __('P.U.') }}</th>
                <th>{{ __('Montant') }}</th>
                <th colspan="2">{{ __('CGST') }}</th>
                <th colspan="2">{{ __('SGST/UTGST') }}</th>
                <th>{{ __('Total') }}</th>
            </tr>
            <tr>
                <th></th><th></th><th></th><th></th><th></th><th></th>
                <th>%</th><th>{{ __('Mnt') }}</th><th>%</th><th>{{ __('Mnt') }}</th><th></th>
            </tr>
        </thead>
        <tbody>
            @forelse($items as $i => $item)
            @php
                $taxRate = $item->tax_rate ?? 0;
                $half = $taxRate / 2;
                $base = $item->total ?? ($item->unit_price * $item->quantity);
                $cgst = $base * ($half / 100);
                $sgst = $cgst;
            @endphp
            <tr>
                <td class="text-center">{{ $i + 1 }}</td>
                <td>{{ $item->product?->name ?? $item->description ?? '-' }}</td>
                <td class="text-center">{{ $item->hsn_code ?? '-' }}</td>
                <td class="text-center">{{ $item->quantity }}</td>
                <td class="text-right">{{ number_format($item->unit_price, 2, ',', ' ') }}</td>
                <td class="text-right">{{ number_format($base, 2, ',', ' ') }}</td>
                <td class="text-center">{{ $half }}%</td>
                <td class="text-right">{{ number_format($cgst, 2, ',', ' ') }}</td>
                <td class="text-center">{{ $half }}%</td>
                <td class="text-right">{{ number_format($sgst, 2, ',', ' ') }}</td>
                <td class="text-right"><strong>{{ number_format($base + $cgst + $sgst, 2, ',', ' ') }}</strong></td>
            </tr>
            @empty
            <tr><td colspan="11" class="text-center" style="padding:15px;color:#999;">{{ __('Aucun article') }}</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="totals-row">
        <div class="amount-words">
            <strong>{{ __('Montant en lettres :') }}</strong><br>
            {{ $settings['amount_in_words'] ?? '' }}
            @if(!empty($invoice->notes))<br><br><strong>{{ __('Notes :') }}</strong> {{ $invoice->notes }}@endif
        </div>
        <div class="totals-col">
            <table>
                <tr><td>{{ __('Sous-total HT') }}</td><td class="text-right">{{ number_format($invoice->subtotal, 2, ',', ' ') }}</td></tr>
                @if(($invoice->tax_amount ?? 0) > 0)<tr><td>{{ __('Total Taxes') }}</td><td class="text-right">{{ number_format($invoice->tax_amount, 2, ',', ' ') }}</td></tr>@endif
                @if(($invoice->discount_amount ?? 0) > 0)<tr><td>{{ __('Remise') }}</td><td class="text-right" style="color:#dc2626;">-{{ number_format($invoice->discount_amount, 2, ',', ' ') }}</td></tr>@endif
                <tr class="grand"><td>{{ __('TOTAL TTC') }}</td><td class="text-right">{{ number_format($invoice->total, 2, ',', ' ') }} {{ $settings['currency'] ?? __('FCFA') }}</td></tr>
            </table>
        </div>
    </div>

    <div class="sig-row">
        <div class="sig-col" style="text-align:left;"><p>{{ __('Signature du client') }}</p><br><br><br><hr style="width:60%;"></div>
        <div class="sig-col" style="text-align:right;"><p>Pour {{ $company['name'] ?? '' }}</p><br><br><br><hr style="width:60%;margin-left:auto;"></div>
    </div>

    <div class="footer">
        Ceci est un document genere par ordinateur. | {{ $company['name'] ?? '' }} @if(!empty($company['tax_number'])) | {{ $company['tax_number'] }} @endif | {{ now()->format('d/m/Y H:i
    </div>
</div>
</body>
</html>
