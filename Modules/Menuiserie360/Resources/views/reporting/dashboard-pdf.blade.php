<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Dashboard Menuiserie — {{ $period['label'] }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #222; margin: 24px; }
        h1 { font-size: 18px; margin: 0 0 4px 0; }
        h2 { font-size: 13px; margin: 16px 0 6px 0; padding-bottom: 2px; border-bottom: 1px solid #ccc; }
        .meta { color: #666; font-size: 10px; margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        th, td { padding: 5px 8px; border: 1px solid #ddd; vertical-align: top; }
        th { background: #f4f4f4; text-align: left; font-weight: 600; }
        .text-end { text-align: right; }
        .kpi-grid { display: table; width: 100%; table-layout: fixed; }
        .kpi-row { display: table-row; }
        .kpi-cell { display: table-cell; padding: 8px; border: 1px solid #ddd; width: 25%; }
        .kpi-label { color: #666; font-size: 9px; text-transform: uppercase; }
        .kpi-value { font-size: 16px; font-weight: bold; margin-top: 2px; }
        .footer { color: #999; font-size: 9px; margin-top: 24px; text-align: center; }
        .bar { background: #007bff; height: 8px; display: inline-block; vertical-align: middle; }
        .bar-track { background: #eee; height: 8px; width: 100%; display: inline-block; vertical-align: middle; }
    </style>
</head>
<body>
    <h1>Dashboard Menuiserie360 — {{ $instance->name ?? 'B360' }}</h1>
    <div class="meta">
        <strong>Période :</strong> {{ $period['label'] }}
        ({{ $period['from']->format('d/m/Y') }} → {{ $period['to']->format('d/m/Y') }})
        — <em>Document généré le {{ $generatedAt->format('d/m/Y H:i') }}</em>
    </div>

    <h2>Indicateurs clés</h2>
    <div class="kpi-grid">
        <div class="kpi-row">
            <div class="kpi-cell">
                <div class="kpi-label">Devis brouillons</div>
                <div class="kpi-value">{{ $kpis['devis_brouillons'] }}</div>
            </div>
            <div class="kpi-cell">
                <div class="kpi-label">Devis acceptés (période)</div>
                <div class="kpi-value">{{ $kpis['devis_acceptes_periode'] }}</div>
            </div>
            <div class="kpi-cell">
                <div class="kpi-label">OF en cours</div>
                <div class="kpi-value">{{ $kpis['of_en_cours'] }}</div>
            </div>
            <div class="kpi-cell">
                <div class="kpi-label">Chantiers en cours</div>
                <div class="kpi-value">{{ $kpis['chantiers_en_cours'] }}</div>
            </div>
        </div>
        <div class="kpi-row">
            <div class="kpi-cell">
                <div class="kpi-label">CA période (XOF)</div>
                <div class="kpi-value">{{ number_format($kpis['ca_periode'], 0, ',', ' ') }}</div>
            </div>
            <div class="kpi-cell">
                <div class="kpi-label">Créances (XOF)</div>
                <div class="kpi-value">{{ number_format($kpis['creances'], 0, ',', ' ') }}</div>
            </div>
            <div class="kpi-cell">
                <div class="kpi-label">Encaissé période (XOF)</div>
                <div class="kpi-value">{{ number_format($kpis['encaisse_periode'], 0, ',', ' ') }}</div>
            </div>
            <div class="kpi-cell">
                <div class="kpi-label">Conversion devis</div>
                <div class="kpi-value">{{ number_format($kpis['taux_conversion_devis_periode'], 1, ',', ' ') }} %</div>
            </div>
        </div>
    </div>

    <h2>CA mensuel (12 derniers mois, TTC)</h2>
    @php($maxCa = max(array_column($caMensuel12m, 'ttc')) ?: 1)
    <table>
        <thead><tr><th>Mois</th><th class="text-end">TTC (XOF)</th><th style="width:50%">Volume relatif</th></tr></thead>
        <tbody>
        @foreach ($caMensuel12m as $row)
            <tr>
                <td>{{ $row['mois'] }}</td>
                <td class="text-end">{{ number_format($row['ttc'], 0, ',', ' ') }}</td>
                <td>
                    <span class="bar-track"><span class="bar" style="width: {{ $maxCa > 0 ? round($row['ttc'] / $maxCa * 100) : 0 }}%"></span></span>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <h2>Top 5 clients ({{ $period['label'] }})</h2>
    <table>
        <thead><tr><th>Client</th><th class="text-end">Nb factures</th><th class="text-end">CA TTC (XOF)</th></tr></thead>
        <tbody>
        @forelse ($topClients as $c)
            <tr>
                <td>Client #{{ $c['client_id'] }}</td>
                <td class="text-end">{{ $c['count'] }}</td>
                <td class="text-end">{{ number_format($c['ttc'], 0, ',', ' ') }}</td>
            </tr>
        @empty
            <tr><td colspan="3" style="text-align:center;color:#999;">Aucune facture sur la période.</td></tr>
        @endforelse
        </tbody>
    </table>

    <h2>Mix paiements ({{ $period['label'] }})</h2>
    <table>
        <thead><tr><th>Méthode</th><th class="text-end">Montant (XOF)</th><th class="text-end">Part (%)</th></tr></thead>
        <tbody>
        @forelse ($mixPaiements as $m)
            <tr>
                <td>{{ $m['method'] }}</td>
                <td class="text-end">{{ number_format($m['total'], 0, ',', ' ') }}</td>
                <td class="text-end">{{ $m['share'] }}</td>
            </tr>
        @empty
            <tr><td colspan="3" style="text-align:center;color:#999;">Aucun encaissement sur la période.</td></tr>
        @endforelse
        </tbody>
    </table>

    <div class="footer">B360 Menuiserie360 — Dashboard direction</div>
</body>
</html>
