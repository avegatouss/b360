@extends('eshop360::setup.layout', ['step' => 1])

@section('content')
    @php $slug = $instance->slug ?? ''; @endphp

    <h5 class="fw-bold mb-3"><i class="ti ti-building-store me-2"></i>Hub central</h5>
    <p class="text-muted mb-4">Le hub central est votre point d'accès principal. Il donne accès à tous les modules.</p>

    <form method="POST" action="{{ route('eshop360.setup.hub.store', $slug) }}">
        @csrf

        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label fw-semibold">Nom du hub <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                       value="{{ old('name', $hub['name']) }}" required>
                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">Code <span class="text-danger">*</span></label>
                <input type="text" name="code" class="form-control @error('code') is-invalid @enderror"
                       value="{{ old('code', $hub['code']) }}" maxlength="20" required>
                @error('code') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">Couleur thème</label>
                <input type="color" name="theme_color" class="form-control form-control-color w-100"
                       value="{{ old('theme_color', $hub['theme_color']) }}">
            </div>
        </div>

        <hr class="my-4">

        <h6 class="fw-bold mb-3"><i class="ti ti-toggles me-2"></i>Modules activés</h6>
        <p class="text-muted small mb-3">Tous les modules sont activés par défaut pour le hub central.</p>

        <div class="row g-2">
            @foreach($featureDefaults as $feature => $default)
                <div class="col-md-4">
                    <div class="form-check form-switch">
                        <input type="hidden" name="features[{{ $feature }}]" value="0">
                        <input class="form-check-input" type="checkbox" name="features[{{ $feature }}]" value="1"
                               id="feat-{{ $feature }}" checked>
                        <label class="form-check-label" for="feat-{{ $feature }}">{{ ucfirst(str_replace('_', ' ', $feature)) }}</label>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="wizard-footer">
            <div></div>
            <button type="submit" class="btn btn-primary">
                Suivant <i class="ti ti-arrow-right ms-1"></i>
            </button>
        </div>
    </form>
@endsection
