<x-dashboard::layouts.master
    :title="__('Presences') . ' — ' . ($instance->name ?? 'B360')"
    :instance="$instance"
    :pageTitle="__('Presences')">

@php
    $slug = $instance->slug ?? '';
    $presents = $attendances->whereNotNull('clock_in')->count();
    $totalEmployees = $employees->count();
    $absents = max(0, $totalEmployees - $attendances->pluck('employee_id')->unique()->count());
    $avgHours = $attendances->whereNotNull('hours_worked')->avg('hours_worked');
    $stillIn = $attendances->filter(fn($a) => $a->clock_in && !$a->clock_out)->count();
    $isRange = $dateFrom !== $dateTo;
    $uniqueDates = $attendances->pluck('date')->map(fn($d) => $d instanceof \Carbon\Carbon ? $d->toDateString() : (string)$d)->unique()->sortDesc();
@endphp

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="fw-bold mb-1"><i class="ti ti-clock me-2"></i>{{ __('Suivi des presences') }}</h4>
        <p class="text-muted mb-0">{{ __('Pointage et suivi des heures de travail') }}</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('eshop360.hr.attendance.report', $slug) }}" class="btn btn-outline-info btn-sm"><i class="ti ti-chart-bar me-1"></i>{{ __('Rapport') }}</a>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#clock-in-modal"><i class="ti ti-login me-1"></i>{{ __('Pointer entree') }}</button>
    </div>
</div>

@if(session('success'))<div class="alert alert-success alert-dismissible fade show"><i class="ti ti-check me-1"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif

{{-- KPIs --}}
<div class="row g-3 mb-3">
    <div class="col-xl-3 col-md-6">
        <div class="card border-0 shadow-sm"><div class="card-body py-3 d-flex align-items-center">
            <div class="rounded-circle bg-success-subtle d-flex align-items-center justify-content-center me-3" style="width:44px;height:44px;"><i class="ti ti-user-check text-success fs-4"></i></div>
            <div><h4 class="fw-bold mb-0 text-success">{{ $presents }}</h4><span class="text-muted">{{ __('Pointages') }}</span></div>
        </div></div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card border-0 shadow-sm"><div class="card-body py-3 d-flex align-items-center">
            <div class="rounded-circle bg-danger-subtle d-flex align-items-center justify-content-center me-3" style="width:44px;height:44px;"><i class="ti ti-user-off text-danger fs-4"></i></div>
            <div><h4 class="fw-bold mb-0 {{ $absents > 0 ? 'text-danger' : '' }}">{{ $absents }}</h4><span class="text-muted">{{ __('Absents') }}</span></div>
        </div></div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card border-0 shadow-sm"><div class="card-body py-3 d-flex align-items-center">
            <div class="rounded-circle bg-info-subtle d-flex align-items-center justify-content-center me-3" style="width:44px;height:44px;"><i class="ti ti-clock text-info fs-4"></i></div>
            <div><h4 class="fw-bold mb-0">{{ $avgHours ? number_format($avgHours, 1) . 'h' : '—' }}</h4><span class="text-muted">{{ __('Heures moyennes') }}</span></div>
        </div></div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card border-0 shadow-sm"><div class="card-body py-3 d-flex align-items-center">
            <div class="rounded-circle bg-warning-subtle d-flex align-items-center justify-content-center me-3" style="width:44px;height:44px;"><i class="ti ti-point-filled text-warning fs-4"></i></div>
            <div><h4 class="fw-bold mb-0">{{ $stillIn }}</h4><span class="text-muted">{{ __('En poste') }}</span></div>
        </div></div>
    </div>
</div>

{{-- Filtres --}}
<div class="card mb-3 border-0 shadow-sm"><div class="card-body py-2">
    <form method="GET" action="{{ route('eshop360.hr.attendance.index', $slug) }}" class="row g-2 align-items-end">
        <div class="col-auto">
            <label class="form-label mb-1">{{ __('Date unique') }}</label>
            <input type="date" name="date" class="form-control form-control-sm" value="{{ !$isRange ? $dateFrom : '' }}">
        </div>
        <div class="col-auto text-muted d-flex align-items-end pb-1 fw-bold">{{ __('ou') }}</div>
        <div class="col-auto">
            <label class="form-label mb-1">{{ __('Du') }}</label>
            <input type="date" name="date_from" class="form-control form-control-sm" value="{{ $isRange ? $dateFrom : '' }}">
        </div>
        <div class="col-auto">
            <label class="form-label mb-1">{{ __('Au') }}</label>
            <input type="date" name="date_to" class="form-control form-control-sm" value="{{ $isRange ? $dateTo : '' }}">
        </div>
        <div class="col-auto"><button type="submit" class="btn btn-sm btn-primary"><i class="ti ti-filter me-1"></i>{{ __('Filtrer') }}</button></div>
        @if(request()->hasAny(['date','date_from','date_to']))<div class="col-auto"><a href="{{ route('eshop360.hr.attendance.index', $slug) }}" class="btn btn-sm btn-outline-secondary"><i class="ti ti-x"></i></a></div>@endif
    </form>
</div></div>

{{-- Quick dates --}}
@if($recentDates->isNotEmpty())
<div class="d-flex gap-2 mb-3 flex-wrap">
    @foreach($recentDates as $rd)
        @php $rdStr = $rd->date instanceof \Carbon\Carbon ? $rd->date->format('Y-m-d') : (string)$rd->date; @endphp
        <a href="{{ route('eshop360.hr.attendance.index', [$slug, 'date' => $rdStr]) }}" class="btn btn-sm {{ $rdStr === $dateFrom && !$isRange ? 'btn-primary' : 'btn-outline-secondary' }}">
            {{ \Carbon\Carbon::parse($rdStr)->format('d/m') }} <span class="badge bg-white text-dark ms-1">{{ $rd->cnt }}</span>
        </a>
    @endforeach
</div>
@endif

{{-- Pointages --}}
<div class="card border-0 shadow-sm">
    <div class="card-header bg-transparent">
        <h6 class="mb-0 fw-bold"><i class="ti ti-clock me-2"></i>
            @if($isRange)
                {{ __('Pointages du :from au :to', ['from' => \Carbon\Carbon::parse($dateFrom)->format('d/m/Y'), 'to' => \Carbon\Carbon::parse($dateTo)->format('d/m/Y')]) }}
            @else
                {{ __('Pointages du :date', ['date' => \Carbon\Carbon::parse($dateFrom)->format('d/m/Y')]) }}
            @endif
            <span class="badge bg-primary ms-1">{{ $attendances->count() }}</span>
        </h6>
    </div>
    <div class="card-body">
        @if($isRange && $uniqueDates->count() > 1)
            @foreach($uniqueDates as $uDate)
                @php $dayAtt = $attendances->filter(fn($a) => ($a->date instanceof \Carbon\Carbon ? $a->date->toDateString() : (string)$a->date) === $uDate); @endphp
                <h6 class="fw-bold mt-3 mb-2 border-bottom pb-2"><i class="ti ti-calendar me-1"></i>{{ \Carbon\Carbon::parse($uDate)->format('d/m/Y') }} <span class="badge bg-primary ms-1">{{ $dayAtt->count() }}</span></h6>
                <div class="row g-3 mb-3">
                    @foreach($dayAtt as $attendance)
                        @include('eshop360::hr.attendance._card', ['attendance' => $attendance, 'slug' => $slug])
                    @endforeach
                </div>
            @endforeach
        @else
            <div class="row g-3">
                @forelse($attendances as $attendance)
                    @include('eshop360::hr.attendance._card', ['attendance' => $attendance, 'slug' => $slug])
                @empty
                    <div class="col-12 text-center text-muted py-4"><i class="ti ti-clock-off fs-1 d-block mb-2"></i>{{ __('Aucun pointage pour cette periode.') }}</div>
                @endforelse
            </div>
        @endif
    </div>
</div>

{{-- Clock In Modal --}}
<div class="modal fade" id="clock-in-modal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
    <div class="modal-header"><h5 class="modal-title"><i class="ti ti-login me-2"></i>{{ __('Pointer une entree') }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <form action="{{ route('eshop360.hr.attendance.clock-in', $slug) }}" method="POST">@csrf
        <div class="modal-body"><div class="mb-3"><label class="form-label">{{ __('Employe') }} <span class="text-danger">*</span></label>
            <select name="employee_id" class="form-select s2-att-modal" required><option value="">{{ __('Selectionner') }}</option>
                @foreach($employees as $emp)<option value="{{ $emp->id }}">{{ $emp->name }} — {{ $emp->position ?? '' }}</option>@endforeach
            </select></div></div>
        <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('Annuler') }}</button><button type="submit" class="btn btn-success"><i class="ti ti-login me-1"></i>{{ __('Pointer') }}</button></div>
    </form>
</div></div></div>

@push('styles')<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet">@endpush
@push('scripts')
<script>
jQuery(function ($) { $('.s2-att-modal').select2({ theme: 'bootstrap-5', width: '100%', dropdownParent: $('#clock-in-modal') }); });
</script>
@endpush

</x-dashboard::layouts.master>
