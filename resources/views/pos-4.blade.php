<?php $page = 'pos-4'; ?>
@extends('layout.mainlayout')
@section('content')

<div class="page-wrapper pos-pg-wrapper ms-0">
    <div class="content pos-design p-0">

        <div class="row align-items-start pos-wrapper">

            <!-- Products -->
            <div class="col-md-12 col-lg-7 col-xl-8">
                <div class="pos-categories tabs_wrapper">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
                        <div>
                            <h5 class="mb-1">Welcome,  Wesley Adrian</h5>
                            <p>December 24, 2024</p>
                        </div>
                        <div class="d-flex align-items-center gap-3">
                            <div class="input-icon-start pos-search position-relative">
                                <span class="input-icon-addon">
                                    <i class="ti ti-search"></i>
                                </span>
                                <input type="text" class="form-control" placeholder="Search Product">
                            </div>
                            <a href="#" class="btn btn-sm btn-primary">View All Categories</a>
                        </div>
                    </div>
                    <ul class="tabs owl-carousel pos-category3 mb-4">
                        <li id="all" class="active">
                            <a href="javascript:void(0);">
                                <img src="{{URL::asset('build/img/categories/category-01.svg')}}" alt="Categories">
                            </a>
                            <h6><a href="javascript:void(0);">All Categories</a></h6>
                        </li>
                        <li id="headphones">
                            <a href="javascript:void(0);">
                                <img src="{{URL::asset('build/img/categories/category-02.svg')}}" alt="Categories">
                            </a>
                            <h6><a href="javascript:void(0);">Headphones</a></h6>
                        </li>
                        <li id="shoes">
                            <a href="javascript:void(0);">
                                <img src="{{URL::asset('build/img/categories/category-03.svg')}}" alt="Categories">
                            </a>
                            <h6><a href="javascript:void(0);">Shoes</a></h6>
                        </li>
                        <li id="mobiles">
                            <a href="javascript:void(0);">
                                <img src="{{URL::asset('build/img/categories/category-04.svg')}}" alt="Categories">
                            </a>
                            <h6><a href="javascript:void(0);">Mobiles</a></h6>
                        </li>
                        <li id="watches">
                            <a href="javascript:void(0);">
                                <img src="{{URL::asset('build/img/categories/category-05.svg')}}" alt="Categories">
                            </a>
                            <h6><a href="javascript:void(0);">Watches</a></h6>
                        </li>
                        <li id="laptops">
                            <a href="javascript:void(0);">
                                <img src="{{URL::asset('build/img/categories/category-06.svg')}}" alt="Categories">
                            </a>
                            <h6><a href="javascript:void(0);">Laptops</a></h6>
                        </li>
                        <li id="homeneed">
                            <a href="javascript:void(0);">
                                <img src="{{URL::asset('build/img/categories/category-07.svg')}}" alt="Categories">
                            </a>
                            <h6><a href="javascript:void(0);">Home Needs</a></h6>
                        </li>
                        <li id="headphone">
                            <a href="javascript:void(0);">
                                <img src="{{URL::asset('build/img/categories/category-02.svg')}}" alt="Categories">
                            </a>
                            <h6><a href="javascript:void(0);">Headphones</a></h6>
                        </li>
                    </ul>
                    <div class="pos-products">
                        <div class="tabs_container">
                            <div  class="tab_content active" data-tab="all">
                                <div class="row row-cols-xxl-5 g-3">
                                    <div class="col-sm-6 col-md-6 col-lg-4 col-xl-3 col-xxl">
                                        <div class="product-info card">
                                            <a href="javascript:void(0);" class="product-image">
                                                <img src="{{URL::asset('build/img/products/pos-product-01.jpg')}}" alt="Products">
                                            </a>
                                            <div class="product-content">
                                                <h6 class="fs-14 fw-bold mb-1"><a href="javascript:void(0);">Charger Cable</a></h6>
                                                <div class="d-flex align-items-center justify-content-between">
                                                    <h6 class="text-teal fs-14 fw-bold">$30</h6>
                                                    <p class="text-pink">40 Pcs</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-md-6 col-lg-4 col-xl-3 col-xxl">
                                        <div class="product-info card active">
                                            <a href="javascript:void(0);" class="product-image">
                                                <img src="{{URL::asset('build/img/products/pos-product-02.jpg')}}" alt="Products">
                                            </a>
                                            <div class="product-content">
                                                <h6 class="fs-14 fw-bold mb-1"><a href="javascript:void(0);">Apple Airpods 2</a></h6>
                                                <div class="d-flex align-items-center justify-content-between">
                                                    <h6 class="text-teal fs-14 fw-bold">$120</h6>
                                                    <p class="text-pink">25 Pcs</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-md-6 col-lg-4 col-xl-3 col-xxl">
                                        <div class="product-info card">
                                            <a href="javascript:void(0);" class="product-image">
                                                <img src="{{URL::asset('build/img/products/pos-product-03.jpg')}}" alt="Products">
                                            </a>
                                            <div class="product-content">
                                                <h6 class="fs-14 fw-bold mb-1"><a href="javascript:void(0);">Vacuum Cleaner</a></h6>
                                                <div class="d-flex align-items-center justify-content-between">
                                                    <h6 class="text-teal fs-14 fw-bold">$800</h6>
                                                    <p class="text-pink">12 Pcs</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-md-6 col-lg-4 col-xl-3 col-xxl">
                                        <div class="product-info card">
                                            <a href="javascript:void(0);" class="product-image">
                                                <img src="{{URL::asset('build/img/products/pos-product-04.jpg')}}" alt="Products">
                                            </a>
                                            <div class="product-content">
                                                <h6 class="fs-14 fw-bold mb-1"><a href="javascript:void(0);">Realme 8 Pro</a></h6>
                                                <div class="d-flex align-items-center justify-content-between">
                                                    <h6 class="text-teal fs-14 fw-bold">$700</h6>
                                                    <p class="text-pink">18 Pcs</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-md-6 col-lg-4 col-xl-3 col-xxl">
                                        <div class="product-info card">
                                            <a href="javascript:void(0);" class="product-image">
                                                <img src="{{URL::asset('build/img/products/pos-product-05.jpg')}}" alt="Products">
                                            </a>
                                            <div class="product-content">
                                                <h6 class="fs-14 fw-bold mb-1"><a href="javascript:void(0);">Vacuum Robot</a></h6>
                                                <div class="d-flex align-items-center justify-content-between">
                                                    <h6 class="text-teal fs-14 fw-bold">$600</h6>
                                                    <p class="text-pink">35 Pcs</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-md-6 col-lg-4 col-xl-3 col-xxl">
                                        <div class="product-info card">
                                            <a href="javascript:void(0);" class="product-image">
                                                <img src="{{URL::asset('build/img/products/pos-product-06.jpg')}}" alt="Products">
                                            </a>
                                            <div class="product-content">
                                                <h6 class="fs-14 fw-bold mb-1"><a href="javascript:void(0);">Apple Watch Series 9</a></h6>
                                                <div class="d-flex align-items-center justify-content-between">
                                                    <h6 class="text-teal fs-14 fw-bold">$300</h6>
                                                    <p class="text-pink">08 Pcs</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-md-6 col-lg-4 col-xl-3 col-xxl">
                                        <div class="product-info card">
                                            <a href="javascript:void(0);" class="product-image">
                                                <img src="{{URL::asset('build/img/products/pos-product-07.jpg')}}" alt="Products">
                                            </a>
                                            <div class="product-content">
                                                <h6 class="fs-14 fw-bold mb-1"><a href="javascript:void(0);">Apple Watch Series 9</a></h6>
                                                <div class="d-flex align-items-center justify-content-between">
                                                    <h6 class="text-teal fs-14 fw-bold">$300</h6>
                                                    <p class="text-pink">08 Pcs</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-md-6 col-lg-4 col-xl-3 col-xxl">
                                        <div class="product-info card">
                                            <a href="javascript:void(0);" class="product-image">
                                                <img src="{{URL::asset('build/img/products/pos-product-08.jpg')}}" alt="Products">
                                            </a>
                                            <div class="product-content">
                                                <h6 class="fs-14 fw-bold mb-1"><a href="javascript:void(0);">Bracelet</a></h6>
                                                <div class="d-flex align-items-center justify-content-between">
                                                    <h6 class="text-teal fs-14 fw-bold">$1430</h6>
                                                    <p class="text-pink">13 Pcs</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-md-6 col-lg-4 col-xl-3 col-xxl">
                                        <div class="product-info card">
                                            <a href="javascript:void(0);" class="product-image">
                                                <img src="{{URL::asset('build/img/products/pos-product-09.jpg')}}" alt="Products">
                                            </a>
                                            <div class="product-content">
                                                <h6 class="fs-14 fw-bold mb-1"><a href="javascript:void(0);">YETI Flask</a></h6>
                                                <div class="d-flex align-items-center justify-content-between">
                                                    <h6 class="text-teal fs-14 fw-bold">$1560</h6>
                                                    <p class="text-pink">30 Pcs</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-md-6 col-lg-4 col-xl-3 col-xxl">
                                        <div class="product-info card">
                                            <a href="javascript:void(0);" class="product-image">
                                                <img src="{{URL::asset('build/img/products/pos-product-10.jpg')}}" alt="Products">
                                            </a>
                                            <div class="product-content">
                                                <h6 class="fs-14 fw-bold mb-1"><a href="javascript:void(0);">Osmo Med Kit</a></h6>
                                                <div class="d-flex align-items-center justify-content-between">
                                                    <h6 class="text-teal fs-14 fw-bold">$410</h6>
                                                    <p class="text-pink">15 Pcs</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-md-6 col-lg-4 col-xl-3 col-xxl">
                                        <div class="product-info card">
                                            <a href="javascript:void(0);" class="product-image">
                                                <img src="{{URL::asset('build/img/products/pos-product-11.jpg')}}" alt="Products">
                                            </a>
                                            <div class="product-content">
                                                <h6 class="fs-14 fw-bold mb-1"><a href="javascript:void(0);">Celestique Perfume</a></h6>
                                                <div class="d-flex align-items-center justify-content-between">
                                                    <h6 class="text-teal fs-14 fw-bold">$150</h6>
                                                    <p class="text-pink">45 Pcs</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-md-6 col-lg-4 col-xl-3 col-xxl">
                                        <div class="product-info card">
                                            <a href="javascript:void(0);" class="product-image">
                                                <img src="{{URL::asset('build/img/products/pos-product-12.jpg')}}" alt="Products">
                                            </a>
                                            <div class="product-content">
                                                <h6 class="fs-14 fw-bold mb-1"><a href="javascript:void(0);">Dell XPS 13</a></h6>
                                                <div class="d-flex align-items-center justify-content-between">
                                                    <h6 class="text-teal fs-14 fw-bold">$1140</h6>
                                                    <p class="text-pink">22 Pcs</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-md-6 col-lg-4 col-xl-3 col-xxl">
                                        <div class="product-info card">
                                            <a href="javascript:void(0);" class="product-image">
                                                <img src="{{URL::asset('build/img/products/pos-product-13.jpg')}}" alt="Products">
                                            </a>
                                            <div class="product-content">
                                                <h6 class="fs-14 fw-bold mb-1"><a href="javascript:void(0);">Cheese Snack</a></h6>
                                                <div class="d-flex align-items-center justify-content-between">
                                                    <h6 class="text-teal fs-14 fw-bold">$15</h6>
                                                    <p class="text-pink">55 Pcs</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-md-6 col-lg-4 col-xl-3 col-xxl">
                                        <div class="product-info card">
                                            <a href="javascript:void(0);" class="product-image">
                                                <img src="{{URL::asset('build/img/products/pos-product-14.jpg')}}" alt="Products">
                                            </a>
                                            <div class="product-content">
                                                <h6 class="fs-14 fw-bold mb-1"><a href="javascript:void(0);">Blue Boot Shoes</a></h6>
                                                <div class="d-flex align-items-center justify-content-between">
                                                    <h6 class="text-teal fs-14 fw-bold">$320</h6>
                                                    <p class="text-pink">30 Pcs</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-md-6 col-lg-4 col-xl-3 col-xxl">
                                        <div class="product-info card">
                                            <a href="javascript:void(0);" class="product-image">
                                                <img src="{{URL::asset('build/img/products/pos-product-15.jpg')}}" alt="Products">
                                            </a>
                                            <div class="product-content">
                                                <h6 class="fs-14 fw-bold mb-1"><a href="javascript:void(0);">Sonic Aura X7</a></h6>
                                                <div class="d-flex align-items-center justify-content-between">
                                                    <h6 class="text-teal fs-14 fw-bold">$230</h6>
                                                    <p class="text-pink">20 Pcs</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-md-6 col-lg-4 col-xl-3 col-xxl">
                                        <div class="product-info card">
                                            <a href="javascript:void(0);" class="product-image">
                                                <img src="{{URL::asset('build/img/products/pos-product-16.jpg')}}" alt="Products">
                                            </a>
                                            <div class="product-content">
                                                <h6 class="fs-14 fw-bold mb-1"><a href="javascript:void(0);">Brown Formal Shoes</a></h6>
                                                <div class="d-flex align-items-center justify-content-between">
                                                    <h6 class="text-teal fs-14 fw-bold">$160</h6>
                                                    <p class="text-pink">13 Pcs</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-md-6 col-lg-4 col-xl-3 col-xxl">
                                        <div class="product-info card">
                                            <a href="javascript:void(0);" class="product-image">
                                                <img src="{{URL::asset('build/img/products/pos-product-17.jpg')}}" alt="Products">
                                            </a>
                                            <div class="product-content">
                                                <h6 class="fs-14 fw-bold mb-1"><a href="javascript:void(0);">Apple Iphone 13 </a></h6>
                                                <div class="d-flex align-items-center justify-content-between">
                                                    <h6 class="text-teal fs-14 fw-bold">$1200</h6>
                                                    <p class="text-pink">15 Pcs</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-md-6 col-lg-4 col-xl-3 col-xxl">
                                        <div class="product-info card">
                                            <a href="javascript:void(0);" class="product-image">
                                                <img src="{{URL::asset('build/img/products/pos-product-18.jpg')}}" alt="Products">
                                            </a>
                                            <div class="product-content">
                                                <h6 class="fs-14 fw-bold mb-1"><a href="javascript:void(0);">PixelCrafter 3000</a></h6>
                                                <div class="d-flex align-items-center justify-content-between">
                                                    <h6 class="text-teal fs-14 fw-bold">$900</h6>
                                                    <p class="text-pink">20 Pcs</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-md-6 col-lg-4 col-xl-3 col-xxl">
                                        <div class="product-info card">
                                            <a href="javascript:void(0);" class="product-image">
                                                <img src="{{URL::asset('build/img/products/pos-product-19.jpg')}}" alt="Products">
                                            </a>
                                            <div class="product-content">
                                                <h6 class="fs-14 fw-bold mb-1"><a href="javascript:void(0);">Citrify Orange Juice</a></h6>
                                                <div class="d-flex align-items-center justify-content-between">
                                                    <h6 class="text-teal fs-14 fw-bold">$80</h6>
                                                    <p class="text-pink">16 Pcs</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-md-6 col-lg-4 col-xl-3 col-xxl">
                                        <div class="product-info card">
                                            <a href="javascript:void(0);" class="product-image">
                                                <img src="{{URL::asset('build/img/products/pos-product-20.jpg')}}" alt="Products">
                                            </a>
                                            <div class="product-content">
                                                <h6 class="fs-14 fw-bold mb-1"><a href="javascript:void(0);">Aroma Coffee Maker</a></h6>
                                                <div class="d-flex align-items-center justify-content-between">
                                                    <h6 class="text-teal fs-14 fw-bold">$170</h6>
                                                    <p class="text-pink">35 Pcs</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div  class="tab_content" data-tab="headphones">
                                <div class="row row-cols-xxl-5 g-3">
                                    <div class="col-sm-6 col-md-6 col-lg-4 col-xl-3 col-xxl">
                                        <div class="product-info card">
                                            <a href="javascript:void(0);" class="product-image">
                                                <img src="{{URL::asset('build/img/products/pos-product-02.jpg')}}" alt="Products">
                                            </a>
                                            <div class="product-content">
                                                <h6 class="fs-14 fw-bold mb-1"><a href="javascript:void(0);">Apple Airpods 2</a></h6>
                                                <div class="d-flex align-items-center justify-content-between">
                                                    <h6 class="text-teal fs-14 fw-bold">$120</h6>
                                                    <p class="text-pink">25 Pcs</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-md-6 col-lg-4 col-xl-3 col-xxl">
                                        <div class="product-info card">
                                            <a href="javascript:void(0);" class="product-image">
                                                <img src="{{URL::asset('build/img/products/pos-product-15.jpg')}}" alt="Products">
                                            </a>
                                            <div class="product-content">
                                                <h6 class="fs-14 fw-bold mb-1"><a href="javascript:void(0);">Sonic Aura X7</a></h6>
                                                <div class="d-flex align-items-center justify-content-between">
                                                    <h6 class="text-teal fs-14 fw-bold">$1200</h6>
                                                    <p class="text-pink">15 Pcs</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div  class="tab_content" data-tab="shoes">
                                <div class="row row-cols-xxl-5 g-3">
                                    <div class="col-sm-6 col-md-6 col-lg-4 col-xl-3 col-xxl">
                                        <div class="product-info card">
                                            <a href="javascript:void(0);" class="product-image">
                                                <img src="{{URL::asset('build/img/products/pos-product-14.jpg')}}" alt="Products">
                                            </a>
                                            <div class="product-content">
                                                <h6 class="fs-14 fw-bold mb-1"><a href="javascript:void(0);">Blue Boot Shoes</a></h6>
                                                <div class="d-flex align-items-center justify-content-between">
                                                    <h6 class="text-teal fs-14 fw-bold">$320</h6>
                                                    <p class="text-pink">30 Pcs</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-md-6 col-lg-4 col-xl-3 col-xxl">
                                        <div class="product-info card">
                                            <a href="javascript:void(0);" class="product-image">
                                                <img src="{{URL::asset('build/img/products/pos-product-16.jpg')}}" alt="Products">
                                            </a>
                                            <div class="product-content">
                                                <h6 class="fs-14 fw-bold mb-1"><a href="javascript:void(0);">Brown Formal Shoes</a></h6>
                                                <div class="d-flex align-items-center justify-content-between">
                                                    <h6 class="text-teal fs-14 fw-bold">$160</h6>
                                                    <p class="text-pink">13 Pcs</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div  class="tab_content" data-tab="mobiles">
                                <div class="row row-cols-xxl-5 g-3">
                                    <div class="col-sm-6 col-md-6 col-lg-4 col-xl-3 col-xxl">
                                        <div class="product-info card">
                                            <a href="javascript:void(0);" class="product-image">
                                                <img src="{{URL::asset('build/img/products/pos-product-01.jpg')}}" alt="Products">
                                            </a>
                                            <div class="product-content">
                                                <h6 class="fs-14 fw-bold mb-1"><a href="javascript:void(0);">Charger Cable</a></h6>
                                                <div class="d-flex align-items-center justify-content-between">
                                                    <h6 class="text-teal fs-14 fw-bold">$30</h6>
                                                    <p class="text-pink">40 Pcs</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-md-6 col-lg-4 col-xl-3 col-xxl">
                                        <div class="product-info card">
                                            <a href="javascript:void(0);" class="product-image">
                                                <img src="{{URL::asset('build/img/products/pos-product-04.jpg')}}" alt="Products">
                                            </a>
                                            <div class="product-content">
                                                <h6 class="fs-14 fw-bold mb-1"><a href="javascript:void(0);">Realme 8 Pro</a></h6>
                                                <div class="d-flex align-items-center justify-content-between">
                                                    <h6 class="text-teal fs-14 fw-bold">$700</h6>
                                                    <p class="text-pink">18 Pcs</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-md-6 col-lg-4 col-xl-3 col-xxl">
                                        <div class="product-info card">
                                            <a href="javascript:void(0);" class="product-image">
                                                <img src="{{URL::asset('build/img/products/pos-product-17.jpg')}}" alt="Products">
                                            </a>
                                            <div class="product-content">
                                                <h6 class="fs-14 fw-bold mb-1"><a href="javascript:void(0);">Apple Iphone 13 </a></h6>
                                                <div class="d-flex align-items-center justify-content-between">
                                                    <h6 class="text-teal fs-14 fw-bold">$1200</h6>
                                                    <p class="text-pink">15 Pcs</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div  class="tab_content" data-tab="watches">
                                <div class="row row-cols-xxl-5 g-3">
                                    <div class="col-sm-6 col-md-6 col-lg-4 col-xl-3 col-xxl">
                                        <div class="product-info card">
                                            <a href="javascript:void(0);" class="product-image">
                                                <img src="{{URL::asset('build/img/products/pos-product-07.jpg')}}" alt="Products">
                                            </a>
                                            <div class="product-content">
                                                <h6 class="fs-14 fw-bold mb-1"><a href="javascript:void(0);">Apple Watch Series 9</a></h6>
                                                <div class="d-flex align-items-center justify-content-between">
                                                    <h6 class="text-teal fs-14 fw-bold">$300</h6>
                                                    <p class="text-pink">08 Pcs</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div  class="tab_content" data-tab="laptops">
                                <div class="row row-cols-xxl-5">
                                    <div class="col-sm-6 col-md-6 col-lg-4 col-xl-3 col-xxl">
                                        <div class="product-info card">
                                            <a href="javascript:void(0);" class="product-image">
                                                <img src="{{URL::asset('build/img/products/pos-product-12.jpg')}}" alt="Products">
                                            </a>
                                            <div class="product-content">
                                                <h6 class="fs-14 fw-bold mb-1"><a href="javascript:void(0);">Dell XPS 13</a></h6>
                                                <div class="d-flex align-items-center justify-content-between">
                                                    <h6 class="text-teal fs-14 fw-bold">$1140</h6>
                                                    <p class="text-pink">22 Pcs</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-md-6 col-lg-4 col-xl-3 col-xxl">
                                        <div class="product-info card">
                                            <a href="javascript:void(0);" class="product-image">
                                                <img src="{{URL::asset('build/img/products/pos-product-01.jpg')}}" alt="Products">
                                            </a>
                                            <div class="product-content">
                                                <h6 class="fs-14 fw-bold mb-1"><a href="javascript:void(0);">Charger Cable</a></h6>
                                                <div class="d-flex align-items-center justify-content-between">
                                                    <h6 class="text-teal fs-14 fw-bold">$30</h6>
                                                    <p class="text-pink">40 Pcs</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div  class="tab_content" data-tab="homeneed">
                                <div class="row row-cols-xxl-5">
                                    <div class="col-sm-6 col-md-6 col-lg-4 col-xl-3 col-xxl">
                                        <div class="product-info card">
                                            <a href="javascript:void(0);" class="product-image">
                                                <img src="{{URL::asset('build/img/products/pos-product-03.jpg')}}" alt="Products">
                                            </a>
                                            <div class="product-content">
                                                <h6 class="fs-14 fw-bold mb-1"><a href="javascript:void(0);">Vacuum Cleaner</a></h6>
                                                <div class="d-flex align-items-center justify-content-between">
                                                    <h6 class="text-teal fs-14 fw-bold">$800</h6>
                                                    <p class="text-pink">12 Pcs</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-md-6 col-lg-4 col-xl-3 col-xxl">
                                        <div class="product-info card">
                                            <a href="javascript:void(0);" class="product-image">
                                                <img src="{{URL::asset('build/img/products/pos-product-05.jpg')}}" alt="Products">
                                            </a>
                                            <div class="product-content">
                                                <h6 class="fs-14 fw-bold mb-1"><a href="javascript:void(0);">Vacuum Robot</a></h6>
                                                <div class="d-flex align-items-center justify-content-between">
                                                    <h6 class="text-teal fs-14 fw-bold">$600</h6>
                                                    <p class="text-pink">35 Pcs</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-md-6 col-lg-4 col-xl-3 col-xxl">
                                        <div class="product-info card">
                                            <a href="javascript:void(0);" class="product-image">
                                                <img src="{{URL::asset('build/img/products/pos-product-13.jpg')}}" alt="Products">
                                            </a>
                                            <div class="product-content">
                                                <h6 class="fs-14 fw-bold mb-1"><a href="javascript:void(0);">Cheese Snack</a></h6>
                                                <div class="d-flex align-items-center justify-content-between">
                                                    <h6 class="text-teal fs-14 fw-bold">$15</h6>
                                                    <p class="text-pink">55 Pcs</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-md-6 col-lg-4 col-xl-3 col-xxl">
                                        <div class="product-info card">
                                            <a href="javascript:void(0);" class="product-image">
                                                <img src="{{URL::asset('build/img/products/pos-product-19.jpg')}}" alt="Products">
                                            </a>
                                            <div class="product-content">
                                                <h6 class="fs-14 fw-bold mb-1"><a href="javascript:void(0);">Citrify Orange Juice</a></h6>
                                                <div class="d-flex align-items-center justify-content-between">
                                                    <h6 class="text-teal fs-14 fw-bold">$80</h6>
                                                    <p class="text-pink">16 Pcs</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-md-6 col-lg-4 col-xl-3 col-xxl">
                                        <div class="product-info card">
                                            <a href="javascript:void(0);" class="product-image">
                                                <img src="{{URL::asset('build/img/products/pos-product-20.jpg')}}" alt="Products">
                                            </a>
                                            <div class="product-content">
                                                <h6 class="fs-14 fw-bold mb-1"><a href="javascript:void(0);">Aroma Coffee Maker</a></h6>
                                                <div class="d-flex align-items-center justify-content-between">
                                                    <h6 class="text-teal fs-14 fw-bold">$170</h6>
                                                    <p class="text-pink">35 Pcs</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div  class="tab_content" data-tab="headphone">
                                <div class="row row-cols-xxl-5">
                                    <div class="col-sm-6 col-md-6 col-lg-4 col-xl-3 col-xxl">
                                        <div class="product-info card">
                                            <a href="javascript:void(0);" class="product-image">
                                                <img src="{{URL::asset('build/img/products/pos-product-02.jpg')}}" alt="Products">
                                            </a>
                                            <div class="product-content">
                                                <h6 class="fs-14 fw-bold mb-1"><a href="javascript:void(0);">Apple Airpods 2</a></h6>
                                                <div class="d-flex align-items-center justify-content-between">
                                                    <h6 class="text-teal fs-14 fw-bold">$120</h6>
                                                    <p class="text-pink">25 Pcs</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-md-6 col-lg-4 col-xl-3 col-xxl">
                                        <div class="product-info card">
                                            <a href="javascript:void(0);" class="product-image">
                                                <img src="{{URL::asset('build/img/products/pos-product-15.jpg')}}" alt="Products">
                                            </a>
                                            <div class="product-content">
                                                <h6 class="fs-14 fw-bold mb-1"><a href="javascript:void(0);">Apple Iphone 13 </a></h6>
                                                <div class="d-flex align-items-center justify-content-between">
                                                    <h6 class="text-teal fs-14 fw-bold">$1200</h6>
                                                    <p class="text-pink">15 Pcs</p>
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
            <div class="col-md-12 col-lg-5 col-xl-4 ps-0 theiaStickySidebar">
                <aside class="product-order-list">
                    <div class="customer-info">
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
                            <div class="d-flex align-items-center">
                                <h4 class="mb-0">New Order</h4>
                                <span class="badge badge-purple badge-xs fs-10 fw-medium ms-2">#5655898</span>
                            </div>
                            <a href="#" class="btn btn-sm btn-outline-primary shadow-primary" data-bs-toggle="modal" data-bs-target="#create">Add Customer</a>
                        </div>
                        <select class="select">
                            <option>Walk in Customer</option>
                            <option>John</option>
                            <option>Smith</option>
                            <option>Ana</option>
                            <option>Elza</option>
                        </select>
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
                                                <th class="bg-transparent fw-bold">QTY</th>
                                                <th class="bg-transparent fw-bold">Price</th>
                                                <th class="bg-transparent fw-bold text-end"></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center mb-1">
                                                        <h6 class="fs-16 fw-medium"><a href="#" data-bs-toggle="modal" data-bs-target="#products">Iphone 11S</a></h6>
                                                        <a href="#" class="ms-2 edit-icon"  data-bs-toggle="modal"
                                                        data-bs-target="#edit-product"><i class="ti ti-edit"></i></a>
                                                    </div>
                                                    Price : $400
                                                </td>
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
                                                    <div class="d-flex align-items-center mb-1">
                                                        <h6 class="fs-16 fw-medium"><a href="#" data-bs-toggle="modal" data-bs-target="#products">Samsung Galaxy S21</a></h6>
                                                        <a href="#" class="ms-2 edit-icon"  data-bs-toggle="modal"
                                                        data-bs-target="#edit-product"><i class="ti ti-edit"></i></a>
                                                    </div>
                                                    Price : $400
                                                </td>
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
                                                    <div class="d-flex align-items-center mb-1">
                                                        <h6 class="fs-16 fw-medium"><a href="#" data-bs-toggle="modal" data-bs-target="#products">Red Boot Shoes</a></h6>
                                                        <a href="#" class="ms-2 edit-icon"  data-bs-toggle="modal"
                                                        data-bs-target="#edit-product"><i class="ti ti-edit"></i></a>
                                                    </div>
                                                    Price : $600
                                                </td>
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
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center mb-1">
                                                        <h6 class="fs-16 fw-medium"><a href="#" data-bs-toggle="modal" data-bs-target="#products">Bracelet</a></h6>
                                                        <a href="#" class="ms-2 edit-icon"  data-bs-toggle="modal"
                                                        data-bs-target="#edit-product"><i class="ti ti-edit"></i></a>
                                                    </div>
                                                    Price : $1400
                                                </td>
                                                <td>
                                                    <div class="qty-item m-0">
                                                        <a href="javascript:void(0);" class="dec d-flex justify-content-center align-items-center" data-bs-toggle="tooltip" data-bs-placement="top" title="minus"><i data-feather="minus-circle" class="feather-14"></i></a>
                                                        <input type="text" class="form-control text-center" name="qty" value="1">
                                                        <a href="javascript:void(0);" class="inc d-flex justify-content-center align-items-center" data-bs-toggle="tooltip" data-bs-placement="top" title="plus"><i data-feather="plus-circle" class="feather-14"></i></a>
                                                    </div>
                                                </td>
                                                <td class="fw-bold">$1400</td>
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
                                        <td>Shipping</td>
                                        <td class="text-end">$35</td>
                                    </tr>
                                    <tr>
                                        <td>Tax (15%)</td>
                                        <td class="text-end">$25</td>
                                    </tr>
                                    <tr>
                                        <td>Discount (5%)</td>
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
                                <a href="javascript:void(0);" class="btn btn-teal d-flex align-items-center justify-content-center w-100 mb-2" data-bs-toggle="modal" data-bs-target="#discount"><i  class="ti ti-percentage me-2"></i>Discount</a>
                                <a href="javascript:void(0);" class="btn btn-orange d-flex align-items-center justify-content-center w-100 mb-2" data-bs-toggle="modal" data-bs-target="#hold-order"><i  class="ti ti-player-pause me-2"></i>Hold</a>
                                <a href="javascript:void(0);" class="btn btn-secondary d-flex align-items-center justify-content-center w-100 mb-2" data-bs-toggle="modal" data-bs-target="#orders"><i class="ti ti-shopping-cart me-2"></i>View Orders</a>
                            </div>
                            <div class="col-sm-4">
                                <a href="javascript:void(0);" class="btn btn-purple d-flex align-items-center justify-content-center w-100 mb-2" data-bs-toggle="modal" data-bs-target="#order-tax"><i  class="ti ti-receipt-tax me-2"></i>Tax</a>
                                <a href="javascript:void(0);" class="btn btn-info d-flex align-items-center justify-content-center w-100 mb-2"><i  class="ti ti-trash me-2"></i>Void</a>
                                <a href="javascript:void(0);" class="btn btn-indigo d-flex align-items-center justify-content-center w-100 mb-2" data-bs-toggle="modal" data-bs-target="#reset"><i class="ti ti-reload me-2"></i>Reset</a>
                            </div>
                            <div class="col-sm-4">
                                <a href="javascript:void(0);" class="btn btn-pink d-flex align-items-center justify-content-center w-100 mb-2" data-bs-toggle="modal" data-bs-target="#shipping-cost"><i  class="ti ti-package-import me-2"></i>Shipping</a>
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