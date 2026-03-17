<x-dashboard::layouts.master
    :title="__('Product Details') . ' —' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance"
    :pageTitle="__('Product Details')">

<div class="page-header">
            <div class="page-title">
                <h4>{{ __('Product Details') }}</h4>
                <h6>{{ __('Full details of a product') }}</h6>
            </div>
        </div>
        <!-- /add -->
        <div class="row">
            <div class="col-lg-8 col-sm-12">
                <div class="card">
                    <div class="card-body">
                        <div class="bar-code-view">
                            @if($product->barcode)
                                <img src="{{ asset('storage/' . $product->barcode) }}" alt="barcode">
                            @else
                                <img src="{{URL::asset('build/img/barcode/barcode1.png')}}" alt="barcode">
                            @endif
                            <a class="printimg">
                                <img src="{{URL::asset('build/img/icons/printer.svg')}}" alt="print">
                            </a>
                        </div>
                        <div class="productdetails">
                            <ul class="product-bar">
                                <li>
                                    <h4>{{ __('Product') }}</h4>
                                    <h6>{{ $product->name }}</h6>
                                </li>
                                <li>
                                    <h4>{{ __('Category') }}</h4>
                                    <h6>{{ $product->category->name ?? 'None' }}</h6>
                                </li>
                                <li>
                                    <h4>{{ __('Sub Category') }}</h4>
                                    <h6>{{ $product->subcategory->name ?? 'None' }}</h6>
                                </li>
                                <li>
                                    <h4>{{ __('Brand') }}</h4>
                                    <h6>{{ $product->brand->name ?? 'None' }}</h6>
                                </li>
                                <li>
                                    <h4>{{ __('Unit') }}</h4>
                                    <h6>{{ $product->unit ?? 'Piece' }}</h6>
                                </li>
                                <li>
                                    <h4>{{ __('SKU') }}</h4>
                                    <h6>{{ $product->sku }}</h6>
                                </li>
                                <li>
                                    <h4>{{ __('Minimum Qty') }}</h4>
                                    <h6>{{ $product->min_quantity ?? 0 }}</h6>
                                </li>
                                <li>
                                    <h4>{{ __('Quantity') }}</h4>
                                    <h6>{{ $totalStock ?? $product->stocks->sum('quantity') }}</h6>
                                </li>
                                <li>
                                    <h4>{{ __('Tax') }}</h4>
                                    <h6>{{ number_format($product->tax ?? 0, 2) }} %</h6>
                                </li>
                                <li>
                                    <h4>{{ __('Discount Type') }}</h4>
                                    <h6>{{ ucfirst($product->discount_type ?? 'None') }}</h6>
                                </li>
                                <li>
                                    <h4>{{ __('Price') }}</h4>
                                    <h6>{{ number_format($product->price, 2) }}</h6>
                                </li>
                                <li>
                                    <h4>{{ __('Status') }}</h4>
                                    <h6>{{ $product->is_active ? 'Active' : 'Inactive' }}</h6>
                                </li>
                                <li>
                                    <h4>{{ __('Description') }}</h4>
                                    <h6>{{ $product->description ?? '—' }}</h6>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-sm-12">
                <div class="card">
                    <div class="card-body">
                        <div class="slider-product-details">
                            <div class="owl-carousel owl-theme product-slide">
                                @if($product->image)
                                <div class="slider-product">
                                    <img src="{{ asset('storage/' . $product->image) }}" alt="img">
                                    <h4>{{ basename($product->image) }}</h4>
                                </div>
                                @else
                                <div class="slider-product">
                                    <img src="{{URL::asset('build/img/products/product69.jpg')}}" alt="img">
                                    <h4>{{ __('No image') }}</h4>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- /add -->

</x-dashboard::layouts.master>
