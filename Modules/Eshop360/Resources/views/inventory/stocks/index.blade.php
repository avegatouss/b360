<x-dashboard::layouts.master
    :title="__('Manage Stocks') . ' —' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance"
    :pageTitle="__('Manage Stocks')">

<div class="page-header">
            <div class="add-item d-flex">
                <div class="page-title">
                    <h4>{{ __('Manage Stock') }}</h4>
                    <h6>{{ __('Manage your stock') }}</h6>
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
                    <a data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('Refresh') }}"><i class="ti ti-refresh"></i></a>
                </li>
                <li>
                    <a data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('Collapse') }}" id="collapse-header"><i class="ti ti-chevron-up"></i></a>
                </li>
            </ul>
            <div class="page-btn">
                <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#add-stock"><i class="ti ti-circle-plus me-1"></i>{{ __('Add Stock') }}</a>
            </div>
        </div>
        <!-- /product list -->
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
                            Warehouse
                        </a>
                        <ul class="dropdown-menu  dropdown-menu-end p-3">
                            @foreach($warehouses as $warehouse)
                            <li>
                                <a href="javascript:void(0);" class="dropdown-item rounded-1">{{ $warehouse->name }}</a>
                            </li>
                            @endforeach
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
                                <th>{{ __('Warehouse') }}</th>
                                <th>{{ __('Product') }}</th>
                                <th>{{ __('Date') }}</th>
                                <th>{{ __('Qty') }}</th>
                                <th class="no-sort"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($stocks as $stock)
                            <tr>
                                <td>
                                    <label class="checkboxs">
                                        <input type="checkbox">
                                        <span class="checkmarks"></span>
                                    </label>
                                </td>
                                <td>{{ $stock->warehouse->name ?? '—' }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <a href="javascript:void(0);" class="avatar avatar-md me-2">
                                            @if($stock->product->image ?? false)
                                                <img src="{{ asset('storage/' . $stock->product->image) }}" alt="product">
                                            @else
                                                <img src="{{URL::asset('build/img/products/stock-img-01.png')}}" alt="product">
                                            @endif
                                        </a>
                                        <a href="javascript:void(0);">{{ $stock->product->name ?? '—' }}</a>
                                    </div>
                                </td>
                                <td>{{ $stock->created_at->format('d M Y') }}</td>
                                <td>{{ $stock->quantity }}</td>
                                <td class="d-flex">
                                    <div class="d-flex align-items-center edit-delete-action">
                                        <a class="me-2 border rounded d-flex align-items-center p-2" href="#" data-bs-toggle="modal" data-bs-target="#edit-stock-{{ $stock->id }}">
                                            <i data-feather="edit" class="feather-edit"></i>
                                        </a>
                                        <form action="{{ route('eshop360.stocks.destroy', [$instance->slug ?? '', $stock]) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-2 border rounded d-flex align-items-center bg-transparent">
                                                <i data-feather="trash-2" class="feather-trash-2"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center">{{ __('No stock entries found.') }}</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($stocks->hasPages())
                <div class="p-3">
                    {{ $stocks->links() }}
                </div>
                @endif
            </div>
        </div>
        <!-- /product list -->

</x-dashboard::layouts.master>
