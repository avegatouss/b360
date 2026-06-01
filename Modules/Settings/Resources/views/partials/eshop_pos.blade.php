@php
    use Illuminate\Support\Facades\Cache;
    $instanceId = $instance->id ?? 0;
    $posSettings = Cache::get("eshop_pos_settings_{$instanceId}", []);
@endphp

<div class="card mb-3">
    <div class="card-header">
        <h5 class="card-title mb-0">Parametres du Point de Vente (POS)</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Disposition par defaut</label>
                <select name="settings[default_layout]" class="form-select">
                    @php $layout = $values['default_layout'] ?? $posSettings['default_layout'] ?? 'layout1'; @endphp
                    <option value="layout1" @selected($layout === 'layout1')>Disposition 1</option>
                    <option value="layout2" @selected($layout === 'layout2')>Disposition 2</option>
                    <option value="layout3" @selected($layout === 'layout3')>Disposition 3</option>
                    <option value="layout4" @selected($layout === 'layout4')>Disposition 4</option>
                    <option value="layout5" @selected($layout === 'layout5')>Disposition 5</option>
                </select>
                <input type="hidden" name="types[default_layout]" value="string">
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Produits par page</label>
                <input type="number" name="settings[products_per_page]" class="form-control"
                       value="{{ $values['products_per_page'] ?? $posSettings['products_per_page'] ?? 24 }}"
                       min="10" max="100">
                <input type="hidden" name="types[products_per_page]" value="integer">
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label">Modes de paiement actifs</label>
            @php
                $methods = ['cash' => 'Especes', 'card' => 'Carte', 'cheque' => 'Cheque', 'paypal' => 'PayPal', 'bank_transfer' => 'Virement', 'points' => 'Points', 'deposit' => 'Acompte', 'gift_card' => 'Carte cadeau', 'external' => 'Externe'];
                $active = $values['payment_methods'] ?? $posSettings['payment_methods'] ?? ['cash', 'card'];
                if (is_string($active)) { $active = json_decode($active, true) ?: []; }
            @endphp
            <div class="d-flex flex-wrap gap-3">
                @foreach($methods as $key => $label)
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox"
                               name="settings[payment_methods][]" value="{{ $key }}"
                               id="pm_{{ $key }}"
                               @checked(in_array($key, $active))>
                        <label class="form-check-label" for="pm_{{ $key }}">{{ $label }}</label>
                    </div>
                @endforeach
            </div>
            <input type="hidden" name="types[payment_methods]" value="json">
        </div>

        <div class="row">
            <div class="col-md-4 mb-3">
                <label class="form-label">Remise par defaut (%)</label>
                <input type="number" name="settings[default_discount]" class="form-control"
                       value="{{ $values['default_discount'] ?? $posSettings['default_discount'] ?? 0 }}"
                       min="0" max="100" step="0.01">
                <input type="hidden" name="types[default_discount]" value="string">
            </div>
        </div>

        <div class="row">
            <div class="col-md-4 mb-3">
                <div class="form-check form-switch">
                    <input type="hidden" name="settings[tax_inclusive]" value="0">
                    <input class="form-check-input" type="checkbox"
                           name="settings[tax_inclusive]" value="1" id="posTaxInclusive"
                           @checked(($values['tax_inclusive'] ?? $posSettings['tax_inclusive'] ?? false) == true)>
                    <input type="hidden" name="types[tax_inclusive]" value="boolean">
                    <label class="form-check-label" for="posTaxInclusive">Prix TTC</label>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="form-check form-switch">
                    <input type="hidden" name="settings[sound_enabled]" value="0">
                    <input class="form-check-input" type="checkbox"
                           name="settings[sound_enabled]" value="1" id="posSound"
                           @checked(($values['sound_enabled'] ?? $posSettings['sound_enabled'] ?? true) == true)>
                    <input type="hidden" name="types[sound_enabled]" value="boolean">
                    <label class="form-check-label" for="posSound">Effets sonores</label>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="form-check form-switch">
                    <input type="hidden" name="settings[print_receipt]" value="0">
                    <input class="form-check-input" type="checkbox"
                           name="settings[print_receipt]" value="1" id="posPrintReceipt"
                           @checked(($values['print_receipt'] ?? $posSettings['print_receipt'] ?? true) == true)>
                    <input type="hidden" name="types[print_receipt]" value="boolean">
                    <label class="form-check-label" for="posPrintReceipt">Imprimer le recu</label>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4 mb-3">
                <div class="form-check form-switch">
                    <input type="hidden" name="settings[allow_manual_price]" value="0">
                    <input class="form-check-input" type="checkbox"
                           name="settings[allow_manual_price]" value="1" id="posManualPrice"
                           @checked(($values['allow_manual_price'] ?? $posSettings['allow_manual_price'] ?? false) == true)>
                    <input type="hidden" name="types[allow_manual_price]" value="boolean">
                    <label class="form-check-label" for="posManualPrice">Autoriser le prix manuel</label>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="form-check form-switch">
                    <input type="hidden" name="settings[barcode_scanner]" value="0">
                    <input class="form-check-input" type="checkbox"
                           name="settings[barcode_scanner]" value="1" id="posBarcodeScanner"
                           @checked(($values['barcode_scanner'] ?? $posSettings['barcode_scanner'] ?? true) == true)>
                    <input type="hidden" name="types[barcode_scanner]" value="boolean">
                    <label class="form-check-label" for="posBarcodeScanner">Scanner de codes-barres</label>
                </div>
            </div>
        </div>
    </div>
</div>
