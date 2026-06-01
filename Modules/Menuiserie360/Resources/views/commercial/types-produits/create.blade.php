<x-menuiserie360::layout title="Nouveau type produit menuiserie">
    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('menuiserie.types-produits.store', ['slug' => request()->route('slug')]) }}">
                @csrf

                <div class="row g-3 mb-3">
                    <div class="col-md-3">
                        <label class="form-label">Code *</label>
                        <input type="text" name="code" maxlength="50" required class="form-control"/>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Nom *</label>
                        <input type="text" name="nom" maxlength="200" required class="form-control"/>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Catégorie *</label>
                        <select name="categorie" class="form-select" required>
                            @foreach ($categories as $cat)
                                <option value="{{ $cat->value }}">{{ $cat->value }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="description" rows="2" maxlength="5000" class="form-control"></textarea>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-3">
                        <label class="form-label">Largeur standard (mm)</label>
                        <input type="number" name="largeur_standard_mm" min="1" class="form-control"/>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Hauteur standard (mm)</label>
                        <input type="number" name="hauteur_standard_mm" min="1" class="form-control"/>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Prix indicatif HT</label>
                        <input type="number" name="prix_indicatif_ht" step="0.01" min="0" class="form-control"/>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Coût indicatif</label>
                        <input type="number" name="cout_indicatif" step="0.01" min="0" class="form-control"/>
                    </div>
                </div>

                <p class="text-muted small">Matières principales (pour le calcul auto besoin OF) : à configurer après création via l'éditeur.</p>

                <button type="submit" class="btn btn-primary">Créer</button>
                <a href="{{ route('menuiserie.types-produits.index', ['slug' => request()->route('slug')]) }}" class="btn btn-link">Annuler</a>
            </form>
        </div>
    </div>
</x-menuiserie360::layout>
