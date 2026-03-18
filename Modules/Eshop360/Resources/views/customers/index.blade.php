<x-dashboard::layouts.master
    :title="__('Customers') . ' —' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance"
    :pageTitle="__('Customers')">

<div class="page-header">
            <div class="add-item d-flex">
                <div class="page-title">
                    <h4 class="fw-bold">{{ __('Customers') }}</h4>
                    <h6>{{ __('Manage your customers') }}</h6>
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
                <li>
                    <a data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('Collapse') }}" id="collapse-header"><i class="ti ti-chevron-up"></i></a>
                </li>
            </ul>
            <div class="page-btn">
                <a href="#" class="btn btn-primary text-white" data-bs-toggle="modal" data-bs-target="#add-customer"><i class="ti ti-circle-plus me-1"></i>{{ __('Add Customer') }}</a>
            </div>
        </div>
        <!-- /product list -->
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
                <div class="search-set">
                    <div class="search-input">
                        <span class="btn-searchset"><i class="ti ti-search fs-14 feather-search"></i></span>
                    </div>
                </div>
                <div class="d-flex table-dropdown my-xl-auto right-content align-items-center flex-wrap row-gap-3">
                    <div class="dropdown">
                        <a href="javascript:void(0);" class="dropdown-toggle btn btn-white btn-md d-inline-flex align-items-center" data-bs-toggle="dropdown">
                            Status
                        </a>
                        <ul class="dropdown-menu  dropdown-menu-end p-3">
                            <li>
                                <a href="javascript:void(0);" class="dropdown-item rounded-1">{{ __('Active') }}</a>
                            </li>
                            <li>
                                <a href="javascript:void(0);" class="dropdown-item rounded-1">{{ __('Inactive') }}</a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
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
                                <th>{{ __('Code') }}</th>
                                <th>{{ __('Customer') }}</th>
                                <th>{{ __('Email') }}</th>
                                <th>{{ __('Phone') }}</th>
                                <th>{{ __('Country') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th class="no-sort"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($customers as $customer)
                            <tr>
                                <td>
                                    <label class="checkboxs">
                                        <input type="checkbox">
                                        <span class="checkmarks"></span>
                                    </label>
                                </td>
                                <td>{{ $customer->code ?? 'CU' . str_pad($customer->id, 3, '0', STR_PAD_LEFT) }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <a href="javascript:void(0);" class="avatar avatar-md me-2">
                                            @if($customer->avatar)
                                                <img src="{{ asset('storage/' . $customer->avatar) }}" alt="product">
                                            @else
                                                <img src="{{URL::asset('build/img/users/user-33.png')}}" alt="product">
                                            @endif
                                        </a>
                                        <a href="javascript:void(0);">{{ $customer->name }}</a>
                                    </div>
                                </td>
                                <td>{{ $customer->email ?? '—' }}</td>
                                <td>{{ $customer->phone ?? '—' }}</td>
                                <td>{{ $customer->country ?? '—' }}</td>
                                <td>
                                    @if($customer->is_active ?? true)
                                        <span class="d-inline-flex align-items-center p-1 pe-2 rounded-1 text-white bg-success fs-10"><i class="ti ti-point-filled me-1 fs-11"></i>{{ __('Active') }}</span>
                                    @else
                                        <span class="d-inline-flex align-items-center p-1 pe-2 rounded-1 text-white bg-danger fs-10"><i class="ti ti-point-filled me-1 fs-11"></i>{{ __('Inactive') }}</span>
                                    @endif
                                </td>
                                <td class="d-flex">
                                    <div class="edit-delete-action d-flex align-items-center">
                                        <a class="me-2 p-2 d-flex align-items-center border rounded" href="{{ route('eshop360.customers.show', [$instance->slug ?? '', $customer]) }}">
                                            <i data-feather="eye" class="feather-eye"></i>
                                        </a>
                                        <a class="me-2 p-2 d-flex align-items-center border rounded" href="#" data-bs-toggle="modal" data-bs-target="#edit-customer-{{ $customer->id }}">
                                            <i data-feather="edit" class="feather-edit"></i>
                                        </a>
                                        <form action="{{ route('eshop360.customers.destroy', [$instance->slug ?? '', $customer]) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure?')">
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
                                <td colspan="8" class="text-center">{{ __('No customers found.') }}</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($customers->hasPages())
                <div class="p-3">
                    {{ $customers->links() }}
                </div>
                @endif
            </div>
        </div>
        <!-- /product list -->

<!-- Add Customer Modal -->
<div class="modal fade" id="add-customer" tabindex="-1" aria-labelledby="add-customer-label" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="add-customer-label">{{ __('Add Customer') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('eshop360.customers.store', [$instance->slug ?? '']) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ __('Name') }} <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ __('Email') }}</label>
                            <input type="email" name="email" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ __('Phone') }}</label>
                            <input type="text" name="phone" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ __('Country') }}</label>
                            <input type="text" name="country" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ __('City') }}</label>
                            <input type="text" name="city" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ __('Address') }}</label>
                            <input type="text" name="address" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-primary">{{ __('Save') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Customer Modals -->
@foreach($customers as $customer)
<div class="modal fade" id="edit-customer-{{ $customer->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('Edit Customer') }} — {{ $customer->name }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('eshop360.customers.update', [$instance->slug ?? '', $customer]) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ __('Name') }} <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" value="{{ $customer->name }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ __('Email') }}</label>
                            <input type="email" name="email" class="form-control" value="{{ $customer->email }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ __('Phone') }}</label>
                            <input type="text" name="phone" class="form-control" value="{{ $customer->phone }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ __('Country') }}</label>
                            <input type="text" name="country" class="form-control" value="{{ $customer->country }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ __('City') }}</label>
                            <input type="text" name="city" class="form-control" value="{{ $customer->city }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ __('Address') }}</label>
                            <input type="text" name="address" class="form-control" value="{{ $customer->address }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ __('Status') }}</label>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" {{ ($customer->is_active ?? true) ? 'checked' : '' }}>
                                <label class="form-check-label">{{ __('Active') }}</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-primary">{{ __('Update') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach

</x-dashboard::layouts.master>
