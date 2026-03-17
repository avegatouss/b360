<x-dashboard::layouts.master
    :title="__('Discount Plans') . ' —' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance"
    :pageTitle="__('Discount Plans')">

<div class="page-header">
            <div class="add-item d-flex">
                <div class="page-title">
                    <h4 class="fw-bold">{{ __('Discount Plan') }}</h4>
                    <h6>{{ __('Manage your discount plans') }}</h6>
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
                <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#add-discount-plan"><i class="ti ti-circle-plus me-1"></i>{{ __('Add Discount Plan') }}</a>
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
                    <div class="dropdown me-2">
                        <a href="javascript:void(0);" class="dropdown-toggle btn btn-white btn-md d-inline-flex align-items-center" data-bs-toggle="dropdown">
                            Customer
                        </a>
                        <ul class="dropdown-menu  dropdown-menu-end p-3">
                            <li>
                                <a href="javascript:void(0);" class="dropdown-item rounded-1">{{ __('All Customers') }}</a>
                            </li>
                            <li>
                                <a href="javascript:void(0);" class="dropdown-item rounded-1">{{ __('Members Only') }}</a>
                            </li>
                            <li>
                                <a href="javascript:void(0);" class="dropdown-item rounded-1">{{ __('High-Spending Customers') }}</a>
                            </li>
                            <li>
                                <a href="javascript:void(0);" class="dropdown-item rounded-1">{{ __('Online Customers') }}</a>
                            </li>
                        </ul>
                    </div>	
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
                                <th>{{ __('Plan Name') }}</th>
                                <th>{{ __('Customers') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th class="no-sort"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <label class="checkboxs">
                                        <input type="checkbox">
                                        <span class="checkmarks"></span>
                                    </label>
                                </td>
                                <td class="text-gray-9">{{ __('Standard Plan') }}</td>											
                                <td>{{ __('All Customers') }}</td>								
                                <td><span class="badge table-badge bg-success fw-medium fs-10">{{ __('Active') }}</span></td>
                                <td class="action-table-data">
                                    <div class="edit-delete-action">										
                                        <a class="me-2 p-2" href="#" data-bs-toggle="modal" data-bs-target="#edit-discount-plan">
                                            <i data-feather="edit" class="feather-edit"></i>
                                        </a>
                                        <a data-bs-toggle="modal" data-bs-target="#delete-modal" class="p-2" href="javascript:void(0);">
                                            <i data-feather="trash-2" class="feather-trash-2"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <label class="checkboxs">
                                        <input type="checkbox">
                                        <span class="checkmarks"></span>
                                    </label>
                                </td>
                                <td class="text-gray-9">{{ __('Membership Plan') }}</td>											
                                <td>{{ __('Members Only') }}</td>								
                                <td><span class="badge table-badge bg-success fw-medium fs-10">{{ __('Active') }}</span></td>
                                <td class="action-table-data">
                                    <div class="edit-delete-action">										
                                        <a class="me-2 p-2" href="#" data-bs-toggle="modal" data-bs-target="#edit-discount-plan">
                                            <i data-feather="edit" class="feather-edit"></i>
                                        </a>
                                        <a data-bs-toggle="modal" data-bs-target="#delete-modal" class="p-2" href="javascript:void(0);">
                                            <i data-feather="trash-2" class="feather-trash-2"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <label class="checkboxs">
                                        <input type="checkbox">
                                        <span class="checkmarks"></span>
                                    </label>
                                </td>
                                <td class="text-gray-9">{{ __('Premium Plan') }}</td>											
                                <td>{{ __('High-Spending Customers') }}</td>								
                                <td><span class="badge table-badge bg-success fw-medium fs-10">{{ __('Active') }}</span></td>
                                <td class="action-table-data">
                                    <div class="edit-delete-action">										
                                        <a class="me-2 p-2" href="#" data-bs-toggle="modal" data-bs-target="#edit-discount-plan">
                                            <i data-feather="edit" class="feather-edit"></i>
                                        </a>
                                        <a data-bs-toggle="modal" data-bs-target="#delete-modal" class="p-2" href="javascript:void(0);">
                                            <i data-feather="trash-2" class="feather-trash-2"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <label class="checkboxs">
                                        <input type="checkbox">
                                        <span class="checkmarks"></span>
                                    </label>
                                </td>
                                <td class="text-gray-9">{{ __('Seasonal Plan') }}</td>											
                                <td>{{ __('All Customers') }}</td>								
                                <td><span class="badge table-badge bg-success fw-medium fs-10">{{ __('Active') }}</span></td>
                                <td class="action-table-data">
                                    <div class="edit-delete-action">										
                                        <a class="me-2 p-2" href="#" data-bs-toggle="modal" data-bs-target="#edit-discount-plan">
                                            <i data-feather="edit" class="feather-edit"></i>
                                        </a>
                                        <a data-bs-toggle="modal" data-bs-target="#delete-modal" class="p-2" href="javascript:void(0);">
                                            <i data-feather="trash-2" class="feather-trash-2"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <label class="checkboxs">
                                        <input type="checkbox">
                                        <span class="checkmarks"></span>
                                    </label>
                                </td>
                                <td class="text-gray-9">{{ __('Student Plan') }}</td>											
                                <td>{{ __('Students') }}</td>								
                                <td><span class="badge table-badge bg-success fw-medium fs-10">{{ __('Active') }}</span></td>
                                <td class="action-table-data">
                                    <div class="edit-delete-action">										
                                        <a class="me-2 p-2" href="#" data-bs-toggle="modal" data-bs-target="#edit-discount-plan">
                                            <i data-feather="edit" class="feather-edit"></i>
                                        </a>
                                        <a data-bs-toggle="modal" data-bs-target="#delete-modal" class="p-2" href="javascript:void(0);">
                                            <i data-feather="trash-2" class="feather-trash-2"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <label class="checkboxs">
                                        <input type="checkbox">
                                        <span class="checkmarks"></span>
                                    </label>
                                </td>
                                <td class="text-gray-9">{{ __('Free Shipping Plan') }}</td>											
                                <td>{{ __('Online Customers') }}</td>								
                                <td><span class="badge table-badge bg-success fw-medium fs-10">{{ __('Active') }}</span></td>
                                <td class="action-table-data">
                                    <div class="edit-delete-action">										
                                        <a class="me-2 p-2" href="#" data-bs-toggle="modal" data-bs-target="#edit-discount-plan">
                                            <i data-feather="edit" class="feather-edit"></i>
                                        </a>
                                        <a data-bs-toggle="modal" data-bs-target="#delete-modal" class="p-2" href="javascript:void(0);">
                                            <i data-feather="trash-2" class="feather-trash-2"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <label class="checkboxs">
                                        <input type="checkbox">
                                        <span class="checkmarks"></span>
                                    </label>
                                </td>
                                <td class="text-gray-9">{{ __('Celebration Plan') }}</td>											
                                <td>{{ __('All Customers') }}</td>								
                                <td><span class="badge table-badge bg-success fw-medium fs-10">{{ __('Active') }}</span></td>
                                <td class="action-table-data">
                                    <div class="edit-delete-action">										
                                        <a class="me-2 p-2" href="#" data-bs-toggle="modal" data-bs-target="#edit-discount-plan">
                                            <i data-feather="edit" class="feather-edit"></i>
                                        </a>
                                        <a data-bs-toggle="modal" data-bs-target="#delete-modal" class="p-2" href="javascript:void(0);">
                                            <i data-feather="trash-2" class="feather-trash-2"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <!-- /product list -->

</x-dashboard::layouts.master>
