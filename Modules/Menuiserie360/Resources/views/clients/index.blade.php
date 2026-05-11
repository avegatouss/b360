<x-menuiserie360::layout title="Clients menuiserie">
    <div class="card">
        <div class="card-body">
            <p class="text-muted">Liste des clients ayant déjà des données menuiserie (préférences, historique).</p>
            <table class="table">
                <thead><tr><th>Customer ID</th><th>Contact préféré</th><th>Chantiers</th><th class="text-end">CA cumulé</th><th></th></tr></thead>
                <tbody>
                @forelse ($extensions as $ext)
                    <tr>
                        <td>#{{ $ext->customer_id }}</td>
                        <td>{{ $ext->preferred_contact_method ?? '—' }}</td>
                        <td>{{ $ext->total_chantiers_count }}</td>
                        <td class="text-end">{{ number_format((float) $ext->total_revenue_xof, 0, ',', ' ') }} XOF</td>
                        <td><a href="{{ route('menuiserie.clients.show', ['slug' => request()->route('slug'), 'customerId' => $ext->customer_id]) }}" class="btn btn-sm btn-outline-primary">Voir</a></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-muted text-center">Aucun client menuiserie enregistré.</td></tr>
                @endforelse
                </tbody>
            </table>
            {{ $extensions->links() }}
        </div>
    </div>
</x-menuiserie360::layout>
