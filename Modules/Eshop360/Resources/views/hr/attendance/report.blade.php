<x-dashboard::layouts.master
    :title="__('Rapport de presence') . ' — ' . ($instance->name ?? 'B360')"
    :instance="$instance"
    :pageTitle="__('Rapport de presence')">

@php
    $slug = $instance->slug ?? '';
    $totalPresent = collect($report)->sum('days_present');
    $totalHours = collect($report)->sum('total_hours');
@endphp

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="fw-bold mb-1"><i class="ti ti-chart-bar me-2"></i>{{ __('Rapport de presence') }}</h4>
        <p class="text-muted mb-0">{{ __('Synthese des presences par employe') }}</p>
    </div>
    <a href="{{ route('eshop360.hr.attendance.index', $slug) }}" class="btn btn-secondary"><i class="ti ti-arrow-left me-1"></i>{{ __('Retour') }}</a>
</div>

<div class="card mb-3 border-0 shadow-sm"><div class="card-body py-2">
    <form action="{{ route('eshop360.hr.attendance.report', $slug) }}" method="GET" class="row g-2 align-items-end">
        <div class="col-auto"><label class="form-label mb-1">{{ __('Du') }}</label><input type="date" name="from" class="form-control form-control-sm" value="{{ $from }}"></div>
        <div class="col-auto"><label class="form-label mb-1">{{ __('Au') }}</label><input type="date" name="to" class="form-control form-control-sm" value="{{ $to }}"></div>
        <div class="col-auto"><button type="submit" class="btn btn-sm btn-primary"><i class="ti ti-filter me-1"></i>{{ __('Filtrer') }}</button></div>
    </form>
</div></div>

<div class="row g-3 mb-3">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm"><div class="card-body py-3 d-flex align-items-center">
            <div class="rounded-circle bg-primary-subtle d-flex align-items-center justify-content-center me-3" style="width:44px;height:44px;"><i class="ti ti-users text-primary fs-4"></i></div>
            <div><h4 class="fw-bold mb-0">{{ count($report) }}</h4><span class="text-muted">{{ __('Employes') }}</span></div>
        </div></div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm"><div class="card-body py-3 d-flex align-items-center">
            <div class="rounded-circle bg-success-subtle d-flex align-items-center justify-content-center me-3" style="width:44px;height:44px;"><i class="ti ti-calendar-check text-success fs-4"></i></div>
            <div><h4 class="fw-bold mb-0">{{ $totalPresent }}</h4><span class="text-muted">{{ __('Jours de presence') }}</span></div>
        </div></div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm"><div class="card-body py-3 d-flex align-items-center">
            <div class="rounded-circle bg-info-subtle d-flex align-items-center justify-content-center me-3" style="width:44px;height:44px;"><i class="ti ti-clock text-info fs-4"></i></div>
            <div><h4 class="fw-bold mb-0">{{ number_format($totalHours, 1) }}h</h4><span class="text-muted">{{ __('Total heures') }}</span></div>
        </div></div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-transparent"><h6 class="fw-bold mb-0"><i class="ti ti-list me-2"></i>{{ __('Detail par employe') }} — {{ \Carbon\Carbon::parse($from)->format('d/m/Y') }} {{ __('au') }} {{ \Carbon\Carbon::parse($to)->format('d/m/Y') }}</h6></div>
    <div class="card-body p-0"><div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light"><tr>
                <th>{{ __('Employe') }}</th><th>{{ __('Poste') }}</th>
                <th class="text-center">{{ __('Jours') }}</th><th class="text-end">{{ __('Total heures') }}</th><th class="text-end">{{ __('Moyenne/jour') }}</th>
            </tr></thead>
            <tbody>
                @forelse($report as $row)
                <tr>
                    <td class="fw-medium">{{ $row['employee']->name ?? '—' }}</td>
                    <td class="text-muted">{{ $row['employee']->position ?? '—' }}</td>
                    <td class="text-center"><span class="badge bg-success-subtle text-success">{{ $row['days_present'] }}</span></td>
                    <td class="text-end fw-bold">{{ number_format($row['total_hours'], 1) }}h</td>
                    <td class="text-end">{{ number_format($row['average_hours'], 1) }}h</td>
                </tr>
                @empty
                <tr><td colspan="5" class="text-center text-muted py-4">{{ __('Aucune donnee.') }}</td></tr>
                @endforelse
            </tbody>
            @if(count($report) > 0)
            <tfoot class="table-light"><tr>
                <td colspan="2" class="fw-bold text-end">{{ __('Totaux') }}</td>
                <td class="text-center fw-bold">{{ $totalPresent }}</td>
                <td class="text-end fw-bold">{{ number_format($totalHours, 1) }}h</td>
                <td class="text-end fw-bold">{{ $totalPresent > 0 ? number_format($totalHours / $totalPresent, 1) : 0 }}h</td>
            </tr></tfoot>
            @endif
        </table>
    </div></div>
</div>

</x-dashboard::layouts.master>
