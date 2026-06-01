<div class="mb-3">
    <label for="merchant_key" class="form-label">{{ __('Merchant Key') }}</label>
    <input type="password" class="form-control" id="merchant_key"
           name="credentials[merchant_key]"
           value="{{ $credentials['merchant_key'] ?? '' }}">
</div>

<div class="mb-3">
    <label for="authorization_header" class="form-label">{{ __('Authorization Header') }}</label>
    <input type="password" class="form-control" id="authorization_header"
           name="credentials[authorization_header]"
           value="{{ $credentials['authorization_header'] ?? '' }}">
</div>
