<x-menuiserie360::layout title="Ordres de fabrication">
    <div class="card">
        <div class="card-body">
            <table class="table">
                <thead><tr><th>Numéro</th><th>BC</th><th>Statut</th><th>Planifié</th><th></th></tr></thead>
                <tbody>
                @forelse ($ofs as $of)
                    <tr>
                        <td><strong>{{ $of->numero }}</strong></td>
                        <td>#{{ $of->bc_id }}</td>
                        <td><span class="badge bg-secondary">{{ $of->statut }}</span></td>
                        <td>{{ optional($of->date_planifiee)->format('Y-m-d') ?? '—' }}</td>
                        <td><a href="{{ route('menuiserie.production.show', ['slug' => request()->route('slug'), 'of' => $of->id]) }}" class="btn btn-sm btn-outline-primary">Atelier</a></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-muted text-center">Aucun OF.</td></tr>
                @endforelse
                </tbody>
            </table>
            {{ $ofs->links() }}
        </div>
    </div>
</x-menuiserie360::layout>
