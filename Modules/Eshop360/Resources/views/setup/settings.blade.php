@extends('eshop360::setup.layout', ['step' => 3])

@section('content')
    @php $slug = $instance->slug ?? ''; @endphp

    <h5 class="fw-bold mb-3"><i class="ti ti-settings me-2"></i>Paramètres de base</h5>
    <p class="text-muted mb-4">Informations de votre entreprise et configuration du point de vente.</p>

    <form method="POST" action="{{ route('eshop360.setup.settings.store', $slug) }}">
        @csrf

        <h6 class="fw-bold mb-3">Informations société</h6>
        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <label class="form-label fw-semibold">Nom de l'entreprise <span class="text-danger">*</span></label>
                <input type="text" name="company_name" class="form-control @error('company_name') is-invalid @enderror"
                       value="{{ old('company_name', $settings['company_name']) }}" required>
                @error('company_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Email</label>
                <input type="email" name="company_email" class="form-control"
                       value="{{ old('company_email', $settings['company_email']) }}">
            </div>
            <div class="col-md-8">
                <label class="form-label fw-semibold">Adresse</label>
                <input type="text" name="company_address" class="form-control"
                       value="{{ old('company_address', $settings['company_address']) }}">
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold">Téléphone</label>
                <input type="text" name="company_phone" class="form-control"
                       value="{{ old('company_phone', $settings['company_phone']) }}">
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold">N° fiscal</label>
                <input type="text" name="tax_number" class="form-control"
                       value="{{ old('tax_number', $settings['tax_number']) }}">
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold">Devise</label>
                <input type="text" name="currency_symbol" class="form-control"
                       value="{{ old('currency_symbol', $settings['currency_symbol']) }}">
            </div>
        </div>

        <hr>

        <h6 class="fw-bold mb-3">Point de vente</h6>
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label fw-semibold">Layout POS</label>
                <select name="pos_layout" class="form-select">
                    @foreach(['layout1' => 'Layout 1 (Standard)', 'layout2' => 'Layout 2', 'layout3' => 'Layout 3', 'layout4' => 'Layout 4', 'layout5' => 'Layout 5'] as $val => $label)
                        <option value="{{ $val }}" @selected(old('pos_layout', $settings['pos_layout']) === $val)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Modes de paiement</label>
                @foreach(['cash' => 'Espèces', 'card' => 'Carte', 'cheque' => 'Chèque', 'bank_transfer' => 'Virement'] as $val => $label)
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="payment_methods[]" value="{{ $val }}"
                               id="pm-{{ $val }}" @checked(in_array($val, old('payment_methods', $settings['payment_methods'])))>
                        <label class="form-check-label" for="pm-{{ $val }}">{{ $label }}</label>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="wizard-footer">
            <a href="{{ route('eshop360.setup.channels', $slug) }}" class="btn btn-outline-secondary">
                <i class="ti ti-arrow-left me-1"></i> Retour
            </a>
            <button type="submit" class="btn btn-success">
                <i class="ti ti-check me-1"></i> Terminer et activer
            </button>
        </div>
    </form>
@endsection
