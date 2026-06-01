<div class="card mb-3">
    <div class="card-header">
        <h5 class="card-title mb-0">Authentification a deux facteurs (2FA)</h5>
    </div>
    <div class="card-body">
        <div class="mb-3">
            <div class="form-check form-switch">
                <input type="hidden" name="settings[2fa_enabled]" value="0">
                <input class="form-check-input" type="checkbox"
                       name="settings[2fa_enabled]" value="1"
                       id="2faEnabled"
                       @checked(($values['2fa_enabled'] ?? false) == true)>
                <input type="hidden" name="types[2fa_enabled]" value="boolean">
                <label class="form-check-label" for="2faEnabled">
                    Activer le 2FA globalement
                </label>
            </div>
            <small class="text-muted">Permet aux utilisateurs d'activer l'authentification a deux facteurs.</small>
        </div>
        <div class="mb-3">
            <div class="form-check form-switch">
                <input type="hidden" name="settings[2fa_force_admins]" value="0">
                <input class="form-check-input" type="checkbox"
                       name="settings[2fa_force_admins]" value="1"
                       id="2faForceAdmins"
                       @checked(($values['2fa_force_admins'] ?? false) == true)>
                <input type="hidden" name="types[2fa_force_admins]" value="boolean">
                <label class="form-check-label" for="2faForceAdmins">
                    Forcer le 2FA pour les administrateurs
                </label>
            </div>
            <small class="text-muted">Les administrateurs devront obligatoirement configurer le 2FA.</small>
        </div>
    </div>
</div>

<div class="card mb-3">
    <div class="card-header">
        <h5 class="card-title mb-0">reCAPTCHA</h5>
    </div>
    <div class="card-body">
        <div class="mb-3">
            <div class="form-check form-switch">
                <input type="hidden" name="settings[recaptcha_enabled]" value="0">
                <input class="form-check-input" type="checkbox"
                       name="settings[recaptcha_enabled]" value="1"
                       id="recaptchaEnabled"
                       @checked(($values['recaptcha_enabled'] ?? false) == true)>
                <input type="hidden" name="types[recaptcha_enabled]" value="boolean">
                <label class="form-check-label" for="recaptchaEnabled">
                    Activer reCAPTCHA
                </label>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Cle du site (Site Key)</label>
                <input type="text" name="settings[recaptcha_site_key]" class="form-control"
                       value="{{ $values['recaptcha_site_key'] ?? '' }}">
                <input type="hidden" name="types[recaptcha_site_key]" value="string">
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Cle secrete (Secret Key)</label>
                <input type="password" name="settings[recaptcha_secret_key]" class="form-control"
                       value="{{ $values['recaptcha_secret_key'] ?? '' }}"
                       autocomplete="off">
                <input type="hidden" name="types[recaptcha_secret_key]" value="string">
                <small class="text-muted">Laissez vide pour conserver la valeur actuelle.</small>
            </div>
        </div>
    </div>
</div>

<div class="card mb-3">
    <div class="card-header">
        <h5 class="card-title mb-0">Regles IP</h5>
    </div>
    <div class="card-body">
        <div class="mb-3">
            <div class="form-check form-switch">
                <input type="hidden" name="settings[ip_rules_enabled]" value="0">
                <input class="form-check-input" type="checkbox"
                       name="settings[ip_rules_enabled]" value="1"
                       id="ipRulesEnabled"
                       @checked(($values['ip_rules_enabled'] ?? false) == true)>
                <input type="hidden" name="types[ip_rules_enabled]" value="boolean">
                <label class="form-check-label" for="ipRulesEnabled">
                    Activer le filtrage par adresse IP
                </label>
            </div>
            <small class="text-muted">
                Permet de bloquer ou autoriser des adresses IP specifiques.
                {{-- Lien vers la gestion des regles IP si la route existe --}}
                @if(Route::has('settings.ip_rules.index'))
                    <a href="{{ route('settings.ip_rules.index', [$instance->slug ?? '']) }}">Gerer les regles IP</a>
                @endif
            </small>
        </div>
    </div>
</div>

<div class="card mb-3">
    <div class="card-header">
        <h5 class="card-title mb-0">Verrouillage automatique</h5>
    </div>
    <div class="card-body">
        <div class="mb-3">
            <label class="form-label">Delai de verrouillage automatique (minutes)</label>
            <input type="number" name="settings[lockscreen_timeout]" class="form-control" style="max-width: 200px;"
                   value="{{ $values['lockscreen_timeout'] ?? 30 }}" min="0">
            <input type="hidden" name="types[lockscreen_timeout]" value="integer">
            <small class="text-muted">0 = desactive. L'ecran se verrouille apres cette duree d'inactivite.</small>
        </div>
    </div>
</div>

<div class="card mb-3">
    <div class="card-header">
        <h5 class="card-title mb-0">Politique de mot de passe</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-4 mb-3">
                <label class="form-label">Longueur minimale</label>
                <input type="number" name="settings[password_min_length]" class="form-control"
                       value="{{ $values['password_min_length'] ?? 8 }}" min="6" max="128">
                <input type="hidden" name="types[password_min_length]" value="integer">
            </div>
            <div class="col-md-4 mb-3">
                <div class="form-check form-switch mt-4">
                    <input type="hidden" name="settings[password_require_uppercase]" value="0">
                    <input class="form-check-input" type="checkbox"
                           name="settings[password_require_uppercase]" value="1"
                           id="passwordUppercase"
                           @checked(($values['password_require_uppercase'] ?? false) == true)>
                    <input type="hidden" name="types[password_require_uppercase]" value="boolean">
                    <label class="form-check-label" for="passwordUppercase">
                        Exiger une majuscule
                    </label>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="form-check form-switch mt-4">
                    <input type="hidden" name="settings[password_require_number]" value="0">
                    <input class="form-check-input" type="checkbox"
                           name="settings[password_require_number]" value="1"
                           id="passwordNumber"
                           @checked(($values['password_require_number'] ?? false) == true)>
                    <input type="hidden" name="types[password_require_number]" value="boolean">
                    <label class="form-check-label" for="passwordNumber">
                        Exiger un chiffre
                    </label>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card mb-3">
    <div class="card-header">
        <h5 class="card-title mb-0">Session</h5>
    </div>
    <div class="card-body">
        <div class="mb-3">
            <label class="form-label">Expiration de session (minutes)</label>
            <input type="number" name="settings[session_timeout]" class="form-control" style="max-width: 200px;"
                   value="{{ $values['session_timeout'] ?? 120 }}" min="5">
            <input type="hidden" name="types[session_timeout]" value="integer">
            <small class="text-muted">Duree d'inactivite avant deconnexion automatique.</small>
        </div>
    </div>
</div>
