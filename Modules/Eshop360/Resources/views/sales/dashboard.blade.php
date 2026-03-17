<x-dashboard::layouts.master
    :title="__('Sales Dashboard') . ' —' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance"
    :pageTitle="__('Sales Dashboard')">

<div class="welcome d-lg-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center welcome-text">
                <h3 class="d-flex align-items-center"><img src="{{URL::asset('build/img/icons/hi.svg')}}" alt="img">&nbsp;Hi {{ auth()->user()->name ?? 'User' }},</h3>&nbsp;<h6>here's what's happening with your store today.</h6>
            </div>
            <div class="d-flex align-items-center">
                <div class="input-icon-start position-relative me-2">
                    <span class="input-icon-addon fs-16 text-gray-9">
                        <i class="ti ti-calendar"></i>
                    </span>
                    <input type="text" class="form-control date-range bookingrange" placeholder="{{ __('Search Product') }}">
                </div>
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
        <div class="row sales-cards">
            <div class="col-xl-6 col-sm-12 col-12 d-flex">
                <div class="card d-flex align-items-center justify-content-between flex-fill mb-4">
                    <div>
                        <h6>{{ __('Weekly Earning') }}</h6>
                        <h3>$<span class="counters" data-count="{{ $weekSales ?? 0 }}">{{ number_format($weekSales ?? 0, 2) }}</span></h3>
                        <p class="sales-range"><span class="text-success"><i data-feather="chevron-up" class="feather-16"></i>—&nbsp;</span>{{ __('compare to last week') }}</p>
                    </div>
                    <img src="{{URL::asset('build/img/icons/weekly-earning.svg')}}" alt="img">
                </div>
            </div>
            <div class="col-xl-3 col-sm-6 col-12 d-flex">
                <div class="card color-info bg-primary flex-fill mb-4">
                    <div class="mb-2">
                        <img src="{{URL::asset('build/img/icons/total-sales.svg')}}" alt="img">
                    </div>
                    <h3 class="counters" data-count="{{ $monthSales ?? 0 }}">{{ number_format($monthSales ?? 0, 2) }}</h3>
                    <p>{{ __('Monthly Sales') }}</p>
                    <i data-feather="rotate-ccw" class="feather-16" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('Refresh') }}"></i>
                </div>
            </div>
            <div class="col-xl-3 col-sm-6 col-12 d-flex">
                <div class="card color-info bg-secondary flex-fill mb-4">
                    <div class="mb-2">
                        <img src="{{URL::asset('build/img/icons/purchased-earnings.svg')}}" alt="img">
                    </div>
                    <h3 class="counters" data-count="{{ $todaySales ?? 0 }}">{{ number_format($todaySales ?? 0, 2) }}</h3>
                    <p>{{ __('Today\'s Sales') }}</p>
                    <i data-feather="rotate-ccw" class="feather-16" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('Refresh') }}"></i>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-12 col-md-12 col-xl-4 d-flex">
                <div class="card flex-fill w-100 mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0">{{ __('Best Seller') }}</h4>
                        <a href="javascript:void(0);" class="btn btn-outline-light btn-sm">{{ __('View All') }}</a>
                    </div>
                    <div class="card-body pb-0">
                        <div class="table-responsive">
                            <table class="table table-borderless best-seller">
                                <tbody>
                                    @forelse($topProducts ?? [] as $topProduct)
                                    <tr>
                                        <td class="ps-0">
                                            <div class="d-flex align-items-center">
                                                <a href="javascript:void(0);" class="avatar avatar-lg me-2">
                                                    @if($topProduct->image)
                                                        <img src="{{ asset('storage/' . $topProduct->image) }}" alt="img">
                                                    @else
                                                        <img src="{{URL::asset('build/img/products/stock-img-01.png')}}" alt="img">
                                                    @endif
                                                </a>
                                                <div>
                                                    <h6><a href="javascript:void(0);" class="fw-bold">{{ $topProduct->name }}</a></h6>
                                                    <p>${{ number_format($topProduct->price, 2) }}</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <p class="text-gray-9 mb-1">{{ __('Sales') }}</p>
                                            <p class="text-gray-9 fw-medium">{{ $topProduct->total_sold ?? $topProduct->orders_count ?? 0 }}</p>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="2" class="text-center">{{ __('No data available.') }}</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-12 col-md-12 col-xl-8 d-flex">
                <div class="card flex-fill w-100 mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0">{{ __('Recent Transactions') }}</h4>
                        <a href="javascript:void(0);" class="btn btn-outline-light btn-sm">{{ __('View All') }}</a>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-borderless recent-transactions">
                                <thead class="thead-light">
                                    <tr>
                                        <th>#</th>
                                        <th>{{ __('Order Details') }}</th>
                                        <th>{{ __('Payment') }}</th>
                                        <th>{{ __('Status') }}</th>
                                        <th>{{ __('Amount') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($recentSales ?? [] as $index => $sale)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div>
                                                    <h6><a href="javascript:void(0);" class="fw-bold">{{ $sale->order_number ?? '#' . $sale->id }}</a></h6>
                                                    <span class="d-flex align-items-center"><i data-feather="clock" class="feather-14"></i>{{ $sale->created_at->diffForHumans() }}</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="d-block head-text">{{ ucfirst($sale->payment_method ?? 'N/A') }}</span>
                                            <span class="text-blue">{{ $sale->order_number ?? '' }}</span>
                                        </td>
                                        <td>
                                            @php
                                                $saleStatusBadge = match($sale->status ?? 'pending') {
                                                    'completed' => 'badge badge-success badge-xs d-inline-flex align-items-center',
                                                    'cancelled' => 'badge badge-danger badge-xs d-inline-flex align-items-center',
                                                    default => 'badge badge-cyan badge-xs d-inline-flex align-items-center',
                                                };
                                            @endphp
                                            <span class="{{ $saleStatusBadge }}"><i class="ti ti-circle-filled fs-5 me-1"></i>{{ ucfirst($sale->status ?? 'Pending') }}</span>
                                        </td>
                                        <td class="fs-16 fw-bold text-gray-9">${{ number_format($sale->total ?? 0, 2) }}</td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="5" class="text-center">{{ __('No recent transactions.') }}</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Button trigger modal -->

        <div class="row sales-board">
            <div class="col-md-12 col-lg-7 col-sm-12 col-12 d-flex">
                <div class="card flex-fill flex-fill">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">{{ __('Sales Analytics') }}</h5>
                        <div class="graph-sets">
                            <div class="dropdown dropdown-wraper">
                                <button class="btn btn-white btn-sm dropdown-toggle d-flex align-items-center" type="button" id="dropdown-sales" data-bs-toggle="dropdown" aria-expanded="false"><i data-feather="calendar" class="feather-14"></i>{{ date('Y') }}</button>
                                <ul class="dropdown-menu" aria-labelledby="dropdown-sales">
                                    <li>
                                        <a href="javascript:void(0);" class="dropdown-item">{{ date('Y') }}</a>
                                    </li>
                                    <li>
                                        <a href="javascript:void(0);" class="dropdown-item">{{ date('Y') - 1 }}</a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="card-body pt-1 pb-0">
                        <div id="sales-analysis" class="chart-set"></div>
                    </div>
                </div>
            </div>
            <div class="col-md-12 col-lg-5 col-sm-12 col-12 d-flex">
                <!-- World Map -->
                <div class="card flex-fill">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">{{ __('Sales by Countries') }}</h5>
                        <div class="graph-sets">
                            <div class="dropdown dropdown-wraper">
                                <button class="btn btn-white btn-sm dropdown-toggle d-flex align-items-center" type="button" id="dropdown-country-sales" data-bs-toggle="dropdown" aria-expanded="false">{{ __('This Week') }}</button>
                                <ul class="dropdown-menu" aria-labelledby="dropdown-country-sales">
                                    <li>
                                        <a href="javascript:void(0);" class="dropdown-item">{{ __('This Month') }}</a>
                                    </li>
                                    <li>
                                        <a href="javascript:void(0);" class="dropdown-item">{{ __('This Year') }}</a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div id="sales_db_world_map" style="height: 265px;"></div>
                        <p class="sales-range"><span class="text-success"><i data-feather="chevron-up" class="feather-16"></i>—&nbsp;</span>{{ __('compare to last week') }}</p>
                    </div>
                </div>
                <!-- /World Map -->
            </div>
        </div>
    </div>

@push('scripts')
<script>
    var dailySalesData = @json($dailySales ?? []);
</script>
@endpush

</x-dashboard::layouts.master>
