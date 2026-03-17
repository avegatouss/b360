<x-dashboard::layouts.master
    :title="__('POS Layout 3') . ' —' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance"
    :pageTitle="__('POS Layout 3')">

<div class="row align-items-start pos-wrapper flex-row-reverse">

            <!-- Products -->
            <div class="col-md-12 col-lg-7 col-xl-8">
                <div class="pos-categories tabs_wrapper pb-0">
                    <div class="card pos-button">
                        <div class="d-flex align-items-center flex-wrap">
                            <a href="javascript:void(0);" class="btn btn-teal btn-md mb-xs-3" data-bs-toggle="modal" data-bs-target="#orders"><i class="ti ti-shopping-cart me-1"></i>{{ __('View Orders') }}</a>
                            <a href="javascript:void(0);" class="btn btn-md btn-indigo" data-bs-toggle="modal" data-bs-target="#reset"><i class="ti ti-reload me-1"></i>{{ __('Reset') }}</a>
                            <a href="javascript:void(0);" class="btn btn-md btn-info" data-bs-toggle="modal" data-bs-target="#recents"><i class="ti ti-refresh-dot me-1"></i>{{ __('Transaction') }}</a>
                        </div>
                    </div>
                    <div class="d-flex align-items-center justify-content-between">
                        <h4 class="mb-3">{{ __('Categories') }}</h4>
                    </div>
                    <ul class="tabs owl-carousel pos-category">
                        <li id="all" class="active">
                            <a href="javascript:void(0);">
                                <img src="{{URL::asset('build/img/products/pos-product-01.png')}}" alt="Categories">
                            </a>
                            <h6><a href="javascript:void(0);">{{ __('All Categories') }}</a></h6>
                            <span>{{ __('80 Items') }}</span>
                        </li>
                        <li id="headphones">
                            <a href="javascript:void(0);">
                                <img src="{{URL::asset('build/img/products/pos-product-08.png')}}" alt="Categories">
                            </a>
                            <h6><a href="javascript:void(0);">{{ __('Headphones') }}</a></h6>
                            <span>{{ __('4 Items') }}</span>
                        </li>
                        <li id="shoes">
                            <a href="javascript:void(0);">
                                <img src="{{URL::asset('build/img/products/pos-product-13.png')}}" alt="Categories">
                            </a>
                            <h6><a href="javascript:void(0);">{{ __('Shoes') }}</a></h6>
                            <span>{{ __('14 Items') }}</span>
                        </li>
                        <li id="mobiles">
                            <a href="javascript:void(0);">
                                <img src="{{URL::asset('build/img/products/pos-product-09.png')}}" alt="Categories">
                            </a>
                            <h6><a href="javascript:void(0);">{{ __('Mobiles') }}</a></h6>
                            <span>{{ __('7 Items') }}</span>
                        </li>
                        <li id="watches">
                            <a href="javascript:void(0);">
                                <img src="{{URL::asset('build/img/products/pos-product-11.png')}}" alt="Categories">
                            </a>
                            <h6><a href="javascript:void(0);">{{ __('Watches') }}</a></h6>
                            <span>{{ __('16 Items') }}</span>
                        </li>
                        <li id="laptops">
                            <a href="javascript:void(0);">
                                <img src="{{URL::asset('build/img/products/pos-product-12.png')}}" alt="Categories">
                            </a>
                            <h6><a href="javascript:void(0);">{{ __('Laptops') }}</a></h6>
                            <span>{{ __('18 Items') }}</span>
                        </li>
                        <li id="allcategory">
                            <a href="javascript:void(0);">
                                <img src="{{URL::asset('build/img/products/pos-product-06.png')}}" alt="Categories">
                            </a>
                            <h6><a href="javascript:void(0);">{{ __('All Categories') }}</a></h6>
                            <span>{{ __('80 Items') }}</span>
                        </li>
                        <li id="headphone">
                            <a href="javascript:void(0);">
                                <img src="{{URL::asset('build/img/products/pos-product-05.png')}}" alt="Categories">
                            </a>
                            <h6><a href="javascript:void(0);">{{ __('Headphones') }}</a></h6>
                            <span>{{ __('4 Items') }}</span>
                        </li>
                        <li id="shoe">
                            <a href="javascript:void(0);">
                                <img src="{{URL::asset('build/img/products/pos-product-04.png')}}" alt="Categories">
                            </a>
                            <h6><a href="javascript:void(0);">{{ __('Shoes') }}</a></h6>
                            <span>{{ __('14 Items') }}</span>
                        </li>
                        <li id="mobile">
                            <a href="javascript:void(0);">
                                <img src="{{URL::asset('build/img/products/pos-product-01.png')}}" alt="Categories">
                            </a>
                            <h6><a href="javascript:void(0);">{{ __('Mobiles') }}</a></h6>
                            <span>{{ __('7 Items') }}</span>
                        </li>
                        <li id="watche">
                            <a href="javascript:void(0);">
                                <img src="{{URL::asset('build/img/products/pos-product-11.png')}}" alt="Categories">
                            </a>
                            <h6><a href="javascript:void(0);">{{ __('Watches') }}</a></h6>
                            <span>{{ __('16 Items') }}</span>
                        </li>
                        <li id="laptop">
                            <a href="javascript:void(0);">
                                <img src="{{URL::asset('build/img/products/pos-product-02.png')}}" alt="Categories">
                            </a>
                            <h6><a href="javascript:void(0);">{{ __('Laptops') }}</a></h6>
                            <span>{{ __('18 Items') }}</span>
                        </li>
                    </ul>
                    <div class="pos-products">
                        <div class="d-flex align-items-center justify-content-between">
                            <h4 class="mb-3">{{ __('Products') }}</h4>
                            <div class="input-icon-start pos-search position-relative mb-3">
                                <span class="input-icon-addon">
                                    <i class="ti ti-search"></i>
                                </span>
                                <input type="text" class="form-control" placeholder="{{ __('Search Product') }}">
                            </div>
                        </div>
                        <div class="tabs_container">
                            <div  class="tab_content active" data-tab="all">
                                <div class="row">
                                    <div class="col-sm-6 col-md-6 col-lg-4 col-xl-3">
                                        <div class="product-info card">
                                            <a href="javascript:void(0);" class="pro-img">
                                                <img src="{{URL::asset('build/img/products/pos-product-01.png')}}" alt="Products">
                                                <span><i class="ti ti-circle-check-filled"></i></span>
                                            </a>
                                            <h6 class="cat-name"><a href="javascript:void(0);">{{ __('Mobiles') }}</a></h6>
                                            <h6 class="product-name"><a href="javascript:void(0);">{{ __('IPhone 14 64GB') }}</a></h6>
                                            <div class="d-flex align-items-center justify-content-between price">
                                                <span>{{ __('30 Pcs') }}</span>
                                                <p>$15800</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-md-6 col-lg-4 col-xl-3">
                                        <div class="product-info card">
                                            <a href="javascript:void(0);" class="pro-img">
                                                <img src="{{URL::asset('build/img/products/pos-product-02.png')}}" alt="Products">
                                                <span><i class="ti ti-circle-check-filled"></i></span>
                                            </a>
                                            <h6 class="cat-name"><a href="javascript:void(0);">{{ __('Computer') }}</a></h6>
                                            <h6 class="product-name"><a href="javascript:void(0);">{{ __('MacBook Pro') }}</a></h6>
                                            <div class="d-flex align-items-center justify-content-between price">
                                                <span>{{ __('140 Pcs') }}</span>
                                                <p>$1000</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-md-6 col-lg-4 col-xl-3">
                                        <div class="product-info card">
                                            <a href="javascript:void(0);" class="pro-img">
                                                <img src="{{URL::asset('build/img/products/pos-product-03.png')}}" alt="Products">
                                                <span><i class="ti ti-circle-check-filled"></i></span>
                                            </a>
                                            <h6 class="cat-name"><a href="javascript:void(0);">{{ __('Watches') }}</a></h6>
                                            <h6 class="product-name"><a href="javascript:void(0);">{{ __('Rolex Tribute V3') }}</a></h6>
                                            <div class="d-flex align-items-center justify-content-between price">
                                                <span>{{ __('220 Pcs') }}</span>
                                                <p>$6800</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-md-6 col-lg-4 col-xl-3">
                                        <div class="product-info card">
                                            <a href="javascript:void(0);" class="pro-img">
                                                <img src="{{URL::asset('build/img/products/pos-product-04.png')}}" alt="Products">
                                                <span><i class="ti ti-circle-check-filled"></i></span>
                                            </a>
                                            <h6 class="cat-name"><a href="javascript:void(0);">{{ __('Shoes') }}</a></h6>
                                            <h6 class="product-name"><a href="javascript:void(0);">{{ __('Red Nike Angelo') }}</a></h6>
                                            <div class="d-flex align-items-center justify-content-between price">
                                                <span>{{ __('78 Pcs') }}</span>
                                                <p>$7800</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-md-6 col-lg-4 col-xl-3">
                                        <div class="product-info card active">
                                            <a href="javascript:void(0);" class="pro-img">
                                                <img src="{{URL::asset('build/img/products/pos-product-05.png')}}" alt="Products">
                                                <span><i class="ti ti-circle-check-filled"></i></span>
                                            </a>
                                            <h6 class="cat-name"><a href="javascript:void(0);">{{ __('Headphones') }}</a></h6>
                                            <h6 class="product-name"><a href="javascript:void(0);">{{ __('Airpod 2') }}</a></h6>
                                            <div class="d-flex align-items-center justify-content-between price">
                                                <span>{{ __('47 Pcs') }}</span>
                                                <p>$5478</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-md-6 col-lg-4 col-xl-3">
                                        <div class="product-info card">
                                            <a href="javascript:void(0);" class="pro-img">
                                                <img src="{{URL::asset('build/img/products/pos-product-06.png')}}" alt="Products">
                                                <span><i class="ti ti-circle-check-filled"></i></span>
                                            </a>
                                            <h6 class="cat-name"><a href="javascript:void(0);">{{ __('Shoes') }}</a></h6>
                                            <h6 class="product-name"><a href="javascript:void(0);">{{ __('Blue White OGR') }}</a></h6>
                                            <div class="d-flex align-items-center justify-content-between price">
                                                <span>{{ __('54 Pcs') }}</span>
                                                <p>$987</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-md-6 col-lg-4 col-xl-3">
                                        <div class="product-info card">
                                            <a href="javascript:void(0);" class="pro-img">
                                                <img src="{{URL::asset('build/img/products/pos-product-07.png')}}" alt="Products">
                                                <span><i class="ti ti-circle-check-filled"></i></span>
                                            </a>
                                            <h6 class="cat-name"><a href="javascript:void(0);">{{ __('Laptop') }}</a></h6>
                                            <h6 class="product-name"><a href="javascript:void(0);">{{ __('IdeaPad Slim 5 Gen 7') }}</a></h6>
                                            <div class="d-flex align-items-center justify-content-between price">
                                                <span>{{ __('74 Pcs') }}</span>
                                                <p>$1454</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-md-6 col-lg-4 col-xl-3">
                                        <div class="product-info card">
                                            <a href="javascript:void(0);" class="pro-img">
                                                <img src="{{URL::asset('build/img/products/pos-product-08.png')}}" alt="Products">
                                                <span><i class="ti ti-circle-check-filled"></i></span>
                                            </a>
                                            <h6 class="cat-name"><a href="javascript:void(0);">{{ __('Headphones') }}</a></h6>
                                            <h6 class="product-name"><a href="javascript:void(0);">{{ __('SWAGME') }}</a></h6>
                                            <div class="d-flex align-items-center justify-content-between price">
                                                <span>{{ __('14 Pcs') }}</span>
                                                <p>$6587</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-md-6 col-lg-4 col-xl-3">
                                        <div class="product-info card">
                                            <a href="javascript:void(0);" class="pro-img">
                                                <img src="{{URL::asset('build/img/products/pos-product-09.png')}}" alt="Products">
                                                <span><i class="ti ti-circle-check-filled"></i></span>
                                            </a>
                                            <h6 class="cat-name"><a href="javascript:void(0);">{{ __('Watches') }}</a></h6>
                                            <h6 class="product-name"><a href="javascript:void(0);">{{ __('Timex Black Silver') }}</a></h6>
                                            <div class="d-flex align-items-center justify-content-between price">
                                                <span>{{ __('24 Pcs') }}</span>
                                                <p>$1457</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-md-6 col-lg-4 col-xl-3">
                                        <div class="product-info card">
                                            <a href="javascript:void(0);" class="pro-img">
                                                <img src="{{URL::asset('build/img/products/pos-product-10.png')}}" alt="Products">
                                                <span><i class="ti ti-circle-check-filled"></i></span>
                                            </a>
                                            <h6 class="cat-name"><a href="javascript:void(0);">{{ __('Computer') }}</a></h6>
                                            <h6 class="product-name"><a href="javascript:void(0);">{{ __('Tablet 1.02 inch') }}</a></h6>
                                            <div class="d-flex align-items-center justify-content-between price">
                                                <span>{{ __('14 Pcs') }}</span>
                                                <p>$4744</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-md-6 col-lg-4 col-xl-3">
                                        <div class="product-info card">
                                            <a href="javascript:void(0);" class="pro-img">
                                                <img src="{{URL::asset('build/img/products/pos-product-11.png')}}" alt="Products">
                                                <span><i class="ti ti-circle-check-filled"></i></span>
                                            </a>
                                            <h6 class="cat-name"><a href="javascript:void(0);">{{ __('Watches') }}</a></h6>
                                            <h6 class="product-name"><a href="javascript:void(0);">{{ __('Fossil Pair Of 3 in 1') }}</a></h6>
                                            <div class="d-flex align-items-center justify-content-between price">
                                                <span>{{ __('40 Pcs') }}</span>
                                                <p>$789</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-md-6 col-lg-4 col-xl-3">
                                        <div class="product-info card">
                                            <a href="javascript:void(0);" class="pro-img">
                                                <img src="{{URL::asset('build/img/products/pos-product-13.png')}}" alt="Products">
                                                <span><i class="ti ti-circle-check-filled"></i></span>
                                            </a>
                                            <h6 class="cat-name"><a href="javascript:void(0);">{{ __('Shoes') }}</a></h6>
                                            <h6 class="product-name"><a href="javascript:void(0);">{{ __('Green Nike Fe') }}</a></h6>
                                            <div class="d-flex align-items-center justify-content-between price">
                                                <span>{{ __('78 Pcs') }}</span>
                                                <p>$7847</p>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                            <div  class="tab_content" data-tab="headphones">
                                <div class="row">
                                    <div class="col-sm-6 col-md-6 col-lg-4 col-xl-3">
                                        <div class="product-info card">
                                            <a href="javascript:void(0);" class="pro-img">
                                                <img src="{{URL::asset('build/img/products/pos-product-05.png')}}" alt="Products">
                                                <span><i class="ti ti-circle-check-filled"></i></span>
                                            </a>
                                            <h6 class="cat-name"><a href="javascript:void(0);">{{ __('Headphones') }}</a></h6>
                                            <h6 class="product-name"><a href="javascript:void(0);">{{ __('Airpod 2') }}</a></h6>
                                            <div class="d-flex align-items-center justify-content-between price">
                                                <span>{{ __('47 Pcs') }}</span>
                                                <p>$5478</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-md-6 col-lg-4 col-xl-3">
                                        <div class="product-info card">
                                            <a href="javascript:void(0);" class="pro-img">
                                                <img src="{{URL::asset('build/img/products/pos-product-08.png')}}" alt="Products">
                                                <span><i data-feather="check" class="feather-16" ></i></span>
                                            </a>
                                            <h6 class="cat-name"><a href="javascript:void(0);">{{ __('Headphones') }}</a></h6>
                                            <h6 class="product-name"><a href="javascript:void(0);">{{ __('SWAGME') }}</a></h6>
                                            <div class="d-flex align-items-center justify-content-between price">
                                                <span>{{ __('14 Pcs') }}</span>
                                                <p>$6587</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div  class="tab_content" data-tab="shoes">
                                <div class="row">
                                    <div class="col-sm-6 col-md-6 col-lg-4 col-xl-3">
                                        <div class="product-info card">
                                            <a href="javascript:void(0);" class="pro-img">
                                                <img src="{{URL::asset('build/img/products/pos-product-04.png')}}" alt="Products">
                                                <span><i class="ti ti-circle-check-filled"></i></span>
                                            </a>
                                            <h6 class="cat-name"><a href="javascript:void(0);">{{ __('Shoes') }}</a></h6>
                                            <h6 class="product-name"><a href="javascript:void(0);">{{ __('Red Nike Angelo') }}</a></h6>
                                            <div class="d-flex align-items-center justify-content-between price">
                                                <span>{{ __('78 Pcs') }}</span>
                                                <p>$7800</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-md-6 col-lg-4 col-xl-3">
                                        <div class="product-info card">
                                            <a href="javascript:void(0);" class="pro-img">
                                                <img src="{{URL::asset('build/img/products/pos-product-06.png')}}" alt="Products">
                                                <span><i class="ti ti-circle-check-filled"></i></span>
                                            </a>
                                            <h6 class="cat-name"><a href="javascript:void(0);">{{ __('Shoes') }}</a></h6>
                                            <h6 class="product-name"><a href="javascript:void(0);">{{ __('Blue White OGR') }}</a></h6>
                                            <div class="d-flex align-items-center justify-content-between price">
                                                <span>{{ __('54 Pcs') }}</span>
                                                <p>$987</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-md-6 col-lg-4 col-xl-3">
                                        <div class="product-info card">
                                            <a href="javascript:void(0);" class="pro-img">
                                                <img src="{{URL::asset('build/img/products/pos-product-13.png')}}" alt="Products">
                                                <span><i class="ti ti-circle-check-filled"></i></span>
                                            </a>
                                            <h6 class="cat-name"><a href="javascript:void(0);">{{ __('Shoes') }}</a></h6>
                                            <h6 class="product-name"><a href="javascript:void(0);">{{ __('Green Nike Fe') }}</a></h6>
                                            <div class="d-flex align-items-center justify-content-between price">
                                                <span>{{ __('78 Pcs') }}</span>
                                                <p>$7847</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div  class="tab_content" data-tab="mobiles">
                                <div class="row">
                                    <div class="col-sm-6 col-md-6 col-lg-4 col-xl-3">
                                        <div class="product-info card">
                                            <a href="javascript:void(0);" class="pro-img">
                                                <img src="{{URL::asset('build/img/products/pos-product-01.png')}}" alt="Products">
                                                <span><i class="ti ti-circle-check-filled"></i></span>
                                            </a>
                                            <h6 class="cat-name"><a href="javascript:void(0);">{{ __('Mobiles') }}</a></h6>
                                            <h6 class="product-name"><a href="javascript:void(0);">{{ __('IPhone 14 64GB') }}</a></h6>
                                            <div class="d-flex align-items-center justify-content-between price">
                                                <span>{{ __('30 Pcs') }}</span>
                                                <p>$15800</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-md-6 col-lg-4 col-xl-3">
                                        <div class="product-info card">
                                            <a href="javascript:void(0);" class="pro-img">
                                                <img src="{{URL::asset('build/img/products/pos-product-14.png')}}" alt="Products">
                                                <span><i class="ti ti-circle-check-filled"></i></span>
                                            </a>
                                            <h6 class="cat-name"><a href="javascript:void(0);">{{ __('Mobiles') }}</a></h6>
                                            <h6 class="product-name"><a href="javascript:void(0);">{{ __('Iphone 11') }}</a></h6>
                                            <div class="d-flex align-items-center justify-content-between price">
                                                <span>{{ __('14 Pcs') }}</span>
                                                <p>$3654</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div  class="tab_content" data-tab="watches">
                                <div class="row">
                                    <div class="col-sm-6 col-md-6 col-lg-4 col-xl-3">
                                        <div class="product-info card">
                                            <a href="javascript:void(0);" class="pro-img">
                                                <img src="{{URL::asset('build/img/products/pos-product-03.png')}}" alt="Products">
                                                <span><i class="ti ti-circle-check-filled"></i></span>
                                            </a>
                                            <h6 class="cat-name"><a href="javascript:void(0);">{{ __('Watches') }}</a></h6>
                                            <h6 class="product-name"><a href="javascript:void(0);">{{ __('Rolex Tribute V3') }}</a></h6>
                                            <div class="d-flex align-items-center justify-content-between price">
                                                <span>{{ __('220 Pcs') }}</span>
                                                <p>$6800</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-md-6 col-lg-4 col-xl-3">
                                        <div class="product-info card">
                                            <a href="javascript:void(0);" class="pro-img">
                                                <img src="{{URL::asset('build/img/products/pos-product-09.png')}}" alt="Products">
                                                <span><i class="ti ti-circle-check-filled"></i></span>
                                            </a>
                                            <h6 class="cat-name"><a href="javascript:void(0);">{{ __('Watches') }}</a></h6>
                                            <h6 class="product-name"><a href="javascript:void(0);">{{ __('Timex Black Silver') }}</a></h6>
                                            <div class="d-flex align-items-center justify-content-between price">
                                                <span>{{ __('24 Pcs') }}</span>
                                                <p>$1457</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-md-6 col-lg-4 col-xl-3">
                                        <div class="product-info card">
                                            <a href="javascript:void(0);" class="pro-img">
                                                <img src="{{URL::asset('build/img/products/pos-product-11.png')}}" alt="Products">
                                                <span><i class="ti ti-circle-check-filled"></i></span>
                                            </a>
                                            <h6 class="cat-name"><a href="javascript:void(0);">{{ __('Watches') }}</a></h6>
                                            <h6 class="product-name"><a href="javascript:void(0);">{{ __('Fossil Pair Of 3 in 1') }}</a></h6>
                                            <div class="d-flex align-items-center justify-content-between price">
                                                <span>{{ __('40 Pcs') }}</span>
                                                <p>$789</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div  class="tab_content" data-tab="laptops">
                                <div class="row">
                                    <div class="col-sm-6 col-md-6 col-lg-4 col-xl-3">
                                        <div class="product-info card">
                                            <a href="javascript:void(0);" class="pro-img">
                                                <img src="{{URL::asset('build/img/products/pos-product-02.png')}}" alt="Products">
                                                <span><i class="ti ti-circle-check-filled"></i></span>
                                            </a>
                                            <h6 class="cat-name"><a href="javascript:void(0);">{{ __('Computer') }}</a></h6>
                                            <h6 class="product-name"><a href="javascript:void(0);">{{ __('MacBook Pro') }}</a></h6>
                                            <div class="d-flex align-items-center justify-content-between price">
                                                <span>{{ __('140 Pcs') }}</span>
                                                <p>$1000</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-md-6 col-lg-4 col-xl-3">
                                        <div class="product-info card">
                                            <a href="javascript:void(0);" class="pro-img">
                                                <img src="{{URL::asset('build/img/products/pos-product-07.png')}}" alt="Products">
                                                <span><i class="ti ti-circle-check-filled"></i></span>
                                            </a>
                                            <h6 class="cat-name"><a href="javascript:void(0);">{{ __('Laptop') }}</a></h6>
                                            <h6 class="product-name"><a href="javascript:void(0);">{{ __('IdeaPad Slim 5 Gen 7') }}</a></h6>
                                            <div class="d-flex align-items-center justify-content-between price">
                                                <span>{{ __('74 Pcs') }}</span>
                                                <p>$1454</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-md-6 col-lg-4 col-xl-3">
                                        <div class="product-info card">
                                            <a href="javascript:void(0);" class="pro-img">
                                                <img src="{{URL::asset('build/img/products/pos-product-10.png')}}" alt="Products">
                                                <span><i class="ti ti-circle-check-filled"></i></span>
                                            </a>
                                            <h6 class="cat-name"><a href="javascript:void(0);">{{ __('Computer') }}</a></h6>
                                            <h6 class="product-name"><a href="javascript:void(0);">{{ __('Tablet 1.02 inch') }}</a></h6>
                                            <div class="d-flex align-items-center justify-content-between price">
                                                <span>{{ __('14 Pcs') }}</span>
                                                <p>$4744</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-md-6 col-lg-4 col-xl-3">
                                        <div class="product-info card">
                                            <a href="javascript:void(0);" class="pro-img">
                                                <img src="{{URL::asset('build/img/products/pos-product-13.png')}}" alt="Products">
                                                <span><i class="ti ti-circle-check-filled"></i></span>
                                            </a>
                                            <h6 class="cat-name"><a href="javascript:void(0);">{{ __('Laptop') }}</a></h6>
                                            <h6 class="product-name"><a href="javascript:void(0);">{{ __('Yoga Book 9i') }}</a></h6>
                                            <div class="d-flex align-items-center justify-content-between price">
                                                <span>{{ __('65 Pcs') }}</span>
                                                <p>$4784</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-md-6 col-lg-4 col-xl-3">
                                        <div class="product-info card">
                                            <a href="javascript:void(0);" class="pro-img">
                                                <img src="{{URL::asset('build/img/products/pos-product-14.png')}}" alt="Products">
                                                <span><i class="ti ti-circle-check-filled"></i></span>
                                            </a>
                                            <h6 class="cat-name"><a href="javascript:void(0);">{{ __('Laptop') }}</a></h6>
                                            <h6 class="product-name"><a href="javascript:void(0);">{{ __('IdeaPad Slim 3i') }}</a></h6>
                                            <div class="d-flex align-items-center justify-content-between price">
                                                <span>{{ __('47 Pcs') }}</span>
                                                <p>$1245</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div  class="tab_content" data-tab="allcategory">
                                <div class="row">
                                    <div class="col-sm-6 col-md-6 col-lg-4 col-xl-3">
                                        <div class="product-info card">
                                            <a href="javascript:void(0);" class="pro-img">
                                                <img src="{{URL::asset('build/img/products/pos-product-01.png')}}" alt="Products">
                                                <span><i class="ti ti-circle-check-filled"></i></span>
                                            </a>
                                            <h6 class="cat-name"><a href="javascript:void(0);">{{ __('Mobiles') }}</a></h6>
                                            <h6 class="product-name"><a href="javascript:void(0);">{{ __('IPhone 14 64GB') }}</a></h6>
                                            <div class="d-flex align-items-center justify-content-between price">
                                                <span>{{ __('30 Pcs') }}</span>
                                                <p>$15800</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-md-6 col-lg-4 col-xl-3">
                                        <div class="product-info card">
                                            <a href="javascript:void(0);" class="pro-img">
                                                <img src="{{URL::asset('build/img/products/pos-product-02.png')}}" alt="Products">
                                                <span><i class="ti ti-circle-check-filled"></i></span>
                                            </a>
                                            <h6 class="cat-name"><a href="javascript:void(0);">{{ __('Computer') }}</a></h6>
                                            <h6 class="product-name"><a href="javascript:void(0);">{{ __('MacBook Pro') }}</a></h6>
                                            <div class="d-flex align-items-center justify-content-between price">
                                                <span>{{ __('140 Pcs') }}</span>
                                                <p>$1000</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-md-6 col-lg-4 col-xl-3">
                                        <div class="product-info card">
                                            <a href="javascript:void(0);" class="pro-img">
                                                <img src="{{URL::asset('build/img/products/pos-product-03.png')}}" alt="Products">
                                                <span><i class="ti ti-circle-check-filled"></i></span>
                                            </a>
                                            <h6 class="cat-name"><a href="javascript:void(0);">{{ __('Watches') }}</a></h6>
                                            <h6 class="product-name"><a href="javascript:void(0);">{{ __('Rolex Tribute V3') }}</a></h6>
                                            <div class="d-flex align-items-center justify-content-between price">
                                                <span>{{ __('220 Pcs') }}</span>
                                                <p>$6800</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-md-6 col-lg-4 col-xl-3">
                                        <div class="product-info card">
                                            <a href="javascript:void(0);" class="pro-img">
                                                <img src="{{URL::asset('build/img/products/pos-product-04.png')}}" alt="Products">
                                                <span><i class="ti ti-circle-check-filled"></i></span>
                                            </a>
                                            <h6 class="cat-name"><a href="javascript:void(0);">{{ __('Shoes') }}</a></h6>
                                            <h6 class="product-name"><a href="javascript:void(0);">{{ __('Red Nike Angelo') }}</a></h6>
                                            <div class="d-flex align-items-center justify-content-between price">
                                                <span>{{ __('78 Pcs') }}</span>
                                                <p>$7800</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-md-6 col-lg-4 col-xl-3">
                                        <div class="product-info card">
                                            <a href="javascript:void(0);" class="pro-img">
                                                <img src="{{URL::asset('build/img/products/pos-product-05.png')}}" alt="Products">
                                                <span><i class="ti ti-circle-check-filled"></i></span>
                                            </a>
                                            <h6 class="cat-name"><a href="javascript:void(0);">{{ __('Headphones') }}</a></h6>
                                            <h6 class="product-name"><a href="javascript:void(0);">{{ __('Airpod 2') }}</a></h6>
                                            <div class="d-flex align-items-center justify-content-between price">
                                                <span>{{ __('47 Pcs') }}</span>
                                                <p>$5478</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-md-6 col-lg-4 col-xl-3">
                                        <div class="product-info card">
                                            <a href="javascript:void(0);" class="pro-img">
                                                <img src="{{URL::asset('build/img/products/pos-product-06.png')}}" alt="Products">
                                                <span><i class="ti ti-circle-check-filled"></i></span>
                                            </a>
                                            <h6 class="cat-name"><a href="javascript:void(0);">{{ __('Shoes') }}</a></h6>
                                            <h6 class="product-name"><a href="javascript:void(0);">{{ __('Blue White OGR') }}</a></h6>
                                            <div class="d-flex align-items-center justify-content-between price">
                                                <span>{{ __('54 Pcs') }}</span>
                                                <p>$987</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-md-6 col-lg-4 col-xl-3">
                                        <div class="product-info card">
                                            <a href="javascript:void(0);" class="pro-img">
                                                <img src="{{URL::asset('build/img/products/pos-product-07.png')}}" alt="Products">
                                                <span><i class="ti ti-circle-check-filled"></i></span>
                                            </a>
                                            <h6 class="cat-name"><a href="javascript:void(0);">{{ __('Laptop') }}</a></h6>
                                            <h6 class="product-name"><a href="javascript:void(0);">{{ __('IdeaPad Slim 5 Gen 7') }}</a></h6>
                                            <div class="d-flex align-items-center justify-content-between price">
                                                <span>{{ __('74 Pcs') }}</span>
                                                <p>$1454</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-md-6 col-lg-4 col-xl-3">
                                        <div class="product-info card">
                                            <a href="javascript:void(0);" class="pro-img">
                                                <img src="{{URL::asset('build/img/products/pos-product-08.png')}}" alt="Products">
                                                <span><i class="ti ti-circle-check-filled"></i></span>
                                            </a>
                                            <h6 class="cat-name"><a href="javascript:void(0);">{{ __('Headphones') }}</a></h6>
                                            <h6 class="product-name"><a href="javascript:void(0);">{{ __('SWAGME') }}</a></h6>
                                            <div class="d-flex align-items-center justify-content-between price">
                                                <span>{{ __('14 Pcs') }}</span>
                                                <p>$6587</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-md-6 col-lg-4 col-xl-3">
                                        <div class="product-info card">
                                            <a href="javascript:void(0);" class="pro-img">
                                                <img src="{{URL::asset('build/img/products/pos-product-09.png')}}" alt="Products">
                                                <span><i class="ti ti-circle-check-filled"></i></span>
                                            </a>
                                            <h6 class="cat-name"><a href="javascript:void(0);">{{ __('Watches') }}</a></h6>
                                            <h6 class="product-name"><a href="javascript:void(0);">{{ __('Timex Black Silver') }}</a></h6>
                                            <div class="d-flex align-items-center justify-content-between price">
                                                <span>{{ __('24 Pcs') }}</span>
                                                <p>$1457</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-md-6 col-lg-4 col-xl-3">
                                        <div class="product-info card">
                                            <a href="javascript:void(0);" class="pro-img">
                                                <img src="{{URL::asset('build/img/products/pos-product-10.png')}}" alt="Products">
                                                <span><i class="ti ti-circle-check-filled"></i></span>
                                            </a>
                                            <h6 class="cat-name"><a href="javascript:void(0);">{{ __('Computer') }}</a></h6>
                                            <h6 class="product-name"><a href="javascript:void(0);">{{ __('Tablet 1.02 inch') }}</a></h6>
                                            <div class="d-flex align-items-center justify-content-between price">
                                                <span>{{ __('14 Pcs') }}</span>
                                                <p>$4744</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-md-6 col-lg-4 col-xl-3">
                                        <div class="product-info card">
                                            <a href="javascript:void(0);" class="pro-img">
                                                <img src="{{URL::asset('build/img/products/pos-product-11.png')}}" alt="Products">
                                                <span><i class="ti ti-circle-check-filled"></i></span>
                                            </a>
                                            <h6 class="cat-name"><a href="javascript:void(0);">{{ __('Watches') }}</a></h6>
                                            <h6 class="product-name"><a href="javascript:void(0);">{{ __('Fossil Pair Of 3 in 1') }}</a></h6>
                                            <div class="d-flex align-items-center justify-content-between price">
                                                <span>{{ __('40 Pcs') }}</span>
                                                <p>$789</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-md-6 col-lg-4 col-xl-3">
                                        <div class="product-info card">
                                            <a href="javascript:void(0);" class="pro-img">
                                                <img src="{{URL::asset('build/img/products/pos-product-13.png')}}" alt="Products">
                                                <span><i class="ti ti-circle-check-filled"></i></span>
                                            </a>
                                            <h6 class="cat-name"><a href="javascript:void(0);">{{ __('Shoes') }}</a></h6>
                                            <h6 class="product-name"><a href="javascript:void(0);">{{ __('Green Nike Fe') }}</a></h6>
                                            <div class="d-flex align-items-center justify-content-between price">
                                                <span>{{ __('78 Pcs') }}</span>
                                                <p>$7847</p>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                            <div  class="tab_content" data-tab="headphone">
                                <div class="row">
                                    <div class="col-sm-6 col-md-6 col-lg-4 col-xl-3">
                                        <div class="product-info card">
                                            <a href="javascript:void(0);" class="pro-img">
                                                <img src="{{URL::asset('build/img/products/pos-product-05.png')}}" alt="Products">
                                                <span><i class="ti ti-circle-check-filled"></i></span>
                                            </a>
                                            <h6 class="cat-name"><a href="javascript:void(0);">{{ __('Headphones') }}</a></h6>
                                            <h6 class="product-name"><a href="javascript:void(0);">{{ __('Airpod 2') }}</a></h6>
                                            <div class="d-flex align-items-center justify-content-between price">
                                                <span>{{ __('47 Pcs') }}</span>
                                                <p>$5478</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-md-6 col-lg-4 col-xl-3">
                                        <div class="product-info card">
                                            <a href="javascript:void(0);" class="pro-img">
                                                <img src="{{URL::asset('build/img/products/pos-product-08.png')}}" alt="Products">
                                                <span><i data-feather="check" class="feather-16" ></i></span>
                                            </a>
                                            <h6 class="cat-name"><a href="javascript:void(0);">{{ __('Headphones') }}</a></h6>
                                            <h6 class="product-name"><a href="javascript:void(0);">{{ __('SWAGME') }}</a></h6>
                                            <div class="d-flex align-items-center justify-content-between price">
                                                <span>{{ __('14 Pcs') }}</span>
                                                <p>$6587</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div  class="tab_content" data-tab="shoe">
                                <div class="row">
                                    <div class="col-sm-6 col-md-6 col-lg-4 col-xl-3">
                                        <div class="product-info card">
                                            <a href="javascript:void(0);" class="pro-img">
                                                <img src="{{URL::asset('build/img/products/pos-product-04.png')}}" alt="Products">
                                                <span><i class="ti ti-circle-check-filled"></i></span>
                                            </a>
                                            <h6 class="cat-name"><a href="javascript:void(0);">{{ __('Shoes') }}</a></h6>
                                            <h6 class="product-name"><a href="javascript:void(0);">{{ __('Red Nike Angelo') }}</a></h6>
                                            <div class="d-flex align-items-center justify-content-between price">
                                                <span>{{ __('78 Pcs') }}</span>
                                                <p>$7800</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-md-6 col-lg-4 col-xl-3">
                                        <div class="product-info card">
                                            <a href="javascript:void(0);" class="pro-img">
                                                <img src="{{URL::asset('build/img/products/pos-product-06.png')}}" alt="Products">
                                                <span><i class="ti ti-circle-check-filled"></i></span>
                                            </a>
                                            <h6 class="cat-name"><a href="javascript:void(0);">{{ __('Shoes') }}</a></h6>
                                            <h6 class="product-name"><a href="javascript:void(0);">{{ __('Blue White OGR') }}</a></h6>
                                            <div class="d-flex align-items-center justify-content-between price">
                                                <span>{{ __('54 Pcs') }}</span>
                                                <p>$987</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-md-6 col-lg-4 col-xl-3">
                                        <div class="product-info card">
                                            <a href="javascript:void(0);" class="pro-img">
                                                <img src="{{URL::asset('build/img/products/pos-product-13.png')}}" alt="Products">
                                                <span><i class="ti ti-circle-check-filled"></i></span>
                                            </a>
                                            <h6 class="cat-name"><a href="javascript:void(0);">{{ __('Shoes') }}</a></h6>
                                            <h6 class="product-name"><a href="javascript:void(0);">{{ __('Green Nike Fe') }}</a></h6>
                                            <div class="d-flex align-items-center justify-content-between price">
                                                <span>{{ __('78 Pcs') }}</span>
                                                <p>$7847</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div  class="tab_content" data-tab="mobile">
                                <div class="row">
                                    <div class="col-sm-6 col-md-6 col-lg-4 col-xl-3">
                                        <div class="product-info card">
                                            <a href="javascript:void(0);" class="pro-img">
                                                <img src="{{URL::asset('build/img/products/pos-product-01.png')}}" alt="Products">
                                                <span><i class="ti ti-circle-check-filled"></i></span>
                                            </a>
                                            <h6 class="cat-name"><a href="javascript:void(0);">{{ __('Mobiles') }}</a></h6>
                                            <h6 class="product-name"><a href="javascript:void(0);">{{ __('IPhone 14 64GB') }}</a></h6>
                                            <div class="d-flex align-items-center justify-content-between price">
                                                <span>{{ __('30 Pcs') }}</span>
                                                <p>$15800</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-md-6 col-lg-4 col-xl-3">
                                        <div class="product-info card">
                                            <a href="javascript:void(0);" class="pro-img">
                                                <img src="{{URL::asset('build/img/products/pos-product-14.png')}}" alt="Products">
                                                <span><i class="ti ti-circle-check-filled"></i></span>
                                            </a>
                                            <h6 class="cat-name"><a href="javascript:void(0);">{{ __('Mobiles') }}</a></h6>
                                            <h6 class="product-name"><a href="javascript:void(0);">{{ __('Iphone 11') }}</a></h6>
                                            <div class="d-flex align-items-center justify-content-between price">
                                                <span>{{ __('14 Pcs') }}</span>
                                                <p>$3654</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div  class="tab_content" data-tab="watche">
                                <div class="row">
                                    <div class="col-sm-6 col-md-6 col-lg-4 col-xl-3">
                                        <div class="product-info card">
                                            <a href="javascript:void(0);" class="pro-img">
                                                <img src="{{URL::asset('build/img/products/pos-product-03.png')}}" alt="Products">
                                                <span><i class="ti ti-circle-check-filled"></i></span>
                                            </a>
                                            <h6 class="cat-name"><a href="javascript:void(0);">{{ __('Watches') }}</a></h6>
                                            <h6 class="product-name"><a href="javascript:void(0);">{{ __('Rolex Tribute V3') }}</a></h6>
                                            <div class="d-flex align-items-center justify-content-between price">
                                                <span>{{ __('220 Pcs') }}</span>
                                                <p>$6800</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-md-6 col-lg-4 col-xl-3">
                                        <div class="product-info card">
                                            <a href="javascript:void(0);" class="pro-img">
                                                <img src="{{URL::asset('build/img/products/pos-product-09.png')}}" alt="Products">
                                                <span><i class="ti ti-circle-check-filled"></i></span>
                                            </a>
                                            <h6 class="cat-name"><a href="javascript:void(0);">{{ __('Watches') }}</a></h6>
                                            <h6 class="product-name"><a href="javascript:void(0);">{{ __('Timex Black Silver') }}</a></h6>
                                            <div class="d-flex align-items-center justify-content-between price">
                                                <span>{{ __('24 Pcs') }}</span>
                                                <p>$1457</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-md-6 col-lg-4 col-xl-3">
                                        <div class="product-info card">
                                            <a href="javascript:void(0);" class="pro-img">
                                                <img src="{{URL::asset('build/img/products/pos-product-11.png')}}" alt="Products">
                                                <span><i class="ti ti-circle-check-filled"></i></span>
                                            </a>
                                            <h6 class="cat-name"><a href="javascript:void(0);">{{ __('Watches') }}</a></h6>
                                            <h6 class="product-name"><a href="javascript:void(0);">{{ __('Fossil Pair Of 3 in 1') }}</a></h6>
                                            <div class="d-flex align-items-center justify-content-between price">
                                                <span>{{ __('40 Pcs') }}</span>
                                                <p>$789</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div  class="tab_content" data-tab="laptop">
                                <div class="row">
                                    <div class="col-sm-6 col-md-6 col-lg-4 col-xl-3">
                                        <div class="product-info card">
                                            <a href="javascript:void(0);" class="pro-img">
                                                <img src="{{URL::asset('build/img/products/pos-product-02.png')}}" alt="Products">
                                                <span><i class="ti ti-circle-check-filled"></i></span>
                                            </a>
                                            <h6 class="cat-name"><a href="javascript:void(0);">{{ __('Computer') }}</a></h6>
                                            <h6 class="product-name"><a href="javascript:void(0);">{{ __('MacBook Pro') }}</a></h6>
                                            <div class="d-flex align-items-center justify-content-between price">
                                                <span>{{ __('140 Pcs') }}</span>
                                                <p>$1000</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-md-6 col-lg-4 col-xl-3">
                                        <div class="product-info card">
                                            <a href="javascript:void(0);" class="pro-img">
                                                <img src="{{URL::asset('build/img/products/pos-product-07.png')}}" alt="Products">
                                                <span><i class="ti ti-circle-check-filled"></i></span>
                                            </a>
                                            <h6 class="cat-name"><a href="javascript:void(0);">{{ __('Laptop') }}</a></h6>
                                            <h6 class="product-name"><a href="javascript:void(0);">{{ __('IdeaPad Slim 5 Gen 7') }}</a></h6>
                                            <div class="d-flex align-items-center justify-content-between price">
                                                <span>{{ __('74 Pcs') }}</span>
                                                <p>$1454</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-md-6 col-lg-4 col-xl-3">
                                        <div class="product-info card">
                                            <a href="javascript:void(0);" class="pro-img">
                                                <img src="{{URL::asset('build/img/products/pos-product-10.png')}}" alt="Products">
                                                <span><i class="ti ti-circle-check-filled"></i></span>
                                            </a>
                                            <h6 class="cat-name"><a href="javascript:void(0);">{{ __('Computer') }}</a></h6>
                                            <h6 class="product-name"><a href="javascript:void(0);">{{ __('Tablet 1.02 inch') }}</a></h6>
                                            <div class="d-flex align-items-center justify-content-between price">
                                                <span>{{ __('14 Pcs') }}</span>
                                                <p>$4744</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-md-6 col-lg-4 col-xl-3">
                                        <div class="product-info card">
                                            <a href="javascript:void(0);" class="pro-img">
                                                <img src="{{URL::asset('build/img/products/pos-product-13.png')}}" alt="Products">
                                                <span><i class="ti ti-circle-check-filled"></i></span>
                                            </a>
                                            <h6 class="cat-name"><a href="javascript:void(0);">{{ __('Laptop') }}</a></h6>
                                            <h6 class="product-name"><a href="javascript:void(0);">{{ __('Yoga Book 9i') }}</a></h6>
                                            <div class="d-flex align-items-center justify-content-between price">
                                                <span>{{ __('65 Pcs') }}</span>
                                                <p>$4784</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-md-6 col-lg-4 col-xl-3">
                                        <div class="product-info card">
                                            <a href="javascript:void(0);" class="pro-img">
                                                <img src="{{URL::asset('build/img/products/pos-product-14.png')}}" alt="Products">
                                                <span><i class="ti ti-circle-check-filled"></i></span>
                                            </a>
                                            <h6 class="cat-name"><a href="javascript:void(0);">{{ __('Laptop') }}</a></h6>
                                            <h6 class="product-name"><a href="javascript:void(0);">{{ __('IdeaPad Slim 3i') }}</a></h6>
                                            <div class="d-flex align-items-center justify-content-between price">
                                                <span>{{ __('47 Pcs') }}</span>
                                                <p>$1245</p>
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
                    <div class="order-head bg-light d-flex align-items-center justify-content-between w-100">
                        <div>
                            <h3>{{ __('Order List') }}</h3>
                            <span>{{ __('Transaction ID : #65565') }}</span>
                        </div>
                        <div>
                            <a class="link-danger fs-16" href="javascript:void(0);"><i class="ti ti-trash-x-filled"></i></a>
                        </div>
                    </div>
                    <div class="customer-info block-section">
                        <h4 class="mb-3">{{ __('Customer Information') }}</h4>
                        <div class="input-block d-flex align-items-center">
                            <div class="flex-grow-1">
                                <select class="select">
                                    <option>{{ __('Walk in Customer') }}</option>
                                    <option>{{ __('John') }}</option>
                                    <option>{{ __('Smith') }}</option>
                                    <option>{{ __('Ana') }}</option>
                                    <option>{{ __('Elza') }}</option>
                                </select>
                            </div>
                            <a href="#" class="btn btn-primary btn-icon" data-bs-toggle="modal" data-bs-target="#create"><i data-feather="user-plus" class="feather-16"></i></a>
                        </div>
                        <div class="input-block">
                            <select class="select">
                                <option>{{ __('Search Products') }}</option>
                                <option>{{ __('IPhone 14 64GB') }}</option>
                                <option>{{ __('MacBook Pro') }}</option>
                                <option>{{ __('Rolex Tribute V3') }}</option>
                                <option>{{ __('Red Nike Angelo') }}</option>
                                <option>{{ __('Airpod 2') }}</option>
                                <option>{{ __('Oldest') }}</option>
                            </select>
                        </div>
                    </div>

                    <div class="product-added block-section">
                        <div class="head-text d-flex align-items-center justify-content-between">
                            <h5 class="d-flex align-items-center mb-0">{{ __('Product Added') }}<span class="count">2</span></h5>
                            <a href="javascript:void(0);" class="d-flex align-items-center link-danger"><span class="me-2"><i data-feather="x" class="feather-16"></i></span>{{ __('Clear all') }}</a>
                        </div>
                        <div class="product-wrap">
                            <div class="empty-cart">
                                <div class="fs-24 mb-1">
                                    <i class="ti ti-shopping-cart"></i>
                                </div>
                                <p class="fw-bold">{{ __('No Products Selected') }}</p>
                            </div>
                            <div class="product-list align-items-center justify-content-between">
                                <div class="d-flex align-items-center product-info" data-bs-toggle="modal" data-bs-target="#products">
                                    <a href="javascript:void(0);" class="pro-img">
                                        <img src="{{URL::asset('build/img/products/pos-product-04.png')}}" alt="Products">
                                    </a>
                                    <div class="info">
                                        <span>{{ __('PT0005') }}</span>
                                        <h6><a href="javascript:void(0);">{{ __('Red Nike Laser') }}</a></h6>
                                        <p class="fw-bold text-teal">$2000</p>
                                    </div>
                                </div>
                                <div class="qty-item text-center">
                                    <a href="javascript:void(0);" class="dec d-flex justify-content-center align-items-center" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('minus') }}"><i data-feather="minus-circle" class="feather-14"></i></a>
                                    <input type="text" class="form-control text-center" name="qty" value="4">
                                    <a href="javascript:void(0);" class="inc d-flex justify-content-center align-items-center" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('plus') }}"><i data-feather="plus-circle" class="feather-14"></i></a>
                                </div>
                                <div class="d-flex align-items-center action">
                                    <a class="btn-icon edit-icon me-1" href="#" data-bs-toggle="modal"
                                        data-bs-target="#edit-product">
                                        <i data-feather="edit" class="feather-14"></i>
                                    </a>
                                    <a class="btn-icon delete-icon" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#delete">
                                        <i data-feather="trash-2" class="feather-14"></i>
                                    </a>
                                </div>
                            </div>
                            <div class="product-list align-items-center justify-content-between">
                                <div class="d-flex align-items-center product-info" data-bs-toggle="modal" data-bs-target="#products">
                                    <a href="javascript:void(0);" class="pro-img">
                                        <img src="{{URL::asset('build/img/products/pos-product-10.png')}}" alt="Products">
                                    </a>
                                    <div class="info">
                                        <span>{{ __('PT0235') }}</span>
                                        <h6><a href="javascript:void(0);">{{ __('Iphone 14') }}</a></h6>
                                        <p class="fw-bold text-teal">$3000</p>
                                    </div>
                                </div>
                                <div class="qty-item text-center">
                                    <a href="javascript:void(0);" class="dec d-flex justify-content-center align-items-center" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('minus') }}"><i data-feather="minus-circle" class="feather-14"></i></a>
                                    <input type="text" class="form-control text-center" name="qty" value="3">
                                    <a href="javascript:void(0);" class="inc d-flex justify-content-center align-items-center" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('plus') }}"><i data-feather="plus-circle" class="feather-14"></i></a>
                                </div>
                                <div class="d-flex align-items-center action">
                                    <a class="btn-icon edit-icon me-1" href="#" data-bs-toggle="modal" data-bs-target="#edit-product">
                                        <i data-feather="edit" class="feather-14"></i>
                                    </a>
                                    <a class="btn-icon delete-icon" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#delete">
                                        <i data-feather="trash-2" class="feather-14"></i>
                                    </a>
                                </div>
                            </div>
                            <div class="product-list align-items-center justify-content-between">
                                <div class="d-flex align-items-center product-info" data-bs-toggle="modal" data-bs-target="#products">
                                    <a href="javascript:void(0);" class="pro-img">
                                        <img src="{{URL::asset('build/img/products/pos-product-09.png')}}" alt="Products">
                                    </a>
                                    <div class="info">
                                        <span>{{ __('PT0242') }}</span>
                                        <h6><a href="javascript:void(0);">{{ __('Timex Black Silver') }}</a></h6>
                                        <p class="fw-bold text-teal">$1457</p>
                                    </div>
                                </div>
                                <div class="qty-item text-center">
                                    <a href="javascript:void(0);" class="dec d-flex justify-content-center align-items-center" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('minus') }}"><i data-feather="minus-circle" class="feather-14"></i></a>
                                    <input type="text" class="form-control text-center" name="qty" value="1">
                                    <a href="javascript:void(0);" class="inc d-flex justify-content-center align-items-center" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('plus') }}"><i data-feather="plus-circle" class="feather-14"></i></a>
                                </div>
                                <div class="d-flex align-items-center action">
                                    <a class="btn-icon edit-icon me-1" href="#" data-bs-toggle="modal"
                                        data-bs-target="#edit-product">
                                        <i data-feather="edit" class="feather-14"></i>
                                    </a>
                                    <a class="btn-icon delete-icon" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#delete">
                                        <i data-feather="trash-2" class="feather-14"></i>
                                    </a>
                                </div>
                            </div>
                            <div class="product-list align-items-center justify-content-between">
                                <div class="d-flex align-items-center product-info" data-bs-toggle="modal" data-bs-target="#products">
                                    <a href="javascript:void(0);" class="pro-img">
                                        <img src="{{URL::asset('build/img/products/pos-product-08.png')}}" alt="Products">
                                    </a>
                                    <div class="info">
                                        <span>{{ __('PT0005') }}</span>
                                        <h6><a href="javascript:void(0);">{{ __('SWAGME') }}</a></h6>
                                        <p class="fw-bold text-teal">$6587</p>
                                    </div>
                                </div>
                                <div class="qty-item text-center">
                                    <a href="javascript:void(0);" class="dec d-flex justify-content-center align-items-center" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('minus') }}"><i data-feather="minus-circle" class="feather-14"></i></a>
                                    <input type="text" class="form-control text-center" name="qty" value="1">
                                    <a href="javascript:void(0);" class="inc d-flex justify-content-center align-items-center" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('plus') }}"><i data-feather="plus-circle" class="feather-14"></i></a>
                                </div>
                                <div class="d-flex align-items-center action">
                                    <a class="btn-icon edit-icon me-1" href="#" data-bs-toggle="modal"
                                        data-bs-target="#edit-product">
                                        <i data-feather="edit" class="feather-14"></i>
                                    </a>
                                    <a class="btn-icon delete-icon" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#delete">
                                        <i data-feather="trash-2" class="feather-14"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="block-section">
                        <div class="selling-info">
                            <div class="row g-3">
                                <div class="col-12 col-sm-4">
                                    <div>
                                        <label class="form-label">{{ __('Order Tax') }}</label>
                                        <select class="select">
                                            <option>{{ __('Select') }}</option>
                                            <option>{{ __('GST 5%') }}</option>
                                            <option>{{ __('GST 10%') }}</option>
                                            <option>{{ __('GST 15%') }}</option>
                                            <option>{{ __('GST 20%') }}</option>
                                            <option>{{ __('GST 25%') }}</option>
                                            <option>{{ __('GST 30%') }}</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-12 col-sm-4">
                                    <div>
                                        <label class="form-label">{{ __('Shipping') }}</label>
                                        <select class="select">
                                            <option>0</option>
                                            <option>15</option>
                                            <option>20</option>
                                            <option>25</option>
                                            <option>30</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-12 col-sm-4">
                                    <div>
                                        <label class="form-label">{{ __('Discount') }}</label>
                                        <select class="select">
                                            <option>0%</option>
                                            <option>10%</option>
                                            <option>10%</option>
                                            <option>15%</option>
                                            <option>20%</option>
                                            <option>25%</option>
                                            <option>30%</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="order-total">
                            <table class="table table-responsive table-borderless">
                                <tr>
                                    <td>{{ __('Sub Total') }}</td>
                                    <td class="text-end">$60,454</td>
                                </tr>
                                <tr>
                                    <td>{{ __('Tax (GST 5%)') }}</td>
                                    <td class="text-end">$40.21</td>
                                </tr>
                                <tr>
                                    <td>{{ __('Shipping') }}</td>
                                    <td class="text-end">$40.21</td>
                                </tr>
                                <tr>
                                    <td>{{ __('Sub Total') }}</td>
                                    <td class="text-end">$60,454</td>
                                </tr>
                                <tr>
                                    <td class="text-danger">{{ __('Discount (10%)') }}</td>
                                    <td class="text-danger text-end">$15.21</td>
                                </tr>
                                <tr>
                                    <td>{{ __('Total') }}</td>
                                    <td class="text-end">$64,024.5</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <div class="block-section payment-method">
                        <h4>{{ __('Payment Method') }}</h4>
                        <div class="row align-items-center justify-content-center methods g-3">
                            <div class="col-sm-6 col-md-4">
                                <a href="javascript:void(0);" class="payment-item" data-bs-toggle="modal" data-bs-target="#payment-cash">
                                    <i class="ti ti-cash-banknote fs-18"></i>
                                    <span>{{ __('Cash') }}</span>
                                </a>
                            </div>
                            <div class="col-sm-6 col-md-4">
                                <a href="javascript:void(0);" class="payment-item" data-bs-toggle="modal" data-bs-target="#payment-card">
                                    <i class="ti ti-credit-card fs-18"></i>
                                    <span>{{ __('Debit Card') }}</span>
                                </a>
                            </div>
                            <div class="col-sm-6 col-md-4">
                                <a href="javascript:void(0);" class="payment-item" data-bs-toggle="modal" data-bs-target="#scan-payment">
                                    <i class="ti ti-scan fs-18"></i>
                                    <span>{{ __('Scan') }}</span>
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="btn-block">
                        <a class="btn btn-secondary w-100" href="javascript:void(0);">
                            Grand Total : $64,024.5
                        </a>
                    </div>
                    <div class="btn-row d-sm-flex align-items-center justify-content-between">
                        <a href="javascript:void(0);" class="btn btn-purple d-flex align-items-center justify-content-center flex-fill" data-bs-toggle="modal" data-bs-target="#hold-order"><i  class="ti ti-player-pause me-1"></i>{{ __('Hold') }}</a>
                        <a href="javascript:void(0);" class="btn btn-danger d-flex align-items-center justify-content-center flex-fill"><i  class="ti ti-trash me-1"></i>{{ __('Void') }}</a>
                        <a href="javascript:void(0);" class="btn btn-success d-flex align-items-center justify-content-center flex-fill" data-bs-toggle="modal" data-bs-target="#payment-completed"><i  class="ti ti-cash-banknote me-1"></i>{{ __('Payment') }}</a>
                    </div>
                </aside>
            </div>
            <!-- /Order Details -->

        </div>

</x-dashboard::layouts.master>
