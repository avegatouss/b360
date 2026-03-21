<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Avoir {{ $return->id ?? '' }}</title>
    <style>
        __BLADE_BLOCK_63__
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 12px; color: #333; background: #fff; }
        .page { padding: 40px; max-width: 800px; margin: 0 auto; }

        .header { display: table; width: 100%; margin-bottom: 25px; border-bottom: 3px solid __BLADE_BLOCK_1__; padding-bottom: 18px; }
        .header-left { display: table-cell; width: 50%; vertical-align: top; }
        .header-right { display: table-cell; width: 50%; vertical-align: top; text-align: right; color: #555; line-height: 1.6; }
        .header-left img { max-height: 65px; }
        .header-left .name { font-size: 20px; font-weight: 700; color: __BLADE_BLOCK_2__; }

        .doc-title { text-align: center; margin-bottom: 20px; }
        .doc-title h1 { font-size: 24px; font-weight: 800; color: __BLADE_BLOCK_3__; letter-spacing: 3px; }
        .doc-title .ref { font-size: 12px; color: #666; margin-top: 4px; }

        .credit-badge { display: inline-block; padding: 4px 16px; background: #fee2e2; color: #991b1b; border-radius: 20px; font-size: 11px; font-weight: 600; margin-top: 6px; }

        .info-row { display: table; width: 100%; margin-bottom: 20px; }
        .info-col { display: table-cell; width: 48%; vertical-align: top; padding: 12px; background: #fef2f2; border: 1px solid #fecaca; line-height: 1.6; }
        .info-spacer { display: table-cell; width: 4%; }
        .info-col h4 { font-size: 10px; text-transform: uppercase; color: __BLADE_BLOCK_4__; letter-spacing: 1px; margin-bottom: 5px; }

        table.items { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        table.items thead th { background: __BLADE_BLOCK_5__; color: #fff; padding: 9px 10px; text-align: left; font-size: 11px; }
        table.items tbody td { padding: 8px 10px; border-bottom: 1px solid #e2e8f0; }
        table.items tbody tr:nth-child(even) { background: #fef2f2; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }

        .totals { float: right; width: 260px; margin-bottom: 15px; }
        .totals table { width: 100%; border-collapse: collapse; }
        .totals td { padding: 5px 10px; border-bottom: 1px solid #eee; }
        .totals .grand { background: __BLADE_BLOCK_6__; color: #fff; font-weight: 700; font-size: 13px; }
        .clearfix::after { content: ''; display: table; clear: both; }

        .reason { clear: both; padding: 12px; background: #fef2f2; border-left: 4px solid __BLADE_BLOCK_7__; margin-bottom: 15px; }
        .reason h4 { font-size: 11px; text-transform: uppercase; color: __BLADE_BLOCK_8__; margin-bottom: 4px; }

        .refund-info { padding: 12px; background: #f0fdf4; border-left: 4px solid #22c55e; margin-bottom: 15px; }

        .sig-row { display: table; width: 100%; margin: 30px 0 15px; }
        .sig-col { display: table-cell; width: 45%; text-align: center; font-size: 11px; color: #555; vertical-align: bottom; }
        .sig-spacer { display: table-cell; width: 10%; }

        .footer { border-top: 2px solid __BLADE_BLOCK_9__; padding-top: 10px; text-align: center; color: #888; font-size: 10px; line-height: 1.6; }

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
            @if(!empty($company['email'])){{ $company['email'] }}@endif
        </div>
    </div>

    <div class="doc-title">
        <h1>{{ __('AVOIR / NOTE DE CREDIT') }}</h1>
        <div class="ref">N&deg; AV-{{ str_pad($return->id ?? 0, 6, '0', STR_PAD_LEFT) }} | Date : {{ $return->date?->format('d/m/Y') ?? now()->format('d/m/Y') }}</div>
        <div class="credit-badge">{{ __('CREDIT') }}</div>
    </div>

    <div class="info-row">
        <div class="info-col">
            <h4>{{ __('Client') }}</h4>
            <strong>{{ $return->customer->name ?? __('Client') }}</strong><br>
            @if($return->customer?->address){{ $return->customer->address }}<br>@endif
            @if($return->customer?->phone)Tel : {{ $return->customer->phone }}@endif
        </div>
        <div class="info-spacer"></div>
        <div class="info-col">
            <h4>{{ __('Reference') }}</h4>
            N&deg; Avoir : AV-{{ str_pad($return->id ?? 0, 6, '0', STR_PAD_LEFT) }}<br>
            Date : {{ $return->date?->format('d/m/Y') ?? now()->format('d/m/Y') }}<br>
            @if($return->order)Commande origine : {{ $return->order->reference ?? '#' . $return->order_id }}<br>@endif
            Statut : {{ ucfirst($return->status ?? 'pending') }}
        </div>
    </div>

    <table class="items">
        <thead>
            <tr>
                <th style="background-color:#1e40af; color:#fff; padding:10px 12px;">#</th>
                <th style="background-color:#1e40af; color:#fff; padding:10px 12px;">{{ __('Designation') }}</th>
                <th class="text-center">{{ __('Qte') }}</th>
                <th class="text-right">{{ __('Prix Unit.') }}</th>
                <th class="text-right">{{ __('Total') }}</th>
            </tr>
        </thead>
        <tbody>
            @if($return->product)
            <tr>
                <td>1</td>
                <td><strong>{{ $return->product->name }}</strong></td>
                <td class="text-center">1</td>
                <td class="text-right">{{ number_format($return->total, 2, ',', ' ') }}</td>
                <td class="text-right">{{ number_format($return->total, 2, ',', ' ') }}</td>
            </tr>
            @elseif(!empty($items))
                @foreach($items as $i => $item)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td><strong>{{ $item->product?->name ?? $item->description ?? '-' }}</strong></td>
                    <td class="text-center">{{ $item->quantity ?? 1 }}</td>
                    <td class="text-right">{{ number_format($item->unit_price ?? $item->total ?? 0, 2, ',', ' ') }}</td>
                    <td class="text-right">{{ number_format($item->total ?? 0, 2, ',', ' ') }}</td>
                </tr>
                @endforeach
            @else
            <tr><td colspan="5" class="text-center" style="padding:15px;color:#999;">{{ __('Aucun article') }}</td></tr>
            @endif
        </tbody>
    </table>

    <div class="clearfix">
        <div class="totals">
            <table>
                <tr class="grand"><td>{{ __('MONTANT DE L\'AVOIR') }}</td><td class="text-right">{{ number_format($return->total, 2, ',', ' ') }} {{ $settings['currency'] ?? __('FCFA') }}</td></tr>
                @if(($return->paid_amount ?? 0) > 0)
                <tr><td>{{ __('Rembourse') }}</td><td class="text-right" style="color:#166534;">{{ number_format($return->paid_amount, 2, ',', ' ') }}</td></tr>
                <tr><td><strong>{{ __('Reste') }}</strong></td><td class="text-right" style="color:#991b1b;">{{ number_format(max(0, $return->total - $return->paid_amount), 2, ',', ' ') }}</td></tr>
                @endif
            </table>
        </div>
    </div>

    @if(!empty($return->notes))
    <div class="reason"><h4>{{ __('Motif du retour') }}</h4><p>{{ $return->notes }}</p></div>
    @endif

    <div class="refund-info">
        <strong>{{ __('Mode de remboursement :') }}</strong> {{ ucfirst($return->payment_status ?? __('En attente')) }}<br>
        <small>{{ __('Le remboursement sera effectue selon les modalites convenues avec le client.') }}</small>
    </div>

    <div class="sig-row">
        <div class="sig-col"><p>{{ __('Vendeur') }}</p><br><br><br><hr style="width:80%;margin:0 auto;"><p style="margin-top:4px;">{{ $company['name'] ?? '' }}</p></div>
        <div class="sig-spacer"></div>
        <div class="sig-col"><p>{{ __('Client') }}</p><br><br><br><hr style="width:80%;margin:0 auto;"><p style="margin-top:4px;">{{ $return->customer->name ?? __('Client') }}</p></div>
    </div>

    <div class="footer">
        {{ $company['name'] ?? '' }} @if(!empty($company['address'])) | {{ $company['address'] }} @endif @if(!empty($company['phone'])) | {{ $company['phone'] }} @endif
        <br>Note de credit generee le {{ now()->format('d/m/Y H:i') }}
    </div>
</div>
</body>
</html>
