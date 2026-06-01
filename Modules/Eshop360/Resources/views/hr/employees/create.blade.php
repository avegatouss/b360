<x-dashboard::layouts.master
    :title="__('Nouvel employe') . ' — ' . ($instance->name ?? 'B360')"
    :instance="$instance"
    :pageTitle="__('Nouvel employe')">

@php $slug = $instance->slug ?? ''; $currency = $eshopCurrency ?? 'FCFA'; @endphp

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="fw-bold mb-1"><i class="ti ti-user-plus me-2"></i>{{ __('Nouvel employe') }}</h4>
        <p class="text-muted mb-0">{{ __('Ajouter un nouveau membre a l\'equipe') }}</p>
    </div>
    <a href="{{ route('eshop360.hr.employees.index', $slug) }}" class="btn btn-secondary"><i class="ti ti-arrow-left me-1"></i>{{ __('Retour') }}</a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <form action="{{ route('eshop360.hr.employees.store', $slug) }}" method="POST">
            @csrf
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">{{ __('Nom complet') }} <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">{{ __('Email') }}</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email') }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">{{ __('Telephone') }}</label>
                    <input type="text" name="phone" class="form-control" value="{{ old('phone') }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">{{ __('Poste') }} <span class="text-danger">*</span></label>
                    <input type="text" name="position" class="form-control @error('position') is-invalid @enderror" value="{{ old('position') }}" required placeholder="{{ __('Ex: Responsable ventes') }}">
                    @error('position')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">{{ __('Departement') }}</label>
                    <input type="text" name="department" class="form-control" value="{{ old('department') }}" placeholder="{{ __('Ex: Commercial, Logistique...') }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">{{ __('Salaire de base') }} <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <input type="number" step="1" name="salary" class="form-control @error('salary') is-invalid @enderror" value="{{ old('salary', 0) }}" min="0" required>
                        <span class="input-group-text">{{ $currency }}</span>
                    </div>
                    @error('salary')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">{{ __('Taux de commission (%)') }}</label>
                    <input type="number" name="commission_rate" class="form-control" value="{{ old('commission_rate', 0) }}" min="0" max="100" step="0.01">
                </div>
                <div class="col-md-6">
                    <label class="form-label">{{ __('Date d\'embauche') }}</label>
                    <input type="date" name="joined_at" class="form-control" value="{{ old('joined_at', date('Y-m-d')) }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">{{ __('Statut') }}</label>
                    <select name="status" class="form-select create-emp-s2">
                        <option value="active" @selected(old('status', 'active') === 'active')>{{ __('Actif') }}</option>
                        <option value="inactive" @selected(old('status') === 'inactive')>{{ __('Inactif') }}</option>
                    </select>
                </div>
            </div>

            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-primary"><i class="ti ti-check me-1"></i>{{ __('Creer l\'employe') }}</button>
                <a href="{{ route('eshop360.hr.employees.index', $slug) }}" class="btn btn-outline-secondary">{{ __('Annuler') }}</a>
            </div>
        </form>
    </div>
</div>

@push('styles')<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet">@endpush
@push('scripts')
<script>
jQuery(function ($) {
    $('.create-emp-s2').select2({ theme: 'bootstrap-5', width: '100%' });
});
</script>
@endpush

</x-dashboard::layouts.master>
