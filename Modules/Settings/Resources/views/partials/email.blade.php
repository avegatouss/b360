<div class="card mb-3">
    <div class="card-header">
        <h5 class="card-title mb-0">Configuration SMTP</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-8 mb-3">
                <label class="form-label">Serveur SMTP (Host)</label>
                <input type="text" name="settings[smtp_host]" class="form-control"
                       value="{{ $values['smtp_host'] ?? '' }}" placeholder="smtp.exemple.com">
                <input type="hidden" name="types[smtp_host]" value="string">
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label">Port</label>
                <input type="number" name="settings[smtp_port]" class="form-control"
                       value="{{ $values['smtp_port'] ?? 587 }}" min="1" max="65535">
                <input type="hidden" name="types[smtp_port]" value="integer">
            </div>
        </div>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Nom d'utilisateur</label>
                <input type="text" name="settings[smtp_username]" class="form-control"
                       value="{{ $values['smtp_username'] ?? '' }}" autocomplete="off">
                <input type="hidden" name="types[smtp_username]" value="string">
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Mot de passe</label>
                <input type="password" name="settings[smtp_password]" class="form-control"
                       value="" autocomplete="new-password"
                       placeholder="{{ !empty($values['smtp_password']) ? '********' : '' }}">
                <input type="hidden" name="types[smtp_password]" value="string">
                <small class="text-muted">Laissez vide pour conserver le mot de passe actuel.</small>
            </div>
        </div>
        <div class="mb-3">
            <label class="form-label">Chiffrement</label>
            <select name="settings[smtp_encryption]" class="form-select" style="max-width: 200px;">
                @php $enc = $values['smtp_encryption'] ?? 'tls'; @endphp
                <option value="" @selected($enc === '')>Aucun</option>
                <option value="tls" @selected($enc === 'tls')>TLS</option>
                <option value="ssl" @selected($enc === 'ssl')>SSL</option>
            </select>
            <input type="hidden" name="types[smtp_encryption]" value="string">
        </div>
    </div>
</div>

<div class="card mb-3">
    <div class="card-header">
        <h5 class="card-title mb-0">Expediteur par defaut</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Adresse email d'envoi (From)</label>
                <input type="email" name="settings[mail_from_address]" class="form-control"
                       value="{{ $values['mail_from_address'] ?? '' }}" placeholder="noreply@exemple.com">
                <input type="hidden" name="types[mail_from_address]" value="string">
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Nom d'envoi (From Name)</label>
                <input type="text" name="settings[mail_from_name]" class="form-control"
                       value="{{ $values['mail_from_name'] ?? '' }}" placeholder="Mon Entreprise">
                <input type="hidden" name="types[mail_from_name]" value="string">
            </div>
        </div>
    </div>
</div>

<div class="card mb-3">
    <div class="card-header">
        <h5 class="card-title mb-0">Test d'envoi</h5>
    </div>
    <div class="card-body">
        <p class="text-muted mb-3">
            Envoyer un email de test a votre adresse (<strong>{{ auth()->user()?->email ?? '—' }}</strong>)
            pour verifier la configuration SMTP.
        </p>
        <button type="button" class="btn btn-outline-primary" id="btn-test-email"
                data-url="{{ route('settings.test_email', [$instance->slug]) }}">
            <i class="ti ti-send me-1"></i>Envoyer un email de test
        </button>
        <div id="test-email-result" class="mt-2"></div>
    </div>
</div>

@push('scripts')
<script>
document.getElementById('btn-test-email')?.addEventListener('click', function () {
    const btn = this;
    const resultDiv = document.getElementById('test-email-result');
    btn.disabled = true;
    btn.textContent = 'Envoi en cours...';
    resultDiv.textContent = '';

    fetch(btn.dataset.url, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || '{{ csrf_token() }}',
            'Accept': 'application/json',
        },
    })
    .then(r => r.json())
    .then(data => {
        resultDiv.textContent = data.message || (data.success ? 'Email envoye.' : 'Echec de l\'envoi.');
        resultDiv.className = 'mt-2 ' + (data.success ? 'text-success' : 'text-danger');
    })
    .catch(() => {
        resultDiv.textContent = 'Erreur lors de l\'envoi.';
        resultDiv.className = 'mt-2 text-danger';
    })
    .finally(() => {
        btn.disabled = false;
        btn.textContent = 'Envoyer un email de test';
    });
});
</script>
@endpush
