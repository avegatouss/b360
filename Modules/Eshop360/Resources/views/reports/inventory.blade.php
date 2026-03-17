<x-dashboard::layouts.master
    :title="__('Inventory Report') . ' —' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance"
    :pageTitle="__('Inventory Report')">

<div class="mb-4">
                <ul class="nav nav-pills">
                    <li class="nav-item">
                        <a class="nav-link active" href="{{url('inventory-report')}}">Inventory Report</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{url('stock-history')}}">Stock History</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{url('sold-stock')}}">Sold Stock</a>
                    </li>
                </ul>
            </div>
            <div>
                <div class="page-header">
                    <div class="add-item d-flex">
                        <div class="page-title">
                            <h4>{{ __('Inventory') }}</h4>
                            <h6>{{ __('View Reports of Inventory') }}</h6>
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
                        <form action="{{url('customer-report')}}">
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
                                                <label class="form-label">{{ __('Category') }}</label>
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
                                                <label class="form-label">{{ __('Products') }}</label>
                                                <select class="select">
                                                    <option>{{ __('All') }}</option>
                                                    <option>{{ __('Lenovo IdeaPad 3') }}</option>
                                                    <option>{{ __('Beats Pro') }}</option>
                                                    <option>{{ __('Nike Jordan') }}</option>															
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="mb-3">
                                                <label class="form-label">{{ __('Units') }}</label>
                                                <select class="select">
                                                    <option>{{ __('All') }}</option>
                                                    <option>{{ __('PC') }}</option>
                                                    <option>{{ __('BX') }}</option>
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
                                        <th>{{ __('SKU') }}</th>
                                        <th>{{ __('Product Name') }}</th>
                                        <th>{{ __('Category') }}</th>
                                        <th>{{ __('Unit') }}</th>
                                        <th>{{ __('InStock') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        
                                        <td>
                                            <a>{{ __('PT001') }}</a>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <a  class="avatar avatar-md"><img src="{{URL::asset('build/img/products/stock-img-01.png')}}" class="img-fluid" alt="img"></a>
                                                <div class="ms-2">
                                                    <p class="text-dark mb-0"><a>{{ __('Lenovo IdeaPad 3') }}</a></p>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            Computers
                                        </td>
                                        <td>
                                            Pc						
                                        </td>
                                        <td>100</td>
                                    </tr>
                                    <tr>
                                        
                                        <td>
                                            <a>{{ __('PT002') }}</a>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <a  class="avatar avatar-md"><img src="{{URL::asset('build/img/products/stock-img-06.png')}}" class="img-fluid" alt="img"></a>
                                                <div class="ms-2">
                                                    <p class="text-dark mb-0"><a>{{ __('Beats Pro') }}</a></p>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            Electronics
                                        </td>
                                        <td>
                                            Pc						
                                        </td>
                                        <td>140</td>
                                    </tr>
                                    <tr>
                                        
                                        <td>
                                            <a>{{ __('PT003') }}</a>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <a  class="avatar avatar-md"><img src="{{URL::asset('build/img/products/stock-img-02.png')}}" class="img-fluid" alt="img"></a>
                                                <div class="ms-2">
                                                    <p class="text-dark mb-0"><a>{{ __('Nike Jordan') }}</a></p>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            Shoe
                                        </td>
                                        <td>
                                            Bx					
                                        </td>
                                        <td>300</td>
                                    </tr>
                                    <tr>
                                        
                                        <td>
                                            <a>{{ __('PT004') }}</a>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <a  class="avatar avatar-md"><img src="{{URL::asset('build/img/products/stock-img-03.png')}}" class="img-fluid" alt="img"></a>
                                                <div class="ms-2">
                                                    <p class="text-dark mb-0"><a>{{ __('Apple Series 5 Watch') }}</a></p>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            Electronics
                                        </td>
                                        <td>
                                            Pc				
                                        </td>
                                        <td>450</td>
                                    </tr>
                                    <tr>
                                        
                                        <td>
                                            <a>{{ __('PT005') }}</a>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <a  class="avatar avatar-md"><img src="{{URL::asset('build/img/products/stock-img-04.png')}}" class="img-fluid" alt="img"></a>
                                                <div class="ms-2">
                                                    <p class="text-dark mb-0"><a>{{ __('Amazon Echo Dot') }}</a></p>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            Electronics
                                        </td>
                                        <td>
                                            Pc				
                                        </td>
                                        <td>320</td>
                                    </tr>
                                    <tr>
                                        
                                        <td>
                                            <a>{{ __('PT006') }}</a>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <a  class="avatar avatar-md"><img src="{{URL::asset('build/img/products/stock-img-05.png')}}" class="img-fluid" alt="img"></a>
                                                <div class="ms-2">
                                                    <p class="text-dark mb-0"><a>{{ __('Sanford Chair Sofa') }}</a></p>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            Furniture
                                        </td>
                                        <td>
                                            Pc				
                                        </td>
                                        <td>650</td>
                                    </tr>
                                    <tr>
                                        
                                        <td>
                                            <a>{{ __('PT007') }}</a>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <a  class="avatar avatar-md"><img src="{{URL::asset('build/img/products/expire-product-01.png')}}" class="img-fluid" alt="img"></a>
                                                <div class="ms-2">
                                                    <p class="text-dark mb-0"><a>{{ __('Red Premium Satchel') }}</a></p>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            Bags
                                        </td>
                                        <td>
                                            Bx			
                                        </td>
                                        <td>700</td>
                                    </tr>
                                    <tr>
                                        
                                        <td>
                                            <a>{{ __('PT008') }}</a>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <a  class="avatar avatar-md"><img src="{{URL::asset('build/img/products/expire-product-02.png')}}" class="img-fluid" alt="img"></a>
                                                <div class="ms-2">
                                                    <p class="text-dark mb-0"><a>{{ __('Iphone 14 Pro') }}</a></p>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            Phone
                                        </td>
                                        <td>
                                            Bx			
                                        </td>
                                        <td>630</td>
                                    </tr>
                                    <tr>
                                        
                                        <td>
                                            <a>{{ __('PT009') }}</a>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <a  class="avatar avatar-md"><img src="{{URL::asset('build/img/products/expire-product-03.png')}}" class="img-fluid" alt="img"></a>
                                                <div class="ms-2">
                                                    <p class="text-dark mb-0"><a>{{ __('Gaming Chair') }}</a></p>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            Furniture
                                        </td>
                                        <td>
                                            Pc		
                                        </td>
                                        <td>410</td>
                                    </tr>
                                    <tr>
                                        
                                        <td>
                                            <a>{{ __('PT010') }}</a>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <a  class="avatar avatar-md"><img src="{{URL::asset('build/img/products/expire-product-04.png')}}" class="img-fluid" alt="img"></a>
                                                <div class="ms-2">
                                                    <p class="text-dark mb-0"><a>{{ __('Borealis Backpack') }}</a></p>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            Bags
                                        </td>
                                        <td>
                                            Bx	
                                        </td>
                                        <td>550</td>
                                    </tr>
                                </tbody>
                            </table>
                        
                        </div>
                    </div>
                </div>
                <!-- /product list -->
            </div>

</x-dashboard::layouts.master>
