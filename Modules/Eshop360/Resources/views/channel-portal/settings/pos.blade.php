@extends('eshop360::channel-portal.layouts.master')

@section('content')
@php
    $instance = \Modules\Core\Support\CurrentInstance::get();
    $slug = $instance->slug ?? '';
    $channelKey = $channel->slug ?? $channel->id;
@endphp

<div class="mb-4">
    <h4 class="fw-bold mb-1">{{ __('Parametres POS') }}</h4>
    <p class="text-muted mb-0">{{ __('Configuration du point de vente pour ce canal') }}</p>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <form method="POST" action="{{ route('eshop360.channel-portal.settings.pos.update', [$slug, $channelKey]) }}">
            @csrf
            @method('PUT')

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">{{ __('Produits par page') }}</label>
                    <input type="number" name="products_per_page" class="form-control" min="4" max="100"
                           value="{{ old('products_per_page', $settings['products_per_page'] ?? 20) }}">
                </div>

                <div class="col-md-6">
                    <label class="form-label">{{ __('Modes de paiement') }}</label>
                    @php
                        $paymentMethods = $settings['payment_methods'] ?? [];
                        $allMethods = ['cash' => 'Especes', 'card' => 'Carte bancaire', 'mobile_money' => 'Mobile Money', 'bank_transfer' => 'Virement', 'credit' => 'Credit'];
                    @endphp
                    @foreach($allMethods as $key => $label)
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="payment_methods[]" value="{{ $key }}" id="pm_{{ $key }}"
                                   {{ in_array($key, (array)$paymentMethods) ? 'checked' : '' }}>
                            <label class="form-check-label" for="pm_{{ $key }}">{{ __($label) }}</label>
                        </div>
                    @endforeach
                </div>

                <div class="col-md-6">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="tax_inclusive" id="tax_inclusive" value="1"
                               {{ ($settings['tax_inclusive'] ?? false) ? 'checked' : '' }}>
                        <label class="form-check-label" for="tax_inclusive">{{ __('Prix TTC (taxes incluses)') }}</label>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="register_required" id="register_required" value="1"
                               {{ ($settings['register_required'] ?? false) ? 'checked' : '' }}>
                        <label class="form-check-label" for="register_required">{{ __('Caisse obligatoire') }}</label>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="allow_walkin_customer" id="allow_walkin_customer" value="1"
                               {{ ($settings['allow_walkin_customer'] ?? true) ? 'checked' : '' }}>
                        <label class="form-check-label" for="allow_walkin_customer">{{ __('Autoriser client anonyme') }}</label>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="allow_manual_price" id="allow_manual_price" value="1"
                               {{ ($settings['allow_manual_price'] ?? false) ? 'checked' : '' }}>
                        <label class="form-check-label" for="allow_manual_price">{{ __('Autoriser modification du prix') }}</label>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="barcode_scanner" id="barcode_scanner" value="1"
                               {{ ($settings['barcode_scanner'] ?? false) ? 'checked' : '' }}>
                        <label class="form-check-label" for="barcode_scanner">{{ __('Activer lecteur code-barres') }}</label>
                    </div>
                </div>
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="ti ti-device-floppy me-1"></i> {{ __('Enregistrer') }}
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
