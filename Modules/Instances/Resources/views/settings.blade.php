<div class="card mb-3">
    <div class="card-header">
        <h5 class="card-title mb-0">Mode d'instances</h5>
    </div>
    <div class="card-body">
        <div class="mb-3">
            <div class="form-check form-switch">
                <input type="hidden" name="settings[allow_creation]" value="0">
                <input class="form-check-input" type="checkbox"
                       name="settings[allow_creation]" value="1"
                       id="allowCreation"
                       @checked(($values['allow_creation'] ?? false) == true || ($values['allow_creation'] ?? '0') === '1')>
                <input type="hidden" name="types[allow_creation]" value="boolean">
                <label class="form-check-label" for="allowCreation">
                    Activer le mode multi-instance
                </label>
            </div>
            <small class="text-muted">
                Par defaut, B360 fonctionne en <strong>mode mono-instance</strong> (seule l'instance root existe).
                Activez cette option pour passer en <strong>mode multi-instance</strong> et permettre la creation de nouvelles instances.
                Les instances existantes continuent de fonctionner quel que soit le mode.
            </small>
        </div>

        @if(($values['allow_creation'] ?? false) == true || ($values['allow_creation'] ?? '0') === '1')
        <div class="mb-3">
            <label class="form-label">Nombre maximum d'instances</label>
            <input type="number" name="settings[max_instances]" class="form-control" style="max-width:200px;"
                   value="{{ $values['max_instances'] ?? '' }}" min="0" placeholder="Illimite">
            <input type="hidden" name="types[max_instances]" value="integer">
            <small class="text-muted">Laissez vide pour illimite.</small>
        </div>
        @endif
    </div>
</div>
