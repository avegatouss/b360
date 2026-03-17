<x-dashboard::layouts.master
    :title="__('Attendance') . ' —' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance"
    :pageTitle="__('Attendance')">

<div class="page-header">
    <div class="add-item d-flex">
        <div class="page-title">
            <h4 class="fw-bold">{{ __('Attendance') }}</h4>
            <h6>Today's attendance &mdash; {{ now()->format('d/m/Y') }}</h6>
        </div>
    </div>
    <div class="page-btn">
        <a href="{{ route('eshop360.hr.attendance.report', $instance->slug ?? '') }}" class="btn btn-secondary"><i class="ti ti-report me-1"></i>View Report</a>
    </div>
</div>

{{-- Clock In/Out Actions --}}
<div class="card mb-4">
    <div class="card-header">
        <h5 class="card-title mb-0">{{ __('Clock In / Clock Out') }}</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('eshop360.hr.attendance.clock-in', $instance->slug ?? '') }}" method="POST">
            @csrf
            <div class="row align-items-end">
                <div class="col-md-5 mb-3">
                    <label class="form-label">{{ __('Employee') }}<span class="text-danger">*</span></label>
                    <select name="employee_id" class="form-select @error('employee_id') is-invalid @enderror" required>
                        <option value="">{{ __('-- Select Employee --') }}</option>
                        @foreach($employees as $employee)
                            <option value="{{ $employee->id }}" {{ old('employee_id') == $employee->id ? 'selected' : '' }}>
                                {{ $employee->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('employee_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">{{ __('Notes') }}</label>
                    <input type="text" name="notes" class="form-control" value="{{ old('notes') }}" placeholder="Optional note">
                </div>
                <div class="col-md-4 mb-3 d-flex gap-2">
                    <button type="submit" name="action" value="clock_in" class="btn btn-success flex-fill"><i class="ti ti-login me-1"></i>{{ __('Clock In') }}</button>
                    <button type="submit" name="action" value="clock_out" class="btn btn-danger flex-fill"><i class="ti ti-logout me-1"></i>{{ __('Clock Out') }}</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Today's Attendance --}}
<div class="card table-list-card">
    <div class="card-header">
        <h5 class="card-title mb-0">{{ __('Today\'s Records') }}</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table datatable">
                <thead class="thead-light">
                    <tr>
                        <th>{{ __('Employee') }}</th>
                        <th>{{ __('Clock In') }}</th>
                        <th>{{ __('Clock Out') }}</th>
                        <th>{{ __('Hours Worked') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th>{{ __('Notes') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($attendance as $att)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <a href="javascript:void(0);" class="avatar avatar-md me-2">
                                    @if($att->employee->avatar ?? false)
                                        <img src="{{ asset('storage/' . $att->employee->avatar) }}" alt="employee">
                                    @else
                                        <img src="{{URL::asset('build/img/users/user-33.png')}}" alt="employee">
                                    @endif
                                </a>
                                {{ $att->employee->name ?? '—' }}
                            </div>
                        </td>
                        <td>{{ $att->clock_in ? \Carbon\Carbon::parse($att->clock_in)->format('H:i') : '—' }}</td>
                        <td>{{ $att->clock_out ? \Carbon\Carbon::parse($att->clock_out)->format('H:i') : '—' }}</td>
                        <td>{{ $att->hours_worked ?? '—' }}</td>
                        <td>
                            @if(($att->status ?? '') === 'present')
                                <span class="badge bg-success">{{ __('Present') }}</span>
                            @elseif(($att->status ?? '') === 'late')
                                <span class="badge bg-warning">{{ __('Late') }}</span>
                            @elseif(($att->status ?? '') === 'absent')
                                <span class="badge bg-danger">{{ __('Absent') }}</span>
                            @else
                                <span class="badge bg-secondary">{{ $att->status ?? '—' }}</span>
                            @endif
                        </td>
                        <td>{{ $att->notes ?? '—' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center">{{ __('No attendance records for today.') }}</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

</x-dashboard::layouts.master>
