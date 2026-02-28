<div class="card mb-3">
    <div class="card-header">
        <h5 class="card-title mb-0">Parametres generaux</h5>
    </div>
    <div class="card-body">
        <div class="mb-3">
            <label class="form-label">Nom de l'application</label>
            <input type="text" name="settings[app_name]" class="form-control"
                   value="{{ $values['app_name'] ?? config('app.name', 'B360') }}">
            <input type="hidden" name="types[app_name]" value="string">
        </div>

        <div class="mb-3">
            <div class="form-check form-switch">
                <input type="hidden" name="settings[maintenance_mode]" value="0">
                <input class="form-check-input" type="checkbox"
                       name="settings[maintenance_mode]" value="1"
                       id="maintenanceMode"
                       @checked(($values['maintenance_mode'] ?? false) === true || ($values['maintenance_mode'] ?? '0') === '1')>
                <input type="hidden" name="types[maintenance_mode]" value="boolean">
                <label class="form-check-label" for="maintenanceMode">
                    Mode maintenance
                </label>
            </div>
            <small class="text-muted">Active le mode maintenance pour toutes les instances.</small>
        </div>
    </div>
</div>
