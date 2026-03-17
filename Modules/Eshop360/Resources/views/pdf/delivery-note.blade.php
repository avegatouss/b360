<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Bon de Livraison {{ $order->reference ?? '' }}</title>
    <style>
        __BLADE_BLOCK_52__
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

        .info-row { display: table; width: 100%; margin-bottom: 20px; }
        .info-col { display: table-cell; width: 48%; vertical-align: top; padding: 12px; background: #ecfdf5; border: 1px solid #a7f3d0; line-height: 1.6; }
        .info-spacer { display: table-cell; width: 4%; }
        .info-col h4 { font-size: 10px; text-transform: uppercase; color: __BLADE_BLOCK_4__; letter-spacing: 1px; margin-bottom: 5px; }

        table.items { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        table.items thead th { background: __BLADE_BLOCK_5__; color: #fff; padding: 9px 10px; text-align: left; font-size: 11px; }
        table.items tbody td { padding: 8px 10px; border-bottom: 1px solid #e2e8f0; }
        table.items tbody tr:nth-child(even) { background: #ecfdf5; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }

        .check-col { width: 60px; text-align: center; }
        .check-box { display: inline-block; width: 16px; height: 16px; border: 2px solid #333; }

        .notes { padding: 10px; background: #fffbeb; border-left: 3px solid #f59e0b; margin-bottom: 15px; font-size: 11px; }

        .sig-row { display: table; width: 100%; margin: 40px 0 20px; }
        .sig-col { display: table-cell; width: 30%; text-align: center; vertical-align: bottom; font-size: 11px; color: #555; }
        .sig-spacer { display: table-cell; width: 5%; }

        .footer { border-top: 2px solid __BLADE_BLOCK_6__; padding-top: 10px; text-align: center; color: #888; font-size: 10px; line-height: 1.6; }

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
        <h1>{{ __('BON DE LIVRAISON') }}</h1>
        <div class="ref">Ref. {{ $order->reference ?? '' }} | Date : {{ $order->created_at?->format('d/m/Y') ?? now()->format('d/m/Y') }}</div>
    </div>

    <div class="info-row">
        <div class="info-col">
            <h4>{{ __('Expediteur') }}</h4>
            <strong>{{ $company['name'] ?? '' }}</strong><br>
            {{ $company['address'] ?? '' }}<br>
            @if(!empty($company['phone']))Tel : {{ $company['phone'] }}@endif
        </div>
        <div class="info-spacer"></div>
        <div class="info-col">
            <h4>{{ __('Destinataire') }}</h4>
            <strong>{{ $order->customer->name ?? __('Client') }}</strong><br>
            @if($order->customer?->address){{ $order->customer->address }}<br>@endif
            @if($order->customer?->city){{ $order->customer->city }}<br>@endif
            @if($order->customer?->phone)Tel : {{ $order->customer->phone }}@endif
        </div>
    </div>

    <table class="items">
        <thead>
            <tr>
                <th style="width:30px;">#</th>
                <th>{{ __('Designation') }}</th>
                <th class="text-center" style="width:60px;">{{ __('Ref.') }}</th>
                <th class="text-center" style="width:60px;">{{ __('Qte Cmd') }}</th>
                <th class="text-center" style="width:60px;">{{ __('Qte Liv.') }}</th>
                <th class="check-col">{{ __('Conforme') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($items as $i => $item)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td><strong>{{ $item->product?->name ?? $item->description ?? '-' }}</strong></td>
                <td class="text-center">{{ $item->product?->sku ?? '-' }}</td>
                <td class="text-center">{{ $item->quantity }}</td>
                <td class="text-center">____</td>
                <td class="check-col"><span class="check-box"></span></td>
            </tr>
            @empty
            <tr><td colspan="6" class="text-center" style="padding:15px;color:#999;">{{ __('Aucun article') }}</td></tr>
            @endforelse
        </tbody>
    </table>

    <p style="margin-bottom:15px;font-size:11px;"><strong>{{ __('Nombre total d\'articles :') }}</strong> {{ collect($items)->sum('quantity') }} | <strong>{{ __('Nombre de lignes :') }}</strong> {{ count($items) }}</p>

    @if(!empty($order->notes))
    <div class="notes"><strong>{{ __('Notes / Instructions :') }}</strong> {{ $order->notes }}</div>
    @endif

    <div class="sig-row">
        <div class="sig-col">
            <p>{{ __('Prepare par') }}</p><br><br><br>
            <hr style="width:80%;margin:0 auto;">
            <p style="margin-top:4px;">{{ __('Nom & Signature') }}</p>
        </div>
        <div class="sig-spacer"></div>
        <div class="sig-col">
            <p>{{ __('Livre par') }}</p><br><br><br>
            <hr style="width:80%;margin:0 auto;">
            <p style="margin-top:4px;">{{ __('Nom & Signature') }}</p>
        </div>
        <div class="sig-spacer"></div>
        <div class="sig-col">
            <p>{{ __('Recu par') }}</p><br><br><br>
            <hr style="width:80%;margin:0 auto;">
            <p style="margin-top:4px;">{{ __('Nom, Signature & Cachet') }}</p>
        </div>
    </div>

    <div class="footer">
        {{ $company['name'] ?? '' }} @if(!empty($company['address'])) | {{ $company['address'] }} @endif @if(!empty($company['phone'])) | {{ $company['phone'] }} @endif
        <br>Bon de livraison genere le {{ now()->format('d/m/Y H:i') }}
    </div>
</div>
</body>
</html>
