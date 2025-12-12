<?php $page = 'stock-adjustment'; ?>
@extends('layout.mainlayout')
@section('content')
<div class="page-wrapper">
    <div class="content">
        <div class="page-header">
            <div class="add-item d-flex">
                <div class="page-title">
                    <h4>Stock Adjustment</h4>
                    <h6>Manage your stock adjustment</h6>
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
                <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#add-stock-adjustment"><i class="ti ti-circle-plus me-1"></i>Add Adjustment</a>
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
                            Warehouse
                        </a>
                        <ul class="dropdown-menu  dropdown-menu-end p-3">
                            <li>
                                <a href="javascript:void(0);" class="dropdown-item rounded-1">Lavish Warehouse</a>
                            </li>
                            <li>
                                <a href="javascript:void(0);" class="dropdown-item rounded-1">Quaint Warehouse </a>
                            </li>
                            <li>
                                <a href="javascript:void(0);" class="dropdown-item rounded-1">Cool Warehouse</a>
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
                                <th>Warehouse</th>
                                <th>Store</th>
                                <th>Product</th>
                                <th>Date</th>
                                <th>Person</th>
                                <th>Qty</th>
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
                                <td>Lavish Warehouse </td>
                                <td>Electro Mart </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <a href="javascript:void(0);" class="avatar avatar-md me-2">
                                            <img src="{{URL::asset('build/img/products/stock-img-01.png')}}" alt="product">
                                        </a>
                                        <a href="javascript:void(0);">Lenovo IdeaPad 3</a>
                                    </div>												
                                </td>
                                <td>24 Dec 2024</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <a href="javascript:void(0);" class="avatar avatar-md me-2">
                                            <img src="{{URL::asset('build/img/users/user-30.jpg')}}" alt="product">
                                        </a>
                                        <a href="javascript:void(0);">James Kirwin</a>
                                    </div>
                                </td>
                                <td>100</td>
                                <td class="d-flex">
                                    <div class="d-flex align-items-center edit-delete-action">
                                        <a class="me-2 border rounded d-flex align-items-center p-2" href="#" data-bs-toggle="modal" data-bs-target="#view-notes">
                                            <i data-feather="file-text" class="feather-file-text"></i>
                                        </a>
                                        <a class="me-2 border rounded d-flex align-items-center p-2" href="#" data-bs-toggle="modal" data-bs-target="#edit-stock-adjustment">
                                            <i data-feather="edit" class="feather-edit"></i>
                                        </a>
                                        <a class="p-2 border rounded d-flex align-items-center" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#delete">
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
                                <td>Quaint Warehouse </td>
                                <td>Quantum Gadgets</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <a href="javascript:void(0);" class="avatar avatar-md me-2">
                                            <img src="{{URL::asset('build/img/products/stock-img-06.png')}}" alt="product">
                                        </a>
                                        <a href="javascript:void(0);">Beats Pro</a>
                                    </div>												
                                </td>
                                <td>10 Dec 2024</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <a href="javascript:void(0);" class="avatar avatar-md me-2">
                                            <img src="{{URL::asset('build/img/users/user-13.jpg')}}" alt="product">
                                        </a>
                                        <a href="javascript:void(0);">Francis Chang</a>
                                    </div>
                                </td>
                                <td>140</td>
                                <td class="d-flex">
                                    <div class="d-flex align-items-center justify-content-between edit-delete-action">
                                        <a class="me-2 border rounded d-flex align-items-center p-2" href="#" data-bs-toggle="modal" data-bs-target="#view-notes">
                                            <i data-feather="file-text" class="feather-file-text"></i>
                                        </a>
                                        <a class="me-2 p-2 border rounded d-flex align-items-center" href="#" data-bs-toggle="modal" data-bs-target="#edit-stock-adjustment">
                                            <i data-feather="edit" class="feather-edit"></i>
                                        </a>
                                        <a class="p-2 border rounded d-flex align-items-center" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#delete">
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
                                <td>Overflow Warehouse</td>
                                <td>Prime Bazaar</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <a href="javascript:void(0);" class="avatar avatar-md me-2">
                                            <img src="{{URL::asset('build/img/products/stock-img-02.png')}}" alt="product">
                                        </a>
                                        <a href="javascript:void(0);">Nike Jordan</a>
                                    </div>												
                                </td>
                                <td>25 Jul 2023</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <a href="javascript:void(0);" class="avatar avatar-md me-2">
                                            <img src="{{URL::asset('build/img/users/user-08.jpg')}}" alt="product">
                                        </a>
                                        <a href="javascript:void(0);">Antonio Engle</a>
                                    </div>
                                </td>
                                <td>120</td>
                                <td class="d-flex">
                                    <div class="d-flex align-items-center justify-content-between edit-delete-action">
                                        <a class="me-2 border rounded d-flex align-items-center p-2" href="#" data-bs-toggle="modal" data-bs-target="#view-notes">
                                            <i data-feather="file-text" class="feather-file-text"></i>
                                        </a>
                                        <a class="me-2 p-2 border rounded d-flex align-items-center" href="#" data-bs-toggle="modal" data-bs-target="#edit-stock-adjustment">
                                            <i data-feather="edit" class="feather-edit"></i>
                                        </a>
                                        <a class="p-2 border rounded d-flex align-items-center" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#delete">
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
                                <td>Quaint Warehouse </td>
                                <td>Gadget World</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <a href="javascript:void(0);" class="avatar avatar-md me-2">
                                            <img src="{{URL::asset('build/img/products/stock-img-03.png')}}" alt="product">
                                        </a>
                                        <a href="javascript:void(0);">Apple Series 5 Watch</a>
                                    </div>												
                                </td>
                                <td>28 Jul 2023</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <a href="javascript:void(0);" class="avatar avatar-md me-2">
                                            <img src="{{URL::asset('build/img/users/user-04.jpg')}}" alt="product">
                                        </a>
                                        <a href="javascript:void(0);">Leo Kelly</a>
                                    </div>
                                </td>
                                <td>130</td>
                                <td class="d-flex">
                                    <div class="d-flex align-items-center justify-content-between edit-delete-action">
                                        <a class="me-2 border rounded d-flex align-items-center p-2" href="#" data-bs-toggle="modal" data-bs-target="#view-notes">
                                            <i data-feather="file-text" class="feather-file-text"></i>
                                        </a>
                                        <a class="me-2 p-2 border rounded d-flex align-items-center" href="#" data-bs-toggle="modal" data-bs-target="#edit-stock-adjustment">
                                            <i data-feather="edit" class="feather-edit"></i>
                                        </a>
                                        <a class="p-2 border rounded d-flex align-items-center" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#delete">
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
                                <td>Traditional Warehouse </td>
                                <td>Volt Vault</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <a href="javascript:void(0);" class="avatar avatar-md me-2">
                                            <img src="{{URL::asset('build/img/products/stock-img-04.png')}}" alt="product">
                                        </a>
                                        <a href="javascript:void(0);">Amazon Echo Dot</a>
                                    </div>												
                                </td>
                                <td>24 Jul 2023</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <a href="javascript:void(0);" class="avatar avatar-md me-2">
                                            <img src="{{URL::asset('build/img/users/user-09.jpg')}}" alt="product">
                                        </a>
                                        <a href="javascript:void(0);">Annette Walker</a>
                                    </div>
                                </td>
                                <td>140</td>
                                <td class="d-flex">
                                    <div class="d-flex align-items-center justify-content-between edit-delete-action">
                                        <a class="me-2 border rounded d-flex align-items-center p-2" href="#" data-bs-toggle="modal" data-bs-target="#view-notes">
                                            <i data-feather="file-text" class="feather-file-text"></i>
                                        </a>
                                        <a class="me-2 p-2 border rounded d-flex align-items-center" href="#" data-bs-toggle="modal" data-bs-target="#edit-stock-adjustment">
                                            <i data-feather="edit" class="feather-edit"></i>
                                        </a>
                                        <a class="p-2 border rounded d-flex align-items-center" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#delete">
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
                                <td>Cool Warehouse </td>
                                <td>Elite Retail</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <a href="javascript:void(0);" class="avatar avatar-md me-2">
                                            <img src="{{URL::asset('build/img/products/stock-img-05.png')}}" alt="product">
                                        </a>
                                        <a href="javascript:void(0);">Lobar Handy</a>
                                    </div>												
                                </td>
                                <td>15 Jul 2023</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <a href="javascript:void(0);" class="avatar avatar-md me-2">
                                            <img src="{{URL::asset('build/img/users/user-10.jpg')}}" alt="product">
                                        </a>
                                        <a href="javascript:void(0);">John Weaver</a>
                                    </div>
                                </td>
                                <td>150</td>
                                <td class="d-flex">
                                    <div class="d-flex align-items-center justify-content-between edit-delete-action">
                                        <a class="me-2 border rounded d-flex align-items-center p-2" href="#" data-bs-toggle="modal" data-bs-target="#view-notes">
                                            <i data-feather="file-text" class="feather-file-text"></i>
                                        </a>
                                        <a class="me-2 p-2 border rounded d-flex align-items-center" href="#" data-bs-toggle="modal" data-bs-target="#edit-stock-adjustment">
                                            <i data-feather="edit" class="feather-edit"></i>
                                        </a>
                                        <a class="p-2 border rounded d-flex align-items-center" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#delete">
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
                                <td>Retail Supply Hub </td>
                                <td>Prime Mart</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <a href="javascript:void(0);" class="avatar avatar-md me-2">
                                            <img src="{{URL::asset('build/img/products/expire-product-01.png')}}" alt="product">
                                        </a>
                                        <a href="javascript:void(0);">Red Premium Satchel</a>
                                    </div>												
                                </td>
                                <td>14 Oct 2024</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <a href="javascript:void(0);" class="avatar avatar-md me-2">
                                            <img src="{{URL::asset('build/img/users/user-08.jpg')}}" alt="product">
                                        </a>
                                        <a href="javascript:void(0);">Gary Hennessy</a>
                                    </div>
                                </td>
                                <td>700</td>
                                <td class="d-flex">
                                    <div class="d-flex align-items-center justify-content-between edit-delete-action">
                                        <a class="me-2 border rounded d-flex align-items-center p-2" href="#" data-bs-toggle="modal" data-bs-target="#view-notes">
                                            <i data-feather="file-text" class="feather-file-text"></i>
                                        </a>
                                        <a class="me-2 p-2 border rounded d-flex align-items-center" href="#" data-bs-toggle="modal" data-bs-target="#edit-stock-adjustment">
                                            <i data-feather="edit" class="feather-edit"></i>
                                        </a>
                                        <a class="p-2 border rounded d-flex align-items-center" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#delete">
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
                                <td>EdgeWare Solutions </td>
                                <td>NeoTech Store</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <a href="javascript:void(0);" class="avatar avatar-md me-2">
                                            <img src="{{URL::asset('build/img/products/expire-product-02.png')}}" alt="product">
                                        </a>
                                        <a href="javascript:void(0);">Iphone 14 Pro</a>
                                    </div>												
                                </td>
                                <td>03 Oct 2024</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <a href="javascript:void(0);" class="avatar avatar-md me-2">
                                            <img src="{{URL::asset('build/img/users/user-04.jpg')}}" alt="product">
                                        </a>
                                        <a href="javascript:void(0);">Eleanor Panek</a>
                                    </div>
                                </td>
                                <td>630</td>
                                <td class="d-flex">
                                    <div class="d-flex align-items-center justify-content-between edit-delete-action">
                                        <a class="me-2 border rounded d-flex align-items-center p-2" href="#" data-bs-toggle="modal" data-bs-target="#view-notes">
                                            <i data-feather="file-text" class="feather-file-text"></i>
                                        </a>
                                        <a class="me-2 p-2 border rounded d-flex align-items-center" href="#" data-bs-toggle="modal" data-bs-target="#edit-stock-adjustment">
                                            <i data-feather="edit" class="feather-edit"></i>
                                        </a>
                                        <a class="p-2 border rounded d-flex align-items-center" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#delete">
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
                                <td>North Zone Warehouse </td>
                                <td>Urban Mart</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <a href="javascript:void(0);" class="avatar avatar-md me-2">
                                            <img src="{{URL::asset('build/img/products/expire-product-03.png')}}" alt="product">
                                        </a>
                                        <a href="javascript:void(0);">Gaming Chair</a>
                                    </div>												
                                </td>
                                <td>20 Sep 2024</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <a href="javascript:void(0);" class="avatar avatar-md me-2">
                                            <img src="{{URL::asset('build/img/users/user-13.jpg')}}" alt="product">
                                        </a>
                                        <a href="javascript:void(0);">William Levy</a>
                                    </div>
                                </td>
                                <td>410</td>
                                <td class="d-flex">
                                    <div class="d-flex align-items-center justify-content-between edit-delete-action">
                                        <a class="me-2 border rounded d-flex align-items-center p-2" href="#" data-bs-toggle="modal" data-bs-target="#view-notes">
                                            <i data-feather="file-text" class="feather-file-text"></i>
                                        </a>
                                        <a class="me-2 p-2 border rounded d-flex align-items-center" href="#" data-bs-toggle="modal" data-bs-target="#edit-stock-adjustment">
                                            <i data-feather="edit" class="feather-edit"></i>
                                        </a>
                                        <a class="p-2 border rounded d-flex align-items-center" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#delete">
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
                                <td>Fulfillment Hub </td>
                                <td>Travel Mart</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <a href="javascript:void(0);" class="avatar avatar-md me-2">
                                            <img src="{{URL::asset('build/img/products/expire-product-04.png')}}" alt="product">
                                        </a>
                                        <a href="javascript:void(0);">Borealis Backpack</a>
                                    </div>												
                                </td>
                                <td>10 Sep 2024</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <a href="javascript:void(0);" class="avatar avatar-md me-2">
                                            <img src="{{URL::asset('build/img/users/user-16.jpg')}}" alt="product">
                                        </a>
                                        <a href="javascript:void(0);">Charlotte Klotz</a>
                                    </div>
                                </td>
                                <td>550</td>
                                <td class="d-flex">
                                    <div class="d-flex align-items-center justify-content-between edit-delete-action">
                                        <a class="me-2 border rounded d-flex align-items-center p-2" href="#" data-bs-toggle="modal" data-bs-target="#view-notes">
                                            <i data-feather="file-text" class="feather-file-text"></i>
                                        </a>
                                        <a class="me-2 p-2 border rounded d-flex align-items-center" href="#" data-bs-toggle="modal" data-bs-target="#edit-stock-adjustment">
                                            <i data-feather="edit" class="feather-edit"></i>
                                        </a>
                                        <a class="p-2 border rounded d-flex align-items-center" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#delete">
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
