<x-dashboard::layouts.master
    :title="$employee->name . ' — ' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance"
    pageTitle="Employee Detail">

<div class="page-header">
    <div class="add-item d-flex">
        <div class="page-title">
            <h4 class="fw-bold">{{ $employee->name }}</h4>
            <h6>Employee details and history</h6>
        </div>
    </div>
    <div class="page-btn">
        <a href="{{ route('eshop360.hr.employees.index', $instance->slug ?? '') }}" class="btn btn-secondary"><i class="ti ti-arrow-left me-1"></i>Back</a>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <div class="d-flex align-items-center">
            <div class="avatar avatar-xl me-3">
                @if($employee->avatar)
                    <img src="{{ asset('storage/' . $employee->avatar) }}" alt="{{ $employee->name }}">
                @else
                    <img src="{{URL::asset('build/img/users/user-33.png')}}" alt="{{ $employee->name }}">
                @endif
            </div>
            <div>
                <h5 class="mb-1">{{ $employee->name }}</h5>
                <p class="text-muted mb-0">{{ $employee->position ?? '—' }} &mdash; {{ $employee->department ?? '—' }}</p>
                <p class="text-muted mb-0">{{ $employee->email ?? '' }} {{ $employee->phone ? '| ' . $employee->phone : '' }}</p>
            </div>
            <div class="ms-auto">
                @if($employee->is_active ?? true)
                    <span class="d-inline-flex align-items-center p-1 pe-2 rounded-1 text-white bg-success fs-10"><i class="ti ti-point-filled me-1 fs-11"></i>Active</span>
                @else
                    <span class="d-inline-flex align-items-center p-1 pe-2 rounded-1 text-white bg-danger fs-10"><i class="ti ti-point-filled me-1 fs-11"></i>Inactive</span>
                @endif
            </div>
        </div>
    </div>
    <div class="card-body">
        <ul class="nav nav-tabs" id="employeeTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="info-tab" data-bs-toggle="tab" data-bs-target="#info" type="button" role="tab">Info</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="salaries-tab" data-bs-toggle="tab" data-bs-target="#salaries" type="button" role="tab">Salaries</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="commissions-tab" data-bs-toggle="tab" data-bs-target="#commissions" type="button" role="tab">Commissions</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="attendance-tab" data-bs-toggle="tab" data-bs-target="#attendance" type="button" role="tab">Attendance</button>
            </li>
        </ul>
        <div class="tab-content mt-3" id="employeeTabsContent">
            {{-- Info Tab --}}
            <div class="tab-pane fade show active" id="info" role="tabpanel">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <strong>Full Name:</strong> {{ $employee->name }}
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Email:</strong> {{ $employee->email ?? '—' }}
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Phone:</strong> {{ $employee->phone ?? '—' }}
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Position:</strong> {{ $employee->position ?? '—' }}
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Department:</strong> {{ $employee->department ?? '—' }}
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Base Salary:</strong> {{ number_format($employee->salary ?? 0, 0, ',', ' ') }} XAF
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Hire Date:</strong> {{ $employee->hire_date ? \Carbon\Carbon::parse($employee->hire_date)->format('d/m/Y') : '—' }}
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Address:</strong> {{ $employee->address ?? '—' }}
                    </div>
                    @if($employee->notes)
                    <div class="col-md-12 mb-3">
                        <strong>Notes:</strong> {{ $employee->notes }}
                    </div>
                    @endif
                </div>
            </div>

            {{-- Salaries Tab --}}
            <div class="tab-pane fade" id="salaries" role="tabpanel">
                <div class="table-responsive">
                    <table class="table">
                        <thead class="thead-light">
                            <tr>
                                <th>Period</th>
                                <th>Base Salary</th>
                                <th>Bonuses</th>
                                <th>Deductions</th>
                                <th>Net Pay</th>
                                <th>Status</th>
                                <th>Paid At</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($employee->salaries ?? [] as $salary)
                            <tr>
                                <td>{{ $salary->period ?? '—' }}</td>
                                <td>{{ number_format($salary->base_amount ?? 0, 0, ',', ' ') }} XAF</td>
                                <td>{{ number_format($salary->bonuses ?? 0, 0, ',', ' ') }} XAF</td>
                                <td>{{ number_format($salary->deductions ?? 0, 0, ',', ' ') }} XAF</td>
                                <td class="fw-bold">{{ number_format($salary->net_pay ?? 0, 0, ',', ' ') }} XAF</td>
                                <td>
                                    @if(($salary->status ?? '') === 'paid')
                                        <span class="badge bg-success">Paid</span>
                                    @else
                                        <span class="badge bg-warning">Pending</span>
                                    @endif
                                </td>
                                <td>{{ $salary->paid_at ? \Carbon\Carbon::parse($salary->paid_at)->format('d/m/Y') : '—' }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center">No salary records found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Commissions Tab --}}
            <div class="tab-pane fade" id="commissions" role="tabpanel">
                <div class="table-responsive">
                    <table class="table">
                        <thead class="thead-light">
                            <tr>
                                <th>Date</th>
                                <th>Description</th>
                                <th>Amount</th>
                                <th>Source</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($employee->commissions ?? [] as $commission)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($commission->created_at)->format('d/m/Y') }}</td>
                                <td>{{ $commission->description ?? '—' }}</td>
                                <td class="fw-bold">{{ number_format($commission->amount ?? 0, 0, ',', ' ') }} XAF</td>
                                <td>{{ $commission->source ?? '—' }}</td>
                                <td>
                                    @if(($commission->status ?? '') === 'paid')
                                        <span class="badge bg-success">Paid</span>
                                    @else
                                        <span class="badge bg-warning">Pending</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center">No commissions found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Attendance Tab --}}
            <div class="tab-pane fade" id="attendance" role="tabpanel">
                <div class="table-responsive">
                    <table class="table">
                        <thead class="thead-light">
                            <tr>
                                <th>Date</th>
                                <th>Clock In</th>
                                <th>Clock Out</th>
                                <th>Hours Worked</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($employee->attendances ?? [] as $att)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($att->date)->format('d/m/Y') }}</td>
                                <td>{{ $att->clock_in ? \Carbon\Carbon::parse($att->clock_in)->format('H:i') : '—' }}</td>
                                <td>{{ $att->clock_out ? \Carbon\Carbon::parse($att->clock_out)->format('H:i') : '—' }}</td>
                                <td>{{ $att->hours_worked ?? '—' }}</td>
                                <td>
                                    @if(($att->status ?? '') === 'present')
                                        <span class="badge bg-success">Present</span>
                                    @elseif(($att->status ?? '') === 'late')
                                        <span class="badge bg-warning">Late</span>
                                    @elseif(($att->status ?? '') === 'absent')
                                        <span class="badge bg-danger">Absent</span>
                                    @else
                                        <span class="badge bg-secondary">{{ $att->status ?? '—' }}</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center">No attendance records found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

</x-dashboard::layouts.master>
