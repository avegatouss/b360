@php
    $clockIn = $attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in) : null;
    $clockOut = $attendance->clock_out ? \Carbon\Carbon::parse($attendance->clock_out) : null;
    $hoursWorked = $attendance->hours_worked ?? (($clockIn && $clockOut) ? round($clockIn->diffInMinutes($clockOut) / 60, 1) : null);
    $isClockedIn = $clockIn && !$clockOut;
    $isLate = $clockIn && $clockIn->hour >= 9;
    $borderColor = $isClockedIn ? 'border-success' : ($isLate ? 'border-warning' : 'border-light');
    $bgAccent = $isClockedIn ? 'bg-success-subtle' : ($isLate ? 'bg-warning-subtle' : 'bg-light');
    $initials = mb_strtoupper(mb_substr($attendance->employee->name ?? '?', 0, 2));
    $colors = ['bg-primary', 'bg-success', 'bg-info', 'bg-warning', 'bg-danger', 'bg-secondary'];
    $color = $colors[($attendance->employee->id ?? 0) % count($colors)];
@endphp
<div class="col-xl-4 col-md-6">
    <div class="card border {{ $borderColor }} h-100">
        <div class="card-body pb-2">
            <div class="d-flex align-items-center mb-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold {{ $color }}" style="width:40px;height:40px;flex-shrink:0;">{{ $initials }}</div>
                <div class="ms-3">
                    <h6 class="mb-0 fw-bold">{{ $attendance->employee->name ?? '—' }}</h6>
                    <span class="text-muted">{{ $attendance->employee->position ?? '' }}</span>
                </div>
            </div>
            <div class="row g-2 text-center mb-2">
                <div class="col-4"><div class="p-2 rounded {{ $bgAccent }}"><div class="text-muted"><i class="ti ti-login me-1"></i>{{ __('Entree') }}</div><div class="fw-bold">{{ $clockIn ? $clockIn->format('H:i') : '—' }}</div></div></div>
                <div class="col-4"><div class="p-2 rounded {{ $bgAccent }}"><div class="text-muted"><i class="ti ti-logout me-1"></i>{{ __('Sortie') }}</div><div class="fw-bold">{{ $clockOut ? $clockOut->format('H:i') : '—' }}</div></div></div>
                <div class="col-4"><div class="p-2 rounded {{ $bgAccent }}"><div class="text-muted"><i class="ti ti-clock me-1"></i>{{ __('Heures') }}</div><div class="fw-bold">{{ $hoursWorked !== null ? number_format($hoursWorked, 1) . 'h' : '—' }}</div></div></div>
            </div>
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    @if($isClockedIn)<span class="badge bg-success-subtle text-success"><i class="ti ti-point-filled me-1"></i>{{ __('En poste') }}</span>
                    @elseif($isLate)<span class="badge bg-warning-subtle text-warning">{{ __('Retard') }}</span>
                    @elseif($clockOut)<span class="badge bg-secondary-subtle text-secondary">{{ __('Termine') }}</span>
                    @endif
                    @if($attendance->notes)<span class="text-muted ms-2">{{ $attendance->notes }}</span>@endif
                </div>
                @if($isClockedIn)
                    <form action="{{ route('eshop360.hr.attendance.clock-out', [$slug, $attendance]) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('Confirmer la sortie ?') }}')">
                        @csrf @method('PATCH')
                        <button class="btn btn-sm btn-outline-danger"><i class="ti ti-logout me-1"></i>{{ __('Sortie') }}</button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>
