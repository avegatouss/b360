<x-dashboard::layouts.master
    :title="'Products — ' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance"
    pageTitle="Products">

<div class="page-header">
                <div class="add-item d-flex">
                    <div class="page-title">
                        <h4 class="fw-bold">Products</h4>
                        <h6>Manage your products</h6>
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
                <div class="page-btn">
                    <a href="{{ route('eshop360.products.create', $instance->slug ?? '') }}" class="btn btn-primary"><i class="ti ti-circle-plus me-1"></i>Add Product</a>
                </div>
                <div class="page-btn import">
                    <a href="#" class="btn btn-primary color" data-bs-toggle="modal" data-bs-target="#view-notes"><i
                        data-feather="download" class="me-2"></i>Import Product</a>
                </div>
            </div>

            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
                    <div class="search-set">
                        <div class="search-input">
                            <span class="btn-searchset"><i class="ti ti-search fs-14 feather-search"></i></span>
                        </div>
                    </div>
                    <div class="d-flex table-dropdown my-xl-auto right-content align-items-center flex-wrap row-gap-3">
                        <div class="dropdown me-2">
                            <a href="javascript:void(0);" class="dropdown-toggle btn btn-white btn-md d-inline-flex align-items-center" data-bs-toggle="dropdown">
                                Category
                            </a>
                            <ul class="dropdown-menu  dropdown-menu-end p-3">
                                @foreach($categories as $cat)
                                <li>
                                    <a href="javascript:void(0);" class="dropdown-item rounded-1">{{ $cat->name }}</a>
                                </li>
                                @endforeach
                            </ul>
                        </div>
                        <div class="dropdown me-2">
                            <a href="javascript:void(0);" class="dropdown-toggle btn btn-white btn-md d-inline-flex align-items-center" data-bs-toggle="dropdown">
                                Brand
                            </a>
                            <ul class="dropdown-menu  dropdown-menu-end p-3">
                                @foreach($brands as $brand)
                                <li>
                                    <a href="javascript:void(0);" class="dropdown-item rounded-1">{{ $brand->name }}</a>
                                </li>
                                @endforeach
                            </ul>
                        </div>
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
                                    <th>SKU</th>
                                    <th>Product</th>
                                    <th>Category</th>
                                    <th>Brand</th>
                                    <th>Price</th>
                                    <th>Unit</th>
                                    <th>Qty</th>
                                    <th>Created By</th>
                                    <th class="no-sort"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($products as $product)
                                <tr>
                                    <td>
                                        <label class="checkboxs">
                                            <input type="checkbox">
                                            <span class="checkmarks"></span>
                                        </label>
                                    </td>
                                    <td>{{ $product->sku }}</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <a href="{{ route('eshop360.products.show', [$instance->slug ?? '', $product]) }}" class="avatar avatar-md me-2">
                                                @if($product->image)
                                                    <img src="{{ asset('storage/' . $product->image) }}" alt="product">
                                                @else
                                                    <img src="{{ URL::asset('build/img/products/stock-img-01.png') }}" alt="product">
                                                @endif
                                            </a>
                                            <a href="{{ route('eshop360.products.show', [$instance->slug ?? '', $product]) }}">{{ $product->name }}</a>
                                        </div>
                                    </td>
                                    <td>{{ $product->category->name ?? '—' }}</td>
                                    <td>{{ $product->brand->name ?? '—' }}</td>
                                    <td>{{ number_format($product->price, 2) }}</td>
                                    <td>{{ $product->unit ?? 'Pc' }}</td>
                                    <td>{{ $product->stocks->sum('quantity') }}</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <a href="javascript:void(0);">{{ $product->creator->name ?? '—' }}</a>
                                        </div>
                                    </td>
                                    <td class="action-table-data">
                                        <div class="edit-delete-action">
                                            <a class="me-2 edit-icon p-2" href="{{ route('eshop360.products.show', [$instance->slug ?? '', $product]) }}">
                                                <i data-feather="eye" class="feather-eye"></i>
                                            </a>
                                            <a class="me-2 p-2" href="{{ route('eshop360.products.edit', [$instance->slug ?? '', $product]) }}">
                                                <i data-feather="edit" class="feather-edit"></i>
                                            </a>
                                            <form action="{{ route('eshop360.products.destroy', [$instance->slug ?? '', $product]) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="p-2 border-0 bg-transparent">
                                                    <i data-feather="trash-2" class="feather-trash-2"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="10" class="text-center">No products found.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($products->hasPages())
                    <div class="p-3">
                        {{ $products->links() }}
                    </div>
                    @endif
                </div>
            </div>
            <!-- /product list -->

</x-dashboard::layouts.master>
