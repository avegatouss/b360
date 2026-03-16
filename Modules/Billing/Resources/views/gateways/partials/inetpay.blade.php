<div class="mb-3">
    <label for="merchant_id" class="form-label">{{ __('Merchant ID') }}</label>
    <input type="text" class="form-control" id="merchant_id"
           name="credentials[merchant_id]"
           value="{{ $credentials['merchant_id'] ?? '' }}">
</div>

<div class="mb-3">
    <label for="secret_key" class="form-label">{{ __('Secret Key') }}</label>
    <input type="password" class="form-control" id="secret_key"
           name="credentials[secret_key]"
           value="{{ $credentials['secret_key'] ?? '' }}">
</div>

<div class="mb-3">
    <label for="base_url" class="form-label">{{ __('Base URL') }}</label>
    <input type="text" class="form-control" id="base_url"
           name="credentials[base_url]"
           value="{{ $credentials['base_url'] ?? '' }}">
</div>
