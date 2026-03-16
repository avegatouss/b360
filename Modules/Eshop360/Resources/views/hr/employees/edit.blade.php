<x-dashboard::layouts.master
    :title="'Edit Employee — ' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance"
    pageTitle="Edit Employee">

<div class="page-header">
    <div class="page-title">
        <h4 class="fw-bold">Edit Employee</h4>
        <h6>Update employee information</h6>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('eshop360.hr.employees.update', [$instance->slug ?? '', $employee]) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $employee->name) }}" required>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $employee->email) }}">
                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Phone</label>
                    <input type="text" name="phone" class="form-control" value="{{ old('phone', $employee->phone) }}">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Position</label>
                    <input type="text" name="position" class="form-control" value="{{ old('position', $employee->position) }}">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Department</label>
                    <input type="text" name="department" class="form-control" value="{{ old('department', $employee->department) }}">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Salary <span class="text-danger">*</span></label>
                    <input type="number" name="salary" class="form-control @error('salary') is-invalid @enderror" value="{{ old('salary', $employee->salary) }}" min="0" step="0.01" required>
                    @error('salary')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Commission Rate (%)</label>
                    <input type="number" name="commission_rate" class="form-control" value="{{ old('commission_rate', $employee->commission_rate) }}" min="0" max="100" step="0.01">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="active" {{ ($employee->status ?? 'active') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ ($employee->status ?? '') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                        <option value="terminated" {{ ($employee->status ?? '') === 'terminated' ? 'selected' : '' }}>Terminated</option>
                    </select>
                </div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">Update Employee</button>
                <a href="{{ route('eshop360.hr.employees.index', $instance->slug ?? '') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

</x-dashboard::layouts.master>
