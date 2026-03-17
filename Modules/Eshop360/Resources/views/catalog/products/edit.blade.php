<x-dashboard::layouts.master
    :title="__('Edit Product') . ' —' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance"
    :pageTitle="__('Edit Product')">

<div class="page-header">
                <div class="add-item d-flex">
                    <div class="page-title">
                        <h4 class="fw-bold">{{ __('Edit Product') }}</h4>
                        <h6>{{ __('Edit Your product') }}</h6>
                    </div>
                </div>
                <ul class="table-top-head">
                    <li>
                        <a data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('Refresh') }}"><i class="ti ti-refresh"></i></a>
                    </li>
                    <li>
                        <a data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('Collapse') }}" id="collapse-header"><i class="ti ti-chevron-up"></i></a>
                    </li>
                </ul>
                <div class="page-btn mt-0">
                    <a href="{{ route('eshop360.products.index', $instance->slug ?? '') }}" class="btn btn-secondary"><i data-feather="arrow-left" class="me-2"></i>Back to Product</a>
                </div>
            </div>
            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <form action="{{ route('eshop360.products.update', [$instance->slug ?? '', $product]) }}" method="POST" enctype="multipart/form-data" class="add-product-form">
                @csrf
                @method('PUT')
                <div class="add-product">
                    <div class="accordions-items-seperate" id="accordionSpacingExample">
                        <div class="accordion-item border mb-4">
                            <h2 class="accordion-header" id="headingSpacingOne">
                                <div class="accordion-button collapsed bg-white" data-bs-toggle="collapse" data-bs-target="#SpacingOne" aria-expanded="true" aria-controls="SpacingOne">
                                    <div class="d-flex align-items-center justify-content-between flex-fill">
                                    <h5 class="d-flex align-items-center"><i data-feather="info" class="text-primary me-2"></i><span>{{ __('Product Information') }}</span></h5>
                                    </div>
                                </div>
                            </h2>
                            <div id="SpacingOne" class="accordion-collapse collapse show" aria-labelledby="headingSpacingOne">
                                <div class="accordion-body border-top">
                                    <div class="row">
                                        <div class="col-sm-6 col-12">
                                            <div class="mb-3">
                                                <label class="form-label">{{ __('Store') }}<span class="text-danger ms-1">*</span></label>
                                                <select class="select" name="store_id">
                                                    <option value="">{{ __('Select') }}</option>
                                                    @foreach($stores ?? [] as $store)
                                                        <option value="{{ $store->id }}" {{ old('store_id', $product->store_id) == $store->id ? 'selected' : '' }}>{{ $store->name }}</option>
                                                    @endforeach
                                                </select>
                                                @error('store_id') <small class="text-danger">{{ $message }}</small> @enderror
                                            </div>
                                        </div>
                                        <div class="col-sm-6 col-12">
                                            <div class="mb-3">
                                                <label class="form-label">{{ __('Warehouse') }}<span class="text-danger ms-1">*</span></label>
                                                <select class="select" name="warehouse_id">
                                                    <option value="">{{ __('Select') }}</option>
                                                    @foreach($warehouses ?? [] as $warehouse)
                                                        <option value="{{ $warehouse->id }}" {{ old('warehouse_id', $product->warehouse_id) == $warehouse->id ? 'selected' : '' }}>{{ $warehouse->name }}</option>
                                                    @endforeach
                                                </select>
                                                @error('warehouse_id') <small class="text-danger">{{ $message }}</small> @enderror
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-6 col-12">
                                            <div class="mb-3">
                                                <label class="form-label">{{ __('Product Name') }}<span class="text-danger ms-1">*</span></label>
                                                <input type="text" class="form-control" name="name" value="{{ old('name', $product->name) }}">
                                                @error('name') <small class="text-danger">{{ $message }}</small> @enderror
                                            </div>
                                        </div>
                                        <div class="col-sm-6 col-12">
                                            <div class="mb-3">
                                                <label class="form-label">{{ __('Slug') }}<span class="text-danger ms-1">*</span></label>
                                                <input type="text" class="form-control" name="slug" value="{{ old('slug', $product->slug) }}">
                                                @error('slug') <small class="text-danger">{{ $message }}</small> @enderror
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-6 col-12">
                                            <div class="mb-3 list position-relative">
                                                <label class="form-label">{{ __('SKU') }}<span class="text-danger ms-1">*</span></label>
                                                <input type="text" class="form-control list" name="sku" value="{{ old('sku', $product->sku) }}">
                                                <button type="submit" class="btn btn-primaryadd">
                                                    Generate
                                                </button>
                                            </div>
                                        </div>
                                        <div class="col-sm-6 col-12">
                                            <div class="mb-3">
                                                <label class="form-label">{{ __('Selling Type') }}<span class="text-danger ms-1">*</span></label>
                                                <select class="select" name="selling_type">
                                                    <option value="">{{ __('Select') }}</option>
                                                    <option value="online" {{ old('selling_type', $product->selling_type) == 'online' ? 'selected' : '' }}>Online</option>
                                                    <option value="pos" {{ old('selling_type', $product->selling_type) == 'pos' ? 'selected' : '' }}>POS</option>
                                                </select>
                                                @error('selling_type') <small class="text-danger">{{ $message }}</small> @enderror
                                            </div>
                                        </div>
                                    </div>
                                    <div class="addservice-info">
                                        <div class="row">
                                            <div class="col-sm-6 col-12">
                                                <div class="mb-3">
                                                    <div class="add-newplus">
                                                        <label class="form-label">{{ __('Category') }}<span class="text-danger ms-1">*</span></label>
                                                        <a href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#add-product-category"><i data-feather="plus-circle" class="plus-down-add"></i><span>Add
                                                                New</span></a>
                                                    </div>
                                                    <select class="select" name="category_id">
                                                        <option value="">{{ __('Select') }}</option>
                                                        @foreach($categories as $cat)
                                                            <option value="{{ $cat->id }}" {{ old('category_id', $product->category_id) == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                                        @endforeach
                                                    </select>
                                                    @error('category_id') <small class="text-danger">{{ $message }}</small> @enderror
                                                </div>
                                            </div>
                                            <div class="col-sm-6 col-12">
                                                <div class="mb-3">
                                                    <label class="form-label">{{ __('Sub Category') }}<span class="text-danger ms-1">*</span></label>
                                                    <select class="select" name="sub_category_id">
                                                        <option value="">{{ __('Select') }}</option>
                                                        @foreach($subCategories ?? [] as $subCat)
                                                            <option value="{{ $subCat->id }}" {{ old('sub_category_id', $product->sub_category_id) == $subCat->id ? 'selected' : '' }}>{{ $subCat->name }}</option>
                                                        @endforeach
                                                    </select>
                                                    @error('sub_category_id') <small class="text-danger">{{ $message }}</small> @enderror
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="add-product-new">
                                        <div class="row">
                                            <div class="col-sm-6 col-12">
                                                <div class="mb-3">
                                                    <div class="add-newplus">
                                                        <label class="form-label">{{ __('Brand') }}<span class="text-danger ms-1">*</span></label>
                                                    </div>
                                                    <select class="select" name="brand_id">
                                                        <option value="">{{ __('Select') }}</option>
                                                        @foreach($brands as $brand)
                                                            <option value="{{ $brand->id }}" {{ old('brand_id', $product->brand_id) == $brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
                                                        @endforeach
                                                    </select>
                                                    @error('brand_id') <small class="text-danger">{{ $message }}</small> @enderror
                                                </div>
                                            </div>
                                            <div class="col-sm-6 col-12">
                                                <div class="mb-3">
                                                    <div class="add-newplus">
                                                        <label class="form-label">{{ __('Unit') }}<span class="text-danger ms-1">*</span></label>
                                                    </div>
                                                    <select class="select" name="unit">
                                                        <option value="">{{ __('Select') }}</option>
                                                        <option value="kg" {{ old('unit', $product->unit) == 'kg' ? 'selected' : '' }}>Kg</option>
                                                        <option value="pcs" {{ old('unit', $product->unit) == 'pcs' ? 'selected' : '' }}>Pcs</option>
                                                        <option value="l" {{ old('unit', $product->unit) == 'l' ? 'selected' : '' }}>L</option>
                                                        <option value="dz" {{ old('unit', $product->unit) == 'dz' ? 'selected' : '' }}>dz</option>
                                                        <option value="bx" {{ old('unit', $product->unit) == 'bx' ? 'selected' : '' }}>bx</option>
                                                    </select>
                                                    @error('unit') <small class="text-danger">{{ $message }}</small> @enderror
                                                </div>
                                            </div>
                                        </div>
                                    </div>			
                                    <div class="row">
                                        <div class="col-sm-6">
                                            <div class="mb-3">
                                                <div class="add-newplus">
                                                    <label class="form-label">{{ __('Barcode Symbology') }}<span class="text-danger ms-1">*</span></label>
                                                </div>
                                                <select class="select" name="barcode_symbology">
                                                    <option value="">{{ __('Select') }}</option>
                                                    <option value="code128" {{ old('barcode_symbology', $product->barcode_symbology) == 'code128' ? 'selected' : '' }}>Code 128</option>
                                                    <option value="code39" {{ old('barcode_symbology', $product->barcode_symbology) == 'code39' ? 'selected' : '' }}>Code 39</option>
                                                    <option value="upca" {{ old('barcode_symbology', $product->barcode_symbology) == 'upca' ? 'selected' : '' }}>UPC-A</option>
                                                    <option value="upce" {{ old('barcode_symbology', $product->barcode_symbology) == 'upce' ? 'selected' : '' }}>UPC-E</option>
                                                    <option value="ean8" {{ old('barcode_symbology', $product->barcode_symbology) == 'ean8' ? 'selected' : '' }}>EAN-8</option>
                                                    <option value="ean13" {{ old('barcode_symbology', $product->barcode_symbology) == 'ean13' ? 'selected' : '' }}>EAN-13</option>
                                                </select>
                                                @error('barcode_symbology') <small class="text-danger">{{ $message }}</small> @enderror
                                            </div>
                                        </div>
                                        <div class="col-lg-6 col-sm-6 col-12">
                                            <div class="mb-3 list position-relative">
                                                <label class="form-label">{{ __('Item Code') }}<span class="text-danger ms-1">*</span></label>
                                                <input type="text" class="form-control list" name="barcode" value="{{ old('barcode', $product->barcode) }}">
                                                <button type="button" class="btn btn-primaryadd">
                                                    Generate
                                                </button>
                                                @error('barcode') <small class="text-danger">{{ $message }}</small> @enderror
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Editor -->
                                    <div class="col-lg-12">
                                        <div class="summer-description-box">
                                            <label class="form-label">{{ __('Description') }}</label>
                                            <textarea name="description" id="summernote" class="summernote">{{ old('description', $product->description) }}</textarea>
                                            <p class="fs-14 mt-1">{{ __('Maximum 60 Words') }}</p>
                                            @error('description') <small class="text-danger">{{ $message }}</small> @enderror
                                        </div>
                                    </div>
                                    <!-- /Editor -->
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item border mb-4"> 
                            <h2 class="accordion-header" id="headingSpacingTwo">
                                <div class="accordion-button collapsed bg-white" data-bs-toggle="collapse" data-bs-target="#SpacingTwo" aria-expanded="true" aria-controls="SpacingTwo">
                                    <div class="d-flex align-items-center justify-content-between flex-fill">
                                    <h5 class="d-flex align-items-center"><i data-feather="life-buoy" class="text-primary me-2"></i><span>{{ __('Pricing & Stocks') }}</span></h5>
                                    </div>
                                </div>
                            </h2>
                            <div id="SpacingTwo" class="accordion-collapse collapse show" aria-labelledby="headingSpacingTwo">
                                <div class="accordion-body border-top">
                                    <div class="mb-3s">
                                        <label class="form-label">{{ __('Product Type') }}<span class="text-danger ms-1">*</span></label>
                                        <div class="single-pill-product mb-3">
                                            <ul class="nav nav-pills" id="pills-tab1" role="tablist">
                                                <li class="nav-item" role="presentation">
                                                    <span class="custom_radio me-4 mb-0 active" id="pills-home-tab" data-bs-toggle="pill"
                                                    data-bs-target="#pills-home"  role="tab" aria-controls="pills-home" aria-selected="true">
                                                        <input type="radio" class="form-control" name="payment">
                                                    <span class="checkmark"></span>{{ __('Single Product') }}</span>
                                                </li>
                                                <li class="nav-item" role="presentation">
                                                    <span  class="custom_radio me-2 mb-0" id="pills-profile-tab" data-bs-toggle="pill"
                                                    data-bs-target="#pills-profile"  role="tab" aria-controls="pills-profile" aria-selected="false">
                                                    <input type="radio" class="form-control" name="sign">
                                                    <span class="checkmark"></span>{{ __('Variable Product') }}</span>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                    <div class="tab-content" id="pills-tabContent">
                                        <div class="tab-pane fade show active" id="pills-home" role="tabpanel"
                                            aria-labelledby="pills-home-tab">
                                            <div class="single-product">
                                            <div class="row">
                                                <div class="col-lg-4 col-sm-6 col-12">
                                                    <div class="mb-3">
                                                        <label class="form-label">{{ __('Quantity') }}<span class="text-danger ms-1">*</span></label>
                                                        <input type="number" class="form-control" name="quantity" value="{{ old('quantity', $product->quantity) }}">
                                                        @error('quantity') <small class="text-danger">{{ $message }}</small> @enderror
                                                    </div>
                                                </div>
                                                <div class="col-lg-4 col-sm-6 col-12">
                                                    <div class="mb-3">
                                                        <label class="form-label">{{ __('Price') }}<span class="text-danger ms-1">*</span></label>
                                                        <input type="number" step="0.01" class="form-control" name="price" value="{{ old('price', $product->price) }}">
                                                        @error('price') <small class="text-danger">{{ $message }}</small> @enderror
                                                    </div>
                                                </div>
                                                <div class="col-lg-4 col-sm-6 col-12">
                                                    <div class="mb-3">
                                                        <label class="form-label">{{ __('Tax Type') }}<span class="text-danger ms-1">*</span></label>
                                                        <select class="select" name="tax_type">
                                                            <option value="">{{ __('Select') }}</option>
                                                            <option value="exclusive" {{ old('tax_type', $product->tax_type) == 'exclusive' ? 'selected' : '' }}>Exclusive</option>
                                                            <option value="inclusive" {{ old('tax_type', $product->tax_type) == 'inclusive' ? 'selected' : '' }}>Inclusive</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-lg-4 col-sm-6 col-12">
                                                    <div class="mb-3">
                                                        <label class="form-label">{{ __('Product Tax') }}<span class="text-danger ms-1">*</span></label>
                                                        <select class="select" name="tax_rate">
                                                            <option value="">{{ __('Select') }}</option>
                                                            @foreach($taxRates ?? [] as $rate)
                                                                <option value="{{ $rate->id ?? $rate }}" {{ old('tax_rate', $product->tax_rate) == ($rate->id ?? $rate) ? 'selected' : '' }}>{{ $rate->name ?? $rate }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="col-lg-4 col-sm-6 col-12">
                                                    <div class="mb-3">
                                                        <label class="form-label">{{ __('Discount Type') }}<span class="text-danger ms-1">*</span></label>
                                                        <select class="select" name="discount_type">
                                                            <option value="">{{ __('Select') }}</option>
                                                            <option value="percentage" {{ old('discount_type', $product->discount_type) == 'percentage' ? 'selected' : '' }}>Percentage</option>
                                                            <option value="fixed" {{ old('discount_type', $product->discount_type) == 'fixed' ? 'selected' : '' }}>Fixed</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-lg-4 col-sm-6 col-12">
                                                    <div class="mb-3">
                                                        <label class="form-label">{{ __('Discount Value') }}<span class="text-danger ms-1">*</span></label>
                                                        <input class="form-control" type="number" step="0.01" name="discount_value" value="{{ old('discount_value', $product->discount_value) }}">
                                                        @error('discount_value') <small class="text-danger">{{ $message }}</small> @enderror
                                                    </div>
                                                </div>
                                                <div class="col-lg-4 col-sm-6 col-12">
                                                    <div class="mb-3">
                                                        <label class="form-label">{{ __('Quantity Alert') }}<span class="text-danger ms-1">*</span></label>
                                                        <input type="number" class="form-control" name="quantity_alert" value="{{ old('quantity_alert', $product->quantity_alert) }}">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>		
                                        </div>
                                        <div class="tab-pane fade" id="pills-profile" role="tabpanel"
                                        aria-labelledby="pills-profile-tab">
                                        <div class="row select-color-add">
                                            <div class="col-lg-6 col-sm-6 col-12">
                                                <div class="mb-3">
                                                    <label class="form-label">{{ __('Variant Attribute') }}<span class="text-danger ms-1">*</span></label>
                                                    <div class="row">
                                                        <div class="col-lg-10 col-sm-10 col-10">
                                                            <select class="form-control variant-select select-option" id="colorSelect">
                                                                <option >{{ __('Choose') }}</option>
                                                                <option >{{ __('Color') }}</option>
                                                                <option value="red" >{{ __('Red') }}</option>
                                                                <option value="black">{{ __('Black') }}</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-lg-2 col-sm-2 col-2 ps-0">
                                                            <div class="add-icon tab">
                                                                <a class="btn btn-filter" data-bs-toggle="modal"
                                                                    data-bs-target="#add-variant-attribute"><i class="feather feather-plus-circle"></i></a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="selected-hide-color" id="input-show">
                                                    <label class="form-label">{{ __('Variant Attribute') }}<span class="text-danger ms-1">*</span></label>
                                                    <div class="row align-items-center" >
                                                        <div class="col-lg-10 col-sm-10 col-10">
                                                            <div class="mb-3">
                                                                <input class="input-tags form-control" id="inputBox" type="text" data-role="tagsinput"  name="specialist" value="red, black" >
                                                            </div>
                                                        </div>
                                                        <div class="col-lg-2 col-sm-2 col-2 ps-0">
                                                            <div class="mb-3 ">
                                                                <a href="javascript:void(0);" class="remove-color"><i class="far fa-trash-alt"></i></a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="modal-body-table border" id="variant-table">
                                            <div class="table-responsive">
                                                <table class="table border">
                                                    <thead>
                                                        <tr>
                                                            <th>{{ __('Variantion') }}</th>
                                                            <th>{{ __('Variant Value') }}</th>
                                                            <th>{{ __('SKU') }}</th>
                                                            <th>{{ __('Quantity') }}</th>
                                                            <th>{{ __('Price') }}</th>
                                                            <th class="no-sort"></th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr>
                                                            <td>
                                                                <div class="add-product">
                                                                    <input type="text" class="form-control" value="color">
                                                                </div>												
                                                            </td>
                                                            <td>
                                                                <div class="add-product">
                                                                    <input type="text" class="form-control" value="red">
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <div class="add-product">
                                                                    <input type="text" class="form-control" value="1234">
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <div class="product-quantity">
                                                                    <span class="quantity-btn"><i data-feather="minus-circle" class="feather-search"></i></span>
                                                                    <input type="text" class="quntity-input form-control" value="2">
                                                                    <span class="quantity-btn">+<i data-feather="plus-circle" class="plus-circle"></i></span>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <div class="add-product">
                                                                    <input type="text" class="form-control" value="50000">
                                                                </div>
                                                            </td>
                                                            <td class="action-table-data">
                                                                <div class="edit-delete-action">
                                                                    <div class="input-block add-lists">
                                                                        <label class="checkboxs">
                                                                            <input type="checkbox" checked>
                                                                            <span class="checkmarks"></span>
                                                                        </label>
                                                                    </div>
                                                                    <a class="me-2 p-2" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#add-variation">
                                                                        <i data-feather="plus" class="feather-edit"></i>
                                                                    </a>
                                                                    <a data-bs-toggle="modal" data-bs-target="#delete-modal" class="p-2" href="javascript:void(0);">
                                                                        <i data-feather="trash-2" class="feather-trash-2"></i>
                                                                    </a>
                                                                </div>
                                                                
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td>
                                                                <div class="add-product">
                                                                    <input type="text" class="form-control" value="color">
                                                                </div>												
                                                            </td>
                                                            <td>
                                                                <div class="add-product">
                                                                    <input type="text" class="form-control" value="black">
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <div class="add-product">
                                                                    <input type="text" class="form-control" value="2345">
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <div class="product-quantity">
                                                                    <span class="quantity-btn"><i data-feather="minus-circle" class="feather-search"></i></span>
                                                                    <input type="text" class="quntity-input form-control" value="3">
                                                                    <span class="quantity-btn">+<i data-feather="plus-circle" class="plus-circle"></i></span>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <div class="add-product">
                                                                    <input type="text" class="form-control" value="50000">
                                                                </div>
                                                            </td>
                                                            <td class="action-table-data">
                                                                <div class="edit-delete-action">
                                                                    <div class="input-block add-lists">
                                                                        <label class="checkboxs">
                                                                            <input type="checkbox" checked>
                                                                            <span class="checkmarks"></span>
                                                                        </label>
                                                                    </div>
                                                                    <a class="me-2 p-2" href="#" data-bs-toggle="modal" data-bs-target="#add-variation">
                                                                        <i data-feather="plus" class="feather-edit"></i>
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
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item border mb-4">
                            <h2 class="accordion-header" id="headingSpacingThree">
                                <div class="accordion-button collapsed bg-white" data-bs-toggle="collapse" data-bs-target="#SpacingThree" aria-expanded="true" aria-controls="SpacingThree">
                                    <div class="d-flex align-items-center justify-content-between flex-fill">
                                    <h5 class="d-flex align-items-center"><i data-feather="image" class="text-primary me-2"></i><span>{{ __('Images') }}</span></h5>
                                    </div>
                                </div>
                            </h2>
                            <div id="SpacingThree" class="accordion-collapse collapse show" aria-labelledby="headingSpacingThree">
                                <div class="accordion-body border-top">
                                    <div class="text-editor add-list add">
                                        <div class="col-lg-12">
                                            <div class="add-choosen">
                                                <div class="mb-3">
                                                <div class="image-upload image-upload-two">
                                                        <input type="file" name="images[]" multiple>
                                                        <div class="image-uploads">
                                                            <i data-feather="plus-circle" class="plus-down-add me-0"></i>
                                                            <h4>{{ __('Add Images') }}</h4>
                                                        </div>
                                                    </div>
                                                    @error('images') <small class="text-danger">{{ $message }}</small> @enderror
                                                    @error('images.*') <small class="text-danger">{{ $message }}</small> @enderror
                                                </div>
                                                @if($product->image)
                                                <div class="phone-img">
                                                    <img src="{{ asset('storage/' . $product->image) }}" alt="image">
                                                    <a href="javascript:void(0);"><i data-feather="x" class="x-square-add remove-product"></i></a>
                                                </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item border mb-4">
                            <h2 class="accordion-header" id="headingSpacingFour">
                                <div class="accordion-button collapsed bg-white" data-bs-toggle="collapse" data-bs-target="#SpacingFour" aria-expanded="true" aria-controls="SpacingFour">
                                    <div class="d-flex align-items-center justify-content-between flex-fill">
                                    <h5 class="d-flex align-items-center"><i data-feather="list" class="text-primary me-2"></i><span>{{ __('Custom Fields') }}</span></h5>
                                    </div>
                                </div>
                            </h2>
                            <div id="SpacingFour" class="accordion-collapse collapse show" aria-labelledby="headingSpacingFour">
                                <div class="accordion-body border-top">
                                    <div>
                                        <div class="p-3 bg-light rounded d-flex align-items-center border mb-3">
                                            <div class=" d-flex align-items-center">
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="checkbox" id="warranties" value="option1">
                                                    <label class="form-check-label" for="warranties">{{ __('Warranties') }}</label>
                                                </div>
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="checkbox" id="manufacturer" value="option2">
                                                    <label class="form-check-label" for="manufacturer">{{ __('Manufacturer') }}</label>
                                                </div>
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="checkbox" id="expiry" value="option2">
                                                    <label class="form-check-label" for="expiry">{{ __('Expiry') }}</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-sm-6 col-12">
                                                <div class="mb-3">
                                                    <label class="form-label">{{ __('Warranty') }}<span class="text-danger ms-1">*</span></label>
                                                    <select class="select">
                                                        <option>{{ __('Select') }}</option>
                                                        <option selected>{{ __('Replacement Warranty') }}</option>
                                                        <option>{{ __('On-Site Warranty') }}</option>
                                                        <option>{{ __('Accidental Protection Plan') }}</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-sm-6 col-12">
                                                <div class="mb-3 add-product">
                                                    <label class="form-label">{{ __('Manufacturer') }}<span class="text-danger ms-1">*</span></label>
                                                    <input type="text" class="form-control" name="manufacturer" value="{{ old('manufacturer', $product->manufacturer) }}">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-sm-6 col-12">
                                                <div class="mb-3">
                                                    <label class="form-label">{{ __('Manufactured Date') }}<span class="text-danger ms-1">*</span></label>

                                                    <div class="input-groupicon calender-input">
                                                        <i data-feather="calendar" class="info-img"></i>
                                                        <input type="text" class="datetimepicker form-control" name="manufactured_date" value="{{ old('manufactured_date', $product->manufactured_date) }}">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-sm-6 col-12">
                                                <div class="mb-3">
                                                    <label class="form-label">{{ __('Expiry On') }}<span class="text-danger ms-1">*</span></label>

                                                    <div class="input-groupicon calender-input">
                                                        <i data-feather="calendar" class="info-img"></i>
                                                        <input type="text" class="datetimepicker form-control" name="expiry_date" value="{{ old('expiry_date', $product->expiry_date) }}">
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
                {{-- ─── Section Tarification SAPHIR ──────────────────────────── --}}
                <div class="accordions-items-seperate mb-4">
                    <div class="accordion-item border mb-4">
                        <h2 class="accordion-header" id="headingSpacingSaphir">
                            <div class="accordion-button collapsed bg-white" data-bs-toggle="collapse"
                                 data-bs-target="#SpacingSaphir" aria-expanded="false" aria-controls="SpacingSaphir">
                                <span class="fw-bold">{{ __('Tarification SAPHIR & Données Pharmaceutiques') }}</span>
                            </div>
                        </h2>
                        <div id="SpacingSaphir" class="accordion-collapse collapse" aria-labelledby="headingSpacingSaphir">
                            <div class="accordion-body border-top">
                                <h6 class="text-muted mb-3">{{ __('Niveaux de prix') }}</h6>
                                <div class="row g-3 mb-4">
                                    <div class="col-md-3">
                                        <label class="form-label">{{ __('Prix d\'achat usine (PA Usine)') }}</label>
                                        <input type="number" step="0.0001" class="form-control" name="purchase_price_factory"
                                               value="{{ old('purchase_price_factory', $product->purchase_price_factory) }}" placeholder="0.0000">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">{{ __('PGHT') }}</label>
                                        <input type="number" step="0.0001" class="form-control" name="pght"
                                               value="{{ old('pght', $product->pght) }}" placeholder="0.0000">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">{{ __('Prix grossiste') }}</label>
                                        <input type="number" step="0.01" class="form-control" name="wholesale_price"
                                               value="{{ old('wholesale_price', $product->wholesale_price) }}" placeholder="0.00">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">{{ __('Prix pharmacie') }}</label>
                                        <input type="number" step="0.01" class="form-control" name="pharmacy_price"
                                               value="{{ old('pharmacy_price', $product->pharmacy_price) }}" placeholder="0.00">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">{{ __('Prix vente CODIFARM') }}</label>
                                        <input type="number" step="0.0001" class="form-control" name="sale_price_codifarm"
                                               value="{{ old('sale_price_codifarm', $product->sale_price_codifarm) }}" placeholder="0.0000">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">{{ __('Qté min. grossiste') }}</label>
                                        <input type="number" min="1" class="form-control" name="min_qty_wholesale"
                                               value="{{ old('min_qty_wholesale', $product->min_qty_wholesale ?? 1) }}">
                                    </div>
                                </div>
                                <h6 class="text-muted mb-3">{{ __('Données pharmaceutiques') }}</h6>
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label">{{ __('DCI (Dénomination Commune Internationale)') }}</label>
                                        <input type="text" class="form-control" name="dci"
                                               value="{{ old('dci', $product->dci) }}" placeholder="Ex: Amoxicilline">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">{{ __('Dosage') }}</label>
                                        <input type="text" class="form-control" name="dosage"
                                               value="{{ old('dosage', $product->dosage) }}" placeholder="Ex: 500mg">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">{{ __('Forme galénique') }}</label>
                                        <select class="form-select" name="form">
                                            <option value="">{{ __('— Sélectionner —') }}</option>
                                            @foreach(['Comprimé','Gélule','Sirop','Injectable','Suppositoire','Pommade','Crème','Gouttes','Spray','Patch','Sachet','Autre'] as $f)
                                                <option value="{{ $f }}" {{ old('form', $product->form) == $f ? 'selected' : '' }}>{{ $f }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">{{ __('Conditionnement') }}</label>
                                        <input type="text" class="form-control" name="packaging"
                                               value="{{ old('packaging', $product->packaging) }}" placeholder="Ex: Boite de 20">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">{{ __('N° de lot') }}</label>
                                        <input type="text" class="form-control" name="batch_number"
                                               value="{{ old('batch_number', $product->batch_number) }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-12">
                    <div class="d-flex align-items-center justify-content-end mb-4">
                        <button type="button" class="btn btn-secondary me-2">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn btn-primary">{{ __('Save Changes') }}</button>
                    </div>
                </div>
            </form>

</x-dashboard::layouts.master>
