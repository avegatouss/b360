<x-dashboard::layouts.master
    :title="__('Salaries') . ' —' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance"
    :pageTitle="__('Salaries')">

<div class="page-header">
    <div class="add-item d-flex">
        <div class="page-title">
            <h4 class="fw-bold">{{ __('Salaries') }}</h4>
            <h6>{{ __('Process and manage employee salaries') }}</h6>
        </div>
    </div>
</div>

{{-- Process Salary Form --}}
<div class="card mb-4">
    <div class="card-header">
        <h5 class="card-title mb-0">{{ __('Process Salary') }}</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('eshop360.hr.salaries.process', $instance->slug ?? '') }}" method="POST">
            @csrf
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">{{ __('Employee') }}<span class="text-danger">*</span></label>
                    <select name="employee_id" class="form-select @error('employee_id') is-invalid @enderror" required>
                        <option value="">{{ __('-- Select Employee --') }}</option>
                        @foreach($employees as $employee)
                            <option value="{{ $employee->id }}" {{ old('employee_id') == $employee->id ? 'selected' : '' }}>
                                {{ $employee->name }} ({{ $employee->position ?? '—' }})
                            </option>
                        @endforeach
                    </select>
                    @error('employee_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">{{ __('Period') }}<span class="text-danger">*</span></label>
                    <input type="month" name="period" class="form-control @error('period') is-invalid @enderror" value="{{ old('period', now()->format('Y-m')) }}" required>
                    @error('period')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">{{ __('Bonuses') }}</label>
                    <input type="number" step="1" name="bonuses" class="form-control @error('bonuses') is-invalid @enderror" value="{{ old('bonuses', 0) }}">
                    @error('bonuses')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">{{ __('Deductions') }}</label>
                    <input type="number" step="1" name="deductions" class="form-control @error('deductions') is-invalid @enderror" value="{{ old('deductions', 0) }}">
                    @error('deductions')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">{{ __('Notes') }}</label>
                    <input type="text" name="notes" class="form-control" value="{{ old('notes') }}">
                </div>
                <div class="col-md-4 mb-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">{{ __('Process Salary') }}</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Salaries List --}}
<div class="card table-list-card">
    <div class="card-header d-flex align-items-center justify-content-between">
        <h5 class="card-title mb-0">{{ __('Salary History') }}</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table datatable">
                <thead class="thead-light">
                    <tr>
                        <th>{{ __('Employee') }}</th>
                        <th>{{ __('Period') }}</th>
                        <th>{{ __('Base Salary') }}</th>
                        <th>{{ __('Bonuses') }}</th>
                        <th>{{ __('Deductions') }}</th>
                        <th>{{ __('Net Pay') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th>{{ __('Paid At') }}</th>
                        <th class="no-sort"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($salaries as $salary)
                    <tr>
                        <td>{{ $salary->employee->name ?? '—' }}</td>
                        <td>{{ $salary->period ?? '—' }}</td>
                        <td>{{ number_format($salary->base_amount ?? 0, 0, ',', ' ') }} XAF</td>
                        <td>{{ number_format($salary->bonuses ?? 0, 0, ',', ' ') }} XAF</td>
                        <td>{{ number_format($salary->deductions ?? 0, 0, ',', ' ') }} XAF</td>
                        <td class="fw-bold">{{ number_format($salary->net_pay ?? 0, 0, ',', ' ') }} XAF</td>
                        <td>
                            @if(($salary->status ?? '') === 'paid')
                                <span class="badge bg-success">{{ __('Paid') }}</span>
                            @else
                                <span class="badge bg-warning">{{ __('Pending') }}</span>
                            @endif
                        </td>
                        <td>{{ $salary->paid_at ? \Carbon\Carbon::parse($salary->paid_at)->format('d/m/Y') : '—' }}</td>
                        <td>
                            <div class="edit-delete-action d-flex align-items-center">
                                @if(($salary->status ?? '') !== 'paid')
                                <form action="{{ route('eshop360.hr.salaries.pay', [$instance->slug ?? '', $salary]) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-sm btn-success" onclick='return confirm(@js(__('Mark as paid?')))'>
                                        <i class="ti ti-check me-1"></i>Pay
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center">{{ __('No salary records found.') }}</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($salaries->hasPages())
        <div class="p-3">
            {{ $salaries->links() }}
        </div>
        @endif
    </div>
</div>

</x-dashboard::layouts.master>
