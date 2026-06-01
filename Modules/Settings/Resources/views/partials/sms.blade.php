<div class="card mb-3">
    <div class="card-header">
        <h5 class="card-title mb-0">Configuration SMS</h5>
    </div>
    <div class="card-body">
        <div class="mb-3">
            <label class="form-label">Passerelle SMS par defaut</label>
            <select name="settings[sms_default_gateway]" class="form-select" style="max-width: 400px;">
                @php
                    $gateways = [
                        '' => '— Aucune —',
                        'twilio' => 'Twilio',
                        'vonage' => 'Vonage (Nexmo)',
                        'africastalking' => 'Africa\'s Talking',
                        'infobip' => 'Infobip',
                        'orange_sms' => 'Orange SMS API',
                        'custom' => 'Passerelle personnalisee',
                    ];
                    $selectedGw = $values['sms_default_gateway'] ?? '';
                @endphp
                @foreach($gateways as $val => $label)
                    <option value="{{ $val }}" @selected($selectedGw === $val)>{{ $label }}</option>
                @endforeach
            </select>
            <input type="hidden" name="types[sms_default_gateway]" value="string">
        </div>

        <div class="mb-3">
            <label class="form-label">Nom de l'expediteur SMS (Sender Name)</label>
            <input type="text" name="settings[sms_sender_name]" class="form-control" style="max-width: 400px;"
                   value="{{ $values['sms_sender_name'] ?? '' }}" maxlength="11"
                   placeholder="MonEntreprise">
            <input type="hidden" name="types[sms_sender_name]" value="string">
            <small class="text-muted">Maximum 11 caracteres. Ce nom apparaitra comme expediteur des SMS.</small>
        </div>

        <div class="mb-3">
            <p class="text-muted mb-0">
                <i class="ti ti-info-circle me-1"></i>
                La configuration detaillee des passerelles SMS (cles API, identifiants) se fait
                dans la section de chaque passerelle.
                @if(Route::has('settings.sms_gateways.index'))
                    <a href="{{ route('settings.sms_gateways.index', [$instance->slug ?? '']) }}">Gerer les passerelles SMS</a>
                @endif
            </p>
        </div>
    </div>
</div>
