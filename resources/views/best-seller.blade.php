<?php $page = 'best-seller'; ?>
@extends('layout.mainlayout')
@section('content')
    <div class="page-wrapper">
        <div class="content">
            <div class="page-header">
                <div class="add-item d-flex">
                    <div class="page-title">
                        <h4>Bestseller Products Report</h4>
                        <h6>View Reports of Best Selling Products</h6>
                    </div>
                </div>
                <ul class="table-top-head">
                    <li class="me-2">
                        <a data-bs-toggle="tooltip" data-bs-placement="top" title="Refresh"><i class="ti ti-refresh"></i></a>
                    </li>
                    <li class="me-2">
                        <a data-bs-toggle="tooltip" data-bs-placement="top" title="Collapse" id="collapse-header"><i class="ti ti-chevron-up"></i></a>
                    </li>
                </ul>
            </div>
            <div class="card">
                <div class="card-body pb-1">
                    <form action="{{url('best-seller')}}">
                        <div class="row align-items-end">
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label class="form-label">Choose Date </label>
                                    <div class="input-icon-start position-relative">
                                        <input type="text" class="form-control date-range bookingrange" placeholder="dd/mm/yyyy - dd/mm/yyyy">
                                        <span class="input-icon-left">
                                            <i class="ti ti-calendar"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
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
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label class="form-label">Products</label>
                                    <select class="select">
                                        <option>All</option>
                                        <option>Lenovo IdeaPad 3</option>
                                        <option>Nike Jordan</option>
                                        <option>Amazon Echo Dot</option>
                                        <option>Red Premium Satchel</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-3">
                                <div class="mb-3">
                                    <button class="btn btn-primary" type="submit">Generate Report</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <!-- /product list -->
            <div class="card no-search">
                <div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
                    <div>
                        <h4>Best Sellers</h4>
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
                                    <th>Brand</th>
                                    <th>Category</th>
                                    <th>Sold Qty</th>
                                    <th>Sold Amount</th>
                                    <th>Instock Qty</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    
                                    <td>
                                        <a>PT001</a>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <a  class="avatar avatar-md"><img src="{{URL::asset('build/img/products/laptop.png')}}" class="img-fluid" alt="img"></a>
                                            <div class="ms-2">
                                                <p class="text-dark mb-0"><a>Lenovo IdeaPad 3</a></p>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        Lenovo
                                    </td>
                                    <td>
                                        Computers									
                                    </td>
                                    <td>05</td>
                                    <td>
                                        $3000
                                    </td>
                                    <td>
                                        100
                                    </td>
                                    
                                </tr>
                                <tr>
                                    
                                    <td>
                                        <a >PT002</a>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <a  class="avatar avatar-md"><img src="{{URL::asset('build/img/products/stock-img-06.png')}}" class="img-fluid" alt="img"></a>
                                            <div class="ms-2">
                                                <p class="text-dark mb-0"><a>Beats Pro </a></p>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        Beats
                                    </td>
                                    <td>
                                        Electronics									
                                    </td>
                                    <td>10</td>
                                    <td>
                                        $1600
                                    </td>
                                    <td>
                                        140
                                    </td>
                                    
                                </tr>
                                <tr>
                                    
                                    <td>
                                        <a >PT003</a>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <a class="avatar avatar-md"><img src="{{URL::asset('build/img/products/stock-img-02.png')}}" class="img-fluid" alt="img"></a>
                                            <div class="ms-2">
                                                <p class="text-dark mb-0"><a>Nike Jordan </a></p>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        Nike
                                    </td>
                                    <td>
                                        Shoe								
                                    </td>
                                    <td>08</td>
                                    <td>
                                        $880
                                    </td>
                                    <td>
                                        300
                                    </td>
                                    
                                </tr>
                                <tr>
                                    
                                    <td>
                                        <a >PT004</a>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <a class="avatar avatar-md"><img src="{{URL::asset('build/img/products/stock-img-03.png')}}" class="img-fluid" alt="img"></a>
                                            <div class="ms-2">
                                                <p class="text-dark mb-0"><a>Apple Series 5 Watch</a></p>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        Apple
                                    </td>
                                    <td>
                                        Electronics								
                                    </td>
                                    <td>10</td>
                                    <td>
                                        $1200
                                    </td>
                                    <td>
                                        450
                                    </td>
                                    
                                </tr>
                                <tr>
                                    
                                    <td>
                                        <a >PT005</a>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <a class="avatar avatar-md"><img src="{{URL::asset('build/img/products/stock-img-04.png')}}" class="img-fluid" alt="img"></a>
                                            <div class="ms-2">
                                                <p class="text-dark mb-0"><a>Amazon Echo Dot</a></p>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        Amazon
                                    </td>
                                    <td>
                                        Electronics								
                                    </td>
                                    <td>05</td>
                                    <td>
                                        $400
                                    </td>
                                    <td>
                                        320
                                    </td>
                                    
                                </tr>
                                <tr>
                                    
                                    <td>
                                        <a >PT006</a>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <a class="avatar avatar-md"><img src="{{URL::asset('build/img/products/stock-img-05.png')}}" class="img-fluid" alt="img"></a>
                                            <div class="ms-2">
                                                <p class="text-dark mb-0"><a>Sanford Chair Sofa</a></p>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        Modern Wave
                                    </td>
                                    <td>
                                        Furniture							
                                    </td>
                                    <td>07</td>
                                    <td>
                                        $2240
                                    </td>
                                    <td>
                                        650
                                    </td>
                                    
                                </tr>
                                <tr>
                                    
                                    <td>
                                        <a >PT007</a>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <a class="avatar avatar-md"><img src="{{URL::asset('build/img/products/expire-product-01.png')}}" class="img-fluid" alt="img"></a>
                                            <div class="ms-2">
                                                <p class="text-dark mb-0"><a>Red Premium Satchel</a></p>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        Dior
                                    </td>
                                    <td>
                                        Bags						
                                    </td>
                                    <td>15</td>
                                    <td>
                                        $900
                                    </td>
                                    <td>
                                        700
                                    </td>
                                    
                                </tr>
                                <tr>
                                    
                                    <td>
                                        <a >PT008</a>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <a class="avatar avatar-md"><img src="{{URL::asset('build/img/products/expire-product-02.png')}}" class="img-fluid" alt="img"></a>
                                            <div class="ms-2">
                                                <p class="text-dark mb-0"><a>Iphone 14 Prol</a></p>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        Apple
                                    </td>
                                    <td>
                                        Phone					
                                    </td>
                                    <td>12</td>
                                    <td>
                                        $6480
                                    </td>
                                    <td>
                                        630
                                    </td>
                                    
                                </tr>
                                <tr>
                                    
                                    <td>
                                        <a >PT009</a>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <a class="avatar avatar-md"><img src="{{URL::asset('build/img/products/expire-product-03.png')}}" class="img-fluid" alt="img"></a>
                                            <div class="ms-2">
                                                <p class="text-dark mb-0"><a>Gaming Chair</a></p>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        Arlime
                                    </td>
                                    <td>
                                        Furniture				
                                    </td>
                                    <td>10</td>
                                    <td>
                                        $2000
                                    </td>
                                    <td>
                                        410
                                    </td>
                                    
                                </tr>
                                <tr>
                                    
                                    <td>
                                        <a >PT010</a>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <a class="avatar avatar-md"><img src="{{URL::asset('build/img/products/expire-product-04.png')}}" class="img-fluid" alt="img"></a>
                                            <div class="ms-2">
                                                <p class="text-dark mb-0"><a>Borealis Backpack</a></p>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        The North Face
                                    </td>
                                    <td>
                                        Bags			
                                    </td>
                                    <td>20</td>
                                    <td>
                                        $900
                                    </td>
                                    <td>
                                        550
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