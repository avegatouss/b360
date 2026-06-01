<x-dashboard::layouts.master
    :title="__('Modifier l\'employe') . ' — ' . ($instance->name ?? 'B360')"
    :instance="$instance"
    :pageTitle="__('Modifier l\'employe')">

@php $slug = $instance->slug ?? ''; $currency = $eshopCurrency ?? 'FCFA'; @endphp

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="fw-bold mb-1"><i class="ti ti-edit me-2"></i>{{ __('Modifier l\'employe') }}</h4>
        <p class="text-muted mb-0">{{ $employee->name }} — {{ $employee->position ?? '' }}</p>
    </div>
    <a href="{{ route('eshop360.hr.employees.show', [$slug, $employee]) }}" class="btn btn-secondary"><i class="ti ti-arrow-left me-1"></i>{{ __('Retour') }}</a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <form action="{{ route('eshop360.hr.employees.update', [$slug, $employee]) }}" method="POST">
            @csrf @method('PUT')
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">{{ __('Nom complet') }} <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $employee->name) }}" required>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">{{ __('Email') }}</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email', $employee->email) }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">{{ __('Telephone') }}</label>
                    <input type="text" name="phone" class="form-control" value="{{ old('phone', $employee->phone) }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">{{ __('Poste') }} <span class="text-danger">*</span></label>
                    <input type="text" name="position" class="form-control" value="{{ old('position', $employee->position) }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">{{ __('Departement') }}</label>
                    <input type="text" name="department" class="form-control" value="{{ old('department', $employee->department) }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">{{ __('Salaire') }} <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <input type="number" name="salary" class="form-control @error('salary') is-invalid @enderror" value="{{ old('salary', (int)$employee->salary) }}" min="0" step="1" required>
                        <span class="input-group-text">{{ $currency }}</span>
                    </div>
                    @error('salary')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">{{ __('Taux de commission (%)') }}</label>
                    <input type="number" name="commission_rate" class="form-control" value="{{ old('commission_rate', $employee->commission_rate) }}" min="0" max="100" step="0.01">
                </div>
                <div class="col-md-6">
                    <label class="form-label">{{ __('Statut') }}</label>
                    <select name="status" class="form-select edit-emp-s2">
                        <option value="active" @selected(($employee->status ?? 'active') === 'active')>{{ __('Actif') }}</option>
                        <option value="inactive" @selected(($employee->status ?? '') === 'inactive')>{{ __('Inactif') }}</option>
                        <option value="terminated" @selected(($employee->status ?? '') === 'terminated')>{{ __('Licencie') }}</option>
                    </select>
                </div>
            </div>

            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-primary"><i class="ti ti-check me-1"></i>{{ __('Enregistrer') }}</button>
                <a href="{{ route('eshop360.hr.employees.show', [$slug, $employee]) }}" class="btn btn-outline-secondary">{{ __('Annuler') }}</a>
            </div>
        </form>
    </div>
</div>

@push('styles')<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet">@endpush
@push('scripts')
<script>
jQuery(function ($) {
    $('.edit-emp-s2').select2({ theme: 'bootstrap-5', width: '100%' });
});
</script>
@endpush

</x-dashboard::layouts.master>
