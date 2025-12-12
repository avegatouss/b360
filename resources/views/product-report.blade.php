<?php $page = 'product-report'; ?>
@extends('layout.mainlayout')
@section('content')
    <div class="page-wrapper">
        <div class="content">
            <div class="mb-4">
                <ul class="nav nav-pills">
                    <li class="nav-item">
                        <a class="nav-link active" href="{{url('product-report')}}">Product Report</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{url('product-expiry-report')}}">Product Expiry Report</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{url('product-quantity-alert')}}">Product Quantity Alert</a>
                    </li>
                </ul>
            </div>
            <div>
                <div class="page-header">
                    <div class="add-item d-flex">
                        <div class="page-title">
                            <h4>Product Report</h4>
                            <h6>View Reports of Products</h6>
                        </div>
                    </div>
                    <ul class="table-top-head">
                        <li class="me-2">
                            <a data-bs-toggle="tooltip" data-bs-placement="top" title="Refresh"><i class="ti ti-refresh"></i></a>
                        </li>
                        <li>
                            <a data-bs-toggle="tooltip" data-bs-placement="top" title="Collapse" id="collapse-header"><i class="ti ti-chevron-up"></i></a>
                        </li>
                    </ul>
                </div>
                <div class="card">
                    <div class="card-body pb-1">
                        <form action="{{url('product-report')}}">
                            <div class="row align-items-end">
                                <div class="col-lg-10">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="mb-3">
                                                <label class="form-label">Choose Date</label>
                                                <div class="input-icon-start position-relative">
                                                    <input type="text" class="form-control date-range bookingrange" placeholder="dd/mm/yyyy - dd/mm/yyyy">
                                                    <span class="input-icon-left">
                                                        <i class="ti ti-calendar"></i>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-9">
                                            <div class="row">
                                                <div class="col-lg-3 col-md-4 col-sm-6">
                                                    <div class="mb-3">
                                                        <label class="form-label">Store</label>
                                                        <select class="select">
                                                            <option>All</option>
                                                            <option>Electro Mart</option>
                                                            <option>Quantum Gadgets</option>
                                                            <option>Prime Bazaar</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-lg-3 col-md-4 col-sm-6">
                                                    <div class="mb-3">
                                                        <label class="form-label">Category</label>
                                                        <select class="select">
                                                            <option>All</option>
                                                            <option>Computers</option>
                                                            <option>Electronics</option>
                                                            <option>Shoe</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-lg-3 col-md-4 col-sm-6">
                                                    <div class="mb-3">
                                                        <label class="form-label">Brand</label>
                                                        <select class="select">
                                                            <option>All</option>
                                                            <option>Lenovo</option>
                                                            <option>Beats</option>
                                                            <option>Nike</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-lg-3 col-md-4 col-sm-6">
                                                    <div class="mb-3">
                                                        <label class="form-label">Product</label>
                                                        <select class="select">
                                                            <option>All</option>
                                                            <option>Lenovo IdeaPad 3</option>
                                                            <option>Beats Pro</option>
                                                            <option>Nike Jordan</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-2">
                                    <div class="mb-3">
                                        <button class="btn btn-primary w-100" type="submit">Generate Report</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div> 
                
                <div class="card no-search">
                    <div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
                        <div>
                            <h4>Product Report</h4>
                        </div>
                        <ul class="table-top-head">
                            <li class="me-2">
                                <a data-bs-toggle="tooltip" data-bs-placement="top" title="Pdf"><img src="{{URL::asset('build/img/icons/pdf.svg')}}" alt="img"></a>
                            </li>
                            <li class="me-2">
                                <a data-bs-toggle="tooltip" data-bs-placement="top" title="Excel"><img src="{{URL::asset('build/img/icons/excel.svg')}}" alt="img"></a>
                            </li>
                            <li>
                                <a data-bs-toggle="tooltip" data-bs-placement="top" title="Print"><i class="ti ti-printer"></i></a>
                            </li>
                        </ul>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table datatable">
                                <thead class="thead-light">
                                    <tr>
                                        <th>SKU</th>
                                        <th>Product Name</th>
                                        <th>Category</th>
                                        <th>Brand</th>
                                        <th>Qty</th>
                                        <th>Price</th>
                                        <th>Total Ordered</th>
                                        <th>Revenue</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><a href="#">PT001</a></td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <a href="#" class="avatar avatar-md"><img src="{{URL::asset('build/img/products/stock-img-01.png')}}" class="img-fluid" alt="img"></a>
                                                <div class="ms-2">
                                                    <p class="text-dark mb-0"><a href="#">Lenovo IdeaPad 3</a></p>
                                                </div>
                                            </div>
                                        </td>
                                        <td>Computers</td>
                                        <td>Lenovo</td>
                                        <td>100</td>
                                        <td>$600</td>
                                        <td>5000</td>
                                        <td>$787258</td>
                                    </tr>
                                    <tr>
                                        <td><a href="#">PT002</a></td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <a href="#" class="avatar avatar-md"><img src="{{URL::asset('build/img/products/stock-img-06.png')}}" class="img-fluid" alt="img"></a>
                                                <div class="ms-2">
                                                    <p class="text-dark mb-0"><a href="#">Beats Pro </a></p>
                                                </div>
                                            </div>
                                        </td>
                                        <td>Electronics</td>
                                        <td>Beats</td>
                                        <td>140</td>
                                        <td>$160</td>
                                        <td>4860</td>
                                        <td>$689788</td>
                                    </tr>
                                    <tr>
                                        <td><a href="#">PT003</a></td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <a href="#" class="avatar avatar-md"><img src="{{URL::asset('build/img/products/stock-img-02.png')}}" class="img-fluid" alt="img"></a>
                                                <div class="ms-2">
                                                    <p class="text-dark mb-0"><a href="#">Nike Jordan</a></p>
                                                </div>
                                            </div>
                                        </td>
                                        <td>Shoe</td>
                                        <td>Nike</td>
                                        <td>300</td>
                                        <td>$110</td>
                                        <td>40</td>
                                        <td>$7757</td>
                                    </tr>
                                    <tr>
                                        <td><a href="#">PT004</a></td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <a href="#" class="avatar avatar-md"><img src="{{URL::asset('build/img/products/stock-img-03.png')}}" class="img-fluid" alt="img"></a>
                                                <div class="ms-2">
                                                    <p class="text-dark mb-0"><a href="#">Apple Series 5 Watch</a></p>
                                                </div>
                                            </div>
                                        </td>
                                        <td>Electronics</td>
                                        <td>Apple</td>
                                        <td>450</td>
                                        <td>$120</td>
                                        <td>9642</td>
                                        <td>$7555</td>
                                    </tr>
                                    <tr>
                                        <td><a href="#">PT005</a></td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <a href="#" class="avatar avatar-md"><img src="{{URL::asset('build/img/products/stock-img-04.png')}}" class="img-fluid" alt="img"></a>
                                                <div class="ms-2">
                                                    <p class="text-dark mb-0"><a href="#">Amazon Echo Dot</a></p>
                                                </div>
                                            </div>
                                        </td>
                                        <td>Electronics</td>
                                        <td>Amazon</td>
                                        <td>320</td>
                                        <td>$80</td>
                                        <td>5464</td>
                                        <td>$39698</td>
                                    </tr>
                                    <tr>
                                        <td><a href="#">PT006</a></td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <a href="#" class="avatar avatar-md"><img src="{{URL::asset('build/img/products/stock-img-05.png')}}" class="img-fluid" alt="img"></a>
                                                <div class="ms-2">
                                                    <p class="text-dark mb-0"><a href="#">Sanford Chair Sofa</a></p>
                                                </div>
                                            </div>
                                        </td>
                                        <td>Furniture</td>
                                        <td>Modern Wave</td>
                                        <td>650</td>
                                        <td>$320</td>
                                        <td>158</td>
                                        <td>$748</td>
                                    </tr>
                                    <tr>
                                        <td><a href="#">PT007</a></td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <a href="#" class="avatar avatar-md"><img src="{{URL::asset('build/img/products/expire-product-01.png')}}" class="img-fluid" alt="img"></a>
                                                <div class="ms-2">
                                                    <p class="text-dark mb-0"><a href="#">Red Premium Satchel</a></p>
                                                </div>
                                            </div>
                                        </td>
                                        <td>Bags</td>
                                        <td>Dior</td>
                                        <td>700</td>
                                        <td>$60</td>
                                        <td>7845</td>
                                        <td>$7985</td>
                                    </tr>
                                    <tr>
                                        <td><a href="#">PT008</a></td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <a href="#" class="avatar avatar-md"><img src="{{URL::asset('build/img/products/expire-product-02.png')}}" class="img-fluid" alt="img"></a>
                                                <div class="ms-2">
                                                    <p class="text-dark mb-0"><a href="#">Iphone 14 Pro</a></p>
                                                </div>
                                            </div>
                                        </td>
                                        <td>Phone</td>
                                        <td>Apple</td>
                                        <td>630</td>
                                        <td>$540</td>
                                        <td>540</td>
                                        <td>$8769798</td>
                                    </tr>
                                    <tr>
                                        <td><a href="#">PT009</a></td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <a href="#" class="avatar avatar-md"><img src="{{URL::asset('build/img/products/expire-product-03.png')}}" class="img-fluid" alt="img"></a>
                                                <div class="ms-2">
                                                    <p class="text-dark mb-0"><a href="#">Gaming Chair</a></p>
                                                </div>
                                            </div>
                                        </td>
                                        <td>Furniture</td>
                                        <td>Arlime</td>
                                        <td>410</td>
                                        <td>$200</td>
                                        <td>200</td>
                                        <td>$788979</td>
                                    </tr>
                                    <tr>
                                        <td><a href="#">PT010</a></td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <a href="#" class="avatar avatar-md"><img src="{{URL::asset('build/img/products/expire-product-04.png')}}" class="img-fluid" alt="img"></a>
                                                <div class="ms-2">
                                                    <p class="text-dark mb-0"><a href="#">Borealis Backpack</a></p>
                                                </div>
                                            </div>
                                        </td>
                                        <td>Bags</td>
                                        <td>The North Face</td>
                                        <td>550</td>
                                        <td>$45</td>
                                        <td>45</td>
                                        <td>$895</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <!-- /product list -->
            </div>
        </div>
        <div class="footer d-sm-flex align-items-center justify-content-between border-top bg-white p-3">
            <p class="mb-0">2014 - 2025 &copy; DreamsPOS. All Right Reserved</p>
            <p>Designed &amp; Developed by <a href="javascript:void(0);" class="text-primary">Dreams</a></p>
        </div>
    </div>
@endsection