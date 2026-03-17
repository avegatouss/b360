<x-dashboard::layouts.master
    :title="__('Discounts') . ' —' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance"
    :pageTitle="__('Discounts')">

<div class="page-header">
            <div class="add-item d-flex">
                <div class="page-title">
                    <h4 class="fw-bold">{{ __('Discount') }}</h4>
                    <h6>{{ __('Manage your discount') }}</h6>
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
                <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#add-discount"><i class="ti ti-circle-plus me-1"></i>{{ __('Add Discount') }}</a>
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
                                <th>{{ __('Name') }}</th>
                                <th>{{ __('Value') }}</th>
                                <th>{{ __('Discount Plan') }}</th>
                                <th>{{ __('Valitidy') }}</th>
                                <th>{{ __('Days') }}</th>
                                <th>{{ __('Products') }}</th>
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
                                <td class="text-gray-9">{{ __('Weekend Deal') }}</td>											
                                <td>{{ __('70 (Percentage)') }}</td>								
                                <td>{{ __('Standard') }}</td>								
                                <td>{{ __('22 May 2025 - 24 Jun 2025') }}</td>								
                                <td>{{ __('Sat, Sun') }}</td>								
                                <td>{{ __('All Products') }}</td>								
                                <td><span class="badge table-badge bg-success fw-medium fs-10">{{ __('Active') }}</span></td>
                                <td class="action-table-data">
                                    <div class="edit-delete-action">										
                                        <a class="me-2 p-2" href="#" data-bs-toggle="modal" data-bs-target="#edit-discount">
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
                                <td class="text-gray-9">{{ __('Loyalty Reward') }}</td>											
                                <td>{{ __('40 (Flat)') }}</td>								
                                <td>{{ __('Membership') }}</td>								
                                <td>{{ __('16 Apr 2025 - 16 May 2025') }}</td>								
                                <td>{{ __('Mon, Tue, Thu, Fri') }}</td>								
                                <td>{{ __('Specific Products') }}</td>								
                                <td><span class="badge table-badge bg-success fw-medium fs-10">{{ __('Active') }}</span></td>
                                <td class="action-table-data">
                                    <div class="edit-delete-action">										
                                        <a class="me-2 p-2" href="#" data-bs-toggle="modal" data-bs-target="#edit-discount">
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
                                <td class="text-gray-9">{{ __('Flash Sale') }}</td>											
                                <td>{{ __('60 (Percentage)') }}</td>								
                                <td>{{ __('Standard') }}</td>								
                                <td>{{ __('20 Mar 2025 - 20 Apr 2025') }}</td>								
                                <td>{{ __('Thu, Fri, Sat, Sun') }}</td>								
                                <td>{{ __('All Products') }}</td>								
                                <td><span class="badge table-badge bg-success fw-medium fs-10">{{ __('Active') }}</span></td>
                                <td class="action-table-data">
                                    <div class="edit-delete-action">										
                                        <a class="me-2 p-2" href="#" data-bs-toggle="modal" data-bs-target="#edit-discount">
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
                                <td class="text-gray-9">{{ __('Super Saver') }}</td>											
                                <td>{{ __('80 (Percentage)') }}</td>								
                                <td>{{ __('Standard') }}</td>								
                                <td>{{ __('15 Feb 2025 - 15 Apr 2025') }}</td>								
                                <td>{{ __('Mon, Tue, Wed') }}</td>								
                                <td>{{ __('All Products') }}</td>								
                                <td><span class="badge table-badge bg-success fw-medium fs-10">{{ __('Active') }}</span></td>
                                <td class="action-table-data">
                                    <div class="edit-delete-action">										
                                        <a class="me-2 p-2" href="#" data-bs-toggle="modal" data-bs-target="#edit-discount">
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
                                <td class="text-gray-9">{{ __('Surprise Savings') }}</td>											
                                <td>{{ __('50 (Flat)') }}</td>								
                                <td>{{ __('Standard') }}</td>								
                                <td>{{ __('24 Jan 2025 - 24 Mar 2025') }}</td>								
                                <td>{{ __('Mon, Tue, Thu, Sat') }}</td>								
                                <td>{{ __('Specific Products') }}</td>								
                                <td><span class="badge table-badge bg-success fw-medium fs-10">{{ __('Active') }}</span></td>
                                <td class="action-table-data">
                                    <div class="edit-delete-action">										
                                        <a class="me-2 p-2" href="#" data-bs-toggle="modal" data-bs-target="#edit-discount">
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
