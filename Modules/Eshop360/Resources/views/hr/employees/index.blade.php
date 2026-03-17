<x-dashboard::layouts.master
    :title="__('Employees') . ' —' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance"
    :pageTitle="__('Employees')">

<div class="page-header">
    <div class="add-item d-flex">
        <div class="page-title">
            <h4 class="fw-bold">{{ __('Employees') }}</h4>
            <h6>{{ __('Manage your employees') }}</h6>
        </div>
    </div>
    <ul class="table-top-head">
        <li>
            <a data-bs-toggle="tooltip" data-bs-placement="top" title="Pdf"><img src="{{URL::asset('build/img/icons/pdf.svg')}}" alt="img"></a>
        </li>
        <li>
            <a data-bs-toggle="tooltip" data-bs-placement="top" title="Excel"><img src="{{URL::asset('build/img/icons/excel.svg')}}" alt="img"></a>
        </li>
        <li>
            <a data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('Refresh') }}"><i class="ti ti-refresh"></i></a>
        </li>
    </ul>
    <div class="page-btn">
        <a href="{{ route('eshop360.hr.employees.create', $instance->slug ?? '') }}" class="btn btn-primary text-white"><i class="ti ti-circle-plus me-1"></i>Add Employee</a>
    </div>
</div>

<div class="card table-list-card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table datatable">
                <thead class="thead-light">
                    <tr>
                        <th class="no-sort">
                            <label class="checkboxs">
                                <input type="checkbox" id="select-all">
                                <span class="checkmarks"></span>
                            </label>
                        </th>
                        <th>{{ __('Name') }}</th>
                        <th>{{ __('Position') }}</th>
                        <th>{{ __('Department') }}</th>
                        <th>{{ __('Salary') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th class="no-sort"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($employees as $employee)
                    <tr>
                        <td>
                            <label class="checkboxs">
                                <input type="checkbox">
                                <span class="checkmarks"></span>
                            </label>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <a href="javascript:void(0);" class="avatar avatar-md me-2">
                                    @if($employee->avatar)
                                        <img src="{{ asset('storage/' . $employee->avatar) }}" alt="employee">
                                    @else
                                        <img src="{{URL::asset('build/img/users/user-33.png')}}" alt="employee">
                                    @endif
                                </a>
                                <a href="{{ route('eshop360.hr.employees.show', [$instance->slug ?? '', $employee]) }}">{{ $employee->name }}</a>
                            </div>
                        </td>
                        <td>{{ $employee->position ?? '—' }}</td>
                        <td>{{ $employee->department ?? '—' }}</td>
                        <td>{{ number_format($employee->salary ?? 0, 0, ',', ' ') }} XAF</td>
                        <td>
                            @if($employee->is_active ?? true)
                                <span class="d-inline-flex align-items-center p-1 pe-2 rounded-1 text-white bg-success fs-10"><i class="ti ti-point-filled me-1 fs-11"></i>{{ __('Active') }}</span>
                            @else
                                <span class="d-inline-flex align-items-center p-1 pe-2 rounded-1 text-white bg-danger fs-10"><i class="ti ti-point-filled me-1 fs-11"></i>{{ __('Inactive') }}</span>
                            @endif
                        </td>
                        <td class="d-flex">
                            <div class="edit-delete-action d-flex align-items-center">
                                <a class="me-2 p-2 d-flex align-items-center border rounded" href="{{ route('eshop360.hr.employees.show', [$instance->slug ?? '', $employee]) }}">
                                    <i data-feather="eye" class="feather-eye"></i>
                                </a>
                                <a class="me-2 p-2 d-flex align-items-center border rounded" href="{{ route('eshop360.hr.employees.edit', [$instance->slug ?? '', $employee]) }}">
                                    <i data-feather="edit" class="feather-edit"></i>
                                </a>
                                <form action="{{ route('eshop360.hr.employees.destroy', [$instance->slug ?? '', $employee]) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-2 d-flex align-items-center border rounded bg-transparent">
                                        <i data-feather="trash-2" class="feather-trash-2"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center">{{ __('No employees found.') }}</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($employees->hasPages())
        <div class="p-3">
            {{ $employees->links() }}
        </div>
        @endif
    </div>
</div>

</x-dashboard::layouts.master>
