<x-dashboard::layouts.master
    :title="__('Sales List') . ' —' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance"
    :pageTitle="__('Sales List')">

@component('components.breadcrumb')
                @slot('title')
                    Sales List
                @endslot
                @slot('li_1')
                    Manage Your Sales
                @endslot
                @slot('li_2')
                    Add New Sales
                @endslot
            @endcomponent

            <!-- /product list -->
            <div class="card table-list-card">
                <div class="card-body">
                    <div class="table-top">
                        <div class="search-set">
                            <div class="search-input">
                                <a href="" class="btn btn-searchset"><i data-feather="search"
                                        class="feather-search"></i></a>
                            </div>
                        </div>
                        <div class="search-path">
                            <div class="d-flex align-items-center">
                                <a class="btn btn-filter" id="filter_search">
                                    <i data-feather="filter" class="filter-icon"></i>
                                    <span><img src="{{ URL::asset('/build/img/icons/closes.svg') }}" alt="img"></span>
                                </a>
                            </div>
                        </div>
                        <div class="form-sort">
                            <i data-feather="sliders" class="info-img"></i>
                            <select class="select">
                                <option>{{ __('Sort by Date') }}</option>
                            </select>
                        </div>
                    </div>
                    <!-- /Filter -->
                    <div class="card" id="filter_inputs">
                        <div class="card-body pb-0">
                            <div class="row">
                                <div class="col-lg-3 col-sm-6 col-12">
                                    <div class="input-blocks">
                                        <i data-feather="user" class="info-img"></i>
                                        <select class="select">
                                            <option>{{ __('Choose Customer Name') }}</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-lg-2 col-sm-6 col-12">
                                    <div class="input-blocks">
                                        <i data-feather="stop-circle" class="info-img"></i>
                                        <select class="select">
                                            <option>{{ __('Choose Status') }}</option>
                                            <option>{{ __('Completed') }}</option>
                                            <option>{{ __('Pending') }}</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-lg-2 col-sm-6 col-12">
                                    <div class="input-blocks">
                                        <i data-feather="file-text" class="info-img"></i>
                                        <input type="text" placeholder="{{ __('Enter Reference') }}" class="form-control">
                                    </div>
                                </div>
                                <div class="col-lg-3 col-sm-6 col-12">
                                    <div class="input-blocks">
                                        <i data-feather="stop-circle" class="info-img"></i>
                                        <select class="select">
                                            <option>{{ __('Choose Payment Status') }}</option>
                                            <option>{{ __('Paid') }}</option>
                                            <option>{{ __('Due') }}</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-lg-2 col-sm-6 col-12">
                                    <div class="input-blocks">
                                        <a class="btn btn-filters ms-auto"> <i data-feather="search"
                                                class="feather-search"></i>{{ __('Search') }}</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- /Filter -->
                    <div class="table-responsive">
                        <table class="table  datanew">
                            <thead>
                                <tr>
                                    <th class="no-sort">
                                        <label class="checkboxs">
                                            <input type="checkbox" id="select-all">
                                            <span class="checkmarks"></span>
                                        </label>
                                    </th>
                                    <th>{{ __('Customer Name') }}</th>
                                    <th>{{ __('Reference') }}</th>
                                    <th>{{ __('Date') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th>{{ __('Grand Total') }}</th>
                                    <th>{{ __('Paid') }}</th>
                                    <th>{{ __('Due') }}</th>
                                    <th>{{ __('Payment Status') }}</th>
                                    <th>{{ __('Biller') }}</th>
                                    <th class="text-center">{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody class="sales-list">
                                @forelse($sales as $sale)
                                <tr>
                                    <td>
                                        <label class="checkboxs">
                                            <input type="checkbox">
                                            <span class="checkmarks"></span>
                                        </label>
                                    </td>
                                    <td>{{ $sale->customer->name ?? $sale->customer_name ?? '—' }}</td>
                                    <td>{{ $sale->order_number ?? $sale->reference ?? '—' }}</td>
                                    <td>{{ $sale->created_at->format('d M Y') }}</td>
                                    <td>
                                        @if(($sale->status ?? '') === 'completed')
                                            <span class="badge badge-bgsuccess">{{ __('Completed') }}</span>
                                        @else
                                            <span class="badge badge-bgdanger">{{ ucfirst($sale->status ?? 'Pending') }}</span>
                                        @endif
                                    </td>
                                    <td>${{ number_format($sale->total ?? $sale->grand_total ?? 0, 2) }}</td>
                                    <td>${{ number_format($sale->paid_amount ?? 0, 2) }}</td>
                                    <td>${{ number_format($sale->due_amount ?? (($sale->total ?? 0) - ($sale->paid_amount ?? 0)), 2) }}</td>
                                    <td>
                                        @if(($sale->due_amount ?? (($sale->total ?? 0) - ($sale->paid_amount ?? 0))) <= 0)
                                            <span class="badge badge-linesuccess">{{ __('Paid') }}</span>
                                        @else
                                            <span class="badge badge-linedanger">{{ __('Due') }}</span>
                                        @endif
                                    </td>
                                    <td>{{ $sale->biller->name ?? 'Admin' }}</td>
                                    <td class="text-center">
                                        <a class="action-set" href="javascript:void(0);" data-bs-toggle="dropdown"
                                            aria-expanded="true">
                                            <i class="fa fa-ellipsis-v" aria-hidden="true"></i>
                                        </a>
                                        <ul class="dropdown-menu">
                                            <li>
                                                <a href="{{ route('eshop360.sales.show', [$instance->slug ?? '', $sale]) }}" class="dropdown-item"><i data-feather="eye"
                                                        class="info-img"></i>{{ __('Sale Detail') }}</a>
                                            </li>
                                            <li>
                                                <a href="{{ route('eshop360.sales.show', [$instance->slug ?? '', $sale]) }}" class="dropdown-item"><i
                                                        data-feather="corner-down-left" class="info-img"></i>{{ __('Return / Update') }}</a>
                                            </li>
                                            <li>
                                                <form action="{{ route('eshop360.sales.destroy', [$instance->slug ?? '', $sale]) }}" method="POST" onsubmit="return confirm('Are you sure?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="dropdown-item"><i
                                                            data-feather="trash-2" class="info-img"></i>{{ __('Delete Sale') }}</button>
                                                </form>
                                            </li>
                                        </ul>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="11" class="text-center">{{ __('No sales found.') }}</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($sales->hasPages())
                    <div class="p-3">
                        {{ $sales->links() }}
                    </div>
                    @endif
                </div>
            </div>
            <!-- /product list -->

</x-dashboard::layouts.master>
