<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Facture {{ $invoice->reference }} (GST)</title>
    <style>
        __BLADE_BLOCK_76__
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 12px; color: #333; background: #fff; }
        .page { padding: 35px; max-width: 800px; margin: 0 auto; }

        .header { display: table; width: 100%; margin-bottom: 20px; border-bottom: 3px solid __BLADE_BLOCK_1__; padding-bottom: 15px; }
        .header-left { display: table-cell; width: 50%; vertical-align: top; }
        .header-right { display: table-cell; width: 50%; vertical-align: top; text-align: right; line-height: 1.6; color: #555; }
        .header-left img { max-height: 60px; }
        .header-left .name { font-size: 20px; font-weight: 700; color: __BLADE_BLOCK_2__; }

        .doc-title { text-align: center; margin-bottom: 20px; }
        .doc-title h1 { font-size: 22px; font-weight: 800; color: __BLADE_BLOCK_3__; letter-spacing: 3px; }
        .doc-title .sub { font-size: 11px; color: #666; }

        .info-row { display: table; width: 100%; margin-bottom: 20px; }
        .info-col { display: table-cell; width: 48%; vertical-align: top; padding: 10px; background: #f8fafc; border: 1px solid #e2e8f0; }
        .info-spacer { display: table-cell; width: 4%; }
        .info-col h4 { font-size: 10px; text-transform: uppercase; color: #64748b; letter-spacing: 1px; margin-bottom: 5px; }

        table.items { width: 100%; border-collapse: collapse; margin-bottom: 15px; font-size: 11px; }
        table.items thead th { background: __BLADE_BLOCK_4__; color: #fff; padding: 7px 6px; text-align: left; font-size: 10px; }
        table.items tbody td { padding: 6px; border-bottom: 1px solid #e2e8f0; }
        table.items tbody tr:nth-child(even) { background: #f8fafc; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }

        .tax-summary { margin-bottom: 15px; }
        .tax-summary h4 { font-size: 11px; color: __BLADE_BLOCK_5__; margin-bottom: 5px; }
        .tax-summary table { width: 100%; border-collapse: collapse; font-size: 11px; }
        .tax-summary th { background: #f1f5f9; padding: 5px 8px; text-align: left; border: 1px solid #e2e8f0; font-size: 10px; }
        .tax-summary td { padding: 5px 8px; border: 1px solid #e2e8f0; }

        .totals-wrap { display: table; width: 100%; margin-bottom: 15px; }
        .totals-left { display: table-cell; width: 55%; vertical-align: top; }
        .totals-right { display: table-cell; width: 45%; vertical-align: top; }
        .totals-right table { width: 100%; border-collapse: collapse; }
        .totals-right td { padding: 5px 8px; border-bottom: 1px solid #eee; }
        .totals-right .grand { background: __BLADE_BLOCK_6__; color: #fff; font-weight: 700; font-size: 12px; }

        .notes { padding: 8px 10px; background: #fffbeb; border-left: 3px solid #f59e0b; margin-bottom: 10px; font-size: 11px; }
        .footer { border-top: 2px solid #e2e8f0; padding-top: 10px; text-align: center; color: #888; font-size: 9px; line-height: 1.6; margin-top: 20px; }

        @media print { body { print-color-adjust: exact; -webkit-print-color-adjust: exact; } }
    </style>
</head>
<body>
<div class="page">
    <div class="header">
        <div class="header-left">
            @if(!empty($settings['logo_url']))<img src="{{ $settings['logo_url'] }}" alt="">@else<div class="name">{{ $company['name'] ?? '' }}</div>@endif
        </div>
        <div class="header-right">
            <strong>{{ $company['name'] ?? '' }}</strong><br>
            {{ $company['address'] ?? '' }}<br>
            @if(!empty($company['phone']))Tel : {{ $company['phone'] }}<br>@endif
            @if(!empty($company['tax_number']))N&deg; Taxe : {{ $company['tax_number'] }}<br>@endif
            @if(!empty($company['gst_number']))GSTIN : {{ $company['gst_number'] }}@endif
        </div>
    </div>

    <div class="doc-title">
        <h1>{{ __('FACTURE GST') }}</h1>
        <div class="sub">Ref. {{ $invoice->reference }} | Date : {{ $invoice->created_at?->format('d/m/Y') }}</div>
    </div>

    <div class="info-row">
        <div class="info-col">
            <h4>{{ __('Facture a') }}</h4>
            <strong>{{ $invoice->customer->name ?? __('Client comptoir') }}</strong><br>
            @if($invoice->customer?->address){{ $invoice->customer->address }}<br>@endif
            @if($invoice->customer?->phone)Tel : {{ $invoice->customer->phone }}<br>@endif
            @if($invoice->customer?->tax_number)N&deg; Taxe : {{ $invoice->customer->tax_number }}@endif
        </div>
        <div class="info-spacer"></div>
        <div class="info-col">
            <h4>{{ __('Details facture') }}</h4>
            N&deg; : {{ $invoice->reference }}<br>
            Date : {{ $invoice->created_at?->format('d/m/Y') }}<br>
            @if($invoice->due_date)Echeance : {{ $invoice->due_date->format('d/m/Y') }}<br>@endif
            Statut : {{ ucfirst($invoice->status ?? 'pending') }}
        </div>
    </div>

    <table class="items">
        <thead>
            <tr>
                <th>#</th>
                <th>{{ __('Designation') }}</th>
                <th class="text-center">{{ __('Qte') }}</th>
                <th class="text-right">{{ __('P.U. HT') }}</th>
                <th class="text-right">{{ __('Montant HT') }}</th>
                <th class="text-center">{{ __('Taux TVA') }}</th>
                <th class="text-right">{{ __('CGST') }}</th>
                <th class="text-right">{{ __('SGST') }}</th>
                <th class="text-right">{{ __('Total TTC') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($items as $i => $item)
            @php
                $taxRate = $item->tax_rate ?? 0;
                $halfRate = $taxRate / 2;
                $taxableAmount = $item->total ?? ($item->unit_price * $item->quantity);
                $cgst = $taxableAmount * ($halfRate / 100);
                $sgst = $cgst;
                $totalTtc = $taxableAmount + $cgst + $sgst;
            @endphp
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $item->product?->name ?? $item->description ?? '-' }}</td>
                <td class="text-center">{{ $item->quantity }}</td>
                <td class="text-right">{{ number_format($item->unit_price, 2, ',', ' ') }}</td>
                <td class="text-right">{{ number_format($taxableAmount, 2, ',', ' ') }}</td>
                <td class="text-center">{{ $taxRate }}%</td>
                <td class="text-right">{{ number_format($cgst, 2, ',', ' ') }}</td>
                <td class="text-right">{{ number_format($sgst, 2, ',', ' ') }}</td>
                <td class="text-right">{{ number_format($totalTtc, 2, ',', ' ') }}</td>
            </tr>
            @empty
            <tr><td colspan="9" class="text-center" style="padding:15px;color:#999;">{{ __('Aucun article') }}</td></tr>
            @endforelse
        </tbody>
    </table>

    {{-- Tax summary --}}
    <div class="tax-summary">
        <h4>{{ __('Recapitulatif des taxes') }}</h4>
        <table>
            <thead><tr><th>{{ __('Taux') }}</th><th class="text-right">{{ __('Base HT') }}</th><th class="text-right">{{ __('CGST') }}</th><th class="text-right">{{ __('SGST') }}</th><th class="text-right">{{ __('Total Taxe') }}</th></tr></thead>
            <tbody>
                @php
                    $taxGroups = collect($items)->groupBy(fn($it) => $it->tax_rate ?? 0);
                @endphp
                @foreach($taxGroups as $rate => $group)
                @php
                    $base = $group->sum('total');
                    $halfR = $rate / 2;
                    $cg = $base * ($halfR / 100);
                    $sg = $cg;
                @endphp
                <tr>
                    <td>{{ $rate }}%</td>
                    <td class="text-right">{{ number_format($base, 2, ',', ' ') }}</td>
                    <td class="text-right">{{ number_format($cg, 2, ',', ' ') }}</td>
                    <td class="text-right">{{ number_format($sg, 2, ',', ' ') }}</td>
                    <td class="text-right">{{ number_format($cg + $sg, 2, ',', ' ') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="totals-wrap">
        <div class="totals-left">
            @if(!empty($invoice->notes))<div class="notes"><strong>{{ __('Notes :') }}</strong> {{ $invoice->notes }}</div>@endif
        </div>
        <div class="totals-right">
            <table>
                <tr><td>{{ __('Sous-total HT') }}</td><td class="text-right">{{ number_format($invoice->subtotal, 2, ',', ' ') }}</td></tr>
                @if(($invoice->tax_amount ?? 0) > 0)<tr><td>{{ __('Total Taxes') }}</td><td class="text-right">{{ number_format($invoice->tax_amount, 2, ',', ' ') }}</td></tr>@endif
                @if(($invoice->discount_amount ?? 0) > 0)<tr><td>{{ __('Remise') }}</td><td class="text-right" style="color:#dc2626;">-{{ number_format($invoice->discount_amount, 2, ',', ' ') }}</td></tr>@endif
                <tr class="grand"><td>{{ __('TOTAL TTC') }}</td><td class="text-right">{{ number_format($invoice->total, 2, ',', ' ') }} {{ $settings['currency'] ?? __('FCFA') }}</td></tr>
            </table>
        </div>
    </div>

    <div class="footer">
        {{ $company['name'] ?? '' }} @if(!empty($company['address'])) | {{ $company['address'] }} @endif @if(!empty($company['tax_number'])) | N&deg; Taxe : {{ $company['tax_number'] }} @endif
        <br>Facture generee le {{ now()->format('d/m/Y H:i
    </div>
</div>
</body>
</html>
