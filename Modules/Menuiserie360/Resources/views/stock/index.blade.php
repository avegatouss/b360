<x-menuiserie360::layout title="Stock matières premières">
    <div class="card">
        <div class="card-body">
            <table class="table">
                <thead><tr><th>Code</th><th>Désignation</th><th>Catégorie</th><th>Unité</th><th>Seuil alerte</th><th></th></tr></thead>
                <tbody>
                @forelse ($matieres as $m)
                    <tr>
                        <td><strong>{{ $m->code }}</strong></td>
                        <td>{{ $m->designation }}</td>
                        <td>{{ $m->categorie }}</td>
                        <td>{{ $m->unite }}</td>
                        <td>{{ $m->seuil_alerte }}</td>
                        <td><a href="{{ route('menuiserie.stocks.show', ['slug' => request()->route('slug'), 'matiere' => $m->id]) }}" class="btn btn-sm btn-outline-primary">Voir / Réception</a></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-muted text-center">Aucune matière au catalogue.</td></tr>
                @endforelse
                </tbody>
            </table>
            {{ $matieres->links() }}
        </div>
    </div>
</x-menuiserie360::layout>
