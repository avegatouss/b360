<x-dashboard::layouts.master
    :title="__('Salaires') . ' — ' . ($instance->name ?? 'B360')"
    :instance="$instance"
    :pageTitle="__('Salaires')">

@php $slug = $instance->slug ?? ''; @endphp

<div class="page-header">
    <div class="add-item d-flex">
        <div class="page-title">
            <h4 class="fw-bold">{{ __('Salaires') }}</h4>
            <h6>{{ __('Gestion de la paie et des remunerations') }}</h6>
        </div>
    </div>
    <div class="page-btn d-flex gap-2">
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#add-salary">
            <i class="ti ti-circle-plus me-1"></i>{{ __('Traiter un salaire') }}
        </button>
    </div>
</div>

{{-- KPI Row --}}
@php
    $totalBrut = $salaries->sum('base_amount');
    $totalNet = $salaries->sum('net_pay');
    $nbPaid = $salaries->where('status', 'paid')->count();
    $nbPending = $salaries->where('status', '!=', 'paid')->count();
@endphp
<div class="row g-3 mb-3">
    <div class="col-xl-3 col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body py-3 d-flex align-items-center">
                <div class="rounded-circle bg-primary-subtle d-flex align-items-center justify-content-center" style="width:44px;height:44px;flex-shrink:0;">
                    <i class="ti ti-cash text-primary fs-4"></i>
                </div>
                <div class="ms-3">
                    <div class="small text-muted">{{ __('Total brut') }}</div>
                    <div class="fw-bold">{{ number_format($totalBrut, 0, ',', ' ') }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body py-3 d-flex align-items-center">
                <div class="rounded-circle bg-success-subtle d-flex align-items-center justify-content-center" style="width:44px;height:44px;flex-shrink:0;">
                    <i class="ti ti-wallet text-success fs-4"></i>
                </div>
                <div class="ms-3">
                    <div class="small text-muted">{{ __('Total net') }}</div>
                    <div class="fw-bold">{{ number_format($totalNet, 0, ',', ' ') }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body py-3 d-flex align-items-center">
                <div class="rounded-circle bg-info-subtle d-flex align-items-center justify-content-center" style="width:44px;height:44px;flex-shrink:0;">
                    <i class="ti ti-check text-info fs-4"></i>
                </div>
                <div class="ms-3">
                    <div class="small text-muted">{{ __('Payes') }}</div>
                    <div class="fw-bold">{{ $nbPaid }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body py-3 d-flex align-items-center">
                <div class="rounded-circle bg-warning-subtle d-flex align-items-center justify-content-center" style="width:44px;height:44px;flex-shrink:0;">
                    <i class="ti ti-clock text-warning fs-4"></i>
                </div>
                <div class="ms-3">
                    <div class="small text-muted">{{ __('En attente') }}</div>
                    <div class="fw-bold">{{ $nbPending }}</div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Filtres --}}
<div class="card mb-3 border-0 shadow-sm">
    <div class="card-body py-2">
        <form method="GET" action="{{ route('eshop360.hr.salaries.index', $slug) }}" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small mb-1">{{ __('Recherche') }}</label>
                <input type="text" name="search" class="form-control form-control-sm" value="{{ request('search') }}" placeholder="{{ __('Nom employe...') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-1">{{ __('Periode') }}</label>
                <input type="month" name="period" class="form-control form-control-sm" value="{{ request('period') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-1">{{ __('Statut') }}</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">{{ __('Tous') }}</option>
                    <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>{{ __('Paye') }}</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>{{ __('En attente') }}</option>
                </select>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-sm btn-primary"><i class="ti ti-search"></i></button>
            </div>
            @if(request()->hasAny(['search', 'period', 'status']))
                <div class="col-auto">
                    <a href="{{ route('eshop360.hr.salaries.index', $slug) }}" class="btn btn-sm btn-outline-secondary"><i class="ti ti-x"></i></a>
                </div>
            @endif
        </form>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show"><i class="ti ti-check me-1"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show"><i class="ti ti-x me-1"></i>{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif

{{-- Salaries Table --}}
<div class="card border-0 shadow-sm">
    <div class="card-header bg-transparent">
        <h6 class="mb-0 fw-bold"><i class="ti ti-report-money me-2"></i>{{ __('Historique des salaires') }} <span class="badge bg-primary ms-1">{{ $salaries->total() }}</span></h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>{{ __('Employe') }}</th>
                        <th>{{ __('Poste') }}</th>
                        <th>{{ __('Periode') }}</th>
                        <th class="text-end">{{ __('Salaire brut') }}</th>
                        <th class="text-end">{{ __('Bonus') }}</th>
                        <th class="text-end">{{ __('Deductions') }}</th>
                        <th class="text-end">{{ __('Net') }}</th>
                        <th class="text-center">{{ __('Statut') }}</th>
                        <th class="text-end" style="width:120px;">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($salaries as $salary)
                        <tr>
                            <td class="fw-medium">{{ $salary->employee->name ?? '—' }}</td>
                            <td class="small text-muted">{{ $salary->employee->position ?? '—' }}</td>
                            <td class="small">{{ $salary->period ?? '—' }}</td>
                            <td class="text-end">{{ number_format($salary->base_amount ?? 0, 0, ',', ' ') }}</td>
                            <td class="text-end text-success">{{ number_format($salary->bonuses ?? 0, 0, ',', ' ') }}</td>
                            <td class="text-end text-danger">{{ number_format($salary->deductions ?? 0, 0, ',', ' ') }}</td>
                            <td class="text-end fw-bold">{{ number_format($salary->net_pay ?? 0, 0, ',', ' ') }}</td>
                            <td class="text-center">
                                @if(($salary->status ?? '') === 'paid')
                                    <span class="badge bg-success-subtle text-success">{{ __('Paye') }}</span>
                                @else
                                    <span class="badge bg-warning-subtle text-warning">{{ __('En attente') }}</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="d-flex gap-1 justify-content-end">
                                    @if(($salary->status ?? '') !== 'paid')
                                        <form action="{{ route('eshop360.hr.salaries.mark-paid', [$slug, $salary]) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('Marquer ce salaire comme paye ?') }}')">
                                            @csrf @method('PATCH')
                                            <button class="btn btn-sm btn-outline-success" title="{{ __('Marquer paye') }}"><i class="ti ti-check"></i></button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">
                                <i class="ti ti-report-money-off fs-1 d-block mb-2"></i>
                                {{ __('Aucun enregistrement de salaire trouve.') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($salaries->hasPages())
            <div class="p-3">{{ $salaries->links() }}</div>
        @endif
    </div>
</div>

{{-- Add Salary Modal --}}
<div class="modal fade" id="add-salary" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('Traiter un salaire') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('eshop360.hr.salaries.process', $slug) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Employe') }} <span class="text-danger">*</span></label>
                            <select name="employee_id" class="form-select select2-employee" required>
                                <option value="">{{ __('Selectionner un employe') }}</option>
                                @foreach($employees as $employee)
                                    <option value="{{ $employee->id }}" {{ old('employee_id') == $employee->id ? 'selected' : '' }}>
                                        {{ $employee->name }} ({{ $employee->position ?? '—' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Periode') }} <span class="text-danger">*</span></label>
                            <input type="month" name="period" class="form-control" value="{{ old('period', now()->format('Y-m')) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Bonus') }}</label>
                            <input type="number" step="1" name="bonuses" class="form-control" value="{{ old('bonuses', 0) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Deductions') }}</label>
                            <input type="number" step="1" name="deductions" class="form-control" value="{{ old('deductions', 0) }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label">{{ __('Notes') }}</label>
                            <input type="text" name="notes" class="form-control" value="{{ old('notes') }}" placeholder="{{ __('Notes optionnelles...') }}">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('Annuler') }}</button>
                    <button type="submit" class="btn btn-primary">{{ __('Traiter') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet">
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (typeof jQuery !== 'undefined' && jQuery.fn.select2) {
        jQuery('.select2-employee').select2({
            theme: 'bootstrap-5',
            allowClear: true,
            width: '100%',
            dropdownParent: jQuery('#add-salary'),
            placeholder: @json(__('Selectionner un employe')),
        });
    }
});
</script>
@endpush

</x-dashboard::layouts.master>
