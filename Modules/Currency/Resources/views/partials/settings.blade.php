<div class="card mb-3">
    <div class="card-header">
        <h5 class="card-title mb-0">Devise</h5>
    </div>
    <div class="card-body">
        <div class="mb-3">
            <label class="form-label">Devise active</label>
            <select name="settings[active]" class="form-select">
                @php $activeCurrency = $values['active'] ?? currency(); @endphp
                @foreach(config('currency.supported', []) as $code => $info)
                    <option value="{{ $code }}" {{ $activeCurrency === $code ? 'selected' : '' }}>
                        {{ $code }} - {{ $info['name'] }} ({{ $info['symbol'] }})
                    </option>
                @endforeach
            </select>
            <input type="hidden" name="types[active]" value="string">
            <small class="text-muted">
                @if(isset($scopeId) && $scopeId > 0)
                    Devise pour cette instance. Si le module Currency est actif, cette valeur prend le dessus sur les parametres de facturation.
                @else
                    Devise par defaut de la plateforme. Chaque instance peut definir sa propre devise.
                @endif
            </small>
        </div>
    </div>
</div>

<div class="card mb-3">
    <div class="card-header">
        <h5 class="card-title mb-0">Taux de change (base EUR)</h5>
    </div>
    <div class="card-body">
        <div class="row">
            @foreach(config('currency.rates', []) as $code => $rate)
                @if($code !== 'EUR')
                <div class="col-md-4 mb-3">
                    <label class="form-label">{{ $code }}</label>
                    <input type="number" name="settings[rate_{{ $code }}]" class="form-control" step="0.0001"
                           value="{{ $values['rate_' . $code] ?? $rate }}">
                    <input type="hidden" name="types[rate_{{ $code }}]" value="string">
                </div>
                @endif
            @endforeach
        </div>
        <small class="text-muted">1 EUR = X unites de la devise cible. Modifiez les taux selon vos besoins.</small>
    </div>
</div>
