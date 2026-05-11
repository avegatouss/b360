<x-menuiserie360::layout title="Nouvelle matière première">
    <div class="card">
        <div class="card-header">
            <a href="{{ route('menuiserie.stocks.index', ['slug' => request()->route('slug')]) }}" class="btn btn-sm btn-outline-secondary">&larr; Catalogue</a>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('menuiserie.stocks.store', ['slug' => request()->route('slug')]) }}">
                @csrf
                @include('menuiserie360::stock._form', ['matiere' => null, 'categories' => $categories, 'unites' => $unites])
                <div class="mt-3">
                    <button type="submit" class="btn btn-primary">Créer la matière</button>
                    <a href="{{ route('menuiserie.stocks.index', ['slug' => request()->route('slug')]) }}" class="btn btn-link">Annuler</a>
                </div>
            </form>
        </div>
    </div>
</x-menuiserie360::layout>
