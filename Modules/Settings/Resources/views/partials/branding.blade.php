<div class="card mb-3">
    <div class="card-header">
        <h5 class="card-title mb-0">Informations de la plateforme</h5>
    </div>
    <div class="card-body">
        <div class="mb-3">
            <label class="form-label">Nom de la plateforme</label>
            <input type="text" name="settings[platform_name]" class="form-control"
                   value="{{ $values['platform_name'] ?? 'B360' }}">
            <input type="hidden" name="types[platform_name]" value="string">
        </div>

        <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea name="settings[platform_description]" class="form-control" rows="2">{{ $values['platform_description'] ?? '' }}</textarea>
            <input type="hidden" name="types[platform_description]" value="string">
        </div>

        <div class="mb-3">
            <label class="form-label">Email de contact</label>
            <input type="email" name="settings[contact_email]" class="form-control"
                   value="{{ $values['contact_email'] ?? '' }}">
            <input type="hidden" name="types[contact_email]" value="string">
        </div>
    </div>
</div>

<div class="card mb-3">
    <div class="card-header">
        <h5 class="card-title mb-0">Logos et images</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Logo principal</label>
                @if(!empty($values['logo']))
                    <div class="mb-2">
                        <img src="{{ asset('storage/' . $values['logo']) }}" alt="Logo" class="img-thumbnail" style="max-height: 60px;">
                    </div>
                @endif
                <input type="file" name="files[logo]" class="form-control" accept="image/*">
                <small class="text-muted">Format recommande : SVG ou PNG transparent, 200x60px max.</small>
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label">Logo sombre (pour fond clair)</label>
                @if(!empty($values['logo_dark']))
                    <div class="mb-2">
                        <img src="{{ asset('storage/' . $values['logo_dark']) }}" alt="Logo dark" class="img-thumbnail" style="max-height: 60px;">
                    </div>
                @endif
                <input type="file" name="files[logo_dark]" class="form-control" accept="image/*">
                <small class="text-muted">Variante du logo pour les fonds clairs.</small>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Favicon</label>
                @if(!empty($values['favicon']))
                    <div class="mb-2">
                        <img src="{{ asset('storage/' . $values['favicon']) }}" alt="Favicon" class="img-thumbnail" style="max-height: 32px;">
                    </div>
                @endif
                <input type="file" name="files[favicon]" class="form-control" accept="image/png,image/x-icon,image/svg+xml">
                <small class="text-muted">Format : PNG ou ICO, 32x32px recommande.</small>
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label">Logo page de connexion</label>
                @if(!empty($values['login_logo']))
                    <div class="mb-2">
                        <img src="{{ asset('storage/' . $values['login_logo']) }}" alt="Login logo" class="img-thumbnail" style="max-height: 60px;">
                    </div>
                @endif
                <input type="file" name="files[login_logo]" class="form-control" accept="image/*">
                <small class="text-muted">Logo affiche sur les pages de connexion/inscription.</small>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Image de couverture (connexion)</label>
                @if(!empty($values['login_cover']))
                    <div class="mb-2">
                        <img src="{{ asset('storage/' . $values['login_cover']) }}" alt="Login cover" class="img-thumbnail" style="max-height: 80px;">
                    </div>
                @endif
                <input type="file" name="files[login_cover]" class="form-control" accept="image/*">
                <small class="text-muted">Image de fond pour la page de connexion.</small>
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label">Image de couverture (inscription)</label>
                @if(!empty($values['register_cover']))
                    <div class="mb-2">
                        <img src="{{ asset('storage/' . $values['register_cover']) }}" alt="Register cover" class="img-thumbnail" style="max-height: 80px;">
                    </div>
                @endif
                <input type="file" name="files[register_cover]" class="form-control" accept="image/*">
                <small class="text-muted">Image de fond pour la page d'inscription.</small>
            </div>
        </div>
    </div>
</div>
