<x-dashboard::layouts.master
    :title="($gateway ? __('Edit') : __('Add')) . ' ' . __('SMS Gateway') . ' — ' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance"
    :pageTitle="$gateway ? __('Edit SMS Gateway') : __('Add SMS Gateway')">

<div class="page-header">
    <div class="add-item d-flex">
        <div class="page-title">
            <h4 class="fw-bold">{{ $gateway ? 'Edit' : 'Add' }} SMS Gateway</h4>
            <h6>{{ $gateway ? 'Update gateway configuration' : 'Configure a new SMS gateway' }}</h6>
        </div>
    </div>
    <div class="page-btn">
        <a href="{{ route('eshop360.sms-gateways.index') }}" class="btn btn-secondary"><i class="ti ti-arrow-left me-1"></i>{{ __('Back to List') }}</a>
    </div>
</div>

@if($errors->any())
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <ul class="mb-0">
        @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<div class="card">
    <div class="card-body">
        <form
            action="{{ $gateway ? route('eshop360.sms-gateways.update', $gateway) : route('eshop360.sms-gateways.store') }}"
            method="POST"
            id="sms-gateway-form"
        >
            @csrf
            @if($gateway)
                @method('PUT')
            @endif

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="display_name" class="form-label">{{ __('Display Name') }}<span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="display_name" name="display_name"
                           value="{{ old('display_name', $gateway->display_name ?? '') }}" required
                           placeholder="{{ __('e.g. My Twilio Account') }}">
                </div>

                <div class="col-md-6 mb-3">
                    <label for="driver" class="form-label">{{ __('Driver') }}<span class="text-danger">*</span></label>
                    <select class="form-select" id="driver" name="driver" required>
                        <option value="">{{ __('— Select a driver —') }}</option>
                        @foreach($availableDrivers as $driverKey => $driverInfo)
                            <option
                                value="{{ $driverKey }}"
                                {{ old('driver', $gateway->driver ?? '') === $driverKey ? 'selected' : '' }}
                            >
                                {{ $driverInfo['label'] }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Dynamic config fields container --}}
            <div id="config-fields-container">
                @foreach($availableDrivers as $driverKey => $driverInfo)
                    <div class="driver-config-panel" id="config-panel-{{ $driverKey }}" style="display: none;">
                        <h6 class="text-muted mb-3 mt-2">{{ $driverInfo['label'] }} Configuration</h6>
                        <div class="row">
                            @foreach($driverInfo['fields'] as $field)
                                <div class="col-md-6 mb-3">
                                    <label for="config_{{ $driverKey }}_{{ $field['name'] }}" class="form-label">
                                        {{ $field['label'] }}
                                        @if($field['required'] ?? false)
                                            <span class="text-danger">*</span>
                                        @endif
                                    </label>

                                    @if(($field['type'] ?? 'text') === 'select')
                                        <select class="form-select config-field"
                                                id="config_{{ $driverKey }}_{{ $field['name'] }}"
                                                name="config[{{ $field['name'] }}]"
                                                data-driver="{{ $driverKey }}"
                                                {{ ($field['required'] ?? false) ? 'required' : '' }}>
                                            @foreach($field['options'] ?? [] as $option)
                                                <option value="{{ $option }}"
                                                    {{ old('config.' . $field['name'], $gateway->config[$field['name']] ?? ($field['default'] ?? '')) === $option ? 'selected' : '' }}
                                                >{{ $option }}</option>
                                            @endforeach
                                        </select>
                                    @elseif(($field['type'] ?? 'text') === 'textarea')
                                        <textarea class="form-control config-field"
                                                  id="config_{{ $driverKey }}_{{ $field['name'] }}"
                                                  name="config[{{ $field['name'] }}]"
                                                  data-driver="{{ $driverKey }}"
                                                  rows="3"
                                                  placeholder="{{ $field['label']  }}"
                                                  {{ ($field['required'] ?? false) ? 'required' : '' }}
                                        >{{ old('config.' . $field['name'], $gateway->config[$field['name']] ?? ($field['default'] ?? '')) }}</textarea>
                                    @else
                                        <input type="{{ $field['type'] ?? 'text' }}"
                                               class="form-control config-field"
                                               id="config_{{ $driverKey }}_{{ $field['name'] }}"
                                               name="config[{{ $field['name'] }}]"
                                               data-driver="{{ $driverKey }}"
                                               value="{{ old('config.' . $field['name'], $gateway->config[$field['name']] ?? ($field['default'] ?? '')) }}"
                                               placeholder="{{ $field['label']  }}"
                                               {{ ($field['required'] ?? false) ? 'required' : '' }}>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
                               {{ old('is_active', $gateway->is_active ?? true) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active">{{ __('Active') }}</label>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="is_default" name="is_default" value="1"
                               {{ old('is_default', $gateway->is_default ?? false) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_default">{{ __('Set as Default Gateway') }}</label>
                    </div>
                </div>
            </div>

            <div class="d-flex gap-2 mt-3">
                <button type="submit" class="btn btn-primary">
                    <i class="ti ti-device-floppy me-1"></i>{{ $gateway ? 'Update' : 'Create' }} Gateway
                </button>
                <a href="{{ route('eshop360.sms-gateways.index') }}" class="btn btn-secondary">{{ __('Cancel') }}</a>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const driverSelect = document.getElementById('driver');
    const panels = document.querySelectorAll('.driver-config-panel');
    const configFields = document.querySelectorAll('.config-field');

    function showDriverPanel(driverName) {
        // Hide all panels
        panels.forEach(function (panel) {
            panel.style.display = 'none';
        });

        // Disable all config fields (so hidden ones aren't submitted)
        configFields.forEach(function (field) {
            field.disabled = true;
            field.removeAttribute('required');
        });

        if (!driverName) return;

        // Show the selected panel
        const activePanel = document.getElementById('config-panel-' + driverName);
        if (activePanel) {
            activePanel.style.display = 'block';
            // Enable & restore required on visible fields
            activePanel.querySelectorAll('.config-field').forEach(function (field) {
                field.disabled = false;
                // Re-check the original required state from the HTML attribute
                if (field.getAttribute('data-required') !== 'false') {
                    // The field was rendered with required if needed
                }
            });
        }
    }

    driverSelect.addEventListener('change', function () {
        showDriverPanel(this.value);
    });

    // Initialize on page load
    showDriverPanel(driverSelect.value);
});
</script>
@endpush

</x-dashboard::layouts.master>
