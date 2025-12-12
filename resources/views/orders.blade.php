<?php $page = 'orders'; ?>
@extends('layout.mainlayout')
@section('content')
    <div class="page-wrapper">
        <div class="content">
            <div class="page-header">
                <div class="add-item d-flex">
                    <div class="page-title">
                        <h4 class="fw-bold">Order List</h4>
                        <h6>Manage your orders</h6>
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
                                    <a href="javascript:void(0);" class="dropdown-item rounded-1">Beats Pro </a>
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
                                Created By
                            </a>
                            <ul class="dropdown-menu  dropdown-menu-end p-3">
                                <li>
                                    <a href="javascript:void(0);" class="dropdown-item rounded-1">James Kirwin</a>
                                </li>
                                <li>
                                    <a href="javascript:void(0);" class="dropdown-item rounded-1">Francis Chang</a>
                                </li>
                                <li>
                                    <a href="javascript:void(0);" class="dropdown-item rounded-1">Antonio Engle</a>
                                </li>
                                <li>
                                    <a href="javascript:void(0);" class="dropdown-item rounded-1">Leo Kelly</a>
                                </li>
                            </ul>
                        </div>
                        <div class="dropdown me-2">
                            <a href="javascript:void(0);" class="dropdown-toggle btn btn-white btn-md d-inline-flex align-items-center" data-bs-toggle="dropdown">
                                Category
                            </a>
                            <ul class="dropdown-menu  dropdown-menu-end p-3">
                                <li>
                                    <a href="javascript:void(0);" class="dropdown-item rounded-1">Computers</a>
                                </li>
                                <li>
                                    <a href="javascript:void(0);" class="dropdown-item rounded-1">Electronics</a>
                                </li>
                                <li>
                                    <a href="javascript:void(0);" class="dropdown-item rounded-1">Shoe</a>
                                </li>
                                <li>
                                    <a href="javascript:void(0);" class="dropdown-item rounded-1">Electronics</a>
                                </li>
                            </ul>
                        </div>
                        <div class="dropdown me-2">
                            <a href="javascript:void(0);" class="dropdown-toggle btn btn-white btn-md d-inline-flex align-items-center" data-bs-toggle="dropdown">
                                Brand
                            </a>
                            <ul class="dropdown-menu  dropdown-menu-end p-3">
                                <li>
                                    <a href="javascript:void(0);" class="dropdown-item rounded-1">Lenovo</a>
                                </li>
                                <li>
                                    <a href="javascript:void(0);" class="dropdown-item rounded-1">Beats</a>
                                </li>
                                <li>
                                    <a href="javascript:void(0);" class="dropdown-item rounded-1">Nike</a>
                                </li>
                                <li>
                                    <a href="javascript:void(0);" class="dropdown-item rounded-1">Apple</a>
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
                                    <th>Order ID </th>
                                    <th>Customer</th>
                                    <th>Payment Type</th>
                                    <th>Amount</th>
                                    <th>Date & Time</th>
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
                                    <td>5655898 </td>
                                    <td>
                                        <div class="userimgname">
                                            <span class="avatar avatar-md me-2">
                                                <a href="javascript:void(0);" >
                                                    <img src="{{URL::asset('build/img/users/user-30.jpg')}}" alt="product">
                                                </a>
                                            </span>
                                            <a href="javascript:void(0);">James Kirwin</a>
                                        </div>
                                    </td>
                                    <td>PayPal</td>
                                    <td>$600</td>
                                    <td>24 Dec 2024, 04:12 PM</td>
                                    <td><span class="bg-success fs-10 text-white p-1 rounded"><i class="ti ti-point-filled me-1"></i>Complete</span></td>
                                    <td class="d-flex">
                                        <div class="edit-delete-action d-flex align-items-center">
                                            <a class="me-2 edit-icon  d-flex align-items-center border rounded p-2" href="{{url('invoice-details')}}">
                                                <i data-feather="eye" class="feather-eye"></i>
                                            </a>
                                            <a class="me-2 p-2 d-flex align-items-center border rounded" href="{{url('edit-product')}}" >
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
                                    <td>5573158</td>
                                    <td>
                                        <div class="userimgname">
                                            <span class="avatar avatar-md me-2">
                                            <a href="javascript:void(0);" >
                                                <img src="{{URL::asset('build/img/users/user-13.jpg')}}" alt="product">
                                            </a>
                                            </span>
                                                <a href="javascript:void(0);">Francis Chang</a>
                                        </div>
                                    </td>
                                    
                                    <td>PayPal</td>
                                    <td>$160</td>
                                    <td>10 Dec 2024, 10:30 AM</td>
                                    <td><span class="bg-cyan fs-10 text-white p-1 rounded"><i class="ti ti-point-filled me-1"></i>Pending</span></td>
                                    <td class="d-flex">
                                        <div class="edit-delete-action d-flex align-items-center">
                                            <a class="me-2 edit-icon p-2 border d-flex align-items-center rounded" href="{{url('invoice-details')}}">
                                                <i data-feather="eye" class="action-eye"></i>
                                            </a>
                                            <a class="me-2 p-2 d-flex align-items-center border rounded" href="{{url('edit-product')}}">
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
                                    <td>4837512</td>
                                    <td>
                                        <div class="userimgname">
                                            <span class="avatar avatar-md me-2">
                                            <a href="javascript:void(0);" >
                                                <img src="{{URL::asset('build/img/users/user-11.jpg')}}" alt="product">
                                            </a>
                                        </span>
                                                <a href="javascript:void(0);">Antonio Engle</a>
                                        </div>
                                    </td>
                                    
                                    <td>Debit Card</td>
                                    <td>$110</td>
                                    <td>27 Nov 2024, 03:15 PM</td>
                                    <td><span class="bg-success fs-10 text-white p-1 rounded"><i class="ti ti-point-filled me-1"></i>Complete</span></td>
                                    <td class="d-flex">
                                        <div class="edit-delete-action d-flex align-items-center">
                                            <a class="me-2 edit-icon p-2 border d-flex align-items-center rounded" href="{{url('invoice-details')}}">
                                                <i data-feather="eye" class="action-eye"></i>
                                            </a>
                                            <a class="me-2 p-2 d-flex align-items-center border rounded" href="{{url('edit-product')}}">
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
                                    <td>4628754</td>
                                    <td>
                                        <div class="userimgname">
                                            <span class="avatar avatar-md me-2">
                                            <a href="javascript:void(0);" >
                                                <img src="{{URL::asset('build/img/users/user-32.jpg')}}" alt="product">
                                            </a>
                                        </span>
                                                <a href="javascript:void(0);">Leo Kelly</a>
                                        </div>
                                    </td>
                                    
                                    <td>PayPal</td>
                                    <td>$120</td>
                                    <td>18 Nov 2024, 09:00 AM</td>
                                    <td><span class="bg-success fs-10 text-white p-1 rounded"><i class="ti ti-point-filled me-1"></i>Complete</span></td>
                                    <td class="d-flex">
                                        <div class="edit-delete-action d-flex align-items-center">
                                            <a class="me-2 edit-icon p-2 border d-flex align-items-center rounded" href="{{url('invoice-details')}}">
                                                <i data-feather="eye" class="action-eye"></i>
                                            </a>
                                            <a class="me-2 p-2 d-flex align-items-center border rounded" href="{{url('edit-product')}}">
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
                                    <td>4279685</td>
                                    <td>
                                        <div class="userimgname">
                                            <span class="avatar avatar-md me-2">
                                            <a href="javascript:void(0);" >
                                                <img src="{{URL::asset('build/img/users/user-02.jpg')}}" alt="product">
                                            </a>
                                        </span>
                                                <a href="javascript:void(0);">Annette Walker</a>
                                        </div>
                                    </td>
                                    
                                    <td>PayPal</td>
                                    <td>$80</td>
                                    <td>06 Nov 2024, 10:45 AM</td>
                                    <td><span class="bg-purple fs-10 text-white p-1 rounded"><i class="ti ti-point-filled me-1"></i>Proccessing</span></td>
                                    <td class="d-flex">
                                        <div class="edit-delete-action d-flex align-items-center">
                                            <a class="me-2 edit-icon p-2 border d-flex align-items-center rounded" href="{{url('invoice-details')}}">
                                                <i data-feather="eye" class="action-eye"></i>
                                            </a>
                                            <a class="me-2 p-2 d-flex align-items-center border rounded" href="{{url('edit-product')}}">
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
                                    <td>3754250</td>
                                    <td>
                                        <div class="userimgname">
                                            <span class="avatar avatar-md me-2">
                                            <a href="javascript:void(0);" >
                                                <img src="{{URL::asset('build/img/users/user-05.jpg')}}" alt="product">
                                            </a>
                                        </span>
                                                <a href="javascript:void(0);">John Weaver</a>
                                        </div>
                                    </td>
                                    
                                    <td>Debit Card</td>
                                    <td>$320</td>
                                    <td>25 Oct 2024, 06:30 PM</td>
                                    <td><span class="bg-success fs-10 text-white p-1 rounded"><i class="ti ti-point-filled me-1"></i>Complete</span></td>
                                    <td class="d-flex">
                                        <div class="edit-delete-action d-flex align-items-center">
                                            <a class="me-2 edit-icon p-2 border d-flex align-items-center rounded" href="{{url('invoice-details')}}">
                                                <i data-feather="eye" class="action-eye"></i>
                                            </a>
                                            <a class="me-2 p-2 d-flex align-items-center border rounded" href="{{url('edit-product')}}">
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
                                    <td>3459687</td>
                                    <td>
                                        <div class="userimgname">
                                            <span class="avatar avatar-md me-2">
                                            <a href="javascript:void(0);" >
                                                <img src="{{URL::asset('build/img/users/user-08.jpg')}}" alt="product">
                                            </a>
                                            </span>
                                                <a href="javascript:void(0);">Gary Hennessy</a>
                                        </div>
                                    </td>
                                    
                                    <td>PayPal</td>
                                    <td>$60</td>
                                    <td>14 Oct 2024, 02:45 PM</td>
                                    <td><span class="bg-success fs-10 text-white p-1 rounded"><i class="ti ti-point-filled me-1"></i>Complete</span></td>
                                    <td class="d-flex">
                                        <div class="edit-delete-action d-flex align-items-center">
                                            <a class="me-2 edit-icon p-2 border d-flex align-items-center rounded" href="{{url('invoice-details')}}">
                                                <i data-feather="eye" class="action-eye"></i>
                                            </a>
                                            <a class="me-2 p-2 d-flex align-items-center border rounded" href="{{url('edit-product')}}">
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
                                    <td>2186348</td>
                                    <td>
                                        <div class="userimgname">
                                            <span class="avatar avatar-md me-2">
                                            <a href="javascript:void(0);" >
                                                <img src="{{URL::asset('build/img/users/user-04.jpg')}}" alt="product">
                                            </a>
                                        </span>
                                                <a href="javascript:void(0);">Eleanor Panek</a>
                                        </div>
                                    </td>
                                    
                                    <td>Credit Card</td>
                                    <td>$540</td>
                                    <td>14 Oct 2024, 02:45 PM</td>
                                    <td><span class="bg-cyan fs-10 text-white p-1 rounded"><i class="ti ti-point-filled me-1"></i>Pending</span></td>
                                    <td class="d-flex">
                                        <div class="edit-delete-action d-flex align-items-center">
                                            <a class="me-2 edit-icon p-2 border d-flex align-items-center rounded" href="{{url('invoice-details')}}">
                                                <i data-feather="eye" class="action-eye"></i>
                                            </a>
                                            <a class="me-2 p-2 d-flex align-items-center border rounded" href="{{url('edit-product')}}">
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
                                    <td>3754250</td>
                                    <td>
                                        <div class="userimgname">
                                            <span class="avatar avatar-md me-2">
                                            <a href="javascript:void(0);" >
                                                <img src="{{URL::asset('build/img/users/user-09.jpg')}}" alt="product">
                                            </a>
                                        </span>
                                                <a href="javascript:void(0);">William Levy</a>
                                        </div>
                                    </td>
                                    
                                    <td>PayPal</td>
                                    <td>$200</td>
                                    <td>03 Oct 2024, 11:20 AM</td>
                                    <td><span class="bg-success fs-10 text-white p-1 rounded"><i class="ti ti-point-filled me-1"></i>Complete</span></td>
                                    <td class="d-flex">
                                        <div class="edit-delete-action d-flex align-items-center">
                                            <a class="me-2 edit-icon p-2 border d-flex align-items-center rounded" href="{{url('invoice-details')}}">
                                                <i data-feather="eye" class="action-eye"></i>
                                            </a>
                                            <a class="me-2 p-2 d-flex align-items-center border rounded" href="{{url('edit-product')}}">
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
                                    <td>2973542</td>
                                    <td>
                                        <div class="userimgname">
                                            <span class="avatar avatar-md me-2">
                                            <a href="javascript:void(0);" >
                                                <img src="{{URL::asset('build/img/users/user-10.jpg')}}" alt="product">
                                            </a>
                                        </span>
                                                <a href="javascript:void(0);">Charlotte Klotz</a>
                                        </div>
                                    </td>
                                    
                                    <td>Credit Card</td>
                                    <td>$45</td>
                                    <td>20 Sep 2024, 07:10 PM</td>
                                    <td><span class="bg-success fs-10 text-white p-1 rounded"><i class="ti ti-point-filled me-1"></i>Complete</span></td>
                                    <td class="d-flex">
                                        <div class="edit-delete-action d-flex align-items-center">
                                            <a class="me-2 edit-icon p-2 border d-flex align-items-center rounded" href="{{url('invoice-details')}}">
                                                <i data-feather="eye" class="action-eye"></i>
                                            </a>
                                            <a class="me-2 p-2 d-flex align-items-center border rounded" href="{{url('edit-product')}}">
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
                                    <td>3754250</td>
                                    <td>
                                        <div class="userimgname">
                                            <span class="avatar avatar-md me-2">
                                            <a href="javascript:void(0);" >
                                                <img src="{{URL::asset('build/img/users/user-10.jpg')}}" alt="product">
                                            </a>
                                        </span>
                                                <a href="javascript:void(0);">Charlotte Klotz</a>
                                        </div>
                                    </td>
                                    
                                    <td>Debit Card</td>
                                    <td>$45</td>
                                    <td>14 Oct 2024, 02:45 PM</td>
                                    <td>550</td>
                                    <td class="d-flex">
                                        <div class="edit-delete-action d-flex align-items-center">
                                            <a class="me-2 edit-icon p-2 border d-flex align-items-center rounded" href="{{url('invoice-details')}}">
                                                <i data-feather="eye" class="action-eye"></i>
                                            </a>
                                            <a class="me-2 p-2 d-flex align-items-center border rounded" href="{{url('edit-product')}}">
                                                <i data-feather="edit" class="feather-edit"></i>
                                            </a>
                                            <a class="p-2 d-flex align-items-center border rounded" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#delete">
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
            <p class="mb-0 text-gray-9">2014 - 2025 &copy; DreamsPOS. All Right Reserved</p>
            <p>Designed &amp; Developed by <a href="javascript:void(0);" class="text-primary">Dreams</a></p>
        </div>
    </div>
@endsection