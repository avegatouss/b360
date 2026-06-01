<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('doc-title') — {{ $settings['company_name'] ?? $instance->name ?? 'B360' }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Arial, sans-serif; font-size: 13px; color: #333; background: #f5f5f5; }
        .print-page { max-width: 800px; margin: 20px auto; background: #fff; padding: 40px; box-shadow: 0 2px 20px rgba(0,0,0,.1); }

        /* Header */
        .doc-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 30px; padding-bottom: 20px; border-bottom: 3px solid #2563eb; }
        .doc-header .company { flex: 1; }
        .doc-header .company img { max-height: 60px; margin-bottom: 8px; }
        .doc-header .company h2 { font-size: 18px; margin: 0 0 4px; color: #2563eb; }
        .doc-header .company p { font-size: 11px; color: #666; margin: 1px 0; }
        .doc-header .doc-type { text-align: right; }
        .doc-header .doc-type h1 { font-size: 22px; color: #2563eb; text-transform: uppercase; letter-spacing: 2px; margin: 0; }
        .doc-header .doc-type .doc-date { font-size: 12px; color: #666; margin-top: 4px; }

        /* Customer & Info */
        .doc-parties { display: flex; gap: 30px; margin-bottom: 25px; }
        .doc-parties .party { flex: 1; padding: 15px; border-radius: 8px; }
        .doc-parties .party-client { background: #f0f7ff; border: 1px solid #d0e3ff; }
        .doc-parties .party-info { background: #f5f5f5; border: 1px solid #e0e0e0; }
        .doc-parties h4 { font-size: 11px; text-transform: uppercase; letter-spacing: 1px; color: #888; margin-bottom: 8px; }
        .doc-parties table { width: 100%; font-size: 12px; }
        .doc-parties td { padding: 2px 0; }
        .doc-parties .text-muted { color: #888; width: 35%; }

        /* Items table */
        .doc-items { margin-bottom: 25px; }
        .doc-items table { width: 100%; border-collapse: collapse; }
        .doc-items th { background: #2563eb; color: #fff; padding: 10px 12px; font-size: 11px; text-transform: uppercase; letter-spacing: .5px; }
        .doc-items td { padding: 8px 12px; border-bottom: 1px solid #eee; }
        .doc-items tbody tr:nth-child(even) { background: #fafafa; }
        .doc-items .text-center { text-align: center; }
        .doc-items .text-end { text-align: right; }
        .doc-items .fw-bold { font-weight: 700; }
        .doc-items small { font-size: 10px; }
        .doc-items .text-muted { color: #999; }
        .doc-items .text-danger { color: #dc3545; }

        /* Totals */
        .doc-totals { display: flex; justify-content: flex-end; margin-bottom: 30px; }
        .doc-totals table { width: 280px; }
        .doc-totals td { padding: 5px 8px; font-size: 13px; }
        .doc-totals .text-end { text-align: right; }
        .doc-totals .text-muted { color: #888; }
        .doc-totals .text-success { color: #198754; }
        .doc-totals .text-danger { color: #dc3545; }
        .doc-totals .fw-bold { font-weight: 700; }
        .doc-totals .fs-5 { font-size: 16px; }
        .doc-totals .border-top td { border-top: 2px solid #2563eb; padding-top: 8px; }

        /* Footer */
        .doc-footer { border-top: 1px solid #eee; padding-top: 15px; font-size: 11px; color: #888; text-align: center; }
        .doc-footer .terms { text-align: left; margin-bottom: 10px; white-space: pre-line; }

        /* Print controls */
        .print-controls { text-align: center; margin: 20px auto; max-width: 800px; }
        .print-controls button { padding: 10px 24px; font-size: 14px; border: none; border-radius: 6px; cursor: pointer; margin: 0 5px; }
        .print-controls .btn-print { background: #2563eb; color: #fff; }
        .print-controls .btn-back { background: #6c757d; color: #fff; }

        @media print {
            body { background: #fff; }
            .print-page { margin: 0; padding: 20px; box-shadow: none; max-width: 100%; }
            .print-controls { display: none !important; }
            .doc-header { border-bottom-color: #2563eb !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .doc-items th { background: #2563eb !important; color: #fff !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .doc-parties .party-client { background: #f0f7ff !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }

        @page { margin: 10mm; size: A4; }
    </style>
</head>
<body>

<div class="print-controls">
    <button class="btn-print" onclick="window.print()"><b>Imprimer</b></button>
    <button class="btn-back" onclick="history.back()">Retour</button>
</div>

<div class="print-page">
    {{-- ══════ HEADER ══════ --}}
    <div class="doc-header">
        <div class="company">
            @if(!empty($settings['company_logo']))
                <img src="{{ asset('storage/' . $settings['company_logo']) }}" alt="Logo">
            @endif
            <h2>{{ $settings['company_name'] ?? $instance->name ?? 'B360' }}</h2>
            @if(!empty($settings['company_address']))<p>{{ $settings['company_address'] }}</p>@endif
            @if(!empty($settings['company_phone']))<p>Tel: {{ $settings['company_phone'] }}</p>@endif
            @if(!empty($settings['company_email']))<p>{{ $settings['company_email'] }}</p>@endif
            @if(!empty($settings['tax_number']))<p>{{ __('N. fiscal') }}: {{ $settings['tax_number'] }}</p>@endif
        </div>
        <div class="doc-type">
            <h1>@yield('doc-title')</h1>
            <div class="doc-date">{{ now()->format('d/m/Y') }}</div>
        </div>
    </div>

    {{-- ══════ PARTIES ══════ --}}
    <div class="doc-parties">
        <div class="party party-client">
            <h4>{{ __('Client') }}</h4>
            <table>
                <tr><td class="text-muted">{{ __('Nom') }}</td><td><strong>{{ $customer->name }}</strong></td></tr>
                @if($customer->company_name)<tr><td class="text-muted">{{ __('Societe') }}</td><td>{{ $customer->company_name }}</td></tr>@endif
                <tr><td class="text-muted">{{ __('Code') }}</td><td>{{ $customer->code }}</td></tr>
                @if($customer->email)<tr><td class="text-muted">{{ __('Email') }}</td><td>{{ $customer->email }}</td></tr>@endif
                @if($customer->phone)<tr><td class="text-muted">{{ __('Tel') }}</td><td>{{ $customer->phone }}</td></tr>@endif
                @if($customer->address)<tr><td class="text-muted">{{ __('Adresse') }}</td><td>{{ $customer->address }}</td></tr>@endif
            </table>
        </div>
        <div class="party party-info">
            <h4>{{ __('Informations') }}</h4>
            <table>@yield('doc-info')</table>
        </div>
    </div>

    {{-- ══════ ITEMS ══════ --}}
    <div class="doc-items">
        <table>
            <thead>
                <tr>
                    <th style="text-align:left;">{{ __('Designation') }}</th>
                    <th class="text-center">{{ __('Qte') }}</th>
                    <th class="text-end">{{ __('PU') }}</th>
                    <th class="text-end">{{ __('Remise') }}</th>
                    <th class="text-end">{{ __('Taxe') }}</th>
                    <th class="text-end">{{ __('Total') }}</th>
                </tr>
            </thead>
            <tbody>@yield('doc-items')</tbody>
        </table>
    </div>

    {{-- ══════ TOTALS ══════ --}}
    <div class="doc-totals">
        <table>@yield('doc-totals')</table>
    </div>

    {{-- ══════ FOOTER ══════ --}}
    <div class="doc-footer">
        @if(!empty($settings['default_terms']))
            <div class="terms">{{ $settings['default_terms'] }}</div>
        @endif
        @if(!empty($settings['default_footer']))
            <p>{{ $settings['default_footer'] }}</p>
        @else
            <p>{{ $settings['company_name'] ?? $instance->name ?? 'B360' }} &mdash; {{ __('Merci pour votre confiance') }}</p>
        @endif
        @if(!empty($settings['bank_name']))
            <p style="margin-top:8px;">{{ __('Banque') }}: {{ $settings['bank_name'] }} | {{ __('Compte') }}: {{ $settings['bank_account'] ?? '' }} @if(!empty($settings['bank_iban']))| IBAN: {{ $settings['bank_iban'] }}@endif</p>
        @endif
    </div>
</div>

</body>
</html>
