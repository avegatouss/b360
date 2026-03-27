<div class="mb-3">
    <label for="publishable_key" class="form-label">{{ __('Publishable Key') }}</label>
    <input type="text" class="form-control" id="publishable_key"
           name="credentials[publishable_key]"
           value="{{ $credentials['publishable_key'] ?? '' }}">
</div>

<div class="mb-3">
    <label for="secret_key" class="form-label">{{ __('Secret Key') }}</label>
    <input type="password" class="form-control" id="secret_key"
           name="credentials[secret_key]"
           value="{{ $credentials['secret_key'] ?? '' }}">
</div>

<div class="mb-3">
    <label for="webhook_secret" class="form-label">{{ __('Webhook Secret') }}</label>
    <input type="password" class="form-control" id="webhook_secret"
           name="credentials[webhook_secret]"
           value="{{ $credentials['webhook_secret'] ?? '' }}">
</div>
