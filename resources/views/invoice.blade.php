<?php $page = 'pos-orders'; ?>
@extends('layout.mainlayout')
@section('content')

<div class="page-wrapper">
    <div class="content">
        <div class="page-header">
            <div class="add-item d-flex">
                <div class="page-title">
                    <h4>Invoices</h4>
                    <h6>Manage your stock invoices</h6>
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
                    <a data-bs-toggle="tooltip" data-bs-placement="top" title="Refresh"><i class="ti ti-refresh"></i></a>
                </li>
                <li>
                    <a data-bs-toggle="tooltip" data-bs-placement="top" title="Collapse" id="collapse-header"><i class="ti ti-chevron-up"></i></a>
                </li>
            </ul>
        </div>
        
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
                                <a href="javascript:void(0);" class="dropdown-item rounded-1">Carl Evans</a>
                            </li>
                            <li>
                                <a href="javascript:void(0);" class="dropdown-item rounded-1">Minerva Rameriz</a>
                            </li>
                            <li>
                                <a href="javascript:void(0);" class="dropdown-item rounded-1">Robert Lamon</a>
                            </li>
                            <li>
                                <a href="javascript:void(0);" class="dropdown-item rounded-1">Patricia Lewis</a>
                            </li>
                        </ul>
                    </div>
                    <div class="dropdown me-2">
                        <a href="javascript:void(0);" class="dropdown-toggle btn btn-white btn-md d-inline-flex align-items-center" data-bs-toggle="dropdown">
                            Status
                        </a>
                        <ul class="dropdown-menu  dropdown-menu-end p-3">
                            <li>
                                <a href="javascript:void(0);" class="dropdown-item rounded-1">Paid</a>
                            </li>
                            <li>
                                <a href="javascript:void(0);" class="dropdown-item rounded-1">Unpaid</a>
                            </li>
                            <li>
                                <a href="javascript:void(0);" class="dropdown-item rounded-1">Overdue</a>
                            </li>
                        </ul>
                    </div>
                    <div class="dropdown">
                        <a href="javascript:void(0);" class="dropdown-toggle btn btn-white btn-md d-inline-flex align-items-center" data-bs-toggle="dropdown">
                            Sort By : Last 7 Days
                        </a>
                        <ul class="dropdown-menu  dropdown-menu-end p-3">
                            <li>
                                <a href="javascript:void(0);" class="dropdown-item rounded-1">Recently Added</a>
                            </li>
                            <li>
                                <a href="javascript:void(0);" class="dropdown-item rounded-1">Ascending</a>
                            </li>
                            <li>
                                <a href="javascript:void(0);" class="dropdown-item rounded-1">Desending</a>
                            </li>
                            <li>
                                <a href="javascript:void(0);" class="dropdown-item rounded-1">Last Month</a>
                            </li>
                            <li>
                                <a href="javascript:void(0);" class="dropdown-item rounded-1">Last 7 Days</a>
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
                                <th>Invoice No</th>
                                <th>Customer</th>
                                <th>Due Date</th>
                                <th>Amount</th>
                                <th>Paid</th>
                                <th>Amount Due</th>
                                <th>Status</th>
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
                                <td><a href="invoice-details">INV001</a></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <a href="javascript:void(0);" class="avatar avatar-md me-2">
                                            <img src="{{URL::asset('build/img/users/user-27.jpg')}}" alt="product">
                                        </a>
                                        <a href="javascript:void(0);">Carl Evans</a>
                                    </div>
                                </td>
                                <td>
                                    24 Dec 2024												
                                </td>
                                <td>
                                    $1000
                                </td>
                                <td>$1000</td>
                                <td>$0.00</td>
                                <td><span class="badge badge-soft-success badge-xs shadow-none"><i class="ti ti-point-filled me-1"></i>Paid</span></td>
                                <td class="d-flex">
                                    <div class="edit-delete-action d-flex align-items-center justify-content-center">
                                        <a class="me-2 p-2 d-flex align-items-center justify-content-between border rounded" href="{{url('invoice-details')}}">
                                            <i data-feather="eye" class="feather-eye"></i>
                                        </a>
                                        <a class="p-2 d-flex align-items-center justify-content-between border rounded" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#delete">
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
                                <td><a href="invoice-details">INV002</a></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <a href="javascript:void(0);" class="avatar avatar-md me-2">
                                            <img src="{{URL::asset('build/img/users/user-02.jpg')}}" alt="product">
                                        </a>
                                        <a href="javascript:void(0);">Minerva Rameriz</a>
                                    </div>
                                </td>
                                <td>
                                    24 Dec 2024												
                                </td>
                                <td>$1500</td>
                                <td>$0.00</td>
                                <td>$1500</td>
                                <td><span class="badge badge-soft-danger badge-xs shadow-none"><i class="ti ti-point-filled me-1"></i>Unpaid</span></td>
                                <td class="d-flex">
                                    <div class="edit-delete-action d-flex align-items-center justify-content-center">
                                        <a class="me-2 p-2 d-flex align-items-center justify-content-between border rounded" href="{{url('invoice-details')}}">
                                            <i data-feather="eye" class="feather-eye"></i>
                                        </a>
                                        <a class="p-2 d-flex align-items-center justify-content-between border rounded" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#delete">
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
                                <td><a href="invoice-details">INV003</a></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <a href="javascript:void(0);" class="avatar avatar-md me-2">
                                            <img src="{{URL::asset('build/img/users/user-05.jpg')}}" alt="product">
                                        </a>
                                        <a href="javascript:void(0);">Robert Lamon</a>
                                    </div>
                                </td>
                                <td>
                                    24 Dec 2024												
                                </td>
                                <td>$1500</td>
                                <td>$0.00</td>
                                <td>$1500</td>
                                <td><span class="badge badge-soft-danger badge-xs shadow-none"><i class="ti ti-point-filled me-1"></i>Unpaid</span></td>
                                <td class="d-flex">
                                    <div class="edit-delete-action d-flex align-items-center justify-content-center">
                                        <a class="me-2 p-2 d-flex align-items-center justify-content-between border rounded" href="{{url('invoice-details')}}">
                                            <i data-feather="eye" class="feather-eye"></i>
                                        </a>
                                        <a class="p-2 d-flex align-items-center justify-content-between border rounded" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#delete">
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
                                <td><a href="invoice-details">INV004</a></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <a href="javascript:void(0);" class="avatar avatar-md me-2">
                                            <img src="{{URL::asset('build/img/users/user-22.jpg')}}" alt="product">
                                        </a>
                                        <a href="javascript:void(0);">Patricia Lewis</a>
                                    </div>
                                </td>
                                <td>
                                    24 Dec 2024												
                                </td>
                                <td>$2000</td>
                                <td>$1000</td>
                                <td>$1000</td>
                                <td><span class="badge badge-soft-warning badge-xs shadow-none"><i class="ti ti-point-filled me-1"></i>Overdue</span></td>
                                <td class="d-flex">
                                    <div class="edit-delete-action d-flex align-items-center justify-content-center">
                                        <a class="me-2 p-2 d-flex align-items-center justify-content-between border rounded" href="{{url('invoice-details')}}">
                                            <i data-feather="eye" class="feather-eye"></i>
                                        </a>
                                        <a class="p-2 d-flex align-items-center justify-content-between border rounded" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#delete">
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
                                <td><a href="invoice-details">INV005</a></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <a href="javascript:void(0);" class="avatar avatar-md me-2">
                                            <img src="{{URL::asset('build/img/users/user-03.jpg')}}" alt="product">
                                        </a>
                                        <a href="javascript:void(0);">Mark Joslyn</a>
                                    </div>
                                </td>
                                <td>
                                    24 Dec 2024												
                                </td>
                                <td>$800</td>
                                <td>$800</td>
                                <td>$0.00</td>
                                <td><span class="badge badge-soft-success badge-xs shadow-none"><i class="ti ti-point-filled me-1"></i>Paid</span></td>
                                <td class="d-flex">
                                    <div class="edit-delete-action d-flex align-items-center justify-content-center">
                                        <a class="me-2 p-2 d-flex align-items-center justify-content-between border rounded" href="{{url('invoice-details')}}">
                                            <i data-feather="eye" class="feather-eye"></i>
                                        </a>
                                        <a class="p-2 d-flex align-items-center justify-content-between border rounded" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#delete">
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
                                <td><a href="invoice-details">INV006</a></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <a href="javascript:void(0);" class="avatar avatar-md me-2">
                                            <img src="{{URL::asset('build/img/users/user-12.jpg')}}" alt="product">
                                        </a>
                                        <a href="javascript:void(0);">Marsha Betts</a>
                                    </div>
                                </td>
                                <td>
                                    24 Dec 2024												
                                </td>
                                <td>$750</td>
                                <td>$0.00</td>
                                <td>$750</td>
                                <td><span class="badge badge-soft-danger badge-xs shadow-none"><i class="ti ti-point-filled me-1"></i>Unpaid</span></td>
                                <td class="d-flex">
                                    <div class="edit-delete-action d-flex align-items-center justify-content-center">
                                        <a class="me-2 p-2 d-flex align-items-center justify-content-between border rounded" href="{{url('invoice-details')}}">
                                            <i data-feather="eye" class="feather-eye"></i>
                                        </a>
                                        <a class="p-2 d-flex align-items-center justify-content-between border rounded" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#delete">
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
                                <td><a href="invoice-details">INV007</a></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <a href="javascript:void(0);" class="avatar avatar-md me-2">
                                            <img src="{{URL::asset('build/img/users/user-06.jpg')}}" alt="product">
                                        </a>
                                        <a href="javascript:void(0);">Daniel Jude</a>
                                    </div>
                                </td>
                                <td>
                                    24 Dec 2024												
                                </td>
                                <td>$1300</td>
                                <td>$1300</td>
                                <td>$0.00</td>
                                <td><span class="badge badge-soft-success badge-xs shadow-none"><i class="ti ti-point-filled me-1"></i>Paid</span></td>
                                <td class="d-flex">
                                    <div class="edit-delete-action d-flex align-items-center justify-content-center">
                                        <a class="me-2 p-2 d-flex align-items-center justify-content-between border rounded" href="{{url('invoice-details')}}">
                                            <i data-feather="eye" class="feather-eye"></i>
                                        </a>
                                        <a class="p-2 d-flex align-items-center justify-content-between border rounded" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#delete">
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
                                <td><a href="invoice-details">INV008</a></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <a href="javascript:void(0);" class="avatar avatar-md me-2">
                                            <img src="{{URL::asset('build/img/users/user-21.jpg')}}" alt="product">
                                        </a>
                                        <a href="javascript:void(0);">Emma Bates</a>
                                    </div>
                                </td>
                                <td>
                                    24 Dec 2024												
                                </td>
                                <td>$1100</td>
                                <td>$1100</td>
                                <td>$0.00</td>
                                <td><span class="badge badge-soft-success badge-xs shadow-none"><i class="ti ti-point-filled me-1"></i>Paid</span></td>
                                <td class="d-flex">
                                    <div class="edit-delete-action d-flex align-items-center justify-content-center">
                                        <a class="me-2 p-2 d-flex align-items-center justify-content-between border rounded" href="{{url('invoice-details')}}">
                                            <i data-feather="eye" class="feather-eye"></i>
                                        </a>
                                        <a class="p-2 d-flex align-items-center justify-content-between border rounded" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#delete">
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
                                <td><a href="invoice-details">INV009</a></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <a href="javascript:void(0);" class="avatar avatar-md me-2">
                                            <img src="{{URL::asset('build/img/users/user-16.jpg')}}" alt="product">
                                        </a>
                                        <a href="javascript:void(0);">Richard Fralick</a>
                                    </div>
                                </td>
                                <td>
                                    24 Dec 2024												
                                </td>
                                <td>$2300</td>
                                <td>$2300</td>
                                <td>$0.00</td>
                                <td><span class="badge badge-soft-success badge-xs shadow-none"><i class="ti ti-point-filled me-1"></i>Paid</span></td>
                                <td class="d-flex">
                                    <div class="edit-delete-action d-flex align-items-center justify-content-center">
                                        <a class="me-2 p-2 d-flex align-items-center justify-content-between border rounded" href="{{url('invoice-details')}}">
                                            <i data-feather="eye" class="feather-eye"></i>
                                        </a>
                                        <a class="p-2 d-flex align-items-center justify-content-between border rounded" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#delete">
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
                                <td><a href="invoice-details">INV010</a></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <a href="javascript:void(0);" class="avatar avatar-md me-2">
                                            <img src="{{URL::asset('build/img/users/user-26.jpg')}}" alt="product">
                                        </a>
                                        <a href="javascript:void(0);">Michelle Robison</a>
                                    </div>
                                </td>
                                <td>
                                    24 Dec 2024												
                                </td>
                                <td>$1700</td>
                                <td>$1700</td>
                                <td>$0.00</td>
                                <td><span class="badge badge-soft-success badge-xs shadow-none"><i class="ti ti-point-filled me-1"></i>Paid</span></td>
                                <td class="d-flex">
                                    <div class="edit-delete-action d-flex align-items-center justify-content-center">
                                        <a class="me-2 p-2 d-flex align-items-center justify-content-between border rounded" href="{{url('invoice-details')}}">
                                            <i data-feather="eye" class="feather-eye"></i>
                                        </a>
                                        <a class="p-2 d-flex align-items-center justify-content-between border rounded" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#delete">
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
                                <td><a href="invoice-details">INV011</a></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <a href="javascript:void(0);" class="avatar avatar-md me-2">
                                            <img src="{{URL::asset('build/img/users/user-05.jpg')}}" alt="product">
                                        </a>
                                        <a href="javascript:void(0);">Robert Lamon</a>
                                    </div>
                                </td>
                                <td>
                                    24 Dec 2024												
                                </td>
                                <td>$1500</td>
                                <td>$0.00</td>
                                <td>$1500</td>
                                <td><span class="badge badge-soft-danger badge-xs shadow-none"><i class="ti ti-point-filled me-1"></i>Unpaid</span></td>
                                <td class="d-flex">
                                    <div class="edit-delete-action d-flex align-items-center justify-content-center">
                                        <a class="me-2 p-2 d-flex align-items-center justify-content-between border rounded" href="{{url('invoice-details')}}">
                                            <i data-feather="eye" class="feather-eye"></i>
                                        </a>
                                        <a class="p-2 d-flex align-items-center justify-content-between border rounded" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#delete">
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
    </div>
    <div class="footer d-sm-flex align-items-center justify-content-between border-top bg-white p-3">
        <p class="mb-0">2014 - 2025 &copy; DreamsPOS. All Right Reserved</p>
        <p>Designed &amp; Developed by <a href="javascript:void(0);" class="text-primary">Dreams</a></p>
    </div>
</div>

@endsection