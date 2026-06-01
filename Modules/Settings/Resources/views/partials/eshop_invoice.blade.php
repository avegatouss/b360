@php
    use Illuminate\Support\Facades\Cache;
    $instanceId = $instance->id ?? 0;
    $invoiceSettings = Cache::get("eshop_invoice_settings_{$instanceId}", []);
@endphp

<div class="card mb-3">
    <div class="card-header">
        <h5 class="card-title mb-0">Parametres de facturation</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Nom de l'entreprise (sur facture)</label>
                <input type="text" name="settings[company_name]" class="form-control"
                       value="{{ $values['company_name'] ?? $invoiceSettings['company_name'] ?? '' }}">
                <input type="hidden" name="types[company_name]" value="string">
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Numero fiscal</label>
                <input type="text" name="settings[tax_number]" class="form-control"
                       value="{{ $values['tax_number'] ?? $invoiceSettings['tax_number'] ?? '' }}">
                <input type="hidden" name="types[tax_number]" value="string">
            </div>
        </div>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Adresse</label>
                <input type="text" name="settings[company_address]" class="form-control"
                       value="{{ $values['company_address'] ?? $invoiceSettings['company_address'] ?? '' }}">
                <input type="hidden" name="types[company_address]" value="string">
            </div>
            <div class="col-md-3 mb-3">
                <label class="form-label">Telephone</label>
                <input type="text" name="settings[company_phone]" class="form-control"
                       value="{{ $values['company_phone'] ?? $invoiceSettings['company_phone'] ?? '' }}">
                <input type="hidden" name="types[company_phone]" value="string">
            </div>
            <div class="col-md-3 mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="settings[company_email]" class="form-control"
                       value="{{ $values['company_email'] ?? $invoiceSettings['company_email'] ?? '' }}">
                <input type="hidden" name="types[company_email]" value="string">
            </div>
        </div>
    </div>
</div>

<div class="card mb-3">
    <div class="card-header">
        <h5 class="card-title mb-0">Options de facture</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-4 mb-3">
                <label class="form-label">Echeance par defaut (jours)</label>
                <input type="number" name="settings[default_due_days]" class="form-control"
                       value="{{ $values['default_due_days'] ?? $invoiceSettings['default_due_days'] ?? 30 }}"
                       min="0" max="365">
                <input type="hidden" name="types[default_due_days]" value="integer">
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label">Modele par defaut</label>
                <select name="settings[default_template]" class="form-select">
                    @php $tpl = $values['default_template'] ?? $invoiceSettings['default_template'] ?? 'default'; @endphp
                    <option value="default" @selected($tpl === 'default')>Standard</option>
                    <option value="a4-v1" @selected($tpl === 'a4-v1')>A4 v1</option>
                    <option value="a4-v2" @selected($tpl === 'a4-v2')>A4 v2</option>
                    <option value="a4-compact" @selected($tpl === 'a4-compact')>A4 Compact</option>
                    <option value="gst-v1" @selected($tpl === 'gst-v1')>GST v1</option>
                    <option value="gst-v2" @selected($tpl === 'gst-v2')>GST v2</option>
                </select>
                <input type="hidden" name="types[default_template]" value="string">
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label">Position de la devise</label>
                <select name="settings[currency_position]" class="form-select">
                    @php $pos = $values['currency_position'] ?? $invoiceSettings['currency_position'] ?? 'before'; @endphp
                    <option value="before" @selected($pos === 'before')>Avant le montant ($ 100)</option>
                    <option value="after" @selected($pos === 'after')>Apres le montant (100 $)</option>
                </select>
                <input type="hidden" name="types[currency_position]" value="string">
            </div>
        </div>

        <div class="row">
            <div class="col-md-3 mb-3">
                <label class="form-label">Symbole de devise</label>
                <input type="text" name="settings[currency_symbol]" class="form-control" style="max-width: 100px;"
                       value="{{ $values['currency_symbol'] ?? $invoiceSettings['currency_symbol'] ?? '$' }}">
                <input type="hidden" name="types[currency_symbol]" value="string">
            </div>
            <div class="col-md-4 mb-3">
                <div class="form-check form-switch mt-4">
                    <input type="hidden" name="settings[show_tax_breakdown]" value="0">
                    <input class="form-check-input" type="checkbox"
                           name="settings[show_tax_breakdown]" value="1" id="invShowTax"
                           @checked(($values['show_tax_breakdown'] ?? $invoiceSettings['show_tax_breakdown'] ?? true) == true)>
                    <input type="hidden" name="types[show_tax_breakdown]" value="boolean">
                    <label class="form-check-label" for="invShowTax">Afficher le detail des taxes</label>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="form-check form-switch mt-4">
                    <input type="hidden" name="settings[show_payment_info]" value="0">
                    <input class="form-check-input" type="checkbox"
                           name="settings[show_payment_info]" value="1" id="invShowPayment"
                           @checked(($values['show_payment_info'] ?? $invoiceSettings['show_payment_info'] ?? true) == true)>
                    <input type="hidden" name="types[show_payment_info]" value="boolean">
                    <label class="form-check-label" for="invShowPayment">Afficher les infos de paiement</label>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card mb-3">
    <div class="card-header">
        <h5 class="card-title mb-0">Informations bancaires (sur facture)</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-4 mb-3">
                <label class="form-label">Nom de la banque</label>
                <input type="text" name="settings[bank_name]" class="form-control"
                       value="{{ $values['bank_name'] ?? $invoiceSettings['bank_name'] ?? '' }}">
                <input type="hidden" name="types[bank_name]" value="string">
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label">Numero de compte</label>
                <input type="text" name="settings[bank_account]" class="form-control"
                       value="{{ $values['bank_account'] ?? $invoiceSettings['bank_account'] ?? '' }}">
                <input type="hidden" name="types[bank_account]" value="string">
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label">IBAN</label>
                <input type="text" name="settings[bank_iban]" class="form-control"
                       value="{{ $values['bank_iban'] ?? $invoiceSettings['bank_iban'] ?? '' }}">
                <input type="hidden" name="types[bank_iban]" value="string">
            </div>
        </div>
    </div>
</div>

<div class="card mb-3">
    <div class="card-header">
        <h5 class="card-title mb-0">Mentions legales</h5>
    </div>
    <div class="card-body">
        <div class="mb-3">
            <label class="form-label">Conditions par defaut (en-tete)</label>
            <textarea name="settings[default_terms]" class="form-control" rows="3">{{ $values['default_terms'] ?? $invoiceSettings['default_terms'] ?? '' }}</textarea>
            <input type="hidden" name="types[default_terms]" value="string">
        </div>
        <div class="mb-3">
            <label class="form-label">Pied de page par defaut</label>
            <textarea name="settings[default_footer]" class="form-control" rows="2">{{ $values['default_footer'] ?? $invoiceSettings['default_footer'] ?? '' }}</textarea>
            <input type="hidden" name="types[default_footer]" value="string">
        </div>
    </div>
</div>
