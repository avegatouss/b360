<x-dashboard::layouts.master
    :title="'Modifier ' . ($channel->name ?? '') . ' — ' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance"
    :pageTitle="'Modifier ' . ($channel->name ?? '')">

<div class="page-header">
    <div class="add-item d-flex">
        <div class="page-title">
            <h4 class="fw-bold">Modifier {{ $channel->name }}</h4>
            <h6>Configuration du canal de distribution</h6>
        </div>
    </div>
    <div class="page-btn">
        <a href="{{ route('eshop360.channels.show', [$instance->slug ?? '', $channel]) }}" class="btn btn-secondary"><i class="ti ti-arrow-left me-1"></i>Retour</a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('eshop360.channels.update', [$instance->slug ?? '', $channel]) }}" method="POST">
            @csrf @method('PUT')
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Nom <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $channel->name) }}" required>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Slug <span class="text-danger">*</span></label>
                    <input type="text" name="slug" class="form-control @error('slug') is-invalid @enderror" value="{{ old('slug', $channel->slug) }}" required>
                    <small class="form-text text-muted">Lettres minuscules, chiffres et tirets uniquement (ex: mon-canal-01)</small>
                    @error('slug')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Code</label>
                    <input type="text" name="code" class="form-control @error('code') is-invalid @enderror" value="{{ old('code', $channel->code) }}">
                    @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Statut</label>
                    <div class="form-check form-switch mt-2">
                        <input type="hidden" name="is_active" value="0">
                        <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active" {{ old('is_active', $channel->is_active) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active">Canal actif</label>
                    </div>
                </div>
                <div class="col-md-12 mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="3">{{ old('description', $channel->description) }}</textarea>
                    @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <hr class="my-4">
            <h5 class="mb-3"><i class="ti ti-percentage me-1"></i>Taux et marges</h5>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Taux de marge <span class="text-danger">*</span></label>
                    <input type="number" step="0.0001" name="margin_rate" class="form-control @error('margin_rate') is-invalid @enderror" value="{{ old('margin_rate', $channel->margin_rate) }}" required>
                    @error('margin_rate')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Taux d'achat <span class="text-danger">*</span></label>
                    <input type="number" step="0.0001" name="buy_rate" class="form-control @error('buy_rate') is-invalid @enderror" value="{{ old('buy_rate', $channel->buy_rate) }}" required>
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
                    <input type="number" step="0.0001" name="debt_share" class="form-control @error('debt_share') is-invalid @enderror" value="{{ old('debt_share', $channel->debt_share) }}" required>
                    @error('debt_share')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Part canal <span class="text-danger">*</span></label>
                    <input type="number" step="0.0001" name="channel_share" class="form-control @error('channel_share') is-invalid @enderror" value="{{ old('channel_share', $channel->channel_share) }}" required>
                    @error('channel_share')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Part propri&eacute;taire <span class="text-danger">*</span></label>
                    <input type="number" step="0.0001" name="owner_share" class="form-control @error('owner_share') is-invalid @enderror" value="{{ old('owner_share', $channel->owner_share) }}" required>
                    @error('owner_share')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="ti ti-check me-1"></i>Mettre &agrave; jour</button>
                <a href="{{ route('eshop360.channels.show', [$instance->slug ?? '', $channel]) }}" class="btn btn-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>

</x-dashboard::layouts.master>
