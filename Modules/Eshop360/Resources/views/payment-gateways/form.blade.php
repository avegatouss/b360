<x-dashboard::layouts.master
    :title="(isset($gateway) ? 'Modifier' : 'Ajouter') . ' passerelle - ' . ($instance->name ?? $instance->slug ?? __('B360'))"
    :instance="$instance"
    ::pageTitle="__('isset($gateway) ? \'Modifier la passerelle\' : \'Ajouter une passerelle\'')">

<div class="page-header">
    <div class="add-item d-flex">
        <div class="page-title">
            <h4 class="fw-bold">{{ isset($gateway) ? 'Modifier' : 'Ajouter' }} une passerelle</h4>
            <h6>{{ $instance->name }} &mdash; Configuration du moyen de paiement</h6>
        </div>
    </div>
    <div class="page-btn">
        <a href="{{ route('eshop360.payment-gateways.index', $instance->slug) }}" class="btn btn-outline-secondary">
            <i class="ti ti-arrow-left me-1"></i>Retour
        </a>
    </div>
</div>

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="row">
    <div class="col-xl-8">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <form method="POST"
                      action="{{ isset($gateway)
                          ? route('eshop360.payment-gateways.update', [$instance->slug, $gateway->id])
                          : route('eshop360.payment-gateways.store', $instance->slug) }}">
                    @csrf
                    @if(isset($gateway)) @method('PUT') @endif

                    @if($errors->any())
                    <div class="alert alert-danger mb-3">
                        <ul class="mb-0">
                            @foreach($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif

                    {{-- Driver selection (only on create) --}}
                    @if(!isset($gateway))
                    <div class="mb-3">
                        <label class="form-label">{{ __('Passerelle') }}<span class="text-danger">*</span></label>
                        <select name="driver" id="driver-select" class="form-select @error('driver') is-invalid @enderror"
                                onchange="window.location.href='{{ route('eshop360.payment-gateways.create', $instance->slug) }}?driver=' + this.value">
                            <option value="">{{ __('-- Choisir --') }}</option>
                            @foreach($available as $key => $info)
                                <option value="{{ $key }}" {{ (old('driver', $selectedDriver ?? '') === $key) ? 'selected' : '' }}>
                                    {{ $info['name'] }}
                                </option>
                            @endforeach
                        </select>
                        @error('driver') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    @else
                    <div class="mb-3">
                        <label class="form-label">{{ __('Passerelle') }}</label>
                        <input type="text" class="form-control" value="{{ $available[$gateway->driver]['name'] ?? $gateway->driver }}" disabled>
                    </div>
                    @endif

                    <div class="mb-3">
                        <label class="form-label">{{ __('Nom d\'affichage') }}<span class="text-danger">*</span></label>
                        <input type="text" name="display_name" class="form-control @error('display_name') is-invalid @enderror"
                               value="{{ old('display_name', $gateway->display_name ?? ($available[$selectedDriver ?? '']['name'] ?? '')) }}"
                               placeholder="{{ __('Ex: Carte bancaire Stripe') }}">
                        @error('display_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    {{-- Dynamic config fields --}}
                    @php $configFields = $fields ?? (isset($gateway) ? app(\Modules\Eshop360\Services\Payment\PaymentGatewayManager::class)->configFields($gateway->driver) : []); @endphp
                    @php $currentConfig = isset($gateway) ? $gateway->getDecryptedConfig() : []; @endphp

                    @if(count($configFields) > 0)
                    <hr class="my-4">
                    <h6 class="mb-3">{{ __('Configuration de la passerelle') }}</h6>

                    @foreach($configFields as $field)
                        <div class="mb-3">
                            <label class="form-label">
                                {{ $field['label'] }}
                                @if($field['required'] ?? false) <span class="text-danger">*</span> @endif
                            </label>

                            @if(($field['type'] ?? 'text') === 'select')
                                <select name="config_{{ $field['name'] }}" class="form-select">
                                    @foreach($field['options'] ?? [] as $optValue => $optLabel)
                                        <option value="{{ $optValue }}"
                                            {{ old('config_' . $field['name'], $currentConfig[$field['name']] ?? '') === $optValue ? 'selected' : '' }}>
                                            {{ $optLabel }}
                                        </option>
                                    @endforeach
                                </select>
                            @elseif(($field['type'] ?? 'text') === 'password')
                                <div class="input-group">
                                    <input type="password" name="config_{{ $field['name'] }}"
                                           class="form-control config-password"
                                           value="{{ old('config_' . $field['name'], $currentConfig[$field['name']] ?? '') }}"
                                           placeholder="{{ __('{{ isset($gateway) && !empty($currentConfig[$field['name']] ?? '') ? '(inchange si vide)' : '' }}') }}">
                                    <button class="btn btn-outline-secondary toggle-pw" type="button">
                                        <i class="ti ti-eye"></i>
                                    </button>
                                </div>
                            @else
                                <input type="text" name="config_{{ $field['name'] }}" class="form-control"
                                       value="{{ old('config_' . $field['name'], $currentConfig[$field['name']] ?? '') }}">
                            @endif
                        </div>
                    @endforeach
                    @endif

                    <hr class="my-4">

                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">{{ __('Ordre d\'affichage') }}</label>
                            <input type="number" name="sort_order" class="form-control"
                                   value="{{ old('sort_order', $gateway->sort_order ?? 0) }}" min="0">
                        </div>
                        <div class="col-md-4">
                            <div class="form-check form-switch mt-4">
                                <input type="hidden" name="is_active" value="0">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1"
                                       id="is_active" {{ old('is_active', $gateway->is_active ?? false) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">{{ __('Active') }}</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-check form-switch mt-4">
                                <input type="hidden" name="is_test_mode" value="0">
                                <input class="form-check-input" type="checkbox" name="is_test_mode" value="1"
                                       id="is_test_mode" {{ old('is_test_mode', $gateway->is_test_mode ?? true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_test_mode">{{ __('Mode test') }}</label>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="ti ti-check me-1"></i>{{ isset($gateway) ? 'Mettre a jour' : 'Enregistrer' }}
                        </button>
                        <a href="{{ route('eshop360.payment-gateways.index', $instance->slug) }}" class="btn btn-outline-secondary">{{ __('Annuler') }}</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.toggle-pw').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var input = this.closest('.input-group').querySelector('input');
            input.type = input.type === 'password' ? 'text' : 'password';
        });
    });
});
</script>
@endpush

</x-dashboard::layouts.master>
