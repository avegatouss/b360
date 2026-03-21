<x-dashboard::layouts.master
    :title="__('Purchase Report') . ' —' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance"
    :pageTitle="__('Purchase Report')">

<div>
                <div class="page-header">
                    <div class="add-item d-flex">
                        <div class="page-title">
                            <h4>{{ __('Purchase report') }}</h4>
                            <h6>{{ __('Manage your Purchase report') }}</h6>
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
                        <form action="{{url('purchase-report')}}">
                            <div class="row align-items-end">
                                <div class="col-lg-10">
                                    <div class="row">
                                        <div class="col-md-4">
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
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label class="form-label">{{ __('Store') }}</label>
                                                <select class="select">
                                                    <option>{{ __('All') }}</option>
                                                    <option>{{ __('Carl Evans') }}</option>
                                                    <option>{{ __('Minerva Rameriz') }}</option>
                                                    <option>{{ __('Robert Lamon') }}</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label class="form-label">{{ __('Products') }}</label>
                                                <select class="select">
                                                    <option>{{ __('All') }}</option>
                                                    <option>{{ __('Lenovo IdeaPad 3') }}</option>
                                                    <option>{{ __('Nike Jordan') }}</option>
                                                    <option>{{ __('Amazon Echo Dot') }}</option>
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
                <!-- /product list -->
                <div class="card no-search">
                    <div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
                        <div>
                            <h4>{{ __('Customer Report') }}</h4>
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
                                        <th>{{ __('SKU') }}</th>
                                        <th>{{ __('Due Date') }}</th>
                                        <th>{{ __('Product Name') }}</th>
                                        <th>{{ __('Category') }}</th>
                                        <th>{{ __('Instock Qty') }}</th>
                                        <th>{{ __('Purchase Qty') }}</th>
                                        <th>{{ __('Purchase Amount') }}</th>
                                        
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                    
                                        <td>
                                            <a href="{{url('purchase-report')}}">PO2025</a>
                                        </td>
                                        <td>
                                            PT001
                                        </td>
                                        <td>
                                            24 Dec 2024
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <a href="{{url('purchase-report')}}" class="avatar avatar-md"><img src="{{URL::asset('build/img/products/stock-img-01.png')}}" class="img-fluid" alt="img"></a>
                                                <div class="ms-2">
                                                    <p class="text-dark mb-0"><a>{{ __('Lenovo IdeaPad 3') }}</a></p>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            Computers
                                        </td>
                                        <td>
                                            100								
                                        </td>
                                        <td>05</td>
                                        <td>
                                            5 {{ $eshopCurrency ?? 'FCFA' }}00
                                        </td>
                                    </tr>
                                    <tr>
                                        
                                        <td>
                                            <a href="{{url('purchase-report')}}">PO2025</a>
                                        </td>
                                        <td>
                                            PT002
                                        </td>
                                        <td>
                                            10 Dec 2024
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <a href="{{url('purchase-report')}}" class="avatar avatar-md"><img src="{{URL::asset('build/img/products/stock-img-06.png')}}" class="img-fluid" alt="img"></a>
                                                <div class="ms-2">
                                                    <p class="text-dark mb-0"><a>{{ __('Beats Pro') }}</a></p>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            Electronics
                                        </td>
                                        <td>
                                            140							
                                        </td>
                                        <td>10</td>
                                        <td>
                                            1 {{ $eshopCurrency ?? 'FCFA' }}500
                                        </td>
                                    </tr>
                                    <tr>
                                        
                                        <td>
                                            <a href="{{url('purchase-report')}}">PO2025</a>
                                        </td>
                                        <td>
                                            PT003
                                        </td>
                                        <td>
                                            27 Nov 2024
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <a href="{{url('purchase-report')}}" class="avatar avatar-md"><img src="{{URL::asset('build/img/products/stock-img-02.png')}}" class="img-fluid" alt="img"></a>
                                                <div class="ms-2">
                                                    <p class="text-dark mb-0"><a>{{ __('Nike Jordan') }}</a></p>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            Shoe
                                        </td>
                                        <td>
                                            300					
                                        </td>
                                        <td>08</td>
                                        <td>
                                            6 {{ $eshopCurrency ?? 'FCFA' }}00
                                        </td>
                                    </tr>
                                    <tr>
                                        
                                        <td>
                                            <a href="{{url('purchase-report')}}">PO2025</a>
                                        </td>
                                        <td>
                                            PT004
                                        </td>
                                        <td>
                                            18 Nov 2024
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <a href="{{url('purchase-report')}}" class="avatar avatar-md"><img src="{{URL::asset('build/img/products/stock-img-03.png')}}" class="img-fluid" alt="img"></a>
                                                <div class="ms-2">
                                                    <p class="text-dark mb-0"><a>{{ __('Apple Series 5 Watch') }}</a></p>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            Electronics
                                        </td>
                                        <td>
                                            450				
                                        </td>
                                        <td>10</td>
                                        <td>
                                            1 {{ $eshopCurrency ?? 'FCFA' }}000
                                        </td>
                                    </tr>
                                    <tr>
                                        
                                        <td>
                                            <a href="{{url('purchase-report')}}">PO2025</a>
                                        </td>
                                        <td>
                                            PT005
                                        </td>
                                        <td>
                                            18 Nov 2024
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <a href="{{url('purchase-report')}}" class="avatar avatar-md"><img src="{{URL::asset('build/img/products/stock-img-04.png')}}" class="img-fluid" alt="img"></a>
                                                <div class="ms-2">
                                                    <p class="text-dark mb-0"><a>{{ __('Amazon Echo Dot') }}</a></p>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            Electronics
                                        </td>
                                        <td>
                                            320			
                                        </td>
                                        <td>05</td>
                                        <td>
                                            1 {{ $eshopCurrency ?? 'FCFA' }}200
                                        </td>
                                    </tr>
                                    <tr>
                                        
                                        <td>
                                            <a href="{{url('purchase-report')}}">PO2025</a>
                                        </td>
                                        <td>
                                            PT006
                                        </td>
                                        <td>
                                            25 Oct 2024
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <a href="{{url('purchase-report')}}" class="avatar avatar-md"><img src="{{URL::asset('build/img/products/stock-img-05.')}}png" class="img-fluid" alt="img"></a>
                                                <div class="ms-2">
                                                    <p class="text-dark mb-0"><a>{{ __('Sanford Chair Sofa') }}</a></p>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            Furniture
                                        </td>
                                        <td>
                                            650		
                                        </td>
                                        <td>07</td>
                                        <td>
                                            8 {{ $eshopCurrency ?? 'FCFA' }}00
                                        </td>
                                    </tr>
                                    <tr>
                                        
                                        <td>
                                            <a href="{{url('purchase-report')}}">PO2025</a>
                                        </td>
                                        <td>
                                            PT007
                                        </td>
                                        <td>
                                            14 Oct 2024
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <a href="{{url('purchase-report')}}" class="avatar avatar-md"><img src="{{URL::asset('build/img/products/expire-product-01.png')}}" class="img-fluid" alt="img"></a>
                                                <div class="ms-2">
                                                    <p class="text-dark mb-0"><a>{{ __('Red Premium Satchel') }}</a></p>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            Bags
                                        </td>
                                        <td>
                                            700	
                                        </td>
                                        <td>15</td>
                                        <td>
                                            2 {{ $eshopCurrency ?? 'FCFA' }}000
                                        </td>
                                    </tr>
                                    <tr>
                                        
                                        <td>
                                            <a href="{{url('purchase-report')}}">PO2025</a>
                                        </td>
                                        <td>
                                            PT008
                                        </td>
                                        <td>
                                            03 Oct 2024
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <a href="{{url('purchase-report')}}" class="avatar avatar-md"><img src="{{URL::asset('build/img/products/expire-product-02.png')}}" class="img-fluid" alt="img"></a>
                                                <div class="ms-2">
                                                    <p class="text-dark mb-0"><a>{{ __('Iphone 14 Pro') }}</a></p>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            Phone
                                        </td>
                                        <td>
                                            630
                                        </td>
                                        <td>12</td>
                                        <td>
                                            2 {{ $eshopCurrency ?? 'FCFA' }}000
                                        </td>
                                    </tr>
                                    <tr>
                                        
                                        <td>
                                            <a href="{{url('purchase-report')}}">PO2025</a>
                                        </td>
                                        <td>
                                            PT009
                                        </td>
                                        <td>
                                            20 Sep 2024
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <a href="{{url('purchase-report')}}" class="avatar avatar-md"><img src="{{URL::asset('build/img/products/expire-product-03.png')}}" class="img-fluid" alt="img"></a>
                                                <div class="ms-2">
                                                    <p class="text-dark mb-0"><a>{{ __('Gaming Chair') }}</a></p>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            Furniture
                                        </td>
                                        <td>
                                            410
                                        </td>
                                        <td>10</td>
                                        <td>
                                            3 {{ $eshopCurrency ?? 'FCFA' }}00
                                        </td>
                                    </tr>
                                    <tr>
                                        
                                        <td>
                                            <a href="{{url('purchase-report')}}">PO2025</a>
                                        </td>
                                        <td>
                                            PT010
                                        </td>
                                        <td>
                                            10 Sep 2024
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <a href="{{url('purchase-report')}}" class="avatar avatar-md"><img src="{{URL::asset('build/img/products/expire-product-04.png')}}" class="img-fluid" alt="img"></a>
                                                <div class="ms-2">
                                                    <p class="text-dark mb-0"><a>{{ __('Borealis Backpack') }}</a></p>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            Bags
                                        </td>
                                        <td>
                                            550
                                        </td>
                                        <td>20</td>
                                        <td>
                                            5 {{ $eshopCurrency ?? 'FCFA' }}000
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <!-- /product list -->
            </div>

</x-dashboard::layouts.master>
