<?php $page = 'purchase-order-report'; ?>
@extends('layout.mainlayout')
@section('content')

<div class="page-wrapper">
    <div class="content">
        <div class="page-header">
            <div class="add-item d-flex">
                <div class="page-title">
                    <h4 class="fw-bold">Purchase order</h4>
                    <h6>Manage your Purchase order</h6>
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
                                <th>Purchased Amount</th>
                                <th>Purchased QTY</th>
                                <th>Instock QTY</th>
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
                                <td class="d-flex align-items-center p-3 px-2">
                                    <a class="avatar avatar-md me-2">
                                        <img src="{{URL::asset('build/img/products/stock-img-01.png')}}" alt="product">
                                    </a>
                                    <a href="javascript:void(0);">Lenovo IdeaPad 3</a>
                                </td>
                                <td>$1000</td>
                                <td>40</td>
                                <td>30</td>
                            </tr>
                            <tr>
                                <td>
                                    <label class="checkboxs">
                                        <input type="checkbox">
                                        <span class="checkmarks"></span>
                                    </label>
                                </td>
                                <td class="d-flex align-items-center p-3 px-2">
                                    <a class="avatar avatar-md me-2">
                                        <img src="{{URL::asset('build/img/products/stock-img-06.png')}}" alt="product">
                                    </a>
                                    <a href="javascript:void(0);">Beats Pro</a>
                                </td>
                                <td>$1500</td>
                                <td>25</td>
                                <td>18</td>
                            </tr>
                            <tr>
                                <td>
                                    <label class="checkboxs">
                                        <input type="checkbox">
                                        <span class="checkmarks"></span>
                                    </label>
                                </td>
                                <td class="d-flex align-items-center p-3 px-2">
                                    <a class="avatar avatar-md me-2">
                                        <img src="{{URL::asset('build/img/products/stock-img-02.png')}}" alt="product">
                                    </a>
                                    <a href="javascript:void(0);">Nike Jordan</a>
                                </td>
                                <td>$1500</td>
                                <td>30</td>
                                <td>35</td>
                            </tr>
                            <tr>
                                <td>
                                    <label class="checkboxs">
                                        <input type="checkbox">
                                        <span class="checkmarks"></span>
                                    </label>
                                </td>
                                <td class="d-flex align-items-center p-3 px-2">
                                    <a class="avatar avatar-md me-2">
                                        <img src="{{URL::asset('build/img/products/stock-img-03.png')}}" alt="product">
                                    </a>
                                    <a href="javascript:void(0);">Apple Series 5 Watch</a>
                                </td>
                                <td>$2000</td>
                                <td>28</td>
                                <td>30</td>
                            </tr>
                            <tr>
                                <td>
                                    <label class="checkboxs">
                                        <input type="checkbox">
                                        <span class="checkmarks"></span>
                                    </label>
                                </td>
                                <td class="d-flex align-items-center p-3 px-2">
                                    <a class="avatar avatar-md me-2">
                                        <img src="{{URL::asset('build/img/products/stock-img-04.png')}}" alt="product">
                                    </a>
                                    <a href="javascript:void(0);">Amazon Echo Dot</a>
                                </td>
                                <td>$800</td>
                                <td>15</td>
                                <td>10</td>
                            </tr>
                            <tr>
                                <td>
                                    <label class="checkboxs">
                                        <input type="checkbox">
                                        <span class="checkmarks"></span>
                                    </label>
                                </td>
                                <td class="d-flex align-items-center p-3 px-2">
                                    <a class="avatar avatar-md me-2">
                                        <img src="{{URL::asset('build/img/products/stock-img-05.png')}}" alt="product">
                                    </a>
                                    <a href="javascript:void(0);">Sanford Chair Sofa</a>
                                </td>
                                <td>$750</td>
                                <td>20</td>
                                <td>15</td>
                            </tr>
                            <tr>
                                <td>
                                    <label class="checkboxs">
                                        <input type="checkbox">
                                        <span class="checkmarks"></span>
                                    </label>
                                </td>
                                <td class="d-flex align-items-center p-3 px-2">
                                    <a class="avatar avatar-md me-2">
                                        <img src="{{URL::asset('build/img/products/expire-product-01.png')}}" alt="product">
                                    </a>
                                    <a href="javascript:void(0);">Red Premium Satchel</a>
                                </td>
                                <td>$1300</td>
                                <td>35</td>
                                <td>40</td>
                            </tr>
                            <tr>
                                <td>
                                    <label class="checkboxs">
                                        <input type="checkbox">
                                        <span class="checkmarks"></span>
                                    </label>
                                </td>
                                <td class="d-flex align-items-center p-3 px-2">
                                    <a class="avatar avatar-md me-2">
                                        <img src="{{URL::asset('build/img/products/expire-product-02.png')}}" alt="product">
                                    </a>
                                    <a href="javascript:void(0);">Iphone 14 Pro</a>
                                </td>
                                <td>$1100</td>
                                <td>45</td>
                                <td>35</td>
                            </tr>
                            <tr>
                                <td>
                                    <label class="checkboxs">
                                        <input type="checkbox">
                                        <span class="checkmarks"></span>
                                    </label>
                                </td>
                                <td class="d-flex align-items-center p-3 px-2">
                                    <a class="avatar avatar-md me-2">
                                        <img src="{{URL::asset('build/img/products/expire-product-03.png')}}" alt="product">
                                    </a>
                                    <a href="javascript:void(0);">Gaming Chair</a>
                                </td>
                                <td>$2300</td>
                                <td>22</td>
                                <td>20</td>
                            </tr>
                            <tr>
                                <td>
                                    <label class="checkboxs">
                                        <input type="checkbox">
                                        <span class="checkmarks"></span>
                                    </label>
                                </td>
                                <td class="d-flex align-items-center p-3 px-2">
                                    <a class="avatar avatar-md me-2">
                                        <img src="{{URL::asset('build/img/products/expire-product-04.png')}}" alt="product">
                                    </a>
                                    <a href="javascript:void(0);">Borealis Backpack</a>
                                </td>
                                <td>$1700</td>
                                <td>18</td>
                                <td>25</td>
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
<!-- /Main Wrapper -->

@endsection
