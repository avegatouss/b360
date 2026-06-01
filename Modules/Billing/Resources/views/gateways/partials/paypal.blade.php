<div class="mb-3">
    <label for="client_id" class="form-label">{{ __('Client ID') }}</label>
    <input type="text" class="form-control" id="client_id"
           name="credentials[client_id]"
           value="{{ $credentials['client_id'] ?? '' }}">
</div>

<div class="mb-3">
    <label for="client_secret" class="form-label">{{ __('Client Secret') }}</label>
    <input type="password" class="form-control" id="client_secret"
           name="credentials[client_secret]"
           value="{{ $credentials['client_secret'] ?? '' }}">
</div>

<div class="mb-3">
    <label for="mode" class="form-label">{{ __('Mode') }}</label>
    <select class="form-control" id="mode" name="credentials[mode]">
        <option value="sandbox" @selected(($credentials['mode'] ?? '') === 'sandbox')>Sandbox</option>
        <option value="live" @selected(($credentials['mode'] ?? '') === 'live')>Live</option>
    </select>
</div>

<div class="mb-3">
    <label for="webhook_id" class="form-label">{{ __('Webhook ID') }}</label>
    <input type="text" class="form-control" id="webhook_id"
           name="credentials[webhook_id]"
           value="{{ $credentials['webhook_id'] ?? '' }}">
</div>
