<x-dashboard::layouts.master
    :title="'Product Report — ' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance"
    pageTitle="Product Report">

<div class="mb-4">
                <ul class="nav nav-pills">
                    <li class="nav-item">
                        <a class="nav-link active" href="{{ route('eshop360.reports.products', $instance->slug ?? '') }}">Product Report</a>
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
                        <form action="{{ route('eshop360.reports.products', $instance->slug ?? '') }}">
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
                                                <div class="col-lg-4 col-md-4 col-sm-6">
                                                    <div class="mb-3">
                                                        <label class="form-label">Category</label>
                                                        <select class="select" name="category">
                                                            <option value="">All</option>
                                                            @foreach($categories ?? [] as $cat)
                                                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-lg-4 col-md-4 col-sm-6">
                                                    <div class="mb-3">
                                                        <label class="form-label">Brand</label>
                                                        <select class="select" name="brand">
                                                            <option value="">All</option>
                                                            @foreach($brands ?? [] as $brand)
                                                                <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-lg-4 col-md-4 col-sm-6">
                                                    <div class="mb-3">
                                                        <label class="form-label">Product</label>
                                                        <select class="select" name="product">
                                                            <option value="">All</option>
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
                                    @forelse($products as $product)
                                    <tr>
                                        <td><a href="#">{{ $product->sku }}</a></td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <a href="#" class="avatar avatar-md">
                                                    @if($product->image)
                                                        <img src="{{ asset('storage/' . $product->image) }}" class="img-fluid" alt="img">
                                                    @else
                                                        <img src="{{URL::asset('build/img/products/stock-img-01.png')}}" class="img-fluid" alt="img">
                                                    @endif
                                                </a>
                                                <div class="ms-2">
                                                    <p class="text-dark mb-0"><a href="#">{{ $product->name }}</a></p>
                                                </div>
                                            </div>
                                        </td>
                                        <td>{{ $product->category->name ?? '—' }}</td>
                                        <td>{{ $product->brand->name ?? '—' }}</td>
                                        <td>{{ $product->stocks_sum_quantity ?? $product->stocks->sum('quantity') ?? 0 }}</td>
                                        <td>${{ number_format($product->price, 2) }}</td>
                                        <td>{{ $product->total_ordered ?? $product->orders_count ?? 0 }}</td>
                                        <td>${{ number_format($product->revenue ?? 0, 2) }}</td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="8" class="text-center">No products found.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        @if(method_exists($products, 'hasPages') && $products->hasPages())
                        <div class="p-3">
                            {{ $products->links() }}
                        </div>
                        @endif
                    </div>
                </div>
                <!-- /product list -->
            </div>

</x-dashboard::layouts.master>
