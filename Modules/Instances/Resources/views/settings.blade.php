<div class="card mb-3">
    <div class="card-header">
        <h5 class="card-title mb-0">Instances</h5>
    </div>
    <div class="card-body">
        <div class="mb-3">
            <div class="form-check form-switch">
                <input type="hidden" name="settings[allow_creation]" value="0">
                <input class="form-check-input" type="checkbox"
                       name="settings[allow_creation]" value="1"
                       id="allowCreation"
                       @checked(($values['allow_creation'] ?? true) === true || ($values['allow_creation'] ?? '1') === '1')>
                <input type="hidden" name="types[allow_creation]" value="boolean">
                <label class="form-check-label" for="allowCreation">
                    Autoriser la creation de nouvelles instances
                </label>
            </div>
            <small class="text-muted">
                Desactiver cette option empeche la creation de nouvelles instances,
                mais les instances existantes continuent de fonctionner normalement.
            </small>
        </div>

        <div class="mb-3">
            <label class="form-label">Nombre maximum d'instances</label>
            <input type="number" name="settings[max_instances]" class="form-control" style="max-width:200px;"
                   value="{{ $values['max_instances'] ?? '' }}" min="0" placeholder="Illimite">
            <input type="hidden" name="types[max_instances]" value="integer">
            <small class="text-muted">Laissez vide pour illimite.</small>
        </div>
    </div>
</div>
