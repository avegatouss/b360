<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Facture Proforma {{ $invoice->reference }}</title>
    <style>
        __BLADE_BLOCK_67__
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 12px; color: #333; background: #fff; }
        .page { padding: 40px; max-width: 800px; margin: 0 auto; }

        .header { display: table; width: 100%; margin-bottom: 25px; border-bottom: 3px solid __BLADE_BLOCK_1__; padding-bottom: 18px; }
        .header-left { display: table-cell; width: 50%; vertical-align: top; }
        .header-right { display: table-cell; width: 50%; vertical-align: top; text-align: right; color: #555; line-height: 1.6; }
        .header-left img { max-height: 65px; }
        .header-left .name { font-size: 20px; font-weight: 700; color: __BLADE_BLOCK_2__; }

        .watermark { text-align: center; margin-bottom: 10px; }
        .watermark span { display: inline-block; padding: 6px 30px; border: 3px solid __BLADE_BLOCK_3__; color: __BLADE_BLOCK_4__; font-size: 24px; font-weight: 800; letter-spacing: 6px; transform: rotate(-3deg); opacity: 0.6; }

        .doc-title { text-align: center; margin-bottom: 20px; }
        .doc-title h1 { font-size: 24px; font-weight: 800; color: __BLADE_BLOCK_5__; letter-spacing: 3px; }
        .doc-title .ref { font-size: 12px; color: #666; margin-top: 4px; }

        .info-row { display: table; width: 100%; margin-bottom: 20px; }
        .info-col { display: table-cell; width: 48%; vertical-align: top; padding: 12px; background: #fff7ed; border: 1px solid #fed7aa; line-height: 1.6; }
        .info-spacer { display: table-cell; width: 4%; }
        .info-col h4 { font-size: 10px; text-transform: uppercase; color: __BLADE_BLOCK_6__; letter-spacing: 1px; margin-bottom: 5px; }

        table.items { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        table.items thead th { background: __BLADE_BLOCK_7__; color: #fff; padding: 9px 10px; text-align: left; font-size: 11px; }
        table.items tbody td { padding: 7px 10px; border-bottom: 1px solid #e2e8f0; }
        table.items tbody tr:nth-child(even) { background: #fff7ed; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }

        .totals { float: right; width: 260px; margin-bottom: 15px; }
        .totals table { width: 100%; border-collapse: collapse; }
        .totals td { padding: 5px 10px; border-bottom: 1px solid #eee; }
        .totals .grand { background: __BLADE_BLOCK_8__; color: #fff; font-weight: 700; font-size: 13px; }
        .clearfix::after { content: ''; display: table; clear: both; }

        .validity { clear: both; padding: 12px; background: #fff7ed; border-left: 4px solid __BLADE_BLOCK_9__; margin-bottom: 15px; }
        .validity h4 { font-size: 11px; text-transform: uppercase; color: __BLADE_BLOCK_10__; margin-bottom: 4px; }

        .notes { padding: 10px; background: #fffbeb; border-left: 3px solid #f59e0b; margin-bottom: 10px; font-size: 11px; }

        .disclaimer { padding: 10px; background: #fee2e2; border-left: 3px solid #dc2626; margin-bottom: 15px; font-size: 11px; color: #991b1b; }

        .footer { border-top: 2px solid __BLADE_BLOCK_11__; padding-top: 10px; text-align: center; color: #888; font-size: 10px; line-height: 1.6; }

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
            @if(!empty($company['email'])){{ $company['email'] }}<br>@endif
            @if(!empty($company['tax_number']))N&deg; Taxe : {{ $company['tax_number'] }}@endif
        </div>
    </div>

    <div class="watermark"><span>{{ __('PROFORMA') }}</span></div>

    <div class="doc-title">
        <h1>{{ __('FACTURE PROFORMA') }}</h1>
        <div class="ref">Ref. {{ $invoice->reference }} | {{ $invoice->created_at?->format('d/m/Y') }}</div>
    </div>

    <div class="info-row">
        <div class="info-col">
            <h4>{{ __('Destinataire') }}</h4>
            <strong>{{ $invoice->customer->name ?? __('Client') }}</strong><br>
            @if($invoice->customer?->address){{ $invoice->customer->address }}<br>@endif
            @if($invoice->customer?->phone)Tel : {{ $invoice->customer->phone }}<br>@endif
            @if($invoice->customer?->email){{ $invoice->customer->email }}@endif
        </div>
        <div class="info-spacer"></div>
        <div class="info-col">
            <h4>{{ __('Details') }}</h4>
            N&deg; : {{ $invoice->reference }}<br>
            Date : {{ $invoice->created_at?->format('d/m/Y') }}<br>
            @if($invoice->due_date)Validite : {{ $invoice->due_date->format('d/m/Y') }}<br>@endif
        </div>
    </div>

    <table class="items">
        <thead><tr><th>#</th><th>{{ __('Designation') }}</th><th class="text-center">{{ __('Qte') }}</th><th class="text-right">{{ __('P.U.') }}</th><th class="text-right">{{ __('TVA') }}</th><th class="text-right">{{ __('Total') }}</th></tr></thead>
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
            <tr><td colspan="6" class="text-center" style="padding:15px;color:#999;">{{ __('Aucun article') }}</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="clearfix">
        <div class="totals">
            <table>
                <tr><td>{{ __('Sous-total HT') }}</td><td class="text-right">{{ number_format($invoice->subtotal, 2, ',', ' ') }}</td></tr>
                @if(($invoice->tax_amount ?? 0) > 0)<tr><td>{{ __('TVA') }}</td><td class="text-right">{{ number_format($invoice->tax_amount, 2, ',', ' ') }}</td></tr>@endif
                @if(($invoice->discount_amount ?? 0) > 0)<tr><td>{{ __('Remise') }}</td><td class="text-right" style="color:#dc2626;">-{{ number_format($invoice->discount_amount, 2, ',', ' ') }}</td></tr>@endif
                <tr class="grand"><td>{{ __('TOTAL TTC') }}</td><td class="text-right">{{ number_format($invoice->total, 2, ',', ' ') }} {{ $settings['currency'] ?? __('FCFA') }}</td></tr>
            </table>
        </div>
    </div>

    <div class="disclaimer">
        <strong>{{ __('IMPORTANT :') }}</strong> Ce document est une facture proforma et ne constitue pas une facture definitive.
        Les prix et disponibilites sont susceptibles de modification. Ce document n'a pas de valeur comptable.
    </div>

    <div class="validity">
        <h4>{{ __('Validite') }}</h4>
        <p>
            {{ __('Cette proforma est valable') }}
            @if($invoice->due_date)
                {{ __('jusqu\'au') }} <strong>{{ $invoice->due_date->format('d/m/Y') }}</strong>
            @else
                {{ __('30 jours a compter de la date d\'emission') }}
            @endif.
            {{ __('Pour confirmer, veuillez nous retourner ce document signe.') }}
        </p>
    </div>

    @if(!empty($invoice->notes))<div class="notes"><strong>{{ __('Notes :') }}</strong> {{ $invoice->notes }}</div>@endif

    <div class="footer">
        {{ $company['name'] ?? '' }} @if(!empty($company['address'])) | {{ $company['address'] }} @endif @if(!empty($company['phone'])) | {{ $company['phone'] }} @endif
        <br>{{ __('Proforma generee le') }} {{ now()->format('d/m/Y H:i') }}
    </div>
</div>
</body>
</html>
