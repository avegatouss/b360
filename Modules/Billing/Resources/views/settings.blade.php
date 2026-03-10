<div class="card mb-3">
    <div class="card-header">
        <h5 class="card-title mb-0">Facturation</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-4 mb-3">
                <label class="form-label">Devise par defaut</label>
                <select name="settings[currency]" class="form-select">
                    @php $cur = $values['currency'] ?? 'EUR'; @endphp
                    <option value="EUR" {{ $cur === 'EUR' ? 'selected' : '' }}>EUR - Euro</option>
                    <option value="USD" {{ $cur === 'USD' ? 'selected' : '' }}>USD - Dollar US</option>
                    <option value="GBP" {{ $cur === 'GBP' ? 'selected' : '' }}>GBP - Livre Sterling</option>
                    <option value="XOF" {{ $cur === 'XOF' ? 'selected' : '' }}>XOF - Franc CFA (BCEAO)</option>
                    <option value="XAF" {{ $cur === 'XAF' ? 'selected' : '' }}>XAF - Franc CFA (BEAC)</option>
                    <option value="MAD" {{ $cur === 'MAD' ? 'selected' : '' }}>MAD - Dirham marocain</option>
                    <option value="TND" {{ $cur === 'TND' ? 'selected' : '' }}>TND - Dinar tunisien</option>
                    <option value="CAD" {{ $cur === 'CAD' ? 'selected' : '' }}>CAD - Dollar canadien</option>
                    <option value="CHF" {{ $cur === 'CHF' ? 'selected' : '' }}>CHF - Franc suisse</option>
                </select>
                <input type="hidden" name="types[currency]" value="string">
            </div>

            <div class="col-md-4 mb-3">
                <label class="form-label">Jours d'essai par defaut</label>
                <input type="number" name="settings[default_trial_days]" class="form-control" min="0"
                       value="{{ $values['default_trial_days'] ?? 14 }}">
                <input type="hidden" name="types[default_trial_days]" value="integer">
                <small class="form-text text-muted">Nombre de jours d'essai pour les nouvelles instances.</small>
            </div>

            <div class="col-md-4 mb-3">
                <label class="form-label">Prefixe des factures</label>
                <input type="text" name="settings[invoice_prefix]" class="form-control"
                       value="{{ $values['invoice_prefix'] ?? 'B360-INV' }}">
                <input type="hidden" name="types[invoice_prefix]" value="string">
            </div>
        </div>

        <div class="mb-3">
            <div class="form-check form-switch">
                <input type="hidden" name="settings[auto_expire]" value="0">
                <input type="checkbox" name="settings[auto_expire]" value="1" class="form-check-input"
                       id="autoExpire"
                       @checked(!empty($values['auto_expire']) && ($values['auto_expire'] === true || $values['auto_expire'] === '1' || $values['auto_expire'] === 1))>
                <input type="hidden" name="types[auto_expire]" value="boolean">
                <label class="form-check-label" for="autoExpire">Expiration automatique des abonnements depasses</label>
            </div>
        </div>
    </div>
</div>

<div class="card mb-3">
    <div class="card-header">
        <h5 class="card-title mb-0">Licence</h5>
    </div>
    <div class="card-body">
        <div class="mb-3">
            <div class="form-check form-switch">
                <input type="hidden" name="settings[verification_enabled]" value="0">
                <input type="checkbox" name="settings[verification_enabled]" value="1" class="form-check-input"
                       id="licenseVerification"
                       @checked(!empty($values['verification_enabled']) && ($values['verification_enabled'] === true || $values['verification_enabled'] === '1' || $values['verification_enabled'] === 1))>
                <input type="hidden" name="types[verification_enabled]" value="boolean">
                <label class="form-check-label" for="licenseVerification">Activer la verification de licence via API</label>
            </div>
            <small class="form-text text-muted">Si active, l'endpoint POST /api/license/verify sera fonctionnel.</small>
        </div>
    </div>
</div>
