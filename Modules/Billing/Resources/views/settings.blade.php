<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Facturation</h5>
    </div>
    <div class="card-body">
        <div class="mb-3">
            <label class="form-label">Jours d'essai par defaut</label>
            <input type="number" name="billing.default_trial_days" class="form-control" min="0"
                   value="{{ $values['default_trial_days'] ?? 14 }}">
            <small class="form-text text-muted">Nombre de jours d'essai accorde par defaut aux nouvelles instances.</small>
        </div>

        <div class="mb-3">
            <label class="form-label">Devise par defaut</label>
            <input type="text" name="billing.currency" class="form-control" maxlength="3"
                   value="{{ $values['currency'] ?? 'EUR' }}">
        </div>

        <div class="mb-3">
            <label class="form-label">Prefixe des factures</label>
            <input type="text" name="billing.invoice_prefix" class="form-control"
                   value="{{ $values['invoice_prefix'] ?? 'B360-INV' }}">
        </div>

        <div class="mb-3">
            <div class="form-check form-switch">
                <input type="hidden" name="billing.auto_expire" value="0">
                <input type="checkbox" name="billing.auto_expire" value="1" class="form-check-input"
                       {{ ($values['auto_expire'] ?? true) ? 'checked' : '' }}>
                <label class="form-check-label">Expiration automatique des abonnements depasses</label>
            </div>
        </div>

        <div class="mb-3">
            <div class="form-check form-switch">
                <input type="hidden" name="license.verification_enabled" value="0">
                <input type="checkbox" name="license.verification_enabled" value="1" class="form-check-input"
                       {{ ($values['verification_enabled'] ?? false) ? 'checked' : '' }}>
                <label class="form-check-label">Activer la verification de licence via API</label>
            </div>
            <small class="form-text text-muted">Si active, l'endpoint POST /api/license/verify sera fonctionnel.</small>
        </div>
    </div>
</div>
