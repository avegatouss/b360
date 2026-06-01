<div class="mb-3">
    <label for="api_key" class="form-label">{{ __('API Key') }}</label>
    <input type="text" class="form-control" id="api_key"
           name="credentials[api_key]"
           value="{{ $credentials['api_key'] ?? '' }}">
</div>

<div class="mb-3">
    <label for="site_id" class="form-label">{{ __('Site ID') }}</label>
    <input type="text" class="form-control" id="site_id"
           name="credentials[site_id]"
           value="{{ $credentials['site_id'] ?? '' }}">
</div>

<div class="mb-3">
    <label for="secret_key" class="form-label">{{ __('Secret Key') }}</label>
    <input type="password" class="form-control" id="secret_key"
           name="credentials[secret_key]"
           value="{{ $credentials['secret_key'] ?? '' }}">
</div>
