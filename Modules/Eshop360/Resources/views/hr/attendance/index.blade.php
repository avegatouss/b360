<x-dashboard::layouts.master
    :title="__('Presence') . ' — ' . ($instance->name ?? 'B360')"
    :instance="$instance"
    :pageTitle="__('Presence')">

@php $slug = $instance->slug ?? ''; @endphp

<div class="page-header">
    <div class="add-item d-flex">
        <div class="page-title">
            <h4 class="fw-bold">{{ __('Presence') }}</h4>
            <h6>{{ __('Pointage et suivi des heures de travail') }}</h6>
        </div>
    </div>
    <div class="page-btn d-flex gap-2">
        <a href="{{ route('eshop360.hr.attendance.report', $slug) }}" class="btn btn-outline-secondary btn-sm">
            <i class="ti ti-chart-bar me-1"></i>{{ __('Rapport') }}
        </a>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#clock-in-modal">
            <i class="ti ti-login me-1"></i>{{ __('Pointer entree') }}
        </button>
    </div>
</div>

{{-- KPI Row --}}
@php
    $presents = $attendances->whereNotNull('clock_in')->count();
    $totalEmployees = $employees->count();
    $absents = $totalEmployees - $presents;
    $avgHours = $attendances->whereNotNull('clock_out')->avg(function ($att) {
        return $att->clock_in && $att->clock_out
            ? \Carbon\Carbon::parse($att->clock_in)->diffInMinutes(\Carbon\Carbon::parse($att->clock_out)) / 60
            : 0;
    });
@endphp
<div class="row g-3 mb-3">
    <div class="col-xl-4 col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body py-3 d-flex align-items-center">
                <div class="rounded-circle bg-success-subtle d-flex align-items-center justify-content-center" style="width:44px;height:44px;flex-shrink:0;">
                    <i class="ti ti-user-check text-success fs-4"></i>
                </div>
                <div class="ms-3">
                    <div class="small text-muted">{{ __('Presents aujourd\'hui') }}</div>
                    <div class="fw-bold">{{ $presents }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-4 col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body py-3 d-flex align-items-center">
                <div class="rounded-circle bg-danger-subtle d-flex align-items-center justify-content-center" style="width:44px;height:44px;flex-shrink:0;">
                    <i class="ti ti-user-off text-danger fs-4"></i>
                </div>
                <div class="ms-3">
                    <div class="small text-muted">{{ __('Absents') }}</div>
                    <div class="fw-bold">{{ $absents < 0 ? 0 : $absents }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-4 col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body py-3 d-flex align-items-center">
                <div class="rounded-circle bg-info-subtle d-flex align-items-center justify-content-center" style="width:44px;height:44px;flex-shrink:0;">
                    <i class="ti ti-clock-hour-4 text-info fs-4"></i>
                </div>
                <div class="ms-3">
                    <div class="small text-muted">{{ __('Heures moyennes') }}</div>
                    <div class="fw-bold">{{ $avgHours ? number_format($avgHours, 1, ',', ' ') . ' h' : '—' }}</div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Filtre date --}}
<div class="card mb-3 border-0 shadow-sm">
    <div class="card-body py-2">
        <form method="GET" action="{{ route('eshop360.hr.attendance.index', $slug) }}" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small mb-1">{{ __('Date') }}</label>
                <input type="date" name="date" class="form-control form-control-sm" value="{{ request('date', now()->format('Y-m-d')) }}">
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-sm btn-primary"><i class="ti ti-search"></i></button>
            </div>
            @if(request('date'))
                <div class="col-auto">
                    <a href="{{ route('eshop360.hr.attendance.index', $slug) }}" class="btn btn-sm btn-outline-secondary"><i class="ti ti-x"></i></a>
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

{{-- Attendance Timeline/Grid --}}
<div class="card border-0 shadow-sm">
    <div class="card-header bg-transparent">
        <h6 class="mb-0 fw-bold"><i class="ti ti-clock me-2"></i>{{ __('Pointage du jour') }} — {{ \Carbon\Carbon::parse(request('date', now()))->format('d/m/Y') }} <span class="badge bg-primary ms-1">{{ $attendances->count() }}</span></h6>
    </div>
    <div class="card-body">
        <div class="row g-3">
            @forelse($attendances as $attendance)
                @php
                    $clockIn = $attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in) : null;
                    $clockOut = $attendance->clock_out ? \Carbon\Carbon::parse($attendance->clock_out) : null;
                    $hoursWorked = ($clockIn && $clockOut) ? $clockIn->diffInMinutes($clockOut) / 60 : null;
                    $isLate = ($attendance->status ?? '') === 'late';
                    $isClockedIn = $clockIn && !$clockOut;

                    if ($isClockedIn) {
                        $borderColor = 'border-success';
                        $bgAccent = 'bg-success-subtle';
                    } elseif ($isLate) {
                        $borderColor = 'border-warning';
                        $bgAccent = 'bg-warning-subtle';
                    } else {
                        $borderColor = 'border-light';
                        $bgAccent = 'bg-light';
                    }

                    $initials = mb_strtoupper(mb_substr($attendance->employee->name ?? '?', 0, 2));
                    $colors = ['bg-primary', 'bg-success', 'bg-info', 'bg-warning', 'bg-danger', 'bg-secondary'];
                    $color = $colors[($attendance->employee->id ?? 0) % count($colors)];
                @endphp
                <div class="col-xl-4 col-md-6">
                    <div class="card border {{ $borderColor }} h-100">
                        <div class="card-body pb-2">
                            {{-- Employee Header --}}
                            <div class="d-flex align-items-center mb-3">
                                <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold {{ $color }}" style="width:40px;height:40px;font-size:.85rem;flex-shrink:0;">
                                    {{ $initials }}
                                </div>
                                <div class="ms-3">
                                    <h6 class="mb-0 fw-bold">{{ $attendance->employee->name ?? '—' }}</h6>
                                    <small class="text-muted">{{ $attendance->employee->position ?? '' }}</small>
                                </div>
                            </div>

                            {{-- Time Info --}}
                            <div class="row g-2 text-center mb-2">
                                <div class="col-4">
                                    <div class="p-2 rounded {{ $bgAccent }}">
                                        <div class="small text-muted"><i class="ti ti-login me-1"></i>{{ __('Entree') }}</div>
                                        <div class="fw-bold">{{ $clockIn ? $clockIn->format('H:i') : '—' }}</div>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="p-2 rounded {{ $bgAccent }}">
                                        <div class="small text-muted"><i class="ti ti-logout me-1"></i>{{ __('Sortie') }}</div>
                                        <div class="fw-bold">{{ $clockOut ? $clockOut->format('H:i') : '—' }}</div>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="p-2 rounded {{ $bgAccent }}">
                                        <div class="small text-muted"><i class="ti ti-clock me-1"></i>{{ __('Heures') }}</div>
                                        <div class="fw-bold">{{ $hoursWorked !== null ? number_format($hoursWorked, 1, ',', ' ') . ' h' : '—' }}</div>
                                    </div>
                                </div>
                            </div>

                            {{-- Status --}}
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    @if($isClockedIn)
                                        <span class="badge bg-success-subtle text-success"><i class="ti ti-point-filled me-1"></i>{{ __('En poste') }}</span>
                                    @elseif($isLate)
                                        <span class="badge bg-warning-subtle text-warning">{{ __('En retard') }}</span>
                                    @elseif($clockOut)
                                        <span class="badge bg-secondary-subtle text-secondary">{{ __('Termine') }}</span>
                                    @else
                                        <span class="badge bg-light text-muted">{{ __('Non pointe') }}</span>
                                    @endif
                                </div>
                                @if($isClockedIn)
                                    <form action="{{ route('eshop360.hr.attendance.clock-out', [$slug, $attendance]) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('Confirmer la sortie de cet employe ?') }}')">
                                        @csrf @method('PATCH')
                                        <button class="btn btn-sm btn-outline-danger" title="{{ __('Pointer sortie') }}">
                                            <i class="ti ti-logout me-1"></i>{{ __('Sortie') }}
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12 text-center text-muted py-4">
                    <i class="ti ti-clock-off fs-1 d-block mb-2"></i>
                    {{ __('Aucun pointage enregistre pour cette date.') }}
                </div>
            @endforelse
        </div>
    </div>
</div>

{{-- Clock In Modal --}}
<div class="modal fade" id="clock-in-modal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('Pointer une entree') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('eshop360.hr.attendance.clock-in', $slug) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">{{ __('Employe') }} <span class="text-danger">*</span></label>
                            <select name="employee_id" class="form-select select2-clock-employee" required>
                                <option value="">{{ __('Selectionner un employe') }}</option>
                                @foreach($employees as $employee)
                                    <option value="{{ $employee->id }}" {{ old('employee_id') == $employee->id ? 'selected' : '' }}>
                                        {{ $employee->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">{{ __('Notes') }}</label>
                            <input type="text" name="notes" class="form-control" value="{{ old('notes') }}" placeholder="{{ __('Notes optionnelles...') }}">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('Annuler') }}</button>
                    <button type="submit" class="btn btn-success"><i class="ti ti-login me-1"></i>{{ __('Pointer entree') }}</button>
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
        jQuery('.select2-clock-employee').select2({
            theme: 'bootstrap-5',
            allowClear: true,
            width: '100%',
            dropdownParent: jQuery('#clock-in-modal'),
            placeholder: @json(__('Selectionner un employe')),
        });
    }
});
</script>
@endpush

</x-dashboard::layouts.master>
