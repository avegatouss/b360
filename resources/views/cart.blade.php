<?php $page = 'cart'; ?>
@extends('layout.mainlayout')
@section('content')
    <div class="page-wrapper">
        <div class="content">
            <div class="page-header">
                <div class="add-item d-flex">
                    <div class="page-title">
                        <h4 class="fw-bold">Cart</h4>
                        <h6>Manage your cart</h6>
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
            <div class="card-body">
            <table class="table border">
                <thead class="thead-light">
                    <tr>
                        <th class="no-sort">
                            <label class="checkboxs">
                                <input type="checkbox" id="select-all">
                                <span class="checkmarks"></span>
                            </label>
                        </th>
                        <th>Code </th>
                        <th>Product</th>
                        <th>Amount</th>
                        <th>Quantity</th>
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
                        <td>CU001 </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <a href="javascript:void(0);" class="avatar avatar-md me-1">
                                    <img src="{{URL::asset('build/img/products/stock-img-01.png')}}" alt="product">
                                </a>
                                <a href="javascript:void(0);">Lenovo IdeaPad 3</a>
                            </div>												
                        </td>
                        <td>$600</td>
                        <td>
                            <div class="product-quantity border-0 bg-secondary-transparent">
                                <span class="quantity-btn"><i data-feather="minus-circle" class="feather-search"></i></span>
                                <input type="text" class="quntity-input bg-transparent" value="2">
                                <span class="quantity-btn">+<i data-feather="plus-circle" class="plus-circle"></i></span>
                            </div>
                        </td>
                        <td>$160</td>
                        <td>
                            <div class="edit-delete-action d-flex align-items-center">
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
                        <td>CU002 </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <a href="javascript:void(0);" class="avatar avatar-md me-1">
                                    <img src="{{URL::asset('build/img/products/stock-img-06.png')}}" alt="product">
                                </a>
                                <a href="javascript:void(0);">Beats Pro</a>
                            </div>												
                        </td>
                        <td>$160</td>
                        <td>
                            <div class="product-quantity border-0 bg-secondary-transparent">
                                <span class="quantity-btn"><i data-feather="minus-circle" class="feather-search"></i></span>
                                <input type="text" class="quntity-input bg-transparent" value="2">
                                <span class="quantity-btn">+<i data-feather="plus-circle" class="plus-circle"></i></span>
                            </div>
                        </td>
                        <td>$1200</td>
                        <td>
                            <div class="edit-delete-action d-flex align-items-center">
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
                        <td>CU003 </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <a href="javascript:void(0);" class="avatar avatar-md me-1">
                                    <img src="{{URL::asset('build/img/products/stock-img-02.png')}}" alt="product">
                                </a>
                                <a href="javascript:void(0);">Nike Jordan</a>
                            </div>												
                        </td>
                        <td>$110</td>
                        <td>
                            <div class="product-quantity border-0 bg-secondary-transparent">
                                <span class="quantity-btn"><i data-feather="minus-circle" class="feather-search"></i></span>
                                <input type="text" class="quntity-input bg-transparent" value="2">
                                <span class="quantity-btn">+<i data-feather="plus-circle" class="plus-circle"></i></span>
                            </div>
                        </td>
                        <td>$330</td>
                        <td>
                            <div class="edit-delete-action d-flex align-items-center">
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
                        <td>CU004 </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <a href="javascript:void(0);" class="avatar avatar-md me-1">
                                    <img src="{{URL::asset('build/img/products/stock-img-03.png')}}" alt="product">
                                </a>
                                <a href="javascript:void(0);">Apple Series 5 Watch</a>
                            </div>												
                        </td>
                        <td>$120</td>
                        <td>
                            <div class="product-quantity border-0 bg-secondary-transparent">
                                <span class="quantity-btn"><i data-feather="minus-circle" class="feather-search"></i></span>
                                <input type="text" class="quntity-input bg-transparent" value="2">
                                <span class="quantity-btn">+<i data-feather="plus-circle" class="plus-circle"></i></span>
                            </div>
                        </td>
                        <td>$1420</td>
                        <td>
                            <div class="edit-delete-action d-flex align-items-center">
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
                        <td>CU005 </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <a href="javascript:void(0);" class="avatar avatar-md me-1">
                                    <img src="{{URL::asset('build/img/products/stock-img-04.png')}}" alt="product">
                                </a>
                                <a href="javascript:void(0);">Amazon Echo Dot</a>
                            </div>												
                        </td>
                        <td>$80</td>
                        <td>
                            <div class="product-quantity border-0 bg-secondary-transparent">
                                <span class="quantity-btn"><i data-feather="minus-circle" class="feather-search"></i></span>
                                <input type="text" class="quntity-input bg-transparent" value="2">
                                <span class="quantity-btn">+<i data-feather="plus-circle" class="plus-circle"></i></span>
                            </div>
                        </td>
                        <td>$1200</td>
                        <td>
                            <div class="edit-delete-action d-flex align-items-center">
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

            <div class="card mb-4">
            <div class="card-body">
            <div class="d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                    <input type="text" class="form-control me-2" placeholder="Enter Coupon Code">
                    <a href="#" class="btn btn-primary">Apply</a>
                </div>
                <div class="d-flex align-items-center">
                    <p class="mb-0 me-2">Total Price : </p>
                    <p class="h6">Total 2230</p>
                </div>
            </div>
            </div>
            </div>

            <div class="d-flex align-items-center justify-content-end mb-4">
                <a href="#" class="btn btn-secondary me-2">Checkout</a>
                <a href="#" class="btn btn-primary">Continue Shopping</a>
            </div>
            
        </div>
        <div class="footer d-sm-flex align-items-center justify-content-between border-top bg-white p-3">
            <p class="mb-0 text-gray-9">2014 - 2025 &copy; DreamsPOS. All Right Reserved</p>
            <p>Designed &amp; Developed by <a href="javascript:void(0);" class="text-primary">Dreams</a></p>
        </div>
    </div>
@endsection