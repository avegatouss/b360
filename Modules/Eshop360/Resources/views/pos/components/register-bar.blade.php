{{-- POS Register Bar --}}
@php $registerRequired = !empty($settings['register_required'] ?? false); @endphp
@if(!$registerOpen)
<div class="card mb-2 {{ $registerRequired ? 'border-danger' : 'border-warning' }}">
    <div class="card-body py-2">
        <div class="d-flex justify-content-between align-items-center">
            <small class="{{ $registerRequired ? 'text-danger fw-bold' : 'text-warning' }}">
                <i class="ti ti-alert-triangle me-1"></i>
                {{ $registerRequired ? __('Caisse requise pour vendre') : __('Caisse non ouverte') }}
            </small>
            <button class="btn btn-sm {{ $registerRequired ? 'btn-danger' : 'btn-warning' }}" data-bs-toggle="collapse" data-bs-target="#register-form">
                <i class="ti ti-lock-open me-1"></i>{{ __('Ouvrir') }}
            </button>
        </div>
        <div class="collapse {{ $registerRequired ? 'show' : '' }} mt-2" id="register-form">
            <form method="POST" action="{{ route('eshop360.pos.registers.open', $instance->slug ?? '') }}">
                @csrf
                <div class="row g-2">
                    <div class="col-6">
                        <select name="store_id" class="form-select form-select-sm pos-select2-filter" data-placeholder="{{ __('Point de vente') }}">
                            <option value=""></option>
                            @foreach($stores as $store)
                                <option value="{{ $store->id }}">{{ $store->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6">
                        <input type="number" name="opening_amount" class="form-control form-control-sm" min="0" step="1" value="0" placeholder="{{ __('Fond de caisse') }}">
                    </div>
                    <div class="col-12 d-grid">
                        <button type="submit" class="btn btn-sm btn-primary">{{ __('Confirmer ouverture') }}</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@else
<div class="card mb-2 border-success">
    <div class="card-body py-2">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <small class="text-success fw-semibold"><i class="ti ti-lock-open me-1"></i>{{ $currentRegister->store->name ?? __('Caisse') }}</small>
                <small class="text-muted ms-2">{{ __('Fond') }}: {{ number_format($currentRegister->opening_amount, 0, ',', ' ') }} | {{ __('Theo.') }}: {{ number_format($currentRegisterExpected ?? 0, 0, ',', ' ') }}</small>
            </div>
            <button class="btn btn-sm btn-outline-danger" data-bs-toggle="collapse" data-bs-target="#register-close-form">
                <i class="ti ti-lock"></i>
            </button>
        </div>
        <div class="collapse mt-2" id="register-close-form">
            <form method="POST" action="{{ route('eshop360.pos.registers.close', [$instance->slug ?? '', $currentRegister]) }}">
                @csrf
                <div class="row g-2">
                    <div class="col-8">
                        <input type="number" name="closing_amount" class="form-control form-control-sm" min="0" step="1" value="{{ $currentRegisterExpected ?? 0 }}" placeholder="{{ __('Montant reel') }}">
                    </div>
                    <div class="col-4 d-grid">
                        <button type="submit" class="btn btn-sm btn-danger">{{ __('Cloturer') }}</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
