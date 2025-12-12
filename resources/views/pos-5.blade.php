<?php $page = 'pos-5'; ?>
@extends('layout.mainlayout')
@section('content')

<div class="page-wrapper pos-pg-wrapper ms-0">
    <div class="content pos-design p-0">

        <div class="row align-items-start pos-wrapper">

            <!-- Products -->
            <div class="col-md-12 col-lg-6">
                <div class="pos-categories tabs_wrapper">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
                        <div>
                            <h5 class="mb-1">Welcome,  Wesley Adrian</h5>
                            <p>December 24, 2024</p>
                        </div>
                        <div class="d-flex align-items-center flex-wrap gap-3">
                            <div class="input-icon-start pos-search position-relative">
                                <span class="input-icon-addon">
                                    <i class="ti ti-search"></i>
                                </span>
                                <input type="text" class="form-control" placeholder="Search Product">
                            </div>
                            <a href="#" class="btn btn-sm btn-primary">View All Categories</a>
                        </div>
                    </div>
                    <ul class="tabs owl-carousel pos-category4 mb-4">
                        <li id="all" class="active">
                            <h6><a href="javascript:void(0);">All Categories</a></h6>
                        </li>
                        <li id="headphones">
                            <h6><a href="javascript:void(0);">Headphones</a></h6>
                        </li>
                        <li id="shoes">
                            <h6><a href="javascript:void(0);">Shoes</a></h6>
                        </li>
                        <li id="mobiles">
                            <h6><a href="javascript:void(0);">Mobiles</a></h6>
                        </li>
                        <li id="watches">
                            <h6><a href="javascript:void(0);">Watches</a></h6>
                        </li>
                        <li id="laptops">
                            <h6><a href="javascript:void(0);">Laptops</a></h6>
                        </li>
                        <li id="homeneed">
                            <h6><a href="javascript:void(0);">Home Needs</a></h6>
                        </li>
                        <li id="headphone">
                            <h6><a href="javascript:void(0);">Headphones</a></h6>
                        </li>
                    </ul>
                    <div class="pos-products">
                        <div class="tabs_container">
                            <div  class="tab_content active" data-tab="all">
                                <div class="row g-3">
                                    <div class="col-sm-6 col-md-4 col-lg-6 col-xl-4 col-xxl-3">
                                        <div class="product-info card">
                                            <a href="javascript:void(0);" class="product-image">
                                                <img src="{{URL::asset('build/img/products/pos-product-01.jpg')}}" alt="Products">
                                            </a>
                                            <div class="product-content text-center">
                                                <h6 class="fs-14 fw-bold mb-1"><a href="javascript:void(0);">Charger Cable</a></h6>
                                                <div class="text-center">
                                                    <span class="fs-14 fw-semibold text-gray-6">$30</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-md-4 col-lg-6 col-xl-4 col-xxl-3">
                                        <div class="product-info card active">
                                            <a href="javascript:void(0);" class="product-image">
                                                <img src="{{URL::asset('build/img/products/pos-product-02.jpg')}}" alt="Products">
                                            </a>
                                            <div class="product-content text-center">
                                                <h6 class="fs-14 fw-bold mb-1"><a href="javascript:void(0);">Apple Airpods 2</a></h6>
                                                <div class="text-center">
                                                    <h6 class="fs-14 fw-semibold text-gray-6">$120</h6>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-md-4 col-lg-6 col-xl-4 col-xxl-3">
                                        <div class="product-info card">
                                            <a href="javascript:void(0);" class="product-image">
                                                <img src="{{URL::asset('build/img/products/pos-product-03.jpg')}}" alt="Products">
                                            </a>
                                            <div class="product-content text-center">
                                                <h6 class="fs-14 fw-bold mb-1"><a href="javascript:void(0);">Vacuum Cleaner</a></h6>
                                                <div class="text-center">
                                                    <h6 class="fs-14 fw-semibold text-gray-6">$800</h6>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-md-4 col-lg-6 col-xl-4 col-xxl-3">
                                        <div class="product-info card">
                                            <a href="javascript:void(0);" class="product-image">
                                                <img src="{{URL::asset('build/img/products/pos-product-04.jpg')}}" alt="Products">
                                            </a>
                                            <div class="product-content text-center">
                                                <h6 class="fs-14 fw-bold mb-1"><a href="javascript:void(0);">Realme 8 Pro</a></h6>
                                                <div class="text-center">
                                                    <h6 class="fs-14 fw-semibold text-gray-6">$700</h6>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-md-4 col-lg-6 col-xl-4 col-xxl-3">
                                        <div class="product-info card">
                                            <a href="javascript:void(0);" class="product-image">
                                                <img src="{{URL::asset('build/img/products/pos-product-05.jpg')}}" alt="Products">
                                            </a>
                                            <div class="product-content text-center">
                                                <h6 class="fs-14 fw-bold mb-1"><a href="javascript:void(0);">Vacuum Robot</a></h6>
                                                <div class="text-center">
                                                    <h6 class="fs-14 fw-semibold text-gray-6">$600</h6>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-md-4 col-lg-6 col-xl-4 col-xxl-3">
                                        <div class="product-info card">
                                            <a href="javascript:void(0);" class="product-image">
                                                <img src="{{URL::asset('build/img/products/pos-product-06.jpg')}}" alt="Products">
                                            </a>
                                            <div class="product-content text-center">
                                                <h6 class="fs-14 fw-bold mb-1"><a href="javascript:void(0);">Apple Watch Series 9</a></h6>
                                                <div class="text-center">
                                                    <h6 class="fs-14 fw-semibold text-gray-6">$300</h6>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-md-4 col-lg-6 col-xl-4 col-xxl-3">
                                        <div class="product-info card">
                                            <a href="javascript:void(0);" class="product-image">
                                                <img src="{{URL::asset('build/img/products/pos-product-07.jpg')}}" alt="Products">
                                            </a>
                                            <div class="product-content text-center">
                                                <h6 class="fs-14 fw-bold mb-1"><a href="javascript:void(0);">Apple Watch Series 9</a></h6>
                                                <div class="text-center">
                                                    <h6 class="fs-14 fw-semibold text-gray-6">$300</h6>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-md-4 col-lg-6 col-xl-4 col-xxl-3">
                                        <div class="product-info card">
                                            <a href="javascript:void(0);" class="product-image">
                                                <img src="{{URL::asset('build/img/products/pos-product-08.jpg')}}" alt="Products">
                                            </a>
                                            <div class="product-content text-center">
                                                <h6 class="fs-14 fw-bold mb-1"><a href="javascript:void(0);">Bracelet</a></h6>
                                                <div class="text-center">
                                                    <h6 class="fs-14 fw-semibold text-gray-6">$1430</h6>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-md-4 col-lg-6 col-xl-4 col-xxl-3">
                                        <div class="product-info card">
                                            <a href="javascript:void(0);" class="product-image">
                                                <img src="{{URL::asset('build/img/products/pos-product-09.jpg')}}" alt="Products">
                                            </a>
                                            <div class="product-content text-center">
                                                <h6 class="fs-14 fw-bold mb-1"><a href="javascript:void(0);">YETI Flask</a></h6>
                                                <div class="text-center">
                                                    <h6 class="fs-14 fw-semibold text-gray-6">$1560</h6>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-md-4 col-lg-6 col-xl-4 col-xxl-3">
                                        <div class="product-info card">
                                            <a href="javascript:void(0);" class="product-image">
                                                <img src="{{URL::asset('build/img/products/pos-product-10.jpg')}}" alt="Products">
                                            </a>
                                            <div class="product-content text-center">
                                                <h6 class="fs-14 fw-bold mb-1"><a href="javascript:void(0);">Osmo Med Kit</a></h6>
                                                <div class="text-center">
                                                    <h6 class="fs-14 fw-semibold text-gray-6">$410</h6>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-md-4 col-lg-6 col-xl-4 col-xxl-3">
                                        <div class="product-info card">
                                            <a href="javascript:void(0);" class="product-image">
                                                <img src="{{URL::asset('build/img/products/pos-product-11.jpg')}}" alt="Products">
                                            </a>
                                            <div class="product-content text-center">
                                                <h6 class="fs-14 fw-bold mb-1"><a href="javascript:void(0);">Celestique Perfume</a></h6>
                                                <div class="text-center">
                                                    <h6 class="fs-14 fw-semibold text-gray-6">$150</h6>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-md-4 col-lg-6 col-xl-4 col-xxl-3">
                                        <div class="product-info card">
                                            <a href="javascript:void(0);" class="product-image">
                                                <img src="{{URL::asset('build/img/products/pos-product-12.jpg')}}" alt="Products">
                                            </a>
                                            <div class="product-content text-center">
                                                <h6 class="fs-14 fw-bold mb-1"><a href="javascript:void(0);">Dell XPS 13</a></h6>
                                                <div class="text-center">
                                                    <h6 class="fs-14 fw-semibold text-gray-6">$1140</h6>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-md-4 col-lg-6 col-xl-4 col-xxl-3">
                                        <div class="product-info card">
                                            <a href="javascript:void(0);" class="product-image">
                                                <img src="{{URL::asset('build/img/products/pos-product-13.jpg')}}" alt="Products">
                                            </a>
                                            <div class="product-content text-center">
                                                <h6 class="fs-14 fw-bold mb-1"><a href="javascript:void(0);">Cheese Snack</a></h6>
                                                <div class="text-center">
                                                    <h6 class="fs-14 fw-semibold text-gray-6">$15</h6>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-md-4 col-lg-6 col-xl-4 col-xxl-3">
                                        <div class="product-info card">
                                            <a href="javascript:void(0);" class="product-image">
                                                <img src="{{URL::asset('build/img/products/pos-product-14.jpg')}}" alt="Products">
                                            </a>
                                            <div class="product-content text-center">
                                                <h6 class="fs-14 fw-bold mb-1"><a href="javascript:void(0);">Blue Boot Shoes</a></h6>
                                                <div class="text-center">
                                                    <h6 class="fs-14 fw-semibold text-gray-6">$320</h6>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-md-4 col-lg-6 col-xl-4 col-xxl-3">
                                        <div class="product-info card">
                                            <a href="javascript:void(0);" class="product-image">
                                                <img src="{{URL::asset('build/img/products/pos-product-15.jpg')}}" alt="Products">
                                            </a>
                                            <div class="product-content text-center">
                                                <h6 class="fs-14 fw-bold mb-1"><a href="javascript:void(0);">Sonic Aura X7</a></h6>
                                                <div class="text-center">
                                                    <h6 class="fs-14 fw-semibold text-gray-6">$230</h6>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-md-4 col-lg-6 col-xl-4 col-xxl-3">
                                        <div class="product-info card">
                                            <a href="javascript:void(0);" class="product-image">
                                                <img src="{{URL::asset('build/img/products/pos-product-16.jpg')}}" alt="Products">
                                            </a>
                                            <div class="product-content text-center">
                                                <h6 class="fs-14 fw-bold mb-1"><a href="javascript:void(0);">Brown Formal Shoes</a></h6>
                                                <div class="text-center">
                                                    <h6 class="fs-14 fw-semibold text-gray-6">$160</h6>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-md-4 col-lg-6 col-xl-4 col-xxl-3">
                                        <div class="product-info card">
                                            <a href="javascript:void(0);" class="product-image">
                                                <img src="{{URL::asset('build/img/products/pos-product-17.jpg')}}" alt="Products">
                                            </a>
                                            <div class="product-content text-center">
                                                <h6 class="fs-14 fw-bold mb-1"><a href="javascript:void(0);">Apple Iphone 13 </a></h6>
                                                <div class="text-center">
                                                    <h6 class="fs-14 fw-semibold text-gray-6">$1200</h6>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-md-4 col-lg-6 col-xl-4 col-xxl-3">
                                        <div class="product-info card">
                                            <a href="javascript:void(0);" class="product-image">
                                                <img src="{{URL::asset('build/img/products/pos-product-18.jpg')}}" alt="Products">
                                            </a>
                                            <div class="product-content text-center">
                                                <h6 class="fs-14 fw-bold mb-1"><a href="javascript:void(0);">PixelCrafter 3000</a></h6>
                                                <div class="text-center">
                                                    <h6 class="fs-14 fw-semibold text-gray-6">$900</h6>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-md-4 col-lg-6 col-xl-4 col-xxl-3">
                                        <div class="product-info card">
                                            <a href="javascript:void(0);" class="product-image">
                                                <img src="{{URL::asset('build/img/products/pos-product-19.jpg')}}" alt="Products">
                                            </a>
                                            <div class="product-content text-center">
                                                <h6 class="fs-14 fw-bold mb-1"><a href="javascript:void(0);">Citrify Orange Juice</a></h6>
                                                <div class="text-center">
                                                    <h6 class="fs-14 fw-semibold text-gray-6">$80</h6>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-md-4 col-lg-6 col-xl-4 col-xxl-3">
                                        <div class="product-info card">
                                            <a href="javascript:void(0);" class="product-image">
                                                <img src="{{URL::asset('build/img/products/pos-product-20.jpg')}}" alt="Products">
                                            </a>
                                            <div class="product-content text-center">
                                                <h6 class="fs-14 fw-bold mb-1"><a href="javascript:void(0);">Aroma Coffee Maker</a></h6>
                                                <div class="text-center">
                                                    <h6 class="fs-14 fw-semibold text-gray-6">$170</h6>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div  class="tab_content" data-tab="headphones">
                                <div class="row g-3">
                                    <div class="col-sm-6 col-md-4 col-lg-6 col-xl-4 col-xxl-3">
                                        <div class="product-info card">
                                            <a href="javascript:void(0);" class="product-image">
                                                <img src="{{URL::asset('build/img/products/pos-product-02.jpg')}}" alt="Products">
                                            </a>
                                            <div class="product-content text-center">
                                                <h6 class="fs-14 fw-bold mb-1"><a href="javascript:void(0);">Apple Airpods 2</a></h6>
                                                <div class="text-center">
                                                    <h6 class="fs-14 fw-semibold text-gray-6">$120</h6>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-md-4 col-lg-6 col-xl-4 col-xxl-3">
                                        <div class="product-info card">
                                            <a href="javascript:void(0);" class="product-image">
                                                <img src="{{URL::asset('build/img/products/pos-product-15.jpg')}}" alt="Products">
                                            </a>
                                            <div class="product-content text-center">
                                                <h6 class="fs-14 fw-bold mb-1"><a href="javascript:void(0);">Sonic Aura X7</a></h6>
                                                <div class="text-center">
                                                    <h6 class="fs-14 fw-semibold text-gray-6">$1200</h6>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div  class="tab_content" data-tab="shoes">
                                <div class="row g-3">
                                    <div class="col-sm-6 col-md-4 col-lg-6 col-xl-4 col-xxl-3">
                                        <div class="product-info card">
                                            <a href="javascript:void(0);" class="product-image">
                                                <img src="{{URL::asset('build/img/products/pos-product-14.jpg')}}" alt="Products">
                                            </a>
                                            <div class="product-content text-center">
                                                <h6 class="fs-14 fw-bold mb-1"><a href="javascript:void(0);">Blue Boot Shoes</a></h6>
                                                <div class="text-center">
                                                    <h6 class="fs-14 fw-semibold text-gray-6">$320</h6>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-md-4 col-lg-6 col-xl-4 col-xxl-3">
                                        <div class="product-info card">
                                            <a href="javascript:void(0);" class="product-image">
                                                <img src="{{URL::asset('build/img/products/pos-product-16.jpg')}}" alt="Products">
                                            </a>
                                            <div class="product-content text-center">
                                                <h6 class="fs-14 fw-bold mb-1"><a href="javascript:void(0);">Brown Formal Shoes</a></h6>
                                                <div class="text-center">
                                                    <h6 class="fs-14 fw-semibold text-gray-6">$160</h6>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div  class="tab_content" data-tab="mobiles">
                                <div class="row g-3">
                                    <div class="col-sm-6 col-md-4 col-lg-6 col-xl-4 col-xxl-3">
                                        <div class="product-info card">
                                            <a href="javascript:void(0);" class="product-image">
                                                <img src="{{URL::asset('build/img/products/pos-product-01.jpg')}}" alt="Products">
                                            </a>
                                            <div class="product-content text-center">
                                                <h6 class="fs-14 fw-bold mb-1"><a href="javascript:void(0);">Charger Cable</a></h6>
                                                <div class="text-center">
                                                    <h6 class="fs-14 fw-semibold text-gray-6">$30</h6>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-md-4 col-lg-6 col-xl-4 col-xxl-3">
                                        <div class="product-info card">
                                            <a href="javascript:void(0);" class="product-image">
                                                <img src="{{URL::asset('build/img/products/pos-product-04.jpg')}}" alt="Products">
                                            </a>
                                            <div class="product-content text-center">
                                                <h6 class="fs-14 fw-bold mb-1"><a href="javascript:void(0);">Realme 8 Pro</a></h6>
                                                <div class="text-center">
                                                    <h6 class="fs-14 fw-semibold text-gray-6">$700</h6>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-md-4 col-lg-6 col-xl-4 col-xxl-3">
                                        <div class="product-info card">
                                            <a href="javascript:void(0);" class="product-image">
                                                <img src="{{URL::asset('build/img/products/pos-product-17.jpg')}}" alt="Products">
                                            </a>
                                            <div class="product-content text-center">
                                                <h6 class="fs-14 fw-bold mb-1"><a href="javascript:void(0);">Apple Iphone 13 </a></h6>
                                                <div class="text-center">
                                                    <h6 class="fs-14 fw-semibold text-gray-6">$1200</h6>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div  class="tab_content" data-tab="watches">
                                <div class="row g-3">
                                    <div class="col-sm-6 col-md-4 col-lg-6 col-xl-4 col-xxl-3">
                                        <div class="product-info card">
                                            <a href="javascript:void(0);" class="product-image">
                                                <img src="{{URL::asset('build/img/products/pos-product-07.jpg')}}" alt="Products">
                                            </a>
                                            <div class="product-content text-center">
                                                <h6 class="fs-14 fw-bold mb-1"><a href="javascript:void(0);">Apple Watch Series 9</a></h6>
                                                <div class="text-center">
                                                    <h6 class="fs-14 fw-semibold text-gray-6">$300</h6>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div  class="tab_content" data-tab="laptops">
                                <div class="row row-cols-xxl-5">
                                    <div class="col-sm-6 col-md-4 col-lg-6 col-xl-4 col-xxl-3">
                                        <div class="product-info card">
                                            <a href="javascript:void(0);" class="product-image">
                                                <img src="{{URL::asset('build/img/products/pos-product-12.jpg')}}" alt="Products">
                                            </a>
                                            <div class="product-content text-center">
                                                <h6 class="fs-14 fw-bold mb-1"><a href="javascript:void(0);">Dell XPS 13</a></h6>
                                                <div class="text-center">
                                                    <h6 class="fs-14 fw-semibold text-gray-6">$1140</h6>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-md-4 col-lg-6 col-xl-4 col-xxl-3">
                                        <div class="product-info card">
                                            <a href="javascript:void(0);" class="product-image">
                                                <img src="{{URL::asset('build/img/products/pos-product-01.jpg')}}" alt="Products">
                                            </a>
                                            <div class="product-content text-center">
                                                <h6 class="fs-14 fw-bold mb-1"><a href="javascript:void(0);">Charger Cable</a></h6>
                                                <div class="text-center">
                                                    <h6 class="fs-14 fw-semibold text-gray-6">$30</h6>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div  class="tab_content" data-tab="homeneed">
                                <div class="row row-cols-xxl-5">
                                    <div class="col-sm-6 col-md-4 col-lg-6 col-xl-4 col-xxl-3">
                                        <div class="product-info card">
                                            <a href="javascript:void(0);" class="product-image">
                                                <img src="{{URL::asset('build/img/products/pos-product-03.jpg')}}" alt="Products">
                                            </a>
                                            <div class="product-content text-center">
                                                <h6 class="fs-14 fw-bold mb-1"><a href="javascript:void(0);">Vacuum Cleaner</a></h6>
                                                <div class="text-center">
                                                    <h6 class="fs-14 fw-semibold text-gray-6">$800</h6>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-md-4 col-lg-6 col-xl-4 col-xxl-3">
                                        <div class="product-info card">
                                            <a href="javascript:void(0);" class="product-image">
                                                <img src="{{URL::asset('build/img/products/pos-product-05.jpg')}}" alt="Products">
                                            </a>
                                            <div class="product-content text-center">
                                                <h6 class="fs-14 fw-bold mb-1"><a href="javascript:void(0);">Vacuum Robot</a></h6>
                                                <div class="text-center">
                                                    <h6 class="fs-14 fw-semibold text-gray-6">$600</h6>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-md-4 col-lg-6 col-xl-4 col-xxl-3">
                                        <div class="product-info card">
                                            <a href="javascript:void(0);" class="product-image">
                                                <img src="{{URL::asset('build/img/products/pos-product-13.jpg')}}" alt="Products">
                                            </a>
                                            <div class="product-content text-center">
                                                <h6 class="fs-14 fw-bold mb-1"><a href="javascript:void(0);">Cheese Snack</a></h6>
                                                <div class="text-center">
                                                    <h6 class="fs-14 fw-semibold text-gray-6">$15</h6>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-md-4 col-lg-6 col-xl-4 col-xxl-3">
                                        <div class="product-info card">
                                            <a href="javascript:void(0);" class="product-image">
                                                <img src="{{URL::asset('build/img/products/pos-product-19.jpg')}}" alt="Products">
                                            </a>
                                            <div class="product-content text-center">
                                                <h6 class="fs-14 fw-bold mb-1"><a href="javascript:void(0);">Citrify Orange Juice</a></h6>
                                                <div class="text-center">
                                                    <h6 class="fs-14 fw-semibold text-gray-6">$80</h6>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-md-4 col-lg-6 col-xl-4 col-xxl-3">
                                        <div class="product-info card">
                                            <a href="javascript:void(0);" class="product-image">
                                                <img src="{{URL::asset('build/img/products/pos-product-20.jpg')}}" alt="Products">
                                            </a>
                                            <div class="product-content text-center">
                                                <h6 class="fs-14 fw-bold mb-1"><a href="javascript:void(0);">Aroma Coffee Maker</a></h6>
                                                <div class="text-center">
                                                    <h6 class="fs-14 fw-semibold text-gray-6">$170</h6>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div  class="tab_content" data-tab="headphone">
                                <div class="row row-cols-xxl-5">
                                    <div class="col-sm-6 col-md-4 col-lg-6 col-xl-4 col-xxl-3">
                                        <div class="product-info card">
                                            <a href="javascript:void(0);" class="product-image">
                                                <img src="{{URL::asset('build/img/products/pos-product-02.jpg')}}" alt="Products">
                                            </a>
                                            <div class="product-content text-center">
                                                <h6 class="fs-14 fw-bold mb-1"><a href="javascript:void(0);">Apple Airpods 2</a></h6>
                                                <div class="text-center">
                                                    <h6 class="fs-14 fw-semibold text-gray-6">$120</h6>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-md-4 col-lg-6 col-xl-4 col-xxl-3">
                                        <div class="product-info card">
                                            <a href="javascript:void(0);" class="product-image">
                                                <img src="{{URL::asset('build/img/products/pos-product-15.jpg')}}" alt="Products">
                                            </a>
                                            <div class="product-content text-center">
                                                <h6 class="fs-14 fw-bold mb-1"><a href="javascript:void(0);">Apple Iphone 13 </a></h6>
                                                <div class="text-center">
                                                    <h6 class="fs-14 fw-semibold text-gray-6">$1200</h6>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /Products -->

            <!-- Order Details -->
            <div class="col-md-12 col-lg-6 ps-0 theiaStickySidebar">
                <aside class="product-order-list">
                    <div class="customer-info">
                        <div class="order-head bg-light d-flex align-items-center justify-content-between w-100 mb-3">
                            <div>
                                <h3>Order List</h3>
                                <span>Transaction ID : #65565</span>
                            </div>
                            <div>
                                <a class="link-danger fs-16" href="javascript:void(0);"><i class="ti ti-trash-x-filled"></i></a>
                            </div>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <div class="input-icon-end position-relative">
                                    <input type="text" class="form-control datetimepicker" placeholder="dd/mm/yyyy">
                                    <span class="input-icon-addon">
                                        <i class="ti ti-calendar text-gray-7"></i>
                                    </span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <input type="text" class="form-control" placeholder="Type Ref Number">
                            </div>
                            <div class="col-md-4">
                                <select class="select">
                                    <option>Search Shop</option>
                                    <option>IPhone 14 64GB</option>
                                    <option>MacBook Pro</option>
                                    <option>Rolex Tribute V3</option>
                                    <option>Red Nike Angelo</option>
                                    <option>Airpod 2</option>
                                    <option>Oldest</option>
                                </select>
                            </div>
                            <div class="col-md-12">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="w-100">
                                        <select class="select">
                                            <option>Walk in Customer</option>
                                            <option>John</option>
                                            <option>Smith</option>
                                            <option>Ana</option>
                                            <option>Elza</option>
                                        </select>
                                    </div>
                                    <a href="#" class="btn btn-primary btn-icon" data-bs-toggle="modal" data-bs-target="#create"><i class="ti ti-user-plus"></i></a>
                                </div>
                            </div>
                            <div class="col-md-6">											
                                <select class="select">
                                    <option>USD</option>
                                    <option>EURO</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <input type="text" class="form-control" placeholder="Currency Exchange Rate">
                            </div>
                        </div>
                    </div>								
                    <div class="product-added block-section">
                        <div class="d-flex align-items-center justify-content-between gap-3 mb-3">
                            <h5 class="d-flex align-items-center mb-0">Order Details</h5>
                            <div class="badge bg-light text-gray-9 fs-12 fw-semibold py-2 border rounded">Items : <span class="text-teal">3</span></div>
                        </div>
                        <div class="product-wrap">
                            <div class="empty-cart">
                                <div class="mb-1">
                                    <img src="{{URL::asset('build/img/icons/empty-cart.svg')}}" alt="img">
                                </div>
                                <p class="fw-bold">No Products Selected</p>
                            </div>
                            <div class="product-list border-0 p-0">
                                <div class="table-responsive">
                                    <table class="table table-borderless">
                                        <thead>
                                            <tr>
                                                <th class="bg-transparent fw-bold">Product</th>
                                                <th class="bg-transparent fw-bold">Batch No</th>
                                                <th class="bg-transparent fw-bold">Price</th>
                                                <th class="bg-transparent fw-bold">QTY</th>
                                                <th class="bg-transparent fw-bold">Sub Total</th>
                                                <th class="bg-transparent fw-bold text-end"></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>
                                                    <h6 class="fs-16 fw-medium mb-1"><a href="#" data-bs-toggle="modal" data-bs-target="#products">Iphone 11S</a></h6>
                                                    In Stock: 10
                                                </td>
                                                <td>
                                                    <input type="text" class="form-control">
                                                </td>
                                                <td class="fw-bold">$400</td>
                                                <td>
                                                    <div class="qty-item m-0">
                                                        <a href="javascript:void(0);" class="dec d-flex justify-content-center align-items-center" data-bs-toggle="tooltip" data-bs-placement="top" title="minus"><i data-feather="minus-circle" class="feather-14"></i></a>
                                                        <input type="text" class="form-control text-center" name="qty" value="4">
                                                        <a href="javascript:void(0);" class="inc d-flex justify-content-center align-items-center" data-bs-toggle="tooltip" data-bs-placement="top" title="plus"><i data-feather="plus-circle" class="feather-14"></i></a>
                                                    </div>
                                                </td>
                                                <td class="fw-bold">$400</td>
                                                <td class="text-end">
                                                    <a class="btn-icon delete-icon" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#delete">
                                                        <i class="ti ti-trash"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <h6 class="fs-16 fw-medium mb-1"><a href="#" data-bs-toggle="modal" data-bs-target="#products">Samsung Galaxy S21</a></h6>
                                                    In Stock: 06
                                                </td>
                                                <td>
                                                    <input type="text" class="form-control">
                                                </td>
                                                <td class="fw-bold">$400</td>
                                                <td>
                                                    <div class="qty-item m-0">
                                                        <a href="javascript:void(0);" class="dec d-flex justify-content-center align-items-center" data-bs-toggle="tooltip" data-bs-placement="top" title="minus"><i data-feather="minus-circle" class="feather-14"></i></a>
                                                        <input type="text" class="form-control text-center" name="qty" value="1">
                                                        <a href="javascript:void(0);" class="inc d-flex justify-content-center align-items-center" data-bs-toggle="tooltip" data-bs-placement="top" title="plus"><i data-feather="plus-circle" class="feather-14"></i></a>
                                                    </div>
                                                </td>
                                                <td class="fw-bold">$400</td>
                                                <td class="text-end">
                                                    <a class="btn-icon delete-icon" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#delete">
                                                        <i class="ti ti-trash"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <h6 class="fs-16 fw-medium mb-1"><a href="#" data-bs-toggle="modal" data-bs-target="#products">Red Boot Shoes</a></h6>
                                                    In Stock: 04
                                                </td>
                                                <td>
                                                    <input type="text" class="form-control">
                                                </td>
                                                <td class="fw-bold">$600</td>
                                                <td>
                                                    <div class="qty-item m-0">
                                                        <a href="javascript:void(0);" class="dec d-flex justify-content-center align-items-center" data-bs-toggle="tooltip" data-bs-placement="top" title="minus"><i data-feather="minus-circle" class="feather-14"></i></a>
                                                        <input type="text" class="form-control text-center" name="qty" value="3">
                                                        <a href="javascript:void(0);" class="inc d-flex justify-content-center align-items-center" data-bs-toggle="tooltip" data-bs-placement="top" title="plus"><i data-feather="plus-circle" class="feather-14"></i></a>
                                                    </div>
                                                </td>
                                                <td class="fw-bold">$600</td>
                                                <td class="text-end">
                                                    <a class="btn-icon delete-icon" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#delete">
                                                        <i class="ti ti-trash"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>										
                        </div>
                    </div>
                    <div class="block-section order-method bg-light m-0">									
                        <div class="order-total">
                            <div class="table-responsive">
                                <table class="table table-borderless">
                                    <tr>
                                        <td>Sub Total</td>
                                        <td class="text-end">$1250</td>
                                    </tr>
                                    <tr>
                                        <td>Shipping<a href="#" class="ms-3 link-default"  data-bs-toggle="modal" data-bs-target="#shipping-cost"><i class="ti ti-edit"></i></a></td>
                                        <td class="text-end">$35</td>
                                    </tr>
                                    <tr>
                                        <td>Tax<a href="#" class="ms-3 link-default"  data-bs-toggle="modal" data-bs-target="#order-tax"><i class="ti ti-edit"></i></a></td>
                                        <td class="text-end">$25</td>
                                    </tr>
                                    <tr>
                                        <td>Coupon<a href="#" class="ms-3 link-default"  data-bs-toggle="modal" data-bs-target="#coupon-code"><i class="ti ti-edit"></i></a></td>
                                        <td class="text-end">$25</td>
                                    </tr>
                                    <tr>
                                        <td><span class="text-danger">Discount</span><a href="#" class="ms-3 link-default"  data-bs-toggle="modal" data-bs-target="#discount"><i class="ti ti-edit"></i></a></td>
                                        <td class="text-danger text-end">-$24</td>
                                    </tr>
                                    <tr>
                                        <td>Grand Total</td>
                                        <td class="text-end">$56590</td>
                                    </tr>
                                </table>
                            </div>
                        </div>			
                        <div class="row gx-2">
                            <div class="col-sm-4">
                                <a href="javascript:void(0);" class="btn btn-orange d-flex align-items-center justify-content-center w-100 mb-2" data-bs-toggle="modal" data-bs-target="#hold-order"><i  class="ti ti-player-pause me-2"></i>Hold</a>
                                <a href="javascript:void(0);" class="btn btn-secondary d-flex align-items-center justify-content-center w-100 mb-2" data-bs-toggle="modal" data-bs-target="#orders"><i class="ti ti-shopping-cart me-2"></i>View Orders</a>
                            </div>
                            <div class="col-sm-4">
                                <a href="javascript:void(0);" class="btn btn-info d-flex align-items-center justify-content-center w-100 mb-2"><i  class="ti ti-trash me-2"></i>Void</a>
                                <a href="javascript:void(0);" class="btn btn-indigo d-flex align-items-center justify-content-center w-100 mb-2" data-bs-toggle="modal" data-bs-target="#reset"><i class="ti ti-reload me-2"></i>Reset</a>
                            </div>
                            <div class="col-sm-4">
                                <a href="javascript:void(0);" class="btn btn-cyan d-flex align-items-center justify-content-center w-100 mb-2" data-bs-toggle="modal" data-bs-target="#payment-completed"><i  class="ti ti-cash-banknote me-2"></i>Payment</a>
                                <a href="javascript:void(0);" class="btn btn-danger d-flex align-items-center justify-content-center w-100 mb-2" data-bs-toggle="modal" data-bs-target="#recents"><i class="ti ti-refresh-dot me-2"></i>Transaction</a>
                            </div>
                        </div>									
                    </div>
                    <div class="block-section payment-method">
                        <h5 class="mb-2">Select Payment</h5>
                        <div class="row align-items-center justify-content-center methods g-2 mb-4">
                            <div class="col-sm-6 col-md-4 col-xl d-flex">
                                <a href="javascript:void(0);" class="payment-item flex-fill" data-bs-toggle="modal" data-bs-target="#payment-cash">
                                    <img src="{{URL::asset('build/img/icons/cash-icon.svg')}}" alt="img">
                                    <p class="fw-medium">Cash</p>
                                </a>
                            </div>
                            <div class="col-sm-6 col-md-4 col-xl d-flex">
                                <a href="javascript:void(0);" class="payment-item flex-fill" data-bs-toggle="modal" data-bs-target="#payment-card">
                                    <img src="{{URL::asset('build/img/icons/card.svg')}}" alt="img">
                                    <p class="fw-medium">Card</p>
                                </a>
                            </div>
                            <div class="col-sm-6 col-md-4 col-xl d-flex">
                                <a href="javascript:void(0);" class="payment-item flex-fill" data-bs-toggle="modal" data-bs-target="#payment-points">
                                    <img src="{{URL::asset('build/img/icons/points.svg')}}" alt="img">
                                    <p class="fw-medium">Points</p>
                                </a>
                            </div>
                            <div class="col-sm-6 col-md-4 col-xl d-flex">
                                <a href="javascript:void(0);" class="payment-item flex-fill" data-bs-toggle="modal" data-bs-target="#payment-deposit">
                                    <img src="{{URL::asset('build/img/icons/deposit.svg')}}" alt="img">
                                    <p class="fw-medium">Deposit</p>
                                </a>
                            </div>
                            <div class="col-sm-6 col-md-4 col-xl d-flex">
                                <a href="javascript:void(0);" class="payment-item flex-fill" data-bs-toggle="modal" data-bs-target="#payment-cheque">
                                    <img src="{{URL::asset('build/img/icons/cheque.svg')}}" alt="img">
                                    <p class="fw-medium">Cheque</p>
                                </a>
                            </div>
                        </div>
                        <div class="btn-block m-0">
                            <a class="btn btn-teal w-100" href="javascript:void(0);">
                                Pay : $56590.00
                            </a>
                        </div>
                    </div>
                </aside>
            </div>
            <!-- /Order Details -->

        </div>
    </div>
</div>
@endsection