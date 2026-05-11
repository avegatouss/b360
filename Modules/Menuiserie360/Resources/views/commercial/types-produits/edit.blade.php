<x-menuiserie360::layout title="Éditer {{ $type->code }}">
    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('menuiserie.types-produits.update', ['slug' => request()->route('slug'), 'type' => $type->id]) }}">
                @csrf @method('PUT')

                <div class="row g-3 mb-3">
                    <div class="col-md-3">
                        <label class="form-label">Code (readonly)</label>
                        <input type="text" value="{{ $type->code }}" readonly class="form-control"/>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Nom *</label>
                        <input type="text" name="nom" value="{{ old('nom', $type->nom) }}" maxlength="200" required class="form-control"/>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Catégorie *</label>
                        <select name="categorie" class="form-select" required>
                            @foreach ($categories as $cat)
                                <option value="{{ $cat->value }}" @selected(old('categorie', $type->categorie) === $cat->value)>{{ $cat->value }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="description" rows="2" maxlength="5000" class="form-control">{{ old('description', $type->description) }}</textarea>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-3">
                        <label class="form-label">Largeur standard (mm)</label>
                        <input type="number" name="largeur_standard_mm" value="{{ old('largeur_standard_mm', $type->largeur_standard_mm) }}" min="1" class="form-control"/>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Hauteur standard (mm)</label>
                        <input type="number" name="hauteur_standard_mm" value="{{ old('hauteur_standard_mm', $type->hauteur_standard_mm) }}" min="1" class="form-control"/>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Prix indicatif HT</label>
                        <input type="number" name="prix_indicatif_ht" value="{{ old('prix_indicatif_ht', $type->prix_indicatif_ht) }}" step="0.01" min="0" class="form-control"/>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Coût indicatif</label>
                        <input type="number" name="cout_indicatif" value="{{ old('cout_indicatif', $type->cout_indicatif) }}" step="0.01" min="0" class="form-control"/>
                    </div>
                </div>

                <div class="mb-3 form-check">
                    <input type="checkbox" name="is_active" value="1" id="is_active" @checked(old('is_active', $type->is_active)) class="form-check-input"/>
                    <label class="form-check-label" for="is_active">Actif</label>
                </div>

                <button type="submit" class="btn btn-primary">Mettre à jour</button>
                <a href="{{ route('menuiserie.types-produits.show', ['slug' => request()->route('slug'), 'type' => $type->id]) }}" class="btn btn-link">Annuler</a>
            </form>
        </div>
    </div>
</x-menuiserie360::layout>
