<?php $page = 'sales-returns'; ?>
@extends('layout.mainlayout')
@section('content')
<div class="page-wrapper">
    <div class="content">
        <div class="page-header">
            <div class="add-item d-flex">
                <div class="page-title">
                    <h4>Sales Return</h4>
                    <h6>Manage your returns</h6>
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
            <div class="page-btn">
                <a href="javascript:void(0);" class="btn btn-primary"  data-bs-toggle="modal" data-bs-target="#add-sales-new"><i class="ti ti-circle-plus me-1"></i>Add Sales Return</a>
            </div>
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
                                <a href="javascript:void(0);" class="dropdown-item rounded-1">Completed</a>
                            </li>
                            <li>
                                <a href="javascript:void(0);" class="dropdown-item rounded-1">Pending</a>
                            </li>
                        </ul>
                    </div>
                    <div class="dropdown me-2">
                        <a href="javascript:void(0);" class="dropdown-toggle btn btn-white btn-md d-inline-flex align-items-center" data-bs-toggle="dropdown">
                            Payment Status
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
                                <th>Product</th>
                                <th>Date</th>
                                <th>Customer</th>
                                <th>Status</th>
                                <th>Total</th>
                                <th>Paid</th>
                                <th>Due</th>
                                <th>Payment Status</th>
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
                                <td>
                                    <div class="d-flex align-items-center">
                                        <a href="javascript:void(0);" class="avatar avatar-md me-2">
                                            <img src="{{URL::asset('build/img/products/pos-product-07.svg')}}" alt="product">
                                        </a>
                                        <a href="javascript:void(0);">Lenovo IdeaPad 3</a>
                                    </div>
                                </td>
                                <td>19 Nov 2022</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <a href="javascript:void(0);" class="avatar avatar-md me-2">
                                            <img src="{{URL::asset('build/img/users/user-27.jpg')}}" alt="product">
                                        </a>
                                        <a href="javascript:void(0);">Carl Evans</a>
                                    </div>
                                </td>
                                <td><span class="badge badge-success shadow-none">Received</span></td>
                                <td>$1000</td>
                                <td>$1000</td>
                                <td>$0.00</td>
                                <td><span class="badge badge-soft-success badge-xs shadow-none"><i class="ti ti-point-filled me2"></i>Paid</span></td>
                                <td class="dflex">
                                    <div class="edit-delete-action d-flex align-items-center">
                                        <a class="me-2 p-2 d-flex align-items-center border rounded" href="#" data-bs-toggle="modal" data-bs-target="#edit-sales-new">
                                            <i data-feather="edit" class="feather-edit"></i>
                                        </a>
                                        <a class="p-2 d-flex align-items-center border rounded" href="javascript:void(0);"  data-bs-toggle="modal" data-bs-target="#delete">
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
                                <td>
                                    <div class="d-flex align-items-center">
                                        <a href="javascript:void(0);" class="avatar avatar-md me-2">
                                            <img src="{{URL::asset('build/img/products/pos-product-10.svg')}}" alt="product">
                                        </a>
                                        <a href="javascript:void(0);">Apple tablet</a>
                                    </div>
                                    
                                </td>
                                <td>19 Nov 2022</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <a href="javascript:void(0);" class="avatar avatar-md me-2">
                                            <img src="{{URL::asset('build/img/users/user-02.jpg')}}" alt="product">
                                        </a>
                                        <a href="javascript:void(0);">Minerva Rameriz</a>
                                    </div>
                                </td>
                                <td><span class="badge badge-cyan shadow-none">Pending</span></td>
                                <td>$1500</td>
                                <td>$0.00</td>
                                <td>$1500</td>
                                <td><span class="badge badge-soft-danger badge-xs shadow-none"><i class="ti ti-point-filled me2"></i>Unpaid</span></td>
                                <td class="dflex">
                                    <div class="edit-delete-action d-flex align-items-center">
                                        <a class="me-2 p-2 d-flex align-items-center border rounded" href="#" data-bs-toggle="modal" data-bs-target="#edit-sales-new">
                                            <i data-feather="edit" class="feather-edit"></i>
                                        </a>
                                        <a class="p-2 d-flex align-items-center border rounded" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#delete">
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
                                <td>
                                    <div class="d-flex align-items-center">
                                        <a href="javascript:void(0);" class="avatar avatar-md me-2">
                                            <img src="{{URL::asset('build/img/products/product-02.jpg')}}" alt="product">
                                        </a>
                                        <a href="javascript:void(0);">Headphone</a>
                                    </div>
                                    
                                </td>
                                <td>19 Nov 2022</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <a href="javascript:void(0);" class="avatar avatar-md me-2">
                                            <img src="{{URL::asset('build/img/users/user-05.jpg')}}" alt="product">
                                        </a>
                                        <a href="javascript:void(0);">Robert Lamon</a>
                                    </div>
                                </td>
                                <td><span class="badge badge-success shadow-none">Received</span></td>
                                <td>$2000</td>
                                <td>$1000</td>
                                <td>$1000</td>
                                <td><span class="badge badge-soft-warning badge-xs shadow-none"><i class="ti ti-point-filled me2"></i>Overdue</span></td>
                                <td class="dflex">
                                    <div class="edit-delete-action d-flex align-items-center">
                                        <a class="me-2 p-2 d-flex align-items-center border rounded" href="#" data-bs-toggle="modal" data-bs-target="#edit-sales-new">
                                            <i data-feather="edit" class="feather-edit"></i>
                                        </a>
                                        <a class="p-2 d-flex align-items-center border rounded" href="javascript:void(0);"  data-bs-toggle="modal" data-bs-target="#delete">
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
                                <td>
                                    <div class="d-flex align-items-center">
                                        <a href="javascript:void(0);" class="avatar avatar-md me-2">
                                            <img src="{{URL::asset('build/img/products/stock-img-02.png')}}" alt="product">
                                        </a>
                                        <a href="javascript:void(0);">Nike Jordan</a>
                                    </div>
                                    
                                </td>
                                <td>19 Nov 2022</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <a href="javascript:void(0);" class="avatar avatar-md me-2">
                                            <img src="{{URL::asset('build/img/users/user-03.jpg')}}" alt="product">
                                        </a>
                                        <a href="javascript:void(0);">Mark Joslyn</a>
                                    </div>
                                </td>
                                <td><span class="badge badge-success shadow-none">Received</span></td>
                                <td>$1500</td>
                                <td>$1500</td>
                                <td>$0.00</td>
                                <td><span class="badge badge-soft-success badge-xs shadow-none"><i class="ti ti-point-filled me2"></i>Paid</span></td>
                                <td class="dflex">
                                    <div class="edit-delete-action d-flex align-items-center">
                                        <a class="me-2 p-2 d-flex align-items-center border rounded" href="#" data-bs-toggle="modal" data-bs-target="#edit-sales-new">
                                            <i data-feather="edit" class="feather-edit"></i>
                                        </a>
                                        <a class="p-2 d-flex align-items-center border rounded" href="javascript:void(0);"  data-bs-toggle="modal" data-bs-target="#delete">
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
                                <td>
                                    <div class="d-flex align-items-center">
                                        <a href="javascript:void(0);" class="avatar avatar-md me-2">
                                            <img src="{{URL::asset('build/img/products/product6.jpg')}}" alt="product">
                                        </a>
                                        <a href="javascript:void(0);">Macbook Pro</a>
                                    </div>
                                </td>
                                <td>19 Nov 2022</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <a href="javascript:void(0);" class="avatar avatar-md me-2">
                                            <img src="{{URL::asset('build/img/users/user-22.jpg')}}" alt="product">
                                        </a>
                                        <a href="javascript:void(0);">Patricia Lewis</a>
                                    </div>
                                </td>
                                <td><span class="badge badge-success shadow-none">Received</span></td>
                                <td>$800</td>
                                <td>$800</td>
                                <td>$0.00</td>
                                <td><span class="badge badge-soft-success badge-xs shadow-none"><i class="ti ti-point-filled me2"></i>Paid</span></td>
                                <td class="dflex">
                                    <div class="edit-delete-action d-flex align-items-center">
                                        <a class="me-2 p-2 d-flex align-items-center border rounded" href="#" data-bs-toggle="modal" data-bs-target="#edit-sales-new">
                                            <i data-feather="edit" class="feather-edit"></i>
                                        </a>
                                        <a class="p-2 d-flex align-items-center border rounded" href="javascript:void(0);"  data-bs-toggle="modal" data-bs-target="#delete">
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
                                <td>
                                    <div class="d-flex align-items-center">
                                        <a href="javascript:void(0);" class="avatar avatar-md me-2">
                                            <img src="{{URL::asset('build/img/products/expire-product-01.png')}}" alt="product">
                                        </a>
                                        <a href="javascript:void(0);">Red Premium Satchel</a>
                                    </div>												
                                </td>
                                <td>19 Nov 2022</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <a href="javascript:void(0);" class="avatar avatar-md me-2">
                                            <img src="{{URL::asset('build/img/users/user-12.jpg')}}" alt="product">
                                        </a>
                                        <a href="javascript:void(0);">Marsha Betts</a>
                                    </div>
                                </td>
                                <td><span class="badge badge-cyan shadow-none">Pending</span></td>
                                <td>$750</td>
                                <td>$0.00</td>
                                <td>$750</td>
                                <td><span class="badge badge-soft-danger badge-xs shadow-none"><i class="ti ti-point-filled me2"></i>Unpaid</span></td>
                                <td class="dflex">
                                    <div class="edit-delete-action d-flex align-items-center">
                                        <a class="me-2 p-2 d-flex align-items-center border rounded" href="#" data-bs-toggle="modal" data-bs-target="#edit-sales-new">
                                            <i data-feather="edit" class="feather-edit"></i>
                                        </a>
                                        <a class="p-2 d-flex align-items-center border rounded" href="javascript:void(0);"  data-bs-toggle="modal" data-bs-target="#delete">
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
                                <td>
                                    <div class="d-flex align-items-center">
                                        <a href="javascript:void(0);" class="avatar avatar-md me-2">
                                            <img src="{{URL::asset('build/img/products/product7.jpg')}}" alt="product">
                                        </a>
                                        <a href="javascript:void(0);">Apple Earpods</a>
                                    </div>													
                                </td>
                                <td>19 Nov 2022</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <a href="javascript:void(0);" class="avatar avatar-md me-2">
                                            <img src="{{URL::asset('build/img/users/user-06.jpg')}}" alt="product">
                                        </a>
                                        <a href="javascript:void(0);">Daniel Jude</a>
                                    </div>
                                </td>
                                <td><span class="badge badge-success shadow-none">Received</span></td>
                                <td>$1300</td>
                                <td>$1300</td>
                                <td>$0.00</td>
                                <td><span class="badge badge-soft-success badge-xs shadow-none"><i class="ti ti-point-filled me2"></i>Paid</span></td>
                                <td class="dflex">
                                    <div class="edit-delete-action d-flex align-items-center">
                                        <a class="me-2 p-2 d-flex align-items-center border rounded" href="#" data-bs-toggle="modal" data-bs-target="#edit-sales-new">
                                            <i data-feather="edit" class="feather-edit"></i>
                                        </a>
                                        <a class="p-2 d-flex align-items-center border rounded" href="javascript:void(0);"  data-bs-toggle="modal" data-bs-target="#delete">
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
                                <td>
                                    <div class="d-flex align-items-center">
                                        <a href="javascript:void(0);" class="avatar avatar-md me-2">
                                            <img src="{{URL::asset('build/img/products/expire-product-02.png')}}" alt="product">
                                        </a>
                                        <a href="javascript:void(0);">Iphone 14 Pro</a>
                                    </div>													
                                </td>
                                <td>19 Nov 2022</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <a href="javascript:void(0);" class="avatar avatar-md me-2">
                                            <img src="{{URL::asset('build/img/users/user-21.jpg')}}" alt="product">
                                        </a>
                                        <a href="javascript:void(0);">Emma Bates</a>
                                    </div>
                                </td>
                                <td><span class="badge badge-success shadow-none">Received</span></td>
                                <td>$1100</td>
                                <td>$1100</td>
                                <td>$0.00</td>
                                <td><span class="badge badge-soft-success badge-xs shadow-none"><i class="ti ti-point-filled me2"></i>Paid</span></td>
                                <td class="dflex">
                                    <div class="edit-delete-action d-flex align-items-center">
                                        <a class="me-2 p-2 d-flex align-items-center border rounded" href="#" data-bs-toggle="modal" data-bs-target="#edit-sales-new">
                                            <i data-feather="edit" class="feather-edit"></i>
                                        </a>
                                        <a class="p-2 d-flex align-items-center border rounded" href="javascript:void(0);"  data-bs-toggle="modal" data-bs-target="#delete">
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
                                <td>
                                    <div class="d-flex align-items-center">
                                        <a href="javascript:void(0);" class="avatar avatar-md me-2">
                                            <img src="{{URL::asset('build/img/products/expire-product-03.png')}}" alt="product">
                                        </a>
                                        <a href="javascript:void(0);">Gaming Chair</a>
                                    </div>													
                                </td>
                                <td>19 Nov 2022</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <a href="javascript:void(0);" class="avatar avatar-md me-2">
                                            <img src="{{URL::asset('build/img/users/user-16.jpg')}}" alt="product">
                                        </a>
                                        <a href="javascript:void(0);">Richard Fralick</a>
                                    </div>
                                </td>
                                <td><span class="badge badge-cyan shadow-none">Pending</span></td>
                                <td>$2300</td>
                                <td>$2300</td>
                                <td>$0.00</td>
                                <td><span class="badge badge-soft-success badge-xs shadow-none"><i class="ti ti-point-filled me2"></i>Paid</span></td>
                                <td class="dflex">
                                    <div class="edit-delete-action d-flex align-items-center">
                                        <a class="me-2 p-2 d-flex align-items-center border rounded" href="#" data-bs-toggle="modal" data-bs-target="#edit-sales-new">
                                            <i data-feather="edit" class="feather-edit"></i>
                                        </a>
                                        <a class="p-2 d-flex align-items-center border rounded" href="javascript:void(0);"  data-bs-toggle="modal" data-bs-target="#delete">
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
                                <td>
                                    <div class="d-flex align-items-center">
                                        <a href="javascript:void(0);" class="avatar avatar-md me-2">
                                            <img src="{{URL::asset('build/img/products/expire-product-04.png')}}" alt="product">
                                        </a>
                                        <a href="javascript:void(0);">Borealis Backpack</a>
                                    </div>													
                                </td>
                                <td>19 Nov 2022</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <a href="javascript:void(0);" class="avatar avatar-md me-2">
                                            <img src="{{URL::asset('build/img/users/user-26.jpg')}}" alt="product">
                                        </a>
                                        <a href="javascript:void(0);">Michelle Robison</a>
                                    </div>
                                </td>
                                <td><span class="badge badge-cyan shadow-none">Pending</span></td>
                                <td>$1700</td>
                                <td>$1700</td>
                                <td>$0.00</td>
                                <td><span class="badge badge-soft-success badge-xs shadow-none"><i class="ti ti-point-filled me2"></i>Paid</span></td>
                                <td class="dflex">
                                    <div class="edit-delete-action d-flex align-items-center">
                                        <a class="me-2 p-2 d-flex align-items-center border rounded" href="#" data-bs-toggle="modal" data-bs-target="#edit-sales-new">
                                            <i data-feather="edit" class="feather-edit"></i>
                                        </a>
                                        <a class="p-2 d-flex align-items-center border rounded" href="javascript:void(0);"  data-bs-toggle="modal" data-bs-target="#delete">
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
