<div class="card mb-3">
    <div class="card-header">
        <h5 class="card-title mb-0">Alertes de stock</h5>
    </div>
    <div class="card-body">
        <div class="mb-3">
            <div class="form-check form-switch">
                <input type="hidden" name="settings[alert_low_stock_enabled]" value="0">
                <input class="form-check-input" type="checkbox"
                       name="settings[alert_low_stock_enabled]" value="1"
                       id="alertLowStock"
                       @checked(($values['alert_low_stock_enabled'] ?? false) == true)>
                <input type="hidden" name="types[alert_low_stock_enabled]" value="boolean">
                <label class="form-check-label" for="alertLowStock">
                    Activer les alertes de stock faible
                </label>
            </div>
        </div>
        <div class="mb-3">
            <label class="form-label">Seuil d'alerte par defaut (quantite)</label>
            <input type="number" name="settings[alert_low_stock_threshold]" class="form-control" style="max-width: 200px;"
                   value="{{ $values['alert_low_stock_threshold'] ?? 10 }}" min="0">
            <input type="hidden" name="types[alert_low_stock_threshold]" value="integer">
            <small class="text-muted">Quantite en dessous de laquelle une alerte est declenchee.</small>
        </div>
    </div>
</div>

<div class="card mb-3">
    <div class="card-header">
        <h5 class="card-title mb-0">Alertes d'expiration</h5>
    </div>
    <div class="card-body">
        <div class="mb-3">
            <div class="form-check form-switch">
                <input type="hidden" name="settings[alert_expiry_enabled]" value="0">
                <input class="form-check-input" type="checkbox"
                       name="settings[alert_expiry_enabled]" value="1"
                       id="alertExpiry"
                       @checked(($values['alert_expiry_enabled'] ?? false) == true)>
                <input type="hidden" name="types[alert_expiry_enabled]" value="boolean">
                <label class="form-check-label" for="alertExpiry">
                    Activer les alertes d'expiration de produits
                </label>
            </div>
        </div>
        <div class="mb-3">
            <label class="form-label">Jours avant expiration</label>
            <input type="number" name="settings[alert_expiry_days]" class="form-control" style="max-width: 200px;"
                   value="{{ $values['alert_expiry_days'] ?? 30 }}" min="1">
            <input type="hidden" name="types[alert_expiry_days]" value="integer">
            <small class="text-muted">Nombre de jours avant la date d'expiration pour declencher l'alerte.</small>
        </div>
    </div>
</div>

<div class="card mb-3">
    <div class="card-header">
        <h5 class="card-title mb-0">Notifications par email</h5>
    </div>
    <div class="card-body">
        <div class="mb-3">
            <div class="form-check form-switch">
                <input type="hidden" name="settings[notify_new_order]" value="0">
                <input class="form-check-input" type="checkbox"
                       name="settings[notify_new_order]" value="1"
                       id="notifyNewOrder"
                       @checked(($values['notify_new_order'] ?? false) == true)>
                <input type="hidden" name="types[notify_new_order]" value="boolean">
                <label class="form-check-label" for="notifyNewOrder">
                    Recevoir un email pour chaque nouvelle commande
                </label>
            </div>
        </div>
        <div class="mb-3">
            <div class="form-check form-switch">
                <input type="hidden" name="settings[notify_payment_received]" value="0">
                <input class="form-check-input" type="checkbox"
                       name="settings[notify_payment_received]" value="1"
                       id="notifyPayment"
                       @checked(($values['notify_payment_received'] ?? false) == true)>
                <input type="hidden" name="types[notify_payment_received]" value="boolean">
                <label class="form-check-label" for="notifyPayment">
                    Recevoir un email pour chaque paiement recu
                </label>
            </div>
        </div>
    </div>
</div>
