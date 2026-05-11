<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Devis {{ $devis->numero }}</title>
<style>
body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 12px; color: #333; }
h1 { color: #4f46e5; margin-bottom: 5px; }
table { width: 100%; border-collapse: collapse; margin-top: 20px; }
th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
th { background: #f5f5f5; }
.totaux p { margin: 4px 0; }
.footer { margin-top: 40px; font-size: 10px; color: #888; }
</style>
</head>
<body>
<h1>Devis {{ $devis->numero }}</h1>
<p><strong>Date :</strong> {{ $devis->created_at?->format('d/m/Y') }}</p>
<p><strong>Client :</strong> #{{ $devis->client_id }}</p>
@if ($devis->date_validite)<p><strong>Valable jusqu'au :</strong> {{ $devis->date_validite->format('d/m/Y') }}</p>@endif

<table>
<thead><tr><th>Désignation</th><th>Qté</th><th>Dimensions (mm)</th><th>P.U. HT</th><th>Montant HT</th></tr></thead>
<tbody>
@foreach ($devis->lignes as $l)
<tr>
<td>{{ $l->designation }}</td>
<td>{{ $l->quantite }}</td>
<td>{{ $l->largeur_mm ?? '-' }} × {{ $l->hauteur_mm ?? '-' }}</td>
<td>{{ number_format((float) $l->prix_unitaire_ht, 2, ',', ' ') }}</td>
<td>{{ number_format((float) $l->montant_ht, 2, ',', ' ') }}</td>
</tr>
@endforeach
</tbody>
</table>

<div class="totaux" style="margin-top: 20px;">
<p><strong>Total HT :</strong> {{ number_format((float) $devis->montant_ht, 2, ',', ' ') }} XOF</p>
<p><strong>TVA ({{ number_format((float) $devis->taux_tva * 100, 0) }}%) :</strong> {{ number_format((float) $devis->montant_tva, 2, ',', ' ') }} XOF</p>
<p><strong>Total TTC :</strong> {{ number_format((float) $devis->montant_ttc, 2, ',', ' ') }} XOF</p>
</div>

@if ($devis->conditions)<h3>Conditions</h3><p>{{ $devis->conditions }}</p>@endif

<div class="footer"><p>Devis généré par B360 Menuiserie360 — {{ now()->format('d/m/Y H:i') }}</p></div>
</body>
</html>
