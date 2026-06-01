<x-menuiserie360::layout title="Modifier {{ $matiere->code }} — {{ $matiere->designation }}">
    <div class="card">
        <div class="card-header">
            <a href="{{ route('menuiserie.stocks.show', ['slug' => request()->route('slug'), 'matiere' => $matiere->id]) }}" class="btn btn-sm btn-outline-secondary">&larr; Retour</a>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('menuiserie.stocks.update', ['slug' => request()->route('slug'), 'matiere' => $matiere->id]) }}">
                @csrf
                @method('PUT')
                @include('menuiserie360::stock._form', ['matiere' => $matiere, 'categories' => $categories, 'unites' => $unites])
                <div class="mt-3">
                    <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
                    <a href="{{ route('menuiserie.stocks.show', ['slug' => request()->route('slug'), 'matiere' => $matiere->id]) }}" class="btn btn-link">Annuler</a>
                </div>
            </form>
        </div>
    </div>
</x-menuiserie360::layout>
