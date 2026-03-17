<x-dashboard::layouts.master
    :title="__('Online Orders') . ' —' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance"
    :pageTitle="__('Online Orders')">

<div class="page-header">
            <div class="add-item d-flex">
                <div class="page-title">
                    <h4>{{ __('Sales') }}</h4>
                    <h6>{{ __('Manage Your Sales') }}</h6>
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
                <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#add-sales-new"><i class="ti ti-circle-plus me-1"></i>{{ __('Add Sales') }}</a>
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
                                <a href="javascript:void(0);" class="dropdown-item rounded-1">{{ __('Carl Evans') }}</a>
                            </li>
                            <li>
                                <a href="javascript:void(0);" class="dropdown-item rounded-1">{{ __('Minerva Rameriz') }}</a>
                            </li>
                            <li>
                                <a href="javascript:void(0);" class="dropdown-item rounded-1">{{ __('Robert Lamon') }}</a>
                            </li>
                            <li>
                                <a href="javascript:void(0);" class="dropdown-item rounded-1">{{ __('Patricia Lewis') }}</a>
                            </li>
                        </ul>
                    </div>
                    <div class="dropdown me-2">
                        <a href="javascript:void(0);" class="dropdown-toggle btn btn-white btn-md d-inline-flex align-items-center" data-bs-toggle="dropdown">
                            Staus
                        </a>
                        <ul class="dropdown-menu  dropdown-menu-end p-3">
                            <li>
                                <a href="javascript:void(0);" class="dropdown-item rounded-1">{{ __('Completed') }}</a>
                            </li>
                            <li>
                                <a href="javascript:void(0);" class="dropdown-item rounded-1">{{ __('Pending') }}</a>
                            </li>
                        </ul>
                    </div>
                    <div class="dropdown me-2">
                        <a href="javascript:void(0);" class="dropdown-toggle btn btn-white btn-md d-inline-flex align-items-center" data-bs-toggle="dropdown">
                            Payment Status
                        </a>
                        <ul class="dropdown-menu  dropdown-menu-end p-3">
                            <li>
                                <a href="javascript:void(0);" class="dropdown-item rounded-1">{{ __('Paid') }}</a>
                            </li>
                            <li>
                                <a href="javascript:void(0);" class="dropdown-item rounded-1">{{ __('Unpaid') }}</a>
                            </li>
                            <li>
                                <a href="javascript:void(0);" class="dropdown-item rounded-1">{{ __('Overdue') }}</a>
                            </li>
                        </ul>
                    </div>
                    <div class="dropdown">
                        <a href="javascript:void(0);" class="dropdown-toggle btn btn-white btn-md d-inline-flex align-items-center" data-bs-toggle="dropdown">
                            Sort By : Last 7 Days
                        </a>
                        <ul class="dropdown-menu  dropdown-menu-end p-3">
                            <li>
                                <a href="javascript:void(0);" class="dropdown-item rounded-1">{{ __('Recently Added') }}</a>
                            </li>
                            <li>
                                <a href="javascript:void(0);" class="dropdown-item rounded-1">{{ __('Ascending') }}</a>
                            </li>
                            <li>
                                <a href="javascript:void(0);" class="dropdown-item rounded-1">{{ __('Desending') }}</a>
                            </li>
                            <li>
                                <a href="javascript:void(0);" class="dropdown-item rounded-1">{{ __('Last Month') }}</a>
                            </li>
                            <li>
                                <a href="javascript:void(0);" class="dropdown-item rounded-1">{{ __('Last 7 Days') }}</a>
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
                                <th>{{ __('Customer') }}</th>
                                <th>{{ __('Reference') }}</th>
                                <th>{{ __('Date') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th>{{ __('Grand Total') }}</th>
                                <th>{{ __('Paid') }}</th>
                                <th>{{ __('Due') }}</th>
                                <th>{{ __('Payment Status') }}</th>
                                <th>{{ __('Biller') }}</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody class="sales-list">
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
                                            <img src="{{URL::asset('build/img/users/user-27.jpg')}}" alt="product">
                                        </a>
                                        <a href="javascript:void(0);">{{ __('Carl Evans') }}</a>
                                    </div>
                                </td>
                                <td>{{ __('SL001') }}</td>
                                <td>{{ __('24 Dec 2024') }}</td>
                                <td><span class="badge badge-success">{{ __('Completed') }}</span></td>
                                <td>$1000</td>
                                <td>$1000</td>
                                <td>$0.00</td>
                                <td><span class="badge badge-soft-success shadow-none badge-xs"><i class="ti ti-point-filled me-1"></i>{{ __('Paid') }}</span></td>
                                <td>{{ __('Admin') }}</td>
                                <td class="text-center">
                                    <a class="action-set" href="javascript:void(0);" data-bs-toggle="dropdown" aria-expanded="true">
                                        <i class="fa fa-ellipsis-v" aria-hidden="true"></i>
                                    </a>
                                    <ul class="dropdown-menu">
                                        <li>
                                            <a href="javascript:void(0);" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#sales-details-new"><i data-feather="eye" class="info-img"></i>{{ __('Sale Detail') }}</a>
                                        </li>
                                        <li>
                                            <a href="javascript:void(0);" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#edit-sales-new"><i data-feather="edit" class="info-img"></i>{{ __('Edit Sale') }}</a>
                                        </li>
                                        <li>
                                            <a href="javascript:void(0);" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#showpayment"><i data-feather="dollar-sign" class="info-img"></i>{{ __('Show Payments') }}</a>
                                        </li>
                                        <li>
                                            <a href="javascript:void(0);" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#createpayment"><i data-feather="plus-circle" class="info-img"></i>{{ __('Create Payment') }}</a>
                                        </li>
                                        <li>
                                            <a href="javascript:void(0);" class="dropdown-item"><i data-feather="download" class="info-img"></i>{{ __('Download pdf') }}</a>
                                        </li>	
                                        <li>
                                            <a href="javascript:void(0);" class="dropdown-item mb-0" data-bs-toggle="modal" data-bs-target="#delete"><i data-feather="trash-2" class="info-img"></i>{{ __('Delete Sale') }}</a>
                                        </li>								
                                    </ul>
                                </td>
                            </tr>
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
                                            <img src="{{URL::asset('build/img/users/user-02.jpg')}}" alt="product">
                                        </a>
                                        <a href="javascript:void(0);">{{ __('Minerva Rameriz') }}</a>
                                    </div>
                                </td>
                                <td>{{ __('SL002') }}</td>
                                <td>{{ __('10 Dec 2024') }}</td>
                                <td><span class="badge badge-cyan">{{ __('Pending') }}</span></td>
                                <td>$1500</td>
                                <td>$0.00</td>
                                <td>$1500</td>
                                <td><span class="badge badge-soft-danger badge-xs shadow-none"><i class="ti ti-point-filled me-1"></i>{{ __('Unpaid') }}</span></td>
                                <td>{{ __('Admin') }}</td>
                                <td class="text-center">
                                    <a class="action-set" href="javascript:void(0);" data-bs-toggle="dropdown" aria-expanded="true">
                                        <i class="fa fa-ellipsis-v" aria-hidden="true"></i>
                                    </a>
                                    <ul class="dropdown-menu">
                                        <li>
                                            <a href="#" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#sales-details-new"><i data-feather="eye" class="info-img"></i>{{ __('Sale Detail') }}</a>
                                        </li>
                                        <li>
                                            <a href="{{url('edit-sales')}}" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#edit-sales-new" ><i data-feather="edit" class="info-img"></i>Edit Sale</a>
                                        </li>
                                        <li>
                                            <a href="javascript:void(0);" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#showpayment"><i data-feather="dollar-sign" class="info-img"></i>{{ __('Show Payments') }}</a>
                                        </li>
                                        <li>
                                            <a href="javascript:void(0);" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#createpayment"><i data-feather="plus-circle" class="info-img"></i>{{ __('Create Payment') }}</a>
                                        </li>
                                        <li>
                                            <a href="javascript:void(0);" class="dropdown-item"><i data-feather="download" class="info-img"></i>{{ __('Download pdf') }}</a>
                                        </li>	
                                        <li>
                                            <a href="javascript:void(0);" class="dropdown-item mb-0" data-bs-toggle="modal" data-bs-target="#delete"><i data-feather="trash-2" class="info-img"></i>{{ __('Delete Sale') }}</a>
                                        </li>								
                                    </ul>
                                </td>
                            </tr>
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
                                            <img src="{{URL::asset('build/img/users/user-05.jpg')}}" alt="product">
                                        </a>
                                        <a href="javascript:void(0);">{{ __('Robert Lamon') }}</a>
                                    </div>
                                </td>
                                <td>{{ __('SL003') }}</td>
                                <td>{{ __('08 Feb 2023') }}</td>
                                <td><span class="badge badge-success">{{ __('Completed') }}</span></td>
                                <td>$1500</td>
                                <td>$0.00</td>
                                <td>$1500</td>
                                <td><span class="badge badge-soft-success shadow-none badge-xs"><i class="ti ti-point-filled me-1"></i>{{ __('Paid') }}</span></td>
                                <td>{{ __('Admin') }}</td>
                                <td class="text-center">
                                    <a class="action-set" href="javascript:void(0);"  data-bs-toggle="dropdown" aria-expanded="true">
                                        <i class="fa fa-ellipsis-v" aria-hidden="true"></i>
                                    </a>
                                    <ul class="dropdown-menu">
                                        <li>
                                            <a href="#" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#sales-details-new"><i data-feather="eye" class="info-img"></i>{{ __('Sale Detail') }}</a>
                                        </li>
                                        <li>
                                            <a href="{{url('edit-sales')}}" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#edit-sales-new"><i data-feather="edit" class="info-img"></i>Edit Sale</a>
                                        </li>
                                        <li>
                                            <a href="javascript:void(0);" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#showpayment"><i data-feather="dollar-sign" class="info-img"></i>{{ __('Show Payments') }}</a>
                                        </li>
                                        <li>
                                            <a href="javascript:void(0);" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#createpayment"><i data-feather="plus-circle" class="info-img"></i>{{ __('Create Payment') }}</a>
                                        </li>
                                        <li>
                                            <a href="javascript:void(0);" class="dropdown-item"><i data-feather="download" class="info-img"></i>{{ __('Download pdf') }}</a>
                                        </li>	
                                        <li>
                                            <a href="javascript:void(0);" class="dropdown-item mb-0" data-bs-toggle="modal" data-bs-target="#delete"><i data-feather="trash-2" class="info-img"></i>{{ __('Delete Sale') }}</a>
                                        </li>								
                                    </ul>
                                </td>
                            </tr>
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
                                            <img src="{{URL::asset('build/img/users/user-22.jpg')}}" alt="product">
                                        </a>
                                        <a href="javascript:void(0);">{{ __('Patricia Lewis') }}</a>
                                    </div>
                                </td>
                                <td>{{ __('SL004') }}</td>
                                <td>{{ __('12 Feb 2023') }}</td>
                                <td><span class="badge badge-success">{{ __('Completed') }}</span></td>
                                <td>$2000</td>
                                <td>$1000</td>
                                <td>$1000</td>
                                <td><span class="badge badge-soft-warning badge-xs shadow-none"><i class="ti ti-point-filled me-1"></i>{{ __('Overdue') }}</span></td>
                                <td>{{ __('Admin') }}</td>
                                <td class="text-center">
                                    <a class="action-set" href="javascript:void(0);" data-bs-toggle="dropdown" aria-expanded="true">
                                        <i class="fa fa-ellipsis-v" aria-hidden="true"></i>
                                    </a>
                                    <ul class="dropdown-menu">
                                        <li>
                                            <a href="#" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#sales-details-new"><i data-feather="eye" class="info-img"></i>{{ __('Sale Detail') }}</a>
                                        </li>
                                        <li>
                                            <a href="{{url('edit-sales')}}" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#edit-sales-new"><i data-feather="edit" class="info-img"></i>Edit Sale</a>
                                        </li>
                                        <li>
                                            <a href="javascript:void(0);" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#showpayment"><i data-feather="dollar-sign" class="info-img"></i>{{ __('Show Payments') }}</a>
                                        </li>
                                        <li>
                                            <a href="javascript:void(0);" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#createpayment"><i data-feather="plus-circle" class="info-img"></i>{{ __('Create Payment') }}</a>
                                        </li>
                                        <li>
                                            <a href="javascript:void(0);" class="dropdown-item"><i data-feather="download" class="info-img"></i>{{ __('Download pdf') }}</a>
                                        </li>	
                                        <li>
                                            <a href="javascript:void(0);" class="dropdown-item mb-0" data-bs-toggle="modal" data-bs-target="#delete"><i data-feather="trash-2" class="info-img"></i>{{ __('Delete Sale') }}</a>
                                        </li>								
                                    </ul>
                                </td>
                            </tr>
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
                                            <img src="{{URL::asset('build/img/users/user-03.jpg')}}" alt="product">
                                        </a>
                                        <a href="javascript:void(0);">{{ __('Mark Joslyn') }}</a>
                                    </div>
                                </td>
                                <td>{{ __('SL005') }}</td>
                                <td>{{ __('17 Mar 2023') }}</td>
                                <td><span class="badge badge-success">{{ __('Completed') }}</span></td>
                                <td>$800</td>
                                <td>$800</td>
                                <td>$0.00</td>
                                <td><span class="badge badge-soft-success shadow-none badge-xs"><i class="ti ti-point-filled me-1"></i>{{ __('Paid') }}</span></td>
                                <td>{{ __('Admin') }}</td>
                                <td class="text-center">
                                    <a class="action-set" href="javascript:void(0);" data-bs-toggle="dropdown" aria-expanded="true">
                                        <i class="fa fa-ellipsis-v" aria-hidden="true"></i>
                                    </a>
                                    <ul class="dropdown-menu">
                                        <li>
                                            <a href="#" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#sales-details-new"><i data-feather="eye" class="info-img"></i>{{ __('Sale Detail') }}</a>
                                        </li>
                                        <li>
                                            <a href="{{url('edit-sales')}}" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#edit-sales-new"><i data-feather="edit" class="info-img"></i>Edit Sale</a>
                                        </li>
                                        <li>
                                            <a href="javascript:void(0);" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#showpayment"><i data-feather="dollar-sign" class="info-img"></i>{{ __('Show Payments') }}</a>
                                        </li>
                                        <li>
                                            <a href="javascript:void(0);" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#createpayment"><i data-feather="plus-circle" class="info-img"></i>{{ __('Create Payment') }}</a>
                                        </li>
                                        <li>
                                            <a href="javascript:void(0);" class="dropdown-item"><i data-feather="download" class="info-img"></i>{{ __('Download pdf') }}</a>
                                        </li>	
                                        <li>
                                            <a href="javascript:void(0);" class="dropdown-item mb-0" data-bs-toggle="modal" data-bs-target="#delete"><i data-feather="trash-2" class="info-img"></i>{{ __('Delete Sale') }}</a>
                                        </li>								
                                    </ul>
                                </td>
                            </tr>
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
                                            <img src="{{URL::asset('build/img/users/user-12.jpg')}}" alt="product">
                                        </a>
                                        <a href="javascript:void(0);">{{ __('Marsha Betts') }}</a>
                                    </div>
                                </td>
                                <td>{{ __('SL006') }}</td>
                                <td>{{ __('24 Mar 2023') }}</td>
                                <td><span class="badge badge-cyan">{{ __('Pending') }}</span></td>
                                <td>$750</td>
                                <td>$0.00</td>
                                <td>$750</td>
                                <td><span class="badge badge-soft-danger badge-xs shadow-none"><i class="ti ti-point-filled me-1"></i>{{ __('Unpaid') }}</span></td>
                                <td>{{ __('Admin') }}</td>
                                <td class="text-center">
                                    <a class="action-set" href="javascript:void(0);" data-bs-toggle="dropdown" aria-expanded="true">
                                        <i class="fa fa-ellipsis-v" aria-hidden="true"></i>
                                    </a>
                                    <ul class="dropdown-menu">
                                        <li>
                                            <a href="#" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#sales-details-new"><i data-feather="eye" class="info-img"></i>{{ __('Sale Detail') }}</a>
                                        </li>
                                        <li>
                                            <a href="{{url('edit-sales')}}" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#edit-sales-new"><i data-feather="edit" class="info-img"></i>Edit Sale</a>
                                        </li>
                                        <li>
                                            <a href="javascript:void(0);" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#showpayment"><i data-feather="dollar-sign" class="info-img"></i>{{ __('Show Payments') }}</a>
                                        </li>
                                        <li>
                                            <a href="javascript:void(0);" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#createpayment"><i data-feather="plus-circle" class="info-img"></i>{{ __('Create Payment') }}</a>
                                        </li>
                                        <li>
                                            <a href="javascript:void(0);" class="dropdown-item"><i data-feather="download" class="info-img"></i>{{ __('Download pdf') }}</a>
                                        </li>	
                                        <li>
                                            <a href="javascript:void(0);" class="dropdown-item mb-0" data-bs-toggle="modal" data-bs-target="#delete"><i data-feather="trash-2" class="info-img"></i>{{ __('Delete Sale') }}</a>
                                        </li>								
                                    </ul>
                                </td>
                            </tr>
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
                                            <img src="{{URL::asset('build/img/users/user-06.jpg')}}" alt="product">
                                        </a>
                                        <a href="javascript:void(0);">{{ __('Daniel Jude') }}</a>
                                    </div>
                                </td>
                                <td>{{ __('SL007') }}</td>
                                <td>{{ __('06 Apr 2023') }}</td>
                                <td><span class="badge badge-success">{{ __('Completed') }}</span></td>
                                <td>$1300</td>
                                <td>$1300</td>
                                <td>$0.00</td>
                                <td><span class="badge badge-soft-success shadow-none badge-xs"><i class="ti ti-point-filled me-1"></i>{{ __('Paid') }}</span></td>
                                <td>{{ __('Admin') }}</td>
                                <td class="text-center">
                                    <a class="action-set" href="javascript:void(0);" data-bs-toggle="dropdown" aria-expanded="true">
                                        <i class="fa fa-ellipsis-v" aria-hidden="true"></i>
                                    </a>
                                    <ul class="dropdown-menu">
                                        <li>
                                            <a href="#" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#sales-details-new"><i data-feather="eye" class="info-img"></i>{{ __('Sale Detail') }}</a>
                                        </li>
                                        <li>
                                            <a href="{{url('edit-sales')}}" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#edit-sales-new"><i data-feather="edit" class="info-img"></i>Edit Sale</a>
                                        </li>
                                        <li>
                                            <a href="javascript:void(0);" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#showpayment"><i data-feather="dollar-sign" class="info-img"></i>{{ __('Show Payments') }}</a>
                                        </li>
                                        <li>
                                            <a href="javascript:void(0);" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#createpayment"><i data-feather="plus-circle" class="info-img"></i>{{ __('Create Payment') }}</a>
                                        </li>
                                        <li>
                                            <a href="javascript:void(0);" class="dropdown-item"><i data-feather="download" class="info-img"></i>{{ __('Download pdf') }}</a>
                                        </li>	
                                        <li>
                                            <a href="javascript:void(0);" class="dropdown-item mb-0" data-bs-toggle="modal" data-bs-target="#delete"><i data-feather="trash-2" class="info-img"></i>{{ __('Delete Sale') }}</a>
                                        </li>								
                                    </ul>
                                </td>
                            </tr>
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
                                            <img src="{{URL::asset('build/img/users/user-21.jpg')}}" alt="product">
                                        </a>
                                        <a href="javascript:void(0);">{{ __('Emma Bates') }}</a>
                                    </div>
                                </td>
                                <td>{{ __('SL008') }}</td>
                                <td>{{ __('16 Apr 2023') }}</td>
                                <td><span class="badge badge-success">{{ __('Completed') }}</span></td>
                                <td>$1100</td>
                                <td>$1100</td>
                                <td>$0.00</td>
                                <td><span class="badge badge-soft-success shadow-none badge-xs"><i class="ti ti-point-filled me-1"></i>{{ __('Paid') }}</span></td>
                                <td>{{ __('Admin') }}</td>
                                <td class="text-center">
                                    <a class="action-set" href="javascript:void(0);" data-bs-toggle="dropdown" aria-expanded="true">
                                        <i class="fa fa-ellipsis-v" aria-hidden="true"></i>
                                    </a>
                                    <ul class="dropdown-menu">
                                        <li>
                                            <a href="#" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#sales-details-new"><i data-feather="eye" class="info-img"></i>{{ __('Sale Detail') }}</a>
                                        </li>
                                        <li>
                                            <a href="{{url('edit-sales')}}" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#edit-sales-new"><i data-feather="edit" class="info-img"></i>Edit Sale</a>
                                        </li>
                                        <li>
                                            <a href="javascript:void(0);" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#showpayment"><i data-feather="dollar-sign" class="info-img"></i>{{ __('Show Payments') }}</a>
                                        </li>
                                        <li>
                                            <a href="javascript:void(0);" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#createpayment"><i data-feather="plus-circle" class="info-img"></i>{{ __('Create Payment') }}</a>
                                        </li>
                                        <li>
                                            <a href="javascript:void(0);" class="dropdown-item"><i data-feather="download" class="info-img"></i>{{ __('Download pdf') }}</a>
                                        </li>	
                                        <li>
                                            <a href="javascript:void(0);" class="dropdown-item mb-0" data-bs-toggle="modal" data-bs-target="#delete"><i data-feather="trash-2" class="info-img"></i>{{ __('Delete Sale') }}</a>
                                        </li>								
                                    </ul>
                                </td>
                            </tr>
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
                                            <img src="{{URL::asset('build/img/users/user-16.jpg')}}" alt="product">
                                        </a>
                                        <a href="javascript:void(0);">{{ __('Richard Fralick') }}</a>
                                    </div>
                                </td>
                                <td>{{ __('SL009') }}</td>
                                <td>{{ __('04 May 2023') }}</td>
                                <td><span class="badge badge-cyan">{{ __('Pending') }}</span></td>
                                <td>$2300</td>
                                <td>$2300</td>
                                <td>$0.00</td>
                                <td><span class="badge badge-soft-success shadow-none badge-xs"><i class="ti ti-point-filled me-1"></i>{{ __('Paid') }}</span></td>
                                <td>{{ __('Admin') }}</td>
                                <td class="text-center">
                                    <a class="action-set" href="javascript:void(0);" data-bs-toggle="dropdown" aria-expanded="true">
                                        <i class="fa fa-ellipsis-v" aria-hidden="true"></i>
                                    </a>
                                    <ul class="dropdown-menu">
                                        <li>
                                            <a href="#" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#sales-details-new"><i data-feather="eye" class="info-img"></i>{{ __('Sale Detail') }}</a>
                                        </li>
                                        <li>
                                            <a href="{{url('edit-sales')}}" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#edit-sales-new"><i data-feather="edit" class="info-img"></i>Edit Sale</a>
                                        </li>
                                        <li>
                                            <a href="javascript:void(0);" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#showpayment"><i data-feather="dollar-sign" class="info-img"></i>{{ __('Show Payments') }}</a>
                                        </li>
                                        <li>
                                            <a href="javascript:void(0);" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#createpayment"><i data-feather="plus-circle" class="info-img"></i>{{ __('Create Payment') }}</a>
                                        </li>
                                        <li>
                                            <a href="javascript:void(0);" class="dropdown-item"><i data-feather="download" class="info-img"></i>{{ __('Download pdf') }}</a>
                                        </li>	
                                        <li>
                                            <a href="javascript:void(0);" class="dropdown-item mb-0" data-bs-toggle="modal" data-bs-target="#delete"><i data-feather="trash-2" class="info-img"></i>{{ __('Delete Sale') }}</a>
                                        </li>								
                                    </ul>
                                </td>
                            </tr>
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
                                            <img src="{{URL::asset('build/img/users/user-26.jpg')}}" alt="product">
                                        </a>
                                        <a href="javascript:void(0);">{{ __('Michelle Robison') }}</a>
                                    </div>
                                </td>
                                <td>{{ __('SL010') }}</td>
                                <td>{{ __('29 May 2023') }}</td>
                                <td><span class="badge badge-cyan">{{ __('Pending') }}</span></td>
                                <td>$1700</td>
                                <td>$1700</td>
                                <td>$0.00</td>
                                <td><span class="badge badge-soft-success shadow-none badge-xs"><i class="ti ti-point-filled me-1"></i>{{ __('Paid') }}</span></td>
                                <td>{{ __('Admin') }}</td>
                                <td class="text-center">
                                    <a class="action-set" href="javascript:void(0);" data-bs-toggle="dropdown" aria-expanded="true">
                                        <i class="fa fa-ellipsis-v" aria-hidden="true"></i>
                                    </a>
                                    <ul class="dropdown-menu">
                                        <li>
                                            <a href="#" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#sales-details-new"><i data-feather="eye" class="info-img"></i>{{ __('Sale Detail') }}</a>
                                        </li>
                                        <li>
                                            <a href="{{url('edit-sales')}}" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#edit-sales-new"><i data-feather="edit" class="info-img"></i>Edit Sale</a>
                                        </li>
                                        <li>
                                            <a href="javascript:void(0);" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#showpayment"><i data-feather="dollar-sign" class="info-img"></i>{{ __('Show Payments') }}</a>
                                        </li>
                                        <li>
                                            <a href="javascript:void(0);" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#createpayment"><i data-feather="plus-circle" class="info-img"></i>{{ __('Create Payment') }}</a>
                                        </li>
                                        <li>
                                            <a href="javascript:void(0);" class="dropdown-item"><i data-feather="download" class="info-img"></i>{{ __('Download pdf') }}</a>
                                        </li>	
                                        <li>
                                            <a href="javascript:void(0);" class="dropdown-item mb-0" data-bs-toggle="modal" data-bs-target="#delete"><i data-feather="trash-2" class="info-img"></i>{{ __('Delete Sale') }}</a>
                                        </li>								
                                    </ul>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <!-- /product list -->

</x-dashboard::layouts.master>
