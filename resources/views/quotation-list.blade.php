<?php $page = 'quotation-list'; ?>
@extends('layout.mainlayout')
@section('content')

<div class="page-wrapper">
    <div class="content">
        <div class="page-header">
            <div class="add-item d-flex">
                <div class="page-title">
                    <h4>Quotation List</h4>
                    <h6>Manage Your Quotation</h6>
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
                <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#add-quotation"><i class="ti ti-circle-plus me-1"></i>Add Quotation</a>
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
                            Product
                        </a>
                        <ul class="dropdown-menu  dropdown-menu-end p-3">
                            <li>
                                <a href="javascript:void(0);" class="dropdown-item rounded-1">Lenovo IdeaPad 3</a>
                            </li>
                            <li>
                                <a href="javascript:void(0);" class="dropdown-item rounded-1">Beats Pro</a>
                            </li>
                            <li>
                                <a href="javascript:void(0);" class="dropdown-item rounded-1">Nike Jordan</a>
                            </li>
                            <li>
                                <a href="javascript:void(0);" class="dropdown-item rounded-1">Apple Series 5 Watch</a>
                            </li>
                        </ul>
                    </div>
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
                                <a href="javascript:void(0);" class="dropdown-item rounded-1">Sent</a>
                            </li>
                            <li>
                                <a href="javascript:void(0);" class="dropdown-item rounded-1">Pending</a>
                            </li>
                            <li>
                                <a href="javascript:void(0);" class="dropdown-item rounded-1">Ordered</a>
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
                                <th>Product Name</th>
                                <th>Custmer Name</th>
                                <th>Status</th>
                                <th>Total</th>
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
                                    <div class="d-flex align-items-center me-2">
                                        <a href="javascript:void(0);" class="avatar avatar-md me-2">
                                            <img src="{{URL::asset('build/img/products/stock-img-01.png')}}" alt="product">
                                        </a>
                                        <a href="javascript:void(0);">Lenovo 3rd Generation</a>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <a href="javascript:void(0);" class="avatar avatar-md me-2">
                                            <img src="{{URL::asset('build/img/users/user-27.jpg')}}" alt="product">
                                        </a>
                                        <a href="javascript:void(0);">Carl Evans</a>
                                    </div>
                                </td>
                                <td><span class="badge badge-success">Sent</span></td>
                                <td>$550</td>
                                <td>
                                    <div class="edit-delete-action d-flex align-items-center">
                                        <a class="me-2 p-2 mb-0 d-flex align-items-center border p-1 rounded" href="javascript:void(0);">
                                            <i data-feather="eye" class="action-eye"></i>
                                        </a>
                                        <a class="me-2 p-2 mb-0 d-flex align-items-center border p-1 rounded" data-bs-toggle="modal" data-bs-target="#edit-quotation">
                                            <i data-feather="edit" class="feather-edit"></i>
                                        </a>
                                        <a class="me-2 p-2 mb-0 d-flex align-items-center border p-1 rounded" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#delete">
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
                                    <div class="d-flex align-items-center me-2">
                                        <a href="javascript:void(0);" class="avatar avatar-md me-2">
                                            <img src="{{URL::asset('build/img/products/stock-img-06.png')}}" alt="product">
                                        </a>
                                        <a href="javascript:void(0);">Bold V3.2</a>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <a href="javascript:void(0);" class="avatar avatar-md me-2">
                                            <img src="{{URL::asset('build/img/users/user-02.jpg')}}" alt="product">
                                        </a>
                                        <a href="javascript:void(0);">Minerva Rameriz</a>
                                    </div>
                                </td>
                                <td><span class="badge badge-success">Sent</span></td>
                                <td>$430</td>
                                <td>
                                    <div class="edit-delete-action d-flex align-items-center">
                                        <a class="me-2 p-2 mb-0 d-flex align-items-center border p-1 rounded" href="javascript:void(0);">
                                            <i data-feather="eye" class="action-eye"></i>
                                        </a>
                                        <a class="me-2 p-2 mb-0 d-flex align-items-center border p-1 rounded" data-bs-toggle="modal" data-bs-target="#edit-quotation">
                                            <i data-feather="edit" class="feather-edit"></i>
                                        </a>
                                        <a class="me-2 p-2 mb-0 d-flex align-items-center border p-1 rounded" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#delete">
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
                                    <div class="d-flex align-items-center me-2">
                                        <a href="javascript:void(0);" class="avatar avatar-md me-2">
                                            <img src="{{URL::asset('build/img/products/stock-img-02.png')}}" alt="product">
                                        </a>
                                        <a href="javascript:void(0);">Nike Jordan</a>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <a href="javascript:void(0);" class="avatar avatar-md me-2">
                                            <img src="{{URL::asset('build/img/users/user-05.jpg')}}" alt="product">
                                        </a>
                                        <a href="javascript:void(0);">Robert Lamon</a>
                                    </div>
                                </td>
                                <td><span class="badge badge-warning">Ordered</span></td>
                                <td>$260</td>
                                <td>
                                    <div class="edit-delete-action d-flex align-items-center">
                                        <a class="me-2 p-2 mb-0 d-flex align-items-center border p-1 rounded" href="javascript:void(0);">
                                            <i data-feather="eye" class="action-eye"></i>
                                        </a>
                                        <a class="me-2 p-2 mb-0 d-flex align-items-center border p-1 rounded" data-bs-toggle="modal" data-bs-target="#edit-quotation">
                                            <i data-feather="edit" class="feather-edit"></i>
                                        </a>
                                        <a class="me-2 p-2 mb-0 d-flex align-items-center border p-1 rounded" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#delete">
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
                                    <div class="d-flex align-items-center me-2">
                                        <a href="javascript:void(0);" class="avatar avatar-md me-2">
                                            <img src="{{URL::asset('build/img/products/stock-img-03.png')}}" alt="product">
                                        </a>
                                        <a href="javascript:void(0);">Apple Series 5 Watch</a>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <a href="javascript:void(0);" class="avatar avatar-md me-2">
                                            <img src="{{URL::asset('build/img/users/user-03.jpg')}}" alt="product">
                                        </a>
                                        <a href="javascript:void(0);">Mark Joslyn</a>
                                    </div>
                                </td>
                                <td><span class="badge badge-success">sent</span></td>
                                <td>$470</td>
                                <td>
                                    <div class="edit-delete-action d-flex align-items-center">
                                        <a class="me-2 p-2 mb-0 d-flex align-items-center border p-1 rounded" href="javascript:void(0);">
                                            <i data-feather="eye" class="action-eye"></i>
                                        </a>
                                        <a class="me-2 p-2 mb-0 d-flex align-items-center border p-1 rounded" data-bs-toggle="modal" data-bs-target="#edit-quotation">
                                            <i data-feather="edit" class="feather-edit"></i>
                                        </a>
                                        <a class="me-2 p-2 mb-0 d-flex align-items-center border p-1 rounded" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#delete">
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
                                    <div class="d-flex align-items-center me-2">
                                        <a href="javascript:void(0);" class="avatar avatar-md me-2">
                                            <img src="{{URL::asset('build/img/products/stock-img-04.png')}}" alt="product">
                                        </a>
                                        <a href="javascript:void(0);">Amazon Echo Dot</a>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <a href="javascript:void(0);" class="avatar avatar-md me-2">
                                            <img src="{{URL::asset('build/img/users/user-22.jpg')}}" alt="product">
                                        </a>
                                        <a href="javascript:void(0);">Patricia Lewis</a>
                                    </div>
                                </td>
                                <td><span class="badge badge-cyan">Pending</span></td>
                                <td>$380</td>
                                <td>
                                    <div class="edit-delete-action d-flex align-items-center">
                                        <a class="me-2 p-2 mb-0 d-flex align-items-center border p-1 rounded" href="javascript:void(0);">
                                            <i data-feather="eye" class="action-eye"></i>
                                        </a>
                                        <a class="me-2 p-2 mb-0 d-flex align-items-center border p-1 rounded" data-bs-toggle="modal" data-bs-target="#edit-quotation">
                                            <i data-feather="edit" class="feather-edit"></i>
                                        </a>
                                        <a class="me-2 p-2 mb-0 d-flex align-items-center border p-1 rounded" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#delete">
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
                                    <div class="d-flex align-items-center me-2">
                                        <a href="javascript:void(0);" class="avatar avatar-md me-2">
                                            <img src="{{URL::asset('build/img/products/stock-img-05.png')}}" alt="product">
                                        </a>
                                        <a href="javascript:void(0);">Lobar Handy</a>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <a href="javascript:void(0);" class="avatar avatar-md me-2">
                                            <img src="{{URL::asset('build/img/users/user-12.jpg')}}" alt="product">
                                        </a>
                                        <a href="javascript:void(0);">Marsha Betts</a>
                                    </div>
                                </td>
                                <td><span class="badge badge-success">Sent</span></td>
                                <td>$190</td>
                                <td>
                                    <div class="edit-delete-action d-flex align-items-center">
                                        <a class="me-2 p-2 mb-0 d-flex align-items-center border p-1 rounded" href="javascript:void(0);">
                                            <i data-feather="eye" class="action-eye"></i>
                                        </a>
                                        <a class="me-2 p-2 mb-0 d-flex align-items-center border p-1 rounded" data-bs-toggle="modal" data-bs-target="#edit-quotation">
                                            <i data-feather="edit" class="feather-edit"></i>
                                        </a>
                                        <a class="me-2 p-2 mb-0 d-flex align-items-center border p-1 rounded" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#delete">
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
                                    <div class="d-flex align-items-center me-2">
                                        <a href="javascript:void(0);" class="avatar avatar-md me-2">
                                            <img src="{{URL::asset('build/img/products/expire-product-01.png')}}" alt="product">
                                        </a>
                                        <a href="javascript:void(0);">Red Premium Handy</a>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <a href="javascript:void(0);" class="avatar avatar-md me-2">
                                            <img src="{{URL::asset('build/img/users/user-06.jpg')}}" alt="product">
                                        </a>
                                        <a href="javascript:void(0);">Daniel Jude</a>
                                    </div>
                                </td>
                                <td><span class="badge badge-cyan">Pending</span></td>
                                <td>$540</td>
                                <td>
                                    <div class="edit-delete-action d-flex align-items-center">
                                        <a class="me-2 p-2 mb-0 d-flex align-items-center border p-1 rounded" href="javascript:void(0);">
                                            <i data-feather="eye" class="action-eye"></i>
                                        </a>
                                        <a class="me-2 p-2 mb-0 d-flex align-items-center border p-1 rounded" data-bs-toggle="modal" data-bs-target="#edit-quotation">
                                            <i data-feather="edit" class="feather-edit"></i>
                                        </a>
                                        <a class="me-2 p-2 mb-0 d-flex align-items-center border p-1 rounded" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#delete">
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
                                    <div class="d-flex align-items-center me-2">
                                        <a href="javascript:void(0);" class="avatar avatar-md me-2">
                                            <img src="{{URL::asset('build/img/products/expire-product-02.png')}}" alt="product">
                                        </a>
                                        <a href="javascript:void(0);">Iphone 14 Pro</a>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <a href="javascript:void(0);" class="avatar avatar-md me-2">
                                            <img src="{{URL::asset('build/img/users/user-21.jpg')}}" alt="product">
                                        </a>
                                        <a href="javascript:void(0);">Emma Bates</a>
                                    </div>
                                </td>
                                <td><span class="badge badge-warning">Ordered</span></td>
                                <td>$610</td>
                                <td>
                                    <div class="edit-delete-action d-flex align-items-center">
                                        <a class="me-2 p-2 mb-0 d-flex align-items-center border p-1 rounded" href="javascript:void(0);">
                                            <i data-feather="eye" class="action-eye"></i>
                                        </a>
                                        <a class="me-2 p-2 mb-0 d-flex align-items-center border p-1 rounded" data-bs-toggle="modal" data-bs-target="#edit-quotation">
                                            <i data-feather="edit" class="feather-edit"></i>
                                        </a>
                                        <a class="me-2 p-2 mb-0 d-flex align-items-center border p-1 rounded" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#delete">
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
                                    <div class="d-flex align-items-center me-2">
                                        <a href="javascript:void(0);" class="avatar avatar-md me-2">
                                            <img src="{{URL::asset('build/img/products/expire-product-03.png')}}" alt="product">
                                        </a>
                                        <a href="javascript:void(0);">Black Slim 200</a>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <a href="javascript:void(0);" class="avatar avatar-md me-2">
                                            <img src="{{URL::asset('build/img/users/user-16.jpg')}}" alt="product">
                                        </a>
                                        <a href="javascript:void(0);">Richard Fralick</a>
                                    </div>
                                </td>
                                <td><span class="badge badge-cyan">Pending</span></td>
                                <td>$220</td>
                                <td>
                                    <div class="edit-delete-action d-flex align-items-center">
                                        <a class="me-2 p-2 mb-0 d-flex align-items-center border p-1 rounded" href="javascript:void(0);">
                                            <i data-feather="eye" class="action-eye"></i>
                                        </a>
                                        <a class="me- p-2 mb-0" data-bs-toggle="modal" data-bs-target="#edit-quotation">
                                            <i data-feather="edit" class="feather-edit"></i>
                                        </a>
                                        <a class="me-2 p-2 mb-0 d-flex align-items-center border p-1 rounded" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#delete">
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
                                    <div class="d-flex align-items-center me-2">
                                        <a href="javascript:void(0);" class="avatar avatar-md me-2">
                                            <img src="{{URL::asset('build/img/products/expire-product-04.png')}}" alt="product">
                                        </a>
                                        <a href="javascript:void(0);">Woodcraft Sandal</a>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <a href="javascript:void(0);" class="avatar avatar-md me-2">
                                            <img src="{{URL::asset('build/img/users/user-26.jpg')}}" alt="product">
                                        </a>
                                        <a href="javascript:void(0);">Michelle Robison</a>
                                    </div>
                                </td>
                                <td><span class="badge badge-success">Sent</span></td>
                                <td>$460</td>
                                <td>
                                    <div class="edit-delete-action d-flex align-items-center">
                                        <a class="me-2 p-2 mb-0 d-flex align-items-center border p-1 rounded" href="javascript:void(0);">
                                            <i data-feather="eye" class="action-eye"></i>
                                        </a>
                                        <a class="me-2 p-2 mb-0 d-flex align-items-center border p-1 rounded" data-bs-toggle="modal" data-bs-target="#edit-quotation">
                                            <i data-feather="edit" class="feather-edit"></i>
                                        </a>
                                        <a class="me-2 p-2 mb-0 d-flex align-items-center border p-1 rounded" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#delete">
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
                                    <div class="d-flex align-items-center me-2">
                                        <a href="javascript:void(0);" class="avatar avatar-md me-2">
                                            <img src="{{URL::asset('build/img/products/expire-product-01.png')}}" alt="product">
                                        </a>
                                        <a href="javascript:void(0);">Lobar Handy</a>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <a href="javascript:void(0);" class="avatar avatar-md me-2">
                                            <img src="{{URL::asset('build/img/users/user-03.jpg')}}" alt="product">
                                        </a>
                                        <a href="javascript:void(0);">Mark Joslyn</a>
                                    </div>
                                </td>
                                <td><span class="badge badge-cyan">Pending</span></td>
                                <td>$250</td>
                                <td>
                                    <div class="edit-delete-action d-flex align-items-center">
                                        <a class="me-2 p-2 mb-0 d-flex align-items-center border p-1 rounded" href="javascript:void(0);">
                                            <i data-feather="eye" class="action-eye"></i>
                                        </a>
                                        <a class="me-2 p-2 mb-0 d-flex align-items-center border p-1 rounded" data-bs-toggle="modal" data-bs-target="#edit-quotation">
                                            <i data-feather="edit" class="feather-edit"></i>
                                        </a>
                                        <a class="me-2 p-2 mb-0 d-flex align-items-center border p-1 rounded" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#delete">
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
                                    <div class="d-flex align-items-center me-2">
                                        <a href="javascript:void(0);" class="avatar avatar-md me-2">
                                            <img src="{{URL::asset('build/img/products/expire-product-02.png')}}" alt="product">
                                        </a>
                                        <a href="javascript:void(0);">Iphone 15 Pro</a>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <a href="javascript:void(0);" class="avatar avatar-md me-2">
                                            <img src="{{URL::asset('build/img/users/user-05.jpg')}}" alt="product">
                                        </a>
                                        <a href="javascript:void(0);">Robert Lamon</a>
                                    </div>
                                </td>
                                <td><span class="badge badge-success">Sent</span></td>
                                <td>$550</td>
                                <td>
                                    <div class="edit-delete-action d-flex align-items-center">
                                        <a class="me-2 p-2 mb-0 d-flex align-items-center border p-1 rounded" href="javascript:void(0);">
                                            <i data-feather="eye" class="action-eye"></i>
                                        </a>
                                        <a class="me-2 p-2 mb-0 d-flex align-items-center border p-1 rounded" data-bs-toggle="modal" data-bs-target="#edit-quotation">
                                            <i data-feather="edit" class="feather-edit"></i>
                                        </a>
                                        <a class="me-2 p-2 mb-0 d-flex align-items-center border p-1 rounded" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#delete">
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
