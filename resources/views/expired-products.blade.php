<?php $page = 'expired-products'; ?>
@extends('layout.mainlayout')
@section('content')

<div class="page-wrapper">
    <div class="content">
        <div class="page-header">
            <div class="add-item d-flex">
                <div class="page-title">
                    <h4 class="fw-bold">Expired Products</h4>
                    <h6>Manage your expired products</h6>
                </div>						
            </div>
            <ul class="table-top-head">
                <li>
                    <a data-bs-placement="top" title="Pdf"><img src="{{URL::asset('build/img/icons/pdf.svg')}}" alt="img"></a>
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
                                <th>SKU</th>
                                <th>Product</th>
                                <th>Manufactured Date</th>
                                <th>Expired Date</th>
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
                                <td>PT001</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <a href="javascript:void(0);" class="avatar avatar-md me-2">
                                            <img src="{{URL::asset('build/img/products/stock-img-01.png')}}" alt="product">
                                        </a>
                                        <a href="javascript:void(0);">Lenovo 3rd Generation </a>
                                    </div>												
                                </td>				
                                <td>24 Dec 2024</td>
                                <td>20 Dec 2026</td>
                                <td class="action-table-data">
                                    <div class="edit-delete-action">
                                        <a data-bs-toggle="modal" data-bs-target="#edit-expired-product" class="me-2 p-2" href="javascript:void(0);">
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
                                <td>PT002</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <a href="javascript:void(0);" class="avatar avatar-md me-2">
                                            <img src="{{URL::asset('build/img/products/stock-img-06.png')}}" alt="product">
                                        </a>
                                        <a href="javascript:void(0);">Beats Pro </a>
                                    </div>												
                                </td>
                                <td>10 Dec 2024</td>
                                <td>07 Dec 2026</td>
                                <td class="action-table-data">
                                    <div class="edit-delete-action">
                                        <a data-bs-toggle="modal" data-bs-target="#edit-expired-product" class="me-2 p-2" href="javascript:void(0);">
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
                                <td>PT003</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <a href="javascript:void(0);" class="avatar avatar-md me-2">
                                            <img src="{{URL::asset('build/img/products/stock-img-02.png')}}" alt="product">
                                        </a>
                                        <a  href="javascript:void(0);">Nike Jordan</a>
                                    </div>												
                                </td>
                                <td>27 Nov 2024</td>
                                <td>20 Nov 2026</td>
                                <td class="action-table-data">
                                    <div class="edit-delete-action">
                                        <a data-bs-toggle="modal" data-bs-target="#edit-expired-product" class="me-2 p-2" href="javascript:void(0);">
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
                                <td>PT004</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <a href="javascript:void(0);" class="avatar avatar-md me-2">
                                            <img src="{{URL::asset('build/img/products/stock-img-03.png')}}" alt="product">
                                        </a>
                                        <a href="javascript:void(0);">Apple Series 5 Watch</a>
                                    </div>												
                                </td>
                                <td>18 Nov 2024</td>
                                <td>15 Nov 2026</td>
                                <td class="action-table-data">
                                    <div class="edit-delete-action">
                                        <a data-bs-toggle="modal" data-bs-target="#edit-expired-product" class="me-2 p-2" href="javascript:void(0);">
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
                                <td>PT005</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <a href="javascript:void(0);" class="avatar avatar-md me-2">
                                            <img src="{{URL::asset('build/img/products/stock-img-04.png')}}" alt="product">
                                        </a>
                                        <a href="javascript:void(0);">Amazon Echo Dot</a>
                                    </div>												
                                </td>
                                <td>06 Nov 2024</td>
                                <td>04 Nov 2026</td>
                                <td class="action-table-data">
                                    <div class="edit-delete-action">
                                        <a data-bs-toggle="modal" data-bs-target="#edit-expired-product" class="me-2 p-2" href="javascript:void(0);">
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
                                <td>PT006</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <a href="javascript:void(0);" class="avatar avatar-md me-2">
                                            <img src="{{URL::asset('build/img/products/stock-img-05.png')}}" alt="product">
                                        </a>
                                        <a href="javascript:void(0);">Sanford Chair Sofa</a>
                                    </div>												
                                </td>
                                <td>25 Oct 2024</td>
                                <td>20 Oct 2026</td>
                                <td class="action-table-data">
                                    <div class="edit-delete-action">
                                        <a data-bs-toggle="modal" data-bs-target="#edit-expired-product" class="me-2 p-2" href="javascript:void(0);">
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
                                <td>PT007</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <a href="javascript:void(0);" class="avatar avatar-md me-2">
                                            <img src="{{URL::asset('build/img/products/expire-product-01.png')}}" alt="product">
                                        </a>
                                        <a href="javascript:void(0);">Red Premium Satchel</a>
                                    </div>												
                                </td>											
                                <td>14 Oct 2024</td>
                                <td>10 Oct 2026</td>
                                <td class="action-table-data">
                                    <div class="edit-delete-action">
                                        <a data-bs-toggle="modal" data-bs-target="#edit-expired-product" class="me-2 p-2" href="javascript:void(0);">
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
                                <td>PT008</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <a href="javascript:void(0);" class="avatar avatar-md me-2">
                                            <img src="{{URL::asset('build/img/products/expire-product-02.png')}}" alt="product">
                                        </a>
                                        <a href="javascript:void(0);">Iphone 14 Pro</a>
                                    </div>												
                                </td>											
                                <td>03 Oct 2024</td>
                                <td>01 Oct 2026</td>
                                <td class="action-table-data">
                                    <div class="edit-delete-action">
                                        <a data-bs-toggle="modal" data-bs-target="#edit-expired-product" class="me-2 p-2" href="javascript:void(0);">
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
                                <td>PT009</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <a href="javascript:void(0);" class="avatar avatar-md me-2">
                                            <img src="{{URL::asset('build/img/products/expire-product-03.png')}}" alt="product">
                                        </a>
                                        <a href="javascript:void(0);">Gaming Chair</a>
                                    </div>												
                                </td>
                                
                                <td>20 Sep 2024</td>
                                <td>16 Sep 2026</td>
                                <td class="action-table-data">
                                    <div class="edit-delete-action">
                                        <a data-bs-toggle="modal" data-bs-target="#edit-expired-product" class="me-2 p-2" href="javascript:void(0);">
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
                                <td>PT010</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <a href="javascript:void(0);" class="avatar avatar-md me-2">
                                            <img src="{{URL::asset('build/img/products/expire-product-04.png')}}" alt="product">
                                        </a>
                                        <a href="javascript:void(0);">Borealis Backpack</a>
                                    </div>												
                                </td>
                                
                                <td>10 Sep 2024</td>
                                <td>06 Sep 2026</td>
                                <td class="action-table-data">
                                    <div class="edit-delete-action">
                                        <a data-bs-toggle="modal" data-bs-target="#edit-expired-product" class="me-2 p-2" href="javascript:void(0);">
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
    </div>
    <div class="footer d-sm-flex align-items-center justify-content-between border-top bg-white p-3">
        <p class="mb-0 text-gray-9">2014 - 2025 &copy; DreamsPOS. All Right Reserved</p>
        <p>Designed &amp; Developed by <a href="javascript:void(0);" class="text-primary">Dreams</a></p>
    </div>
</div>

@endsection
