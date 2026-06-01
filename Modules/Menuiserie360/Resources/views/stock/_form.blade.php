{{-- Partial réutilisé par stock/create.blade.php et stock/edit.blade.php. --}}
@php($current = $matiere ?? null)
<div class="row g-3">
    <div class="col-md-4">
        <label class="form-label">Code <span class="text-danger">*</span></label>
        <input type="text" name="code" maxlength="50" required class="form-control" value="{{ old('code', $current?->code) }}"/>
        <small class="text-muted">Unique par instance. Ex : ALU-1234.</small>
    </div>
    <div class="col-md-8">
        <label class="form-label">Désignation <span class="text-danger">*</span></label>
        <input type="text" name="designation" maxlength="200" required class="form-control" value="{{ old('designation', $current?->designation) }}"/>
    </div>

    <div class="col-md-4">
        <label class="form-label">Catégorie <span class="text-danger">*</span></label>
        <select name="categorie" required class="form-select">
            @foreach ($categories as $cat)
                <option value="{{ $cat->value }}" @selected(old('categorie', $current?->categorie) === $cat->value)>{{ $cat->value }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4">
        <label class="form-label">Unité <span class="text-danger">*</span></label>
        <select name="unite" required class="form-select">
            @foreach ($unites as $u)
                <option value="{{ $u->value }}" @selected(old('unite', $current?->unite) === $u->value)>{{ $u->value }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4">
        <label class="form-label">Prix unitaire (XOF) <span class="text-danger">*</span></label>
        <input type="number" name="prix_unitaire" step="0.0001" min="0" required class="form-control" value="{{ old('prix_unitaire', $current?->prix_unitaire ?? 0) }}"/>
    </div>

    <div class="col-md-4">
        <label class="form-label">Seuil d'alerte <span class="text-danger">*</span></label>
        <input type="number" name="seuil_alerte" step="0.0001" min="0" required class="form-control" value="{{ old('seuil_alerte', $current?->seuil_alerte ?? 0) }}"/>
        <small class="text-muted">Quantité à partir de laquelle la matière est notée en stock bas.</small>
    </div>
    <div class="col-md-8">
        <label class="form-label">Fournisseur principal</label>
        <input type="text" name="fournisseur_principal" maxlength="200" class="form-control" value="{{ old('fournisseur_principal', $current?->fournisseur_principal) }}"/>
    </div>

    <div class="col-12">
        <label class="form-label">Notes</label>
        <textarea name="notes" rows="3" maxlength="5000" class="form-control">{{ old('notes', $current?->notes) }}</textarea>
    </div>

    <div class="col-12">
        <div class="form-check">
            <input type="hidden" name="is_active" value="0"/>
            <input type="checkbox" name="is_active" value="1" id="is_active" class="form-check-input" @checked(old('is_active', $current?->is_active ?? true))>
            <label class="form-check-label" for="is_active">Matière active</label>
        </div>
    </div>
</div>
