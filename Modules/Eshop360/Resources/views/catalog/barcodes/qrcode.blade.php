<x-dashboard::layouts.master
    :title="__('Print QR Code') . ' —' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance"
    :pageTitle="__('Print QR Code')">

<div class="page-header">
            <div class="add-item d-flex">
                <div class="page-title">
                    <h4 class="fw-bold">{{ __('Print QR Code') }}</h4>
                    <h6>{{ __('Manage your QR code') }}</h6>
                </div>
            </div>
            <div class="d-flex align-items-center">
                <ul class="table-top-head">
                    <li>
                        <a data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('Refresh') }}"><i class="ti ti-refresh"></i></a>
                    </li>
                    <li>
                        <a data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('Collapse') }}" id="collapse-header"><i class="ti ti-chevron-up"></i></a>
                    </li>
                </ul>
            </div>
        </div>
        <div class="barcode-content-list">
            <form method="GET" action="{{ route('eshop360.barcodes.qrcode', $instance->slug ?? '') }}">
                <div class="row">
                    <div class="col-lg-6 col-12">
                        <div class="row seacrh-barcode-item">
                            <div class="col-sm-6 mb-3 seacrh-barcode-item-one">
                                <label class="form-label">{{ __('Warehouse') }}<span class="text-danger ms-1">*</span></label>
                                <select class="select" name="warehouse_id">
                                    <option value="">{{ __('Select') }}</option>
                                    @foreach($warehouses as $wh)
                                        <option value="{{ $wh->id }}" @selected(request('warehouse_id') == $wh->id)>{{ $wh->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-sm-6 mb-3 seacrh-barcode-item-one">
                                <label class="form-label">{{ __('Store') }}<span class="text-danger ms-1">*</span></label>
                                <select class="select" name="store_id">
                                    <option value="">{{ __('Select') }}</option>
                                    @foreach($stores as $store)
                                        <option value="{{ $store->id }}" @selected(request('store_id') == $store->id)>{{ $store->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-6">
                        <div class="search-form seacrh-barcode-item">
                            <div class="search-form">
                                <label class="form-label">{{ __('Product') }}<span class="text-danger ms-1">*</span></label>
                                <div class="position-relative">
                                    <input type="text" name="search" class="form-control" placeholder="{{ __('Search Product by Code') }}" value="{{ request('search') }}">
                                    <i data-feather="search" class="feather-search"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row mb-3 mt-2">
                    <div class="col-lg-6">
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="ti ti-search me-1"></i>{{ __('Search') }}
                        </button>
                    </div>
                </div>
            </form>

            <div class="col-lg-12">
                <div class="modal-body-table search-modal-header bg-light p-2 p-sm-4">
                    <div class="table-responsive rounded-1 qrcode-table">
                        <table class="table datatable">
                            <thead>
                                <tr>
                                    <th>{{ __('Product') }}</th>
                                    <th>{{ __('SKU') }}</th>
                                    <th>{{ __('Code') }}</th>
                                    <th>{{ __('Reference Number') }}</th>
                                    <th>{{ __('Qty') }}</th>
                                    <th class="text-center no-sort bg-secondary-transparent"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($products as $product)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <a href="javascript:void(0);" class="avatar avatar-md me-2">
                                                @if($product->image)
                                                    <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}">
                                                @else
                                                    <img src="{{ URL::asset('build/img/products/stock-img-02.png') }}" alt="product">
                                                @endif
                                            </a>
                                            <a href="javascript:void(0);">{{ $product->name }}</a>
                                        </div>
                                    </td>
                                    <td>{{ $product->sku }}</td>
                                    <td>{{ $product->qrcode ?? $product->barcode ?? '—' }}</td>
                                    <td>{{ $product->batch_number ?? '—' }}</td>
                                    <td>
                                        <div class="product-quantity">
                                            <span class="quantity-btn"><i data-feather="minus-circle" class="feather-search"></i></span>
                                            <input type="text" class="quntity-input" value="1">
                                            <span class="quantity-btn">+<i data-feather="plus-circle" class="plus-circle"></i></span>
                                        </div>
                                    </td>
                                    <td class="action-table-data justify-content-center">
                                        <div class="edit-delete-action">
                                            <a data-bs-toggle="modal" data-bs-target="#delete-modal" class="barcode-delete-icon" href="javascript:void(0);" data-product-id="{{ $product->id }}">
                                                <i data-feather="trash-2" class="feather-trash-2"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted">{{ __('No products found.') }}</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>

            <div class="paper-search-size">
                <div class="row align-items-center">
                    <div class="col-lg-6">
                        <form class="mb-0">
                            <label class="form-label">{{ __('Paper Size') }}<span class="text-danger ms-1">*</span></label>
                            <select class="select" id="paper-size">
                                <option value="">{{ __('Select') }}</option>
                                <option value="a4">{{ __('A4') }}</option>
                                <option value="a3">{{ __('A3') }}</option>
                                <option value="a5">{{ __('A5') }}</option>
                                <option value="a6">{{ __('A6') }}</option>
                            </select>
                        </form>
                    </div>
                    <div class="col-lg-6 pt-3">
                        <div class="row">
                            <div class="col-sm-4">
                                <div class="search-toggle-list">
                                    <p>{{ __('Reference Number') }}</p>
                                    <div class="m-0">
                                        <div class="status-toggle modal-status d-flex justify-content-between align-items-center">
                                            <input type="checkbox" id="user7" class="check" checked>
                                            <label for="user7" class="checktoggle mb-0"></label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="search-barcode-button">
                <a href="javascript:void(0);" class="btn btn-submit me-2 mt-0 fs-13 btn-primary shadow-none" data-bs-toggle="modal" data-bs-target="#prints-barcode">
                    <span><i class="fas fa-eye me-2"></i></span>
                    {{ __('Generate QR Code') }}</a>
                <a href="javascript:void(0);" class="btn btn-cancel me-2 fs-13 btn-secondary shadow-none" onclick="window.location.href='{{ route('eshop360.barcodes.qrcode', $instance->slug ?? '') }}'">
                    <span><i class="fas fa-power-off me-2"></i></span>
                    {{ __('Reset') }}</a>
                <a href="javascript:void(0);" class="btn btn-cancel close-btn fs-13 btn-danger shadow-none" onclick="document.getElementById('qr-print-batch-form').submit();">
                    <span><i class="fas fa-print me-2"></i></span>
                    {{ __('Print QRcode') }}</a>
            </div>
        </div>

{{-- Delete Confirmation Modal --}}
<div class="modal fade" id="delete-modal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">{{ __('Remove Product') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
            </div>
            <div class="modal-body">
                <p class="mb-0">{{ __('Are you sure you want to remove this product from the list?') }}</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal" id="confirm-delete-btn">{{ __('Remove') }}</button>
            </div>
        </div>
    </div>
</div>

{{-- Print QR Code Modal --}}
<div class="modal fade" id="prints-barcode" tabindex="-1" aria-labelledby="printsQrcodeLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="printsQrcodeLabel">{{ __('Print QR Code') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
            </div>
            <form id="qr-print-batch-form" method="POST" action="{{ route('eshop360.barcodes.print-batch', $instance->slug ?? '') }}">
                @csrf
                <input type="hidden" name="type" value="qrcode">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">{{ __('Select Products') }}</label>
                        <div class="border rounded p-3" style="max-height: 250px; overflow-y: auto;">
                            @forelse($products as $product)
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" name="product_ids[]" value="{{ $product->id }}" id="qr-product-{{ $product->id }}">
                                    <label class="form-check-label" for="qr-product-{{ $product->id }}">
                                        {{ $product->name }} <span class="text-muted">({{ $product->sku }})</span>
                                    </label>
                                </div>
                            @empty
                                <p class="text-muted mb-0">{{ __('No products available.') }}</p>
                            @endforelse
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">{{ __('Per Row') }}</label>
                            <select class="form-select" name="per_row">
                                <option value="2">2</option>
                                <option value="3" selected>3</option>
                                <option value="4">4</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">{{ __('Size') }}</label>
                            <select class="form-select" name="size">
                                <option value="small">{{ __('Small') }}</option>
                                <option value="medium" selected>{{ __('Medium') }}</option>
                                <option value="large">{{ __('Large') }}</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">{{ __('Quantity') }}</label>
                            <input type="number" class="form-control" name="quantity" value="1" min="1" max="100">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <div class="form-check mt-2">
                                <input class="form-check-input" type="checkbox" name="show_name" value="1" id="qr-show-name" checked>
                                <label class="form-check-label" for="qr-show-name">{{ __('Show Name') }}</label>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="form-check mt-2">
                                <input class="form-check-input" type="checkbox" name="show_price" value="1" id="qr-show-price" checked>
                                <label class="form-check-label" for="qr-show-price">{{ __('Show Price') }}</label>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="form-check mt-2">
                                <input class="form-check-input" type="checkbox" name="show_sku" value="1" id="qr-show-sku">
                                <label class="form-check-label" for="qr-show-sku">{{ __('Show SKU') }}</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-print me-1"></i>{{ __('Print QR Code') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

</x-dashboard::layouts.master>
