<div class="mb-3">
    <label for="subscription_key" class="form-label">{{ __('Subscription Key') }}</label>
    <input type="password" class="form-control" id="subscription_key"
           name="credentials[subscription_key]"
           value="{{ $credentials['subscription_key'] ?? '' }}">
</div>

<div class="mb-3">
    <label for="api_user" class="form-label">{{ __('API User') }}</label>
    <input type="text" class="form-control" id="api_user"
           name="credentials[api_user]"
           value="{{ $credentials['api_user'] ?? '' }}">
</div>

<div class="mb-3">
    <label for="api_key" class="form-label">{{ __('API Key') }}</label>
    <input type="password" class="form-control" id="api_key"
           name="credentials[api_key]"
           value="{{ $credentials['api_key'] ?? '' }}">
</div>

<div class="mb-3">
    <label for="environment" class="form-label">{{ __('Environment') }}</label>
    <select class="form-control" id="environment" name="credentials[environment]">
        <option value="sandbox" @selected(($credentials['environment'] ?? '') === 'sandbox')>Sandbox</option>
        <option value="production" @selected(($credentials['environment'] ?? '') === 'production')>Production</option>
    </select>
</div>

<div class="mb-3">
    <label for="target_environment" class="form-label">{{ __('Target Environment') }}</label>
    <input type="text" class="form-control" id="target_environment"
           name="credentials[target_environment]"
           value="{{ $credentials['target_environment'] ?? '' }}">
</div>
