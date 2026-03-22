<x-dashboard::layouts.master
    :title="__('Employes') . ' — ' . ($instance->name ?? 'B360')"
    :instance="$instance"
    :pageTitle="__('Gestion des employes')">

@php $slug = $instance->slug ?? ''; $currency = $eshopCurrency ?? 'FCFA'; @endphp

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="fw-bold mb-1"><i class="ti ti-users me-2"></i>{{ __('Gestion des employes') }}</h4>
        <p class="text-muted mb-0">{{ __('Equipe, salaires, commissions et presences') }}</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('eshop360.hr.salaries.index', $slug) }}" class="btn btn-outline-info btn-sm"><i class="ti ti-cash me-1"></i>{{ __('Salaires') }}</a>
        <a href="{{ route('eshop360.hr.attendance.index', $slug) }}" class="btn btn-outline-success btn-sm"><i class="ti ti-clock me-1"></i>{{ __('Presences') }}</a>
        <a href="{{ route('eshop360.hr.employees.create', $slug) }}" class="btn btn-primary"><i class="ti ti-circle-plus me-1"></i>{{ __('Nouvel employe') }}</a>
    </div>
</div>

@if(session('success'))<div class="alert alert-success alert-dismissible fade show"><i class="ti ti-check me-1"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif
@if(session('error'))<div class="alert alert-danger alert-dismissible fade show"><i class="ti ti-x me-1"></i>{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif

{{-- KPIs --}}
<div class="row g-3 mb-3">
    <div class="col-xl col-sm-6">
        <div class="card border-0 shadow-sm h-100"><div class="card-body py-3">
            <div class="d-flex align-items-center">
                <div class="rounded-circle bg-primary-subtle d-flex align-items-center justify-content-center" style="width:44px;height:44px;"><i class="ti ti-users text-primary fs-4"></i></div>
                <div class="ms-3"><h3 class="fw-bold mb-0">{{ $kpi->total }}</h3><span class="text-muted">{{ __('Employes') }}</span></div>
            </div>
            <div class="mt-2">
                <span class="badge bg-success-subtle text-success">{{ $kpi->active }} {{ __('actifs') }}</span>
                @if($kpi->inactive > 0)<span class="badge bg-secondary-subtle text-secondary ms-1">{{ $kpi->inactive }} {{ __('inactifs') }}</span>@endif
                <span class="badge bg-info-subtle text-info ms-1">{{ $kpi->with_account }} {{ __('avec compte') }}</span>
            </div>
        </div></div>
    </div>
    <div class="col-xl col-sm-6">
        <div class="card border-0 shadow-sm h-100"><div class="card-body py-3">
            <div class="d-flex align-items-center">
                <div class="rounded-circle bg-danger-subtle d-flex align-items-center justify-content-center" style="width:44px;height:44px;"><i class="ti ti-cash text-danger fs-4"></i></div>
                <div class="ms-3"><h3 class="fw-bold mb-0">{{ number_format($kpi->total_salary, 0, ',', ' ') }}</h3><span class="text-muted">{{ __('Masse salariale') }}</span></div>
            </div>
            <div class="mt-2 text-muted">{{ __('Moyenne') }}: {{ number_format($kpi->avg_salary, 0, ',', ' ') }} {{ $currency }}</div>
        </div></div>
    </div>
    <div class="col-xl col-sm-6">
        <div class="card border-0 shadow-sm h-100"><div class="card-body py-3">
            <div class="d-flex align-items-center">
                <div class="rounded-circle bg-warning-subtle d-flex align-items-center justify-content-center" style="width:44px;height:44px;"><i class="ti ti-percentage text-warning fs-4"></i></div>
                <div class="ms-3"><h3 class="fw-bold mb-0">{{ number_format($kpi->commissions_month, 0, ',', ' ') }}</h3><span class="text-muted">{{ __('Commissions du mois') }}</span></div>
            </div>
        </div></div>
    </div>
    <div class="col-xl col-sm-6">
        <div class="card border-0 shadow-sm h-100"><div class="card-body py-3">
            <div class="d-flex align-items-center">
                <div class="rounded-circle bg-success-subtle d-flex align-items-center justify-content-center" style="width:44px;height:44px;"><i class="ti ti-clock-check text-success fs-4"></i></div>
                <div class="ms-3"><h3 class="fw-bold mb-0">{{ $kpi->present_today }}</h3><span class="text-muted">{{ __('Presents aujourd\'hui') }}</span></div>
            </div>
        </div></div>
    </div>
</div>

{{-- Filtres --}}
<div class="card mb-3 border-0 shadow-sm"><div class="card-body py-2">
    <form method="GET" action="{{ route('eshop360.hr.employees.index', $slug) }}" class="row g-2 align-items-end">
        <div class="col-md-3"><label class="form-label mb-1">{{ __('Recherche') }}</label>
            <div class="input-group input-group-sm"><span class="input-group-text"><i class="ti ti-search"></i></span><input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="{{ __('Nom, email, poste...') }}"></div></div>
        <div class="col-md-2"><label class="form-label mb-1">{{ __('Departement') }}</label>
            <select name="department" class="form-select form-select-sm emp-filter-s2" data-placeholder="{{ __('Tous') }}"><option value=""></option>
                @foreach($departments as $dept)<option value="{{ $dept }}" @selected(request('department') === $dept)>{{ $dept }}</option>@endforeach
            </select></div>
        <div class="col-md-2"><label class="form-label mb-1">{{ __('Statut') }}</label>
            <select name="status" class="form-select form-select-sm emp-filter-s2" data-placeholder="{{ __('Tous') }}"><option value=""></option>
                <option value="active" @selected(request('status') === 'active')>{{ __('Actif') }}</option>
                <option value="inactive" @selected(request('status') === 'inactive')>{{ __('Inactif') }}</option>
                <option value="terminated" @selected(request('status') === 'terminated')>{{ __('Licencie') }}</option>
            </select></div>
        <div class="col-auto"><button type="submit" class="btn btn-sm btn-primary"><i class="ti ti-filter me-1"></i>{{ __('Filtrer') }}</button></div>
        @if(request()->hasAny(['search','department','status']))<div class="col-auto"><a href="{{ route('eshop360.hr.employees.index', $slug) }}" class="btn btn-sm btn-outline-secondary"><i class="ti ti-x"></i></a></div>@endif
    </form>
</div></div>

{{-- Employee Cards --}}
<div class="card border-0 shadow-sm">
    <div class="card-header bg-transparent"><h6 class="mb-0 fw-bold"><i class="ti ti-users me-2"></i>{{ __('Employes') }} <span class="badge bg-primary ms-1">{{ $employees->total() }}</span></h6></div>
    <div class="card-body">
        <div class="row g-3">
            @forelse($employees as $employee)
                @php
                    $initials = mb_strtoupper(mb_substr($employee->name, 0, 2));
                    $colors = ['bg-primary', 'bg-success', 'bg-info', 'bg-warning', 'bg-danger', 'bg-secondary'];
                    $color = $colors[$employee->id % count($colors)];
                    $tenure = $employee->joined_at ? $employee->joined_at->diffInMonths(now()) : null;
                    $sc = match($employee->status) { 'active' => 'success', 'terminated' => 'danger', default => 'secondary' };
                    $sl = match($employee->status) { 'active' => __('Actif'), 'terminated' => __('Licencie'), default => __('Inactif') };
                @endphp
                <div class="col-xl-4 col-md-6">
                    <div class="card border shadow-sm h-100 {{ $employee->status !== 'active' ? 'opacity-50' : '' }}">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold {{ $color }}" style="width:48px;height:48px;flex-shrink:0;">{{ $initials }}</div>
                                <div class="ms-3">
                                    <h6 class="mb-0 fw-bold"><a href="{{ route('eshop360.hr.employees.show', [$slug, $employee]) }}" class="text-decoration-none">{{ $employee->name }}</a></h6>
                                    <span class="text-muted">{{ $employee->position ?? '—' }}</span>
                                    @if($employee->department) <span class="badge bg-light text-dark ms-1">{{ $employee->department }}</span> @endif
                                </div>
                            </div>

                            <div class="row g-2 mb-3">
                                <div class="col-4 text-center">
                                    <div class="text-muted">{{ __('Salaire') }}</div>
                                    <div class="fw-bold">{{ number_format($employee->salary ?? 0, 0, ',', ' ') }}</div>
                                </div>
                                <div class="col-4 text-center">
                                    <div class="text-muted">{{ __('Commission') }}</div>
                                    <div class="fw-bold">{{ $employee->commission_rate ?? 0 }}%</div>
                                </div>
                                <div class="col-4 text-center">
                                    <div class="text-muted">{{ __('Anciennete') }}</div>
                                    <div class="fw-bold">
                                        @if($tenure !== null)
                                            @if($tenure < 1) {{ __('< 1 mois') }}
                                            @elseif($tenure < 12) {{ $tenure }} {{ __('mois') }}
                                            @else {{ intdiv($tenure, 12) }} {{ __('an(s)') }}
                                            @endif
                                        @else — @endif
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                @if($employee->email)<div class="text-muted"><i class="ti ti-mail me-1"></i>{{ $employee->email }}</div>@endif
                                @if($employee->phone)<div class="text-muted"><i class="ti ti-phone me-1"></i>{{ $employee->phone }}</div>@endif
                            </div>

                            <div class="d-flex justify-content-between align-items-center">
                                <span class="badge bg-{{ $sc }}-subtle text-{{ $sc }}">{{ $sl }}</span>
                                @if($employee->user_id)
                                    <span class="badge bg-info-subtle text-info"><i class="ti ti-user-check me-1"></i>{{ __('Compte') }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="card-footer bg-transparent border-top d-flex gap-1 justify-content-end py-2">
                            @if(!$employee->user_id && $employee->status === 'active')
                                <button class="btn btn-sm btn-outline-warning me-auto" data-bs-toggle="modal" data-bs-target="#create-account-{{ $employee->id }}"><i class="ti ti-user-plus me-1"></i>{{ __('Compte') }}</button>
                            @endif
                            <a href="{{ route('eshop360.hr.employees.show', [$slug, $employee]) }}" class="btn btn-sm btn-outline-info"><i class="ti ti-eye"></i></a>
                            <a href="{{ route('eshop360.hr.employees.edit', [$slug, $employee]) }}" class="btn btn-sm btn-outline-primary"><i class="ti ti-edit"></i></a>
                            <form action="{{ route('eshop360.hr.employees.destroy', [$slug, $employee]) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('Supprimer cet employe ?') }}')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger"><i class="ti ti-trash"></i></button></form>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12 text-center text-muted py-4"><i class="ti ti-users-minus fs-1 d-block mb-2"></i>{{ __('Aucun employe trouve.') }}</div>
            @endforelse
        </div>

        @if($employees->hasPages())<div class="pt-3">{{ $employees->links() }}</div>@endif
    </div>
</div>

{{-- Create Account Modals --}}
@foreach($employees->where('user_id', null) as $employee)
<div class="modal fade" id="create-account-{{ $employee->id }}" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
    <div class="modal-header"><h5 class="modal-title"><i class="ti ti-user-plus me-2"></i>{{ __('Compte pour :name', ['name' => $employee->name]) }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <form method="POST" action="{{ route('eshop360.hr.employees.create-account', [$slug, $employee]) }}">@csrf
        <div class="modal-body">
            <div class="alert alert-info py-2"><i class="ti ti-info-circle me-1"></i>{{ __('Un compte sera cree pour acceder a l\'application.') }}</div>
            <div class="mb-3"><label class="form-label">{{ __('Email') }} <span class="text-danger">*</span></label><input type="email" name="email" class="form-control" required value="{{ $employee->email }}"></div>
            <div class="mb-3"><label class="form-label">{{ __('Mot de passe') }} <span class="text-danger">*</span></label><input type="password" name="password" class="form-control" required minlength="8"></div>
            <div class="mb-3"><label class="form-label">{{ __('Confirmer') }} <span class="text-danger">*</span></label><input type="password" name="password_confirmation" class="form-control" required minlength="8"></div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('Annuler') }}</button><button type="submit" class="btn btn-primary"><i class="ti ti-user-plus me-1"></i>{{ __('Creer') }}</button></div>
    </form>
</div></div></div>
@endforeach

@push('styles')<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet">@endpush
@push('scripts')
<script>
jQuery(function ($) {
    $('.emp-filter-s2').each(function () { $(this).select2({ theme: 'bootstrap-5', allowClear: true, width: '100%', placeholder: $(this).data('placeholder') || '' }).on('select2:select select2:clear', function () { $(this).closest('form')[0].submit(); }); });
});
</script>
@endpush

</x-dashboard::layouts.master>
