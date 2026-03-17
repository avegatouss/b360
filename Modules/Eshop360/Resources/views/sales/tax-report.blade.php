<x-dashboard::layouts.master
    :title="__('Sales Tax Report') . ' —' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance"
    :pageTitle="__('Sales Tax Report')">

<div class="mb-4">
                <ul class="nav nav-pills">
                    <li class="nav-item">
                        <a class="nav-link" href="{{url('tax-reports')}}">Purchase tax</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="{{url('sales-tax')}}">Sales Tax</a>
                    </li>
                </ul>
            </div>
            <div>
                <div class="page-header">
                    <div class="add-item d-flex">
                        <div class="page-title">
                            <h4>{{ __('Sales tax') }}</h4>
                            <h6>{{ __('View Reports of Sales tax') }}</h6>
                        </div>
                    </div>
                    <ul class="table-top-head">
                        <li class="me-2">
                            <a data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('Refresh') }}"><i class="ti ti-refresh"></i></a>
                        </li>
                        <li>
                            <a data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('Collapse') }}" id="collapse-header"><i class="ti ti-chevron-up"></i></a>
                        </li>
                    </ul>
                </div>
                <div class="card">
                    <div class="card-body pb-1">
                        <form action="{{url('sales-tax')}}">
                            <div class="row align-items-end">
                                <div class="col-lg-10">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="mb-3">
                                                <label class="form-label">{{ __('Choose Date') }}</label>
                                                <div class="input-icon-start position-relative">
                                                    <input type="text" class="form-control date-range bookingrange" placeholder="{{ __('dd/mm/yyyy - dd/mm/yyyy') }}">
                                                    <span class="input-icon-left">
                                                        <i class="ti ti-calendar"></i>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="mb-3">
                                                <label class="form-label">{{ __('Store') }}</label>
                                                <select class="select">
                                                    <option>{{ __('All') }}</option>
                                                    <option>{{ __('Electro Mart') }}</option>
                                                    <option>{{ __('Quantum Gadgets') }}</option>
                                                    <option>{{ __('Prime Bazaar') }}</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="mb-3">
                                                <label class="form-label">{{ __('Customer') }}</label>
                                                <select class="select">
                                                    <option>{{ __('All') }}</option>
                                                    <option>{{ __('Carl Evans') }}</option>
                                                    <option>{{ __('Minerva Rameriz') }}</option>
                                                    <option>{{ __('Robert Lamon') }}</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="mb-3">
                                                <label class="form-label">{{ __('Payment Method') }}</label>
                                                <select class="select">
                                                    <option>{{ __('All') }}</option>
                                                    <option>{{ __('Stripe') }}</option>
                                                    <option>{{ __('Paypal') }}</option>
                                                    <option>{{ __('Cash') }}</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-2">
                                    <div class="mb-3">
                                        <button class="btn btn-primary w-100" type="submit">{{ __('Generate Report') }}</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                
                <div class="card no-search">
                    <div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
                        <div>
                            <h4>{{ __('Sales Tax Report') }}</h4>
                        </div>
                        <ul class="table-top-head">
                            <li class="me-2">
                                <a data-bs-toggle="tooltip" data-bs-placement="top" title="Pdf"><img src="{{URL::asset('build/img/icons/pdf.svg')}}" alt="img"></a>
                            </li>
                            <li class="me-2">
                                <a data-bs-toggle="tooltip" data-bs-placement="top" title="Excel"><img src="{{URL::asset('build/img/icons/excel.svg')}}" alt="img"></a>
                            </li>
                            <li>
                                <a data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('Print') }}"><i class="ti ti-printer"></i></a>
                            </li>
                        </ul>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table datatable">
                                <thead class="thead-light">
                                    <tr>
                                        <th>{{ __('Reference') }}</th>
                                        <th>{{ __('Customer') }}</th>
                                        <th>{{ __('Date') }}</th>
                                        <th>{{ __('Store') }}</th>
                                        <th>{{ __('Amount') }}</th>
                                        <th>{{ __('Payment Method') }}</th>
                                        <th>{{ __('Discount') }}</th>
                                        <th>{{ __('Tax Amount') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><a href="#">#4237300</a></td>
                                        <td>{{ __('Carl Evans') }}</td>
                                        <td>{{ __('24 Dec 2024') }}</td>
                                        <td>{{ __('Electro Mart') }}</td>
                                        <td>$200</td>
                                        <td>{{ __('Stripe') }}</td>
                                        <td>$200</td>
                                        <td>$200</td>
                                    </tr>
                                    <tr>
                                        <td><a href="#">#7590325</a></td>
                                        <td>{{ __('Minerva Rameriz') }}</td>
                                        <td>{{ __('10 Dec 2024') }}</td>
                                        <td>{{ __('Quantum Gadgets') }}</td>
                                        <td>$50</td>
                                        <td>{{ __('Paypal') }}</td>
                                        <td>$50</td>
                                        <td>$50</td>
                                    </tr>
                                    <tr>
                                        <td><a href="#">#9814521</a></td>
                                        <td>{{ __('Robert Lamon') }}</td>
                                        <td>{{ __('27 Nov 2024') }}</td>
                                        <td>{{ __('Prime Bazaar') }}</td>
                                        <td>$800</td>
                                        <td>{{ __('Cash') }}</td>
                                        <td>$800</td>
                                        <td>$800</td>
                                    </tr>
                                    <tr>
                                        <td><a href="#">#8745225</a></td>
                                        <td>{{ __('Patricia Lewis') }}</td>
                                        <td>{{ __('18 Nov 2024') }}</td>
                                        <td>{{ __('Gadget World') }}</td>
                                        <td>$100</td>
                                        <td>{{ __('Paypal') }}</td>
                                        <td>$100</td>
                                        <td>$100</td>
                                    </tr>
                                    <tr>
                                        <td><a href="#">#4237022</a></td>
                                        <td>{{ __('Mark Joslyn') }}</td>
                                        <td>{{ __('06 Nov 2024') }}</td>
                                        <td>{{ __('Volt Vault') }}</td>
                                        <td>$700</td>
                                        <td>{{ __('Cash') }}</td>
                                        <td>$700</td>
                                        <td>$700</td>
                                    </tr>
                                    <tr>
                                        <td><a href="#">#8744439</a></td>
                                        <td>{{ __('Marsha Betts') }}</td>
                                        <td>{{ __('25 Oct 2024') }}</td>
                                        <td>{{ __('Elite Retail') }}</td>
                                        <td>$1000</td>
                                        <td>{{ __('Cash') }}</td>
                                        <td>$1000</td>
                                        <td>$1000</td>
                                    </tr>
                                    <tr>
                                        <td><a href="#">#7590365</a></td>
                                        <td>{{ __('Daniel Jude') }}</td>
                                        <td>{{ __('14 Oct 2024') }}</td>
                                        <td>{{ __('Prime Mart') }}</td>
                                        <td>$1200</td>
                                        <td>{{ __('Paypal') }}</td>
                                        <td>$1200</td>
                                        <td>$1200</td>
                                    </tr>
                                    <tr>
                                        <td><a href="#">#8745478</a></td>
                                        <td>{{ __('Emma Bates') }}</td>
                                        <td>{{ __('03 Oct 2024') }}</td>
                                        <td>{{ __('NeoTech Store') }}</td>
                                        <td>$750</td>
                                        <td>{{ __('Stripe') }}</td>
                                        <td>$750</td>
                                        <td>$750</td>
                                    </tr>
                                    <tr>
                                        <td><a href="#">#7590321</a></td>
                                        <td>{{ __('Richard Fralick') }}</td>
                                        <td>{{ __('20 Sep 2024') }}</td>
                                        <td>{{ __('Urban Mart') }}</td>
                                        <td>$450</td>
                                        <td>{{ __('Stripe') }}</td>
                                        <td>$450</td>
                                        <td>$450</td>
                                    </tr>
                                    <tr>
                                        <td><a href="#">#8745245</a></td>
                                        <td>{{ __('Michelle Robison') }}</td>
                                        <td>{{ __('10 Sep 2024') }}</td>
                                        <td>{{ __('Travel Mart') }}</td>
                                        <td>$300</td>
                                        <td>{{ __('Cash') }}</td>
                                        <td>$300</td>
                                        <td>$300</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <!-- /product list -->
            </div>

</x-dashboard::layouts.master>
