<div class="card mb-3">
    <div class="card-header">
        <h5 class="card-title mb-0">Langue</h5>
    </div>
    <div class="card-body">
        <div class="mb-3">
            <label class="form-label">Langue par defaut</label>
            <select name="settings[locale]" class="form-select">
                @php $currentLocale = $values['locale'] ?? config('lang.default', 'fr'); @endphp
                @foreach(config('lang.supported', ['fr', 'en']) as $loc)
                    <option value="{{ $loc }}" {{ $currentLocale === $loc ? 'selected' : '' }}>
                        {{ config("lang.labels.{$loc}", $loc) }}
                    </option>
                @endforeach
            </select>
            <input type="hidden" name="types[locale]" value="string">
            <small class="text-muted">
                @if(isset($scopeId) && $scopeId > 0)
                    Langue par defaut pour cette instance. Si non defini, la langue de la plateforme sera utilisee.
                @else
                    Langue par defaut de la plateforme. Chaque instance peut definir sa propre langue.
                @endif
            </small>
        </div>
    </div>
</div>
