<x-dashboard::layouts.master
    :title="__('POS Settings') . ' — ' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance"
    :pageTitle="__('POS Settings')">

@php
    $paymentMethodOptions = [
        'cash'          => __('Especes'),
        'card'          => __('Carte bancaire'),
        'cheque'        => __('Cheque'),
        'bank_transfer' => __('Virement bancaire'),
        'wallet'        => __('Compte client'),
        'gift_card'     => __('Carte cadeau'),
        'points'        => __('Points fidelite'),
        'deposit'       => __('Acompte'),
        'paypal'        => __('PayPal'),
        'external'      => __('Externe'),
    ];
    $activePaymentMethods = $settings['payment_methods'] ?? ['cash', 'card'];
    $layoutOptions = [
        'layout1' => __('Classique'),
        'layout2' => __('Layout 2'),
        'layout3' => __('Layout 3'),
        'layout4' => __('Layout 4'),
        'layout5' => __('Layout 5'),
    ];
@endphp

<div class="page-header">
    <div class="add-item d-flex">
        <div class="page-title">
            <h4>{{ __('Parametres POS') }}</h4>
            <h6>{{ __('Configurez le point de vente') }}</h6>
        </div>
    </div>
</div>

<form method="POST" action="{{ route('eshop360.pos.settings.update', $instance->slug ?? '') }}">
    @csrf
    @method('PUT')

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        {{-- ============================== REGISTRE DE CAISSE ============================== --}}
        <div class="col-lg-6 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="ti ti-cash-register me-2 text-primary"></i>{{ __('Registre de caisse') }}</h5>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1">{{ __('Caisse obligatoire') }}</h6>
                                <p class="text-muted mb-0" style="font-size: .85rem;">{{ __('Exiger l\'ouverture d\'une caisse avant de pouvoir effectuer une vente au POS.') }}</p>
                            </div>
                            <div class="form-check form-switch ms-3">
                                <input type="hidden" name="register_required" value="0">
                                <input class="form-check-input" type="checkbox" role="switch" id="register_required" name="register_required" value="1" {{ !empty($settings['register_required']) ? 'checked' : '' }}>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1">{{ __('Client de passage (walk-in)') }}</h6>
                                <p class="text-muted mb-0" style="font-size: .85rem;">{{ __('Autoriser les ventes sans selectionner de client. Si desactive, un client doit obligatoirement etre selectionne.') }}</p>
                            </div>
                            <div class="form-check form-switch ms-3">
                                <input type="hidden" name="allow_walkin_customer" value="0">
                                <input class="form-check-input" type="checkbox" role="switch" id="allow_walkin_customer" name="allow_walkin_customer" value="1" {{ ($settings['allow_walkin_customer'] ?? true) ? 'checked' : '' }}>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="text-muted" style="font-size: .85rem;">
                        <i class="ti ti-info-circle me-1"></i>
                        {{ __('Quand active, le caissier devra ouvrir une caisse (avec un fond de caisse) avant de pouvoir valider des ventes. A la fermeture, un ecart sera calcule automatiquement entre le montant theorique et le montant reel.') }}
                    </div>
                </div>
            </div>
        </div>

        {{-- ============================== COMPTE CLIENT ============================== --}}
        <div class="col-lg-6 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="ti ti-wallet me-2 text-success"></i>{{ __('Compte client') }}</h5>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1">{{ __('Activer le paiement par compte') }}</h6>
                                <p class="text-muted mb-0" style="font-size: .85rem;">{{ __('Permet aux clients disposant d\'un credit de payer leurs achats via leur solde de compte (portefeuille).') }}</p>
                            </div>
                            <div class="form-check form-switch ms-3">
                                <input type="hidden" name="customer_account_enabled" value="0">
                                <input class="form-check-input" type="checkbox" role="switch" id="customer_account_enabled" name="customer_account_enabled" value="1" {{ !empty($settings['customer_account_enabled']) ? 'checked' : '' }}>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="text-muted" style="font-size: .85rem;">
                        <i class="ti ti-info-circle me-1"></i>
                        {{ __('Quand active, le mode de paiement "Compte client" apparait au POS. Le client doit avoir un solde suffisant ou un credit autorise. Le rechargement du compte se fait depuis la fiche client.') }}
                    </div>
                </div>
            </div>
        </div>

        {{-- ============================== MOYENS DE PAIEMENT ============================== --}}
        <div class="col-lg-6 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="ti ti-credit-card me-2 text-info"></i>{{ __('Moyens de paiement') }}</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-3" style="font-size: .85rem;">{{ __('Selectionnez les moyens de paiement disponibles au POS.') }}</p>
                    <div class="row g-2">
                        @foreach($paymentMethodOptions as $value => $label)
                            <div class="col-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="payment_methods[]" value="{{ $value }}" id="pm_{{ $value }}" {{ in_array($value, $activePaymentMethods) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="pm_{{ $value }}">{{ $label }}</label>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- ============================== GENERAL ============================== --}}
        <div class="col-lg-6 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="ti ti-settings me-2 text-secondary"></i>{{ __('General') }}</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold" for="default_layout">{{ __('Layout par defaut') }}</label>
                        <select name="default_layout" id="default_layout" class="form-select">
                            @foreach($layoutOptions as $val => $label)
                                <option value="{{ $val }}" {{ ($settings['default_layout'] ?? 'layout1') === $val ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold" for="products_per_page">{{ __('Produits par page') }}</label>
                        <input type="number" name="products_per_page" id="products_per_page" class="form-control" min="10" max="100" value="{{ $settings['products_per_page'] ?? 24 }}">
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <label class="form-label fw-semibold mb-0">{{ __('Son') }}</label>
                        <div class="form-check form-switch">
                            <input type="hidden" name="sound_enabled" value="0">
                            <input class="form-check-input" type="checkbox" role="switch" name="sound_enabled" value="1" {{ !empty($settings['sound_enabled']) ? 'checked' : '' }}>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <label class="form-label fw-semibold mb-0">{{ __('Impression ticket') }}</label>
                        <div class="form-check form-switch">
                            <input type="hidden" name="print_receipt" value="0">
                            <input class="form-check-input" type="checkbox" role="switch" name="print_receipt" value="1" {{ !empty($settings['print_receipt']) ? 'checked' : '' }}>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <label class="form-label fw-semibold mb-0">{{ __('Prix TTC') }}</label>
                        <div class="form-check form-switch">
                            <input type="hidden" name="tax_inclusive" value="0">
                            <input class="form-check-input" type="checkbox" role="switch" name="tax_inclusive" value="1" {{ !empty($settings['tax_inclusive']) ? 'checked' : '' }}>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ============================== SUBMIT ============================== --}}
    <div class="d-flex justify-content-end gap-2 mb-4">
        <a href="{{ route('eshop360.pos.index', $instance->slug ?? '') }}" class="btn btn-outline-secondary">
            <i class="ti ti-arrow-left me-1"></i>{{ __('Retour au POS') }}
        </a>
        <button type="submit" class="btn btn-primary">
            <i class="ti ti-device-floppy me-1"></i>{{ __('Enregistrer') }}
        </button>
    </div>
</form>

</x-dashboard::layouts.master>
