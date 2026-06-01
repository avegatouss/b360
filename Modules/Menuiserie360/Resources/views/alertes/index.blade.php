<x-menuiserie360::layout title="Alertes Menuiserie360">
    <x-menuiserie360::page-header
        title="Alertes opérationnelles"
        :subtitle="$totalAlertes > 0 ? $totalAlertes . ' alerte(s) active(s)' : 'Aucune alerte active.'"
    />

    @if ($totalAlertes === 0)
        <div class="alert alert-success">
            <strong>Tout est sous contrôle.</strong> Aucune matière en stock critique, aucun devis expiré, aucun chantier en retard, aucune facture impayée en retard.
        </div>
    @endif

    {{-- 1. Stocks critiques --}}
    @if ($stocksBas->isNotEmpty())
    <div class="card mb-3 border-warning">
        <div class="card-header bg-warning bg-opacity-25">
            <strong>{{ $stocksBas->count() }} matière(s) en stock critique</strong>
        </div>
        <div class="card-body p-0">
            <table class="table mb-0">
                <thead><tr><th>Code</th><th>Désignation</th><th class="text-end">Stock disponible</th><th class="text-end">Seuil alerte</th><th></th></tr></thead>
                <tbody>
                @foreach ($stocksBas as $s)
                    @php($dispo = (float) ($s->quantite_actuelle ?? 0) - (float) ($s->quantite_reservee ?? 0))
                    <tr>
                        <td><strong>{{ $s->code }}</strong></td>
                        <td>{{ $s->designation }}</td>
                        <td class="text-end text-danger fw-semibold">{{ $dispo }} {{ $s->unite }}</td>
                        <td class="text-end">{{ $s->seuil_alerte }} {{ $s->unite }}</td>
                        <td><a href="{{ route('menuiserie.stocks.show', ['slug' => request()->route('slug'), 'matiere' => $s->id]) }}" class="btn btn-sm btn-outline-primary">Voir / Réception</a></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- 2. Devis expirés --}}
    @if ($devisExpires->isNotEmpty())
    <div class="card mb-3 border-secondary">
        <div class="card-header bg-secondary bg-opacity-25">
            <strong>{{ $devisExpires->count() }} devis expiré(s) sans suite</strong>
        </div>
        <div class="card-body p-0">
            <table class="table mb-0">
                <thead><tr><th>Numéro</th><th>Client</th><th>Statut</th><th>Validité</th><th class="text-end">Montant TTC</th><th></th></tr></thead>
                <tbody>
                @foreach ($devisExpires as $d)
                    <tr>
                        <td><strong>{{ $d->numero }}</strong></td>
                        <td>#{{ $d->client_id }}</td>
                        <td><span class="badge bg-light text-dark">{{ $d->statut }}</span></td>
                        <td class="small text-muted">créé {{ $d->created_at?->format('Y-m-d') }} ({{ $d->validite_jours }}j)</td>
                        <td class="text-end">{{ number_format((float) $d->montant_ttc, 0, ',', ' ') }} XOF</td>
                        <td><a href="{{ route('menuiserie.devis.show', ['slug' => request()->route('slug'), 'devis' => $d->id]) }}" class="btn btn-sm btn-outline-primary">Voir</a></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- 3. Chantiers en retard --}}
    @if ($chantiersEnRetard->isNotEmpty())
    <div class="card mb-3 border-danger">
        <div class="card-header bg-danger bg-opacity-25">
            <strong>{{ $chantiersEnRetard->count() }} chantier(s) en retard</strong>
        </div>
        <div class="card-body p-0">
            <table class="table mb-0">
                <thead><tr><th>Numéro</th><th>Client</th><th>Statut</th><th>Fin prévue</th><th></th></tr></thead>
                <tbody>
                @foreach ($chantiersEnRetard as $c)
                    <tr>
                        <td><strong>{{ $c->numero }}</strong></td>
                        <td>#{{ $c->client_id }}</td>
                        <td><span class="badge bg-info">{{ $c->statut }}</span></td>
                        <td class="text-danger fw-semibold">{{ optional($c->date_fin_prevue)->format('Y-m-d') }}</td>
                        <td><a href="{{ route('menuiserie.chantiers.show', ['slug' => request()->route('slug'), 'chantier' => $c->id]) }}" class="btn btn-sm btn-outline-primary">Voir</a></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- 4. Factures impayées > 30j --}}
    @if ($facturesImpayees->isNotEmpty())
    <div class="card mb-3 border-danger">
        <div class="card-header bg-danger bg-opacity-25">
            <strong>{{ $facturesImpayees->count() }} facture(s) impayée(s) (> 30 jours)</strong>
        </div>
        <div class="card-body p-0">
            <table class="table mb-0">
                <thead><tr><th>Numéro</th><th>Client</th><th>Statut</th><th>Émise le</th><th class="text-end">TTC</th><th class="text-end">Restant dû</th><th></th></tr></thead>
                <tbody>
                @foreach ($facturesImpayees as $inv)
                    @php($due = max(0, (float) $inv->amount_ttc - (float) $inv->paid_amount))
                    <tr>
                        <td><strong>{{ $inv->invoice_number }}</strong></td>
                        <td>#{{ $inv->client_id }}</td>
                        <td><span class="badge bg-light text-dark">{{ $inv->status }}</span></td>
                        <td>{{ optional($inv->issued_at)->format('Y-m-d') }}</td>
                        <td class="text-end">{{ number_format((float) $inv->amount_ttc, 0, ',', ' ') }}</td>
                        <td class="text-end text-danger fw-semibold">{{ number_format($due, 0, ',', ' ') }}</td>
                        <td><a href="{{ route('menuiserie.factures.show', ['slug' => request()->route('slug'), 'invoice' => $inv->id]) }}" class="btn btn-sm btn-outline-primary">Voir</a></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</x-menuiserie360::layout>
