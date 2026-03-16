<x-dashboard::layouts.master
    :title="'Nouveau canal de distribution — ' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance"
    pageTitle="Nouveau canal de distribution">

<div class="page-header">
    <div class="add-item d-flex">
        <div class="page-title">
            <h4 class="fw-bold">Nouveau canal de distribution</h4>
            <h6>Configurer un nouveau canal de vente</h6>
        </div>
    </div>
    <div class="page-btn">
        <a href="{{ route('eshop360.channels.index', $instance->slug ?? '') }}" class="btn btn-secondary"><i class="ti ti-arrow-left me-1"></i>Retour</a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('eshop360.channels.store', $instance->slug ?? '') }}" method="POST">
            @csrf
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Nom <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Slug <span class="text-danger">*</span></label>
                    <input type="text" name="slug" class="form-control @error('slug') is-invalid @enderror" value="{{ old('slug') }}" required>
                    <small class="form-text text-muted">Lettres minuscules, chiffres et tirets uniquement (ex: mon-canal-01)</small>
                    @error('slug')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Code</label>
                    <input type="text" name="code" class="form-control @error('code') is-invalid @enderror" value="{{ old('code') }}">
                    @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-12 mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="3">{{ old('description') }}</textarea>
                    @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <hr class="my-4">
            <h5 class="mb-3"><i class="ti ti-percentage me-1"></i>Taux et marges</h5>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Taux de marge <span class="text-danger">*</span></label>
                    <input type="number" step="0.0001" name="margin_rate" class="form-control @error('margin_rate') is-invalid @enderror" value="{{ old('margin_rate', 0.13) }}" required>
                    @error('margin_rate')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Taux d'achat <span class="text-danger">*</span></label>
                    <input type="number" step="0.0001" name="buy_rate" class="form-control @error('buy_rate') is-invalid @enderror" value="{{ old('buy_rate', 0.20) }}" required>
                    @error('buy_rate')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <hr class="my-4">
            <h5 class="mb-3"><i class="ti ti-chart-pie me-1"></i>R&eacute;partition des parts</h5>

            <div class="alert alert-info">
                <i class="ti ti-info-circle me-1"></i>
                Le taux de marge calcule le PGHT, le taux d'achat calcule le prix canal. Les parts (dette, canal, propri&eacute;taire) doivent totaliser 1.
            </div>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Part dette <span class="text-danger">*</span></label>
                    <input type="number" step="0.0001" name="debt_share" class="form-control @error('debt_share') is-invalid @enderror" value="{{ old('debt_share', 0.3333) }}" required>
                    @error('debt_share')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Part canal <span class="text-danger">*</span></label>
                    <input type="number" step="0.0001" name="channel_share" class="form-control @error('channel_share') is-invalid @enderror" value="{{ old('channel_share', 0.3333) }}" required>
                    @error('channel_share')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Part propri&eacute;taire <span class="text-danger">*</span></label>
                    <input type="number" step="0.0001" name="owner_share" class="form-control @error('owner_share') is-invalid @enderror" value="{{ old('owner_share', 0.3334) }}" required>
                    @error('owner_share')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="ti ti-check me-1"></i>Cr&eacute;er le canal</button>
                <a href="{{ route('eshop360.channels.index', $instance->slug ?? '') }}" class="btn btn-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>

</x-dashboard::layouts.master>
