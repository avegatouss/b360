<div class="mb-3">
    <label for="api_key" class="form-label">{{ __('API Key') }}</label>
    <input type="text" class="form-control" id="api_key"
           name="credentials[api_key]"
           value="{{ $credentials['api_key'] ?? '' }}">
</div>

<div class="mb-3">
    <label for="webhook_secret" class="form-label">{{ __('Webhook Secret') }}</label>
    <input type="password" class="form-control" id="webhook_secret"
           name="credentials[webhook_secret]"
           value="{{ $credentials['webhook_secret'] ?? '' }}">
</div>

<div class="mb-3">
    <label for="business_id" class="form-label">{{ __('Business ID') }}</label>
    <input type="text" class="form-control" id="business_id"
           name="credentials[business_id]"
           value="{{ $credentials['business_id'] ?? '' }}">
</div>
