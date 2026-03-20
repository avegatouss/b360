<x-dashboard::layouts.master
    :title="__('Affectations de') . ' ' . ($user->full_name ?? $user->email) . ' — ' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance"
    :pageTitle="__('Affectations utilisateur')">

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
<style>
    .select2-container--bootstrap-5 .select2-selection--multiple .select2-selection__choice {
        background-color: var(--bs-primary);
        color: #fff;
        border: none;
    }
    .select2-container--bootstrap-5 .select2-selection--multiple .select2-selection__choice__remove {
        color: #fff;
    }
</style>
@endpush

<div class="page-header">
    <div class="add-item d-flex">
        <div class="page-title">
            <h4>{{ __('Affectation des ressources') }}</h4>
            <h6>{{ __('Configurez les ressources accessibles pour cet utilisateur') }}</h6>
        </div>
    </div>
    <ul class="table-top-head">
        <li>
            <a href="{{ route('eshop360.settings.user-assignments.index', $instance->slug ?? '') }}" class="btn btn-secondary">
                <i class="ti ti-arrow-left me-1"></i>{{ __('Retour') }}
            </a>
        </li>
    </ul>
</div>

{{-- User info header --}}
<div class="card mb-4">
    <div class="card-body">
        <div class="d-flex align-items-center">
            <div class="avatar avatar-lg me-3 bg-primary rounded-circle d-flex align-items-center justify-content-center">
                <span class="text-white fw-bold fs-18">{{ strtoupper(substr($user->full_name ?? $user->email, 0, 2)) }}</span>
            </div>
            <div>
                <h5 class="mb-1">{{ $user->full_name ?? $user->email }}</h5>
                <p class="text-muted mb-1">{{ $user->email }}</p>
                <div>
                    @foreach($user->roles as $role)
                        <span class="badge bg-primary-transparent me-1">{{ $role->name }}</span>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Info box --}}
<div class="alert alert-info d-flex align-items-start mb-4">
    <i class="ti ti-info-circle fs-20 me-2 mt-1"></i>
    <div>
        {{ __('Si aucune ressource n\'est selectionnee pour un type, l\'utilisateur voit toutes les ressources de ce type.') }}
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="ti ti-check me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<form action="{{ route('eshop360.settings.user-assignments.update', [$instance->slug ?? '', $user->id]) }}" method="POST">
    @csrf
    @method('PUT')

    <div class="row">
        {{-- Entrepots --}}
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="ti ti-building-warehouse me-2 text-primary"></i>{{ __('Entrepots') }}
                    </h5>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-3">{{ __('Selectionnez les entrepots auxquels cet utilisateur a acces.') }}</p>
                    <select name="warehouses[]" class="form-select select2-multiple" multiple="multiple" data-placeholder="{{ __('Tous les entrepots (par defaut)') }}">
                        @foreach($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}"
                                @if(in_array($warehouse->id, $currentAssignments['warehouse'] ?? [])) selected @endif
                            >{{ $warehouse->name }}</option>
                        @endforeach
                    </select>
                    @error('warehouses')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                    @error('warehouses.*')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>

        {{-- Magasins --}}
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="ti ti-building-store me-2 text-success"></i>{{ __('Magasins') }}
                    </h5>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-3">{{ __('Selectionnez les magasins auxquels cet utilisateur a acces.') }}</p>
                    <select name="stores[]" class="form-select select2-multiple" multiple="multiple" data-placeholder="{{ __('Tous les magasins (par defaut)') }}">
                        @foreach($stores as $store)
                            <option value="{{ $store->id }}"
                                @if(in_array($store->id, $currentAssignments['store'] ?? [])) selected @endif
                            >{{ $store->name }}</option>
                        @endforeach
                    </select>
                    @error('stores')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                    @error('stores.*')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>

        {{-- Clients --}}
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="ti ti-users me-2 text-warning"></i>{{ __('Clients') }}
                    </h5>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-3">{{ __('Selectionnez les clients auxquels cet utilisateur a acces.') }}</p>
                    <select name="customers[]" class="form-select select2-multiple" multiple="multiple" data-placeholder="{{ __('Tous les clients (par defaut)') }}">
                        @foreach($customers as $customer)
                            <option value="{{ $customer->id }}"
                                @if(in_array($customer->id, $currentAssignments['customer'] ?? [])) selected @endif
                            >{{ $customer->name }}</option>
                        @endforeach
                    </select>
                    @error('customers')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                    @error('customers.*')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-end gap-2 mb-4">
        <a href="{{ route('eshop360.settings.user-assignments.index', $instance->slug ?? '') }}" class="btn btn-secondary">
            <i class="ti ti-x me-1"></i>{{ __('Annuler') }}
        </a>
        <button type="submit" class="btn btn-primary">
            <i class="ti ti-device-floppy me-1"></i>{{ __('Enregistrer les affectations') }}
        </button>
    </div>
</form>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        $('.select2-multiple').select2({
            theme: 'bootstrap-5',
            width: '100%',
            allowClear: true,
            closeOnSelect: false
        });
    });
</script>
@endpush

</x-dashboard::layouts.master>
