{{-- POS Top Bar --}}
<div class="d-flex justify-content-between align-items-center mb-3">
    <div class="d-flex align-items-center gap-3">
        <div>
            <h4 class="fw-bold mb-0"><i class="ti ti-device-desktop me-2"></i>{{ __('Terminal POS') }}</h4>
            @if($activeContextLabel)
                <div class="small text-primary mt-1">
                    <i class="ti ti-tags me-1"></i>{{ __('Tarification active') }}: {{ $activeContextLabel }}
                </div>
            @endif
        </div>
        @if($registerOpen)
            <span class="badge bg-success-subtle text-success px-3 py-2">
                <i class="ti ti-lock-open me-1"></i>{{ __('Caisse ouverte') }}
            </span>
        @else
            <span class="badge bg-warning-subtle text-warning px-3 py-2">
                <i class="ti ti-lock me-1"></i>{{ __('Caisse fermee') }}
            </span>
        @endif
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('eshop360.pos.orders', $instance->slug ?? '') }}" class="btn btn-outline-secondary btn-sm">
            <i class="ti ti-list-details me-1"></i>{{ __('Historique') }}
        </a>
        <a href="{{ route('eshop360.pos.settings', $instance->slug ?? '') }}" class="btn btn-outline-secondary btn-sm">
            <i class="ti ti-settings me-1"></i>{{ __('Parametres') }}
        </a>
    </div>
</div>
