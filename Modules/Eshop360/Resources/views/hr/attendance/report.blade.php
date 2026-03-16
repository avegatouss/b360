<x-dashboard::layouts.master
    :title="'Attendance Report — ' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance"
    pageTitle="Attendance Report">

<div class="page-header">
    <div class="add-item d-flex">
        <div class="page-title">
            <h4 class="fw-bold">Attendance Report</h4>
            <h6>View attendance statistics by date range</h6>
        </div>
    </div>
    <div class="page-btn">
        <a href="{{ route('eshop360.hr.attendance.index', $instance->slug ?? '') }}" class="btn btn-secondary"><i class="ti ti-arrow-left me-1"></i>Back</a>
    </div>
</div>

{{-- Date Filter --}}
<div class="card mb-4">
    <div class="card-body">
        <form action="{{ route('eshop360.hr.attendance.report', $instance->slug ?? '') }}" method="GET">
            <div class="row align-items-end">
                <div class="col-md-4 mb-3">
                    <label class="form-label">From</label>
                    <input type="date" name="from" class="form-control" value="{{ $from ?? now()->startOfMonth()->format('Y-m-d') }}">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">To</label>
                    <input type="date" name="to" class="form-control" value="{{ $to ?? now()->format('Y-m-d') }}">
                </div>
                <div class="col-md-4 mb-3">
                    <button type="submit" class="btn btn-primary w-100"><i class="ti ti-filter me-1"></i>Filter</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Report Table --}}
<div class="card table-list-card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table">
                <thead class="thead-light">
                    <tr>
                        <th>Employee</th>
                        <th>Total Days</th>
                        <th>Present</th>
                        <th>Late</th>
                        <th>Absent</th>
                        <th>Total Hours</th>
                        <th>Avg Hours/Day</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($report ?? [] as $row)
                    <tr>
                        <td>{{ $row['employee_name'] ?? '—' }}</td>
                        <td>{{ $row['total_days'] ?? 0 }}</td>
                        <td><span class="text-success fw-bold">{{ $row['present'] ?? 0 }}</span></td>
                        <td><span class="text-warning fw-bold">{{ $row['late'] ?? 0 }}</span></td>
                        <td><span class="text-danger fw-bold">{{ $row['absent'] ?? 0 }}</span></td>
                        <td>{{ number_format($row['total_hours'] ?? 0, 1) }}h</td>
                        <td>{{ number_format($row['avg_hours'] ?? 0, 1) }}h</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center">No data for the selected period.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

</x-dashboard::layouts.master>
