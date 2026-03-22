<x-dashboard::layouts.master
    :title="__('Salaires') . ' — ' . ($instance->name ?? 'B360')"
    :instance="$instance"
    :pageTitle="__('Salaires')">

@php
    $slug = $instance->slug ?? '';
    $currency = $eshopCurrency ?? 'FCFA';
    $totalBrut = $salaries->sum('amount');
    $totalNet = $salaries->sum('net_amount');
    $nbPaid = $salaries->filter(fn($s) => $s->paid_at !== null)->count();
    $nbPending = $salaries->filter(fn($s) => $s->paid_at === null)->count();
@endphp

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="fw-bold mb-1"><i class="ti ti-report-money me-2"></i>{{ __('Gestion des salaires') }}</h4>
        <p class="text-muted mb-0">{{ __('Traitement de la paie et suivi des remunerations') }}</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('eshop360.hr.employees.index', $slug) }}" class="btn btn-outline-secondary btn-sm"><i class="ti ti-users me-1"></i>{{ __('Employes') }}</a>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#add-salary"><i class="ti ti-circle-plus me-1"></i>{{ __('Traiter un salaire') }}</button>
    </div>
</div>

@if(session('success'))<div class="alert alert-success alert-dismissible fade show"><i class="ti ti-check me-1"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif

{{-- KPIs --}}
<div class="row g-3 mb-3">
    <div class="col-xl-3 col-md-6">
        <div class="card border-0 shadow-sm"><div class="card-body py-3 d-flex align-items-center">
            <div class="rounded-circle bg-primary-subtle d-flex align-items-center justify-content-center me-3" style="width:44px;height:44px;"><i class="ti ti-cash text-primary fs-4"></i></div>
            <div><h4 class="fw-bold mb-0">{{ number_format($totalBrut, 0, ',', ' ') }}</h4><span class="text-muted">{{ __('Total brut') }}</span></div>
        </div></div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card border-0 shadow-sm"><div class="card-body py-3 d-flex align-items-center">
            <div class="rounded-circle bg-success-subtle d-flex align-items-center justify-content-center me-3" style="width:44px;height:44px;"><i class="ti ti-wallet text-success fs-4"></i></div>
            <div><h4 class="fw-bold mb-0 text-success">{{ number_format($totalNet, 0, ',', ' ') }}</h4><span class="text-muted">{{ __('Total net') }}</span></div>
        </div></div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card border-0 shadow-sm"><div class="card-body py-3 d-flex align-items-center">
            <div class="rounded-circle bg-info-subtle d-flex align-items-center justify-content-center me-3" style="width:44px;height:44px;"><i class="ti ti-check text-info fs-4"></i></div>
            <div><h4 class="fw-bold mb-0">{{ $nbPaid }}</h4><span class="text-muted">{{ __('Payes') }}</span></div>
        </div></div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card border-0 shadow-sm"><div class="card-body py-3 d-flex align-items-center">
            <div class="rounded-circle bg-warning-subtle d-flex align-items-center justify-content-center me-3" style="width:44px;height:44px;"><i class="ti ti-clock text-warning fs-4"></i></div>
            <div><h4 class="fw-bold mb-0 {{ $nbPending > 0 ? 'text-warning' : '' }}">{{ $nbPending }}</h4><span class="text-muted">{{ __('En attente') }}</span></div>
        </div></div>
    </div>
</div>

{{-- Filtres --}}
<div class="card mb-3 border-0 shadow-sm"><div class="card-body py-2">
    <form method="GET" action="{{ route('eshop360.hr.salaries.index', $slug) }}" class="row g-2 align-items-end">
        <div class="col-md-3"><label class="form-label mb-1">{{ __('Recherche') }}</label><input type="text" name="search" class="form-control form-control-sm" value="{{ request('search') }}" placeholder="{{ __('Nom employe...') }}"></div>
        <div class="col-md-2"><label class="form-label mb-1">{{ __('Periode') }}</label><input type="month" name="period" class="form-control form-control-sm" value="{{ request('period') }}"></div>
        <div class="col-md-2"><label class="form-label mb-1">{{ __('Statut') }}</label>
            <select name="status" class="form-select form-select-sm sal-filter-s2" data-placeholder="{{ __('Tous') }}"><option value=""></option>
                <option value="paid" @selected(request('status') === 'paid')>{{ __('Paye') }}</option>
                <option value="pending" @selected(request('status') === 'pending')>{{ __('En attente') }}</option>
            </select></div>
        <div class="col-auto"><button type="submit" class="btn btn-sm btn-primary"><i class="ti ti-filter me-1"></i>{{ __('Filtrer') }}</button></div>
        @if(request()->hasAny(['search','period','status']))<div class="col-auto"><a href="{{ route('eshop360.hr.salaries.index', $slug) }}" class="btn btn-sm btn-outline-secondary"><i class="ti ti-x"></i></a></div>@endif
    </form>
</div></div>

{{-- Table --}}
<div class="card border-0 shadow-sm">
    <div class="card-header bg-transparent"><h6 class="mb-0 fw-bold"><i class="ti ti-report-money me-2"></i>{{ __('Historique des salaires') }} <span class="badge bg-primary ms-1">{{ $salaries->total() }}</span></h6></div>
    <div class="card-body p-0"><div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light"><tr>
                <th>{{ __('Employe') }}</th><th>{{ __('Poste') }}</th><th>{{ __('Periode') }}</th>
                <th class="text-end">{{ __('Base') }}</th><th class="text-end">{{ __('Prime') }}</th><th class="text-end">{{ __('Retenues') }}</th>
                <th class="text-end">{{ __('Net') }}</th><th class="text-center">{{ __('Statut') }}</th><th class="text-end" style="width:80px;"></th>
            </tr></thead>
            <tbody>
                @forelse($salaries as $salary)
                    <tr>
                        <td class="fw-medium">{{ $salary->employee->name ?? '—' }}</td>
                        <td class="text-muted">{{ $salary->employee->position ?? '—' }}</td>
                        <td>{{ $salary->period ?? '—' }}</td>
                        <td class="text-end">{{ number_format($salary->amount ?? 0, 0, ',', ' ') }}</td>
                        <td class="text-end text-success">{{ ($salary->bonus ?? 0) > 0 ? '+' . number_format($salary->bonus, 0, ',', ' ') : '—' }}</td>
                        <td class="text-end text-danger">{{ ($salary->deductions ?? 0) > 0 ? '-' . number_format($salary->deductions, 0, ',', ' ') : '—' }}</td>
                        <td class="text-end fw-bold">{{ number_format($salary->net_amount ?? 0, 0, ',', ' ') }} {{ $currency }}</td>
                        <td class="text-center">
                            @if($salary->paid_at)
                                <span class="badge bg-success-subtle text-success">{{ __('Paye') }}</span>
                            @else
                                <span class="badge bg-warning-subtle text-warning">{{ __('En attente') }}</span>
                            @endif
                        </td>
                        <td class="text-end">
                            @if(!$salary->paid_at)
                                <form action="{{ route('eshop360.hr.salaries.pay', [$slug, $salary]) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('Marquer comme paye ?') }}')">
                                    @csrf @method('PATCH')
                                    <button class="btn btn-sm btn-outline-success"><i class="ti ti-check"></i></button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="text-center text-muted py-4"><i class="ti ti-report-money fs-1 d-block mb-2"></i>{{ __('Aucun salaire enregistre.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div></div>
    @if($salaries->hasPages())<div class="p-3">{{ $salaries->links() }}</div>@endif
</div>

{{-- Add Salary Modal --}}
<div class="modal fade" id="add-salary" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
    <div class="modal-header"><h5 class="modal-title"><i class="ti ti-report-money me-2"></i>{{ __('Traiter un salaire') }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <form action="{{ route('eshop360.hr.salaries.process', $slug) }}" method="POST">@csrf
        <div class="modal-body"><div class="row g-3">
            <div class="col-md-6"><label class="form-label">{{ __('Employe') }} <span class="text-danger">*</span></label>
                <select name="employee_id" class="form-select s2-sal-modal" required><option value="">{{ __('Selectionner') }}</option>
                    @foreach($employees as $emp)<option value="{{ $emp->id }}">{{ $emp->name }} — {{ $emp->position ?? '' }} ({{ number_format($emp->salary, 0, ',', ' ') }})</option>@endforeach
                </select></div>
            <div class="col-md-6"><label class="form-label">{{ __('Periode') }} <span class="text-danger">*</span></label><input type="month" name="period" class="form-control" value="{{ now()->format('Y-m') }}" required></div>
            <div class="col-md-6"><label class="form-label">{{ __('Prime') }}</label>
                <div class="input-group"><input type="number" step="1" name="bonus" class="form-control" value="0" min="0"><span class="input-group-text">{{ $currency }}</span></div></div>
            <div class="col-md-6"><label class="form-label">{{ __('Retenues') }}</label>
                <div class="input-group"><input type="number" step="1" name="deductions" class="form-control" value="0" min="0"><span class="input-group-text">{{ $currency }}</span></div></div>
            <div class="col-12"><label class="form-label">{{ __('Notes') }}</label><input type="text" name="notes" class="form-control" placeholder="{{ __('Notes optionnelles...') }}"></div>
        </div></div>
        <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('Annuler') }}</button><button type="submit" class="btn btn-primary"><i class="ti ti-check me-1"></i>{{ __('Traiter') }}</button></div>
    </form>
</div></div></div>

@push('styles')<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet">@endpush
@push('scripts')
<script>
jQuery(function ($) {
    $('.sal-filter-s2').each(function () { $(this).select2({ theme: 'bootstrap-5', allowClear: true, width: '100%', placeholder: $(this).data('placeholder') || '' }).on('select2:select select2:clear', function () { $(this).closest('form')[0].submit(); }); });
    var $m = $('#add-salary'); $('.s2-sal-modal').each(function () { $(this).select2({ theme: 'bootstrap-5', width: '100%', dropdownParent: $m }); });
});
</script>
@endpush

</x-dashboard::layouts.master>
