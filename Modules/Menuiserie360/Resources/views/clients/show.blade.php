<x-menuiserie360::layout title="Client {{ $client->name }}">
    <div class="card">
        <div class="card-body">
            <p><strong>Code :</strong> {{ $client->code }}</p>
            <p><strong>Nom :</strong> {{ $client->name }}</p>
            <p><strong>Email :</strong> {{ $client->email ?? '—' }}</p>
            <p><strong>Téléphone :</strong> {{ $client->phone ?? '—' }}</p>
            <p><strong>Adresse :</strong> {{ $client->address ?? '—' }}, {{ $client->city ?? '' }}</p>
            <p><strong>Statut :</strong>
                @if ($client->isActive)
                    <span class="badge bg-success">Actif</span>
                @else
                    <span class="badge bg-secondary">Inactif</span>
                @endif
            </p>

            <hr/>
            <h5>Données menuiserie</h5>
            <p><strong>Contact préféré :</strong> {{ $client->preferredContactMethod ?? '—' }}</p>
            <p><strong>Chantiers réalisés :</strong> {{ $client->totalChantiersCount }}</p>
            <p><strong>CA cumulé :</strong> {{ number_format($client->totalRevenueXof, 2, ',', ' ') }} XOF</p>
        </div>
    </div>
</x-menuiserie360::layout>
