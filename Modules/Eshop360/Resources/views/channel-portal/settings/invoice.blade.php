@extends('eshop360::channel-portal.layouts.master')

@section('content')
@php
    $instance = \Modules\Core\Support\CurrentInstance::get();
    $slug = $instance->slug ?? '';
    $channelKey = $channel->slug ?? $channel->id;
@endphp

<div class="mb-4">
    <h4 class="fw-bold mb-1">{{ __('Parametres facturation') }}</h4>
    <p class="text-muted mb-0">{{ __('Informations affichees sur les factures et devis') }}</p>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <form method="POST" action="{{ route('eshop360.channel-portal.settings.invoice.update', [$slug, $channelKey]) }}">
            @csrf
            @method('PUT')

            <h6 class="fw-bold mb-3">{{ __('Informations societe') }}</h6>
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label">{{ __('Nom de la societe') }}</label>
                    <input type="text" name="company_name" class="form-control"
                           value="{{ old('company_name', $settings['company_name'] ?? '') }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">{{ __('Numero fiscal / contribuable') }}</label>
                    <input type="text" name="tax_number" class="form-control"
                           value="{{ old('tax_number', $settings['tax_number'] ?? '') }}">
                </div>
                <div class="col-md-12">
                    <label class="form-label">{{ __('Adresse') }}</label>
                    <textarea name="company_address" class="form-control" rows="2">{{ old('company_address', $settings['company_address'] ?? '') }}</textarea>
                </div>
                <div class="col-md-4">
                    <label class="form-label">{{ __('Telephone') }}</label>
                    <input type="text" name="company_phone" class="form-control"
                           value="{{ old('company_phone', $settings['company_phone'] ?? '') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">{{ __('Email') }}</label>
                    <input type="email" name="company_email" class="form-control"
                           value="{{ old('company_email', $settings['company_email'] ?? '') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">{{ __('Symbole devise') }}</label>
                    <input type="text" name="currency_symbol" class="form-control"
                           value="{{ old('currency_symbol', $settings['currency_symbol'] ?? 'XAF') }}">
                </div>
            </div>

            <h6 class="fw-bold mb-3">{{ __('Parametres facture') }}</h6>
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <label class="form-label">{{ __('Delai de paiement (jours)') }}</label>
                    <input type="number" name="default_due_days" class="form-control" min="0"
                           value="{{ old('default_due_days', $settings['default_due_days'] ?? 30) }}">
                </div>
                <div class="col-md-8">
                    <label class="form-label">{{ __('Conditions par defaut') }}</label>
                    <textarea name="default_terms" class="form-control" rows="2">{{ old('default_terms', $settings['default_terms'] ?? '') }}</textarea>
                </div>
                <div class="col-md-12">
                    <label class="form-label">{{ __('Pied de page facture') }}</label>
                    <textarea name="default_footer" class="form-control" rows="2">{{ old('default_footer', $settings['default_footer'] ?? '') }}</textarea>
                </div>
            </div>

            <h6 class="fw-bold mb-3">{{ __('Coordonnees bancaires') }}</h6>
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <label class="form-label">{{ __('Nom de la banque') }}</label>
                    <input type="text" name="bank_name" class="form-control"
                           value="{{ old('bank_name', $settings['bank_name'] ?? '') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">{{ __('Numero de compte') }}</label>
                    <input type="text" name="bank_account" class="form-control"
                           value="{{ old('bank_account', $settings['bank_account'] ?? '') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">{{ __('IBAN') }}</label>
                    <input type="text" name="bank_iban" class="form-control"
                           value="{{ old('bank_iban', $settings['bank_iban'] ?? '') }}">
                </div>
            </div>

            <div class="mt-2">
                <button type="submit" class="btn btn-primary">
                    <i class="ti ti-device-floppy me-1"></i> {{ __('Enregistrer') }}
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
