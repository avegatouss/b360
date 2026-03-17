<x-dashboard::layouts.master
    :title="__('Stock Transfer') . ' —' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance"
    :pageTitle="__('Stock Transfer')">

<div class="page-header">
                <div class="add-item d-flex">
                    <div class="page-title">
                        <h4>{{ __('Stock Transfer') }}</h4>
                        <h6>{{ __('Manage your stock transfer') }}</h6>
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
                    <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#add-stock-transfer"><i class="ti ti-circle-plus me-1"></i>{{ __('Add New') }}</a>
                </div>
                <div class="page-btn import">
                    <a href="#" class="btn btn-secondary color" data-bs-toggle="modal" data-bs-target="#view-notes"><i
                        data-feather="download" class="me-1"></i>{{ __('Import Transfer') }}</a>
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
                                From Warehouse
                            </a>
                            <ul class="dropdown-menu  dropdown-menu-end p-3">
                                <li>
                                    <a href="javascript:void(0);" class="dropdown-item rounded-1">{{ __('Lavish Warehouse') }}</a>
                                </li>
                                <li>
                                    <a href="javascript:void(0);" class="dropdown-item rounded-1">{{ __('Quaint Warehouse') }}</a>
                                </li>
                                <li>
                                    <a href="javascript:void(0);" class="dropdown-item rounded-1">{{ __('Traditional Warehouse') }}</a>
                                </li>
                                <li>
                                    <a href="javascript:void(0);" class="dropdown-item rounded-1">{{ __('Cool Warehouse') }}</a>
                                </li>
                            </ul>
                        </div>
                        <div class="dropdown me-2">
                            <a href="javascript:void(0);" class="dropdown-toggle btn btn-white btn-md d-inline-flex align-items-center" data-bs-toggle="dropdown">
                                To Warehouse
                            </a>
                            <ul class="dropdown-menu  dropdown-menu-end p-3">
                                <li>
                                    <a href="javascript:void(0);" class="dropdown-item rounded-1">{{ __('North Zone Warehouse') }}</a>
                                </li>
                                <li>
                                    <a href="javascript:void(0);" class="dropdown-item rounded-1">{{ __('Nova Storage Hub') }}</a>
                                </li>
                                <li>
                                    <a href="javascript:void(0);" class="dropdown-item rounded-1">{{ __('Cool Warehouse') }}</a>
                                </li>
                                <li>
                                    <a href="javascript:void(0);" class="dropdown-item rounded-1">{{ __('Retail Supply Hub') }}</a>
                                </li>
                            </ul>
                        </div>
                        <div class="dropdown">
                            <a href="javascript:void(0);" class="dropdown-toggle btn btn-white btn-md d-inline-flex align-items-center" data-bs-toggle="dropdown">
                                Sort By : Last 7 Days
                            </a>
                            <ul class="dropdown-menu  dropdown-menu-end p-3">
                                <li>
                                    <a href="javascript:void(0);" class="dropdown-item rounded-1">{{ __('Recently Added') }}</a>
                                </li>
                                <li>
                                    <a href="javascript:void(0);" class="dropdown-item rounded-1">{{ __('Ascending') }}</a>
                                </li>
                                <li>
                                    <a href="javascript:void(0);" class="dropdown-item rounded-1">{{ __('Desending') }}</a>
                                </li>
                                <li>
                                    <a href="javascript:void(0);" class="dropdown-item rounded-1">{{ __('Last Month') }}</a>
                                </li>
                                <li>
                                    <a href="javascript:void(0);" class="dropdown-item rounded-1">{{ __('Last 7 Days') }}</a>
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
                                    <th>{{ __('From Warehouse') }}</th>
                                    <th>{{ __('To Warehouse') }}</th>
                                    <th>{{ __('No of Products') }}</th>
                                    <th>{{ __('Quantity Transfered') }}</th>
                                    <th>{{ __('Ref Number') }}</th>
                                    <th>{{ __('Date') }}</th>
                                    <th class="no-sort"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <label class="checkboxs">
                                            <input type="checkbox">
                                            <span class="checkmarks"></span>
                                        </label>
                                    </td>
                                    <td>{{ __('Lavish Warehouse') }}</td>
                                    <td>{{ __('North Zone Warehouse') }}</td>
                                    <td>
                                        20												
                                    </td>
                                    <td>
                                        15
                                    </td>
                                    <td>#458924</td>
                                    <td>{{ __('24 Dec 2024') }}</td>
                                    <td class="d-flex">
                                        <div class="edit-delete-action d-flex align-items-center justify-content-center">
                                            <a class="me-2 p-2 d-flex align-items-center justify-content-between border rounded" href="#" data-bs-toggle="modal" data-bs-target="#edit-stock-transfer">
                                                <i data-feather="edit" class="feather-edit"></i>
                                            </a>
                                            <a class="p-2 d-flex align-items-center justify-content-between border rounded" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#delete">
                                                <i data-feather="trash-2" class="feather-trash-2"></i>
                                            </a>
                                        </div>
                                        
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <label class="checkboxs">
                                            <input type="checkbox">
                                            <span class="checkmarks"></span>
                                        </label>
                                    </td>
                                    <td>{{ __('Lobar Handy') }}</td>
                                    <td>{{ __('Nova Storage Hub') }}</td>
                                    <td>
                                        04												
                                    </td>
                                    <td>
                                        14
                                    </td>
                                    <td>#145445</td>
                                    <td>{{ __('25 Jul 2023') }}</td>
                                    <td class="d-flex">
                                        <div class="edit-delete-action d-flex align-items-center justify-content-center">
                                            <a class="me-2 p-2 d-flex align-items-center justify-content-between border rounded" href="#" data-bs-toggle="modal" data-bs-target="#edit-stock-transfer">
                                                <i data-feather="edit" class="feather-edit"></i>
                                            </a>
                                            <a class="p-2 d-flex align-items-center justify-content-between border rounded" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#delete">
                                                <i data-feather="trash-2" class="feather-trash-2"></i>
                                            </a>
                                        </div>
                                        
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <label class="checkboxs">
                                            <input type="checkbox">
                                            <span class="checkmarks"></span>
                                        </label>
                                    </td>
                                    <td>{{ __('Quaint Warehouse') }}</td>
                                    <td>{{ __('Cool Warehouse') }}</td>
                                    <td>
                                        21										
                                    </td>
                                    <td>
                                        10
                                    </td>
                                    <td>#135478</td>
                                    <td>{{ __('28 Jul 2023') }}</td>
                                    <td class="d-flex">
                                        <div class="edit-delete-action d-flex align-items-center justify-content-center">
                                            <a class="me-2 p-2 d-flex align-items-center justify-content-between border rounded" href="#" data-bs-toggle="modal" data-bs-target="#edit-stock-transfer">
                                                <i data-feather="edit" class="feather-edit"></i>
                                            </a>
                                            <a class="p-2 d-flex align-items-center justify-content-between border rounded" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#delete">
                                                <i data-feather="trash-2" class="feather-trash-2"></i>
                                            </a>
                                        </div>
                                        
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <label class="checkboxs">
                                            <input type="checkbox">
                                            <span class="checkmarks"></span>
                                        </label>
                                    </td>
                                    <td>{{ __('Traditional Warehouse') }}</td>
                                    <td>{{ __('Retail Supply Hub') }}</td>
                                    <td>
                                        15											
                                    </td>
                                    <td>
                                        14
                                    </td>
                                    <td>#145124</td>
                                    <td>{{ __('24 Jul 2023') }}</td>
                                    <td class="d-flex">
                                        <div class="edit-delete-action d-flex align-items-center justify-content-center">
                                            <a class="me-2 p-2 d-flex align-items-center justify-content-between border rounded" href="#" data-bs-toggle="modal" data-bs-target="#edit-stock-transfer">
                                                <i data-feather="edit" class="feather-edit"></i>
                                            </a>
                                            <a class="p-2 d-flex align-items-center justify-content-between border rounded" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#delete">
                                                <i data-feather="trash-2" class="feather-trash-2"></i>
                                            </a>
                                        </div>
                                        
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <label class="checkboxs">
                                            <input type="checkbox">
                                            <span class="checkmarks"></span>
                                        </label>
                                    </td>
                                    <td>{{ __('Cool Warehouse') }}</td>
                                    <td>{{ __('EdgeWare Solutions') }}</td>
                                    <td>
                                        14												
                                    </td>
                                    <td>
                                        74
                                    </td>
                                    <td>#474541</td>
                                    <td>{{ __('15 Jul 2023') }}</td>
                                    <td class="d-flex">
                                        <div class="edit-delete-action d-flex align-items-center justify-content-center">
                                            <a class="me-2 p-2 d-flex align-items-center justify-content-between border rounded" href="#" data-bs-toggle="modal" data-bs-target="#edit-stock-transfer">
                                                <i data-feather="edit" class="feather-edit"></i>
                                            </a>
                                            <a class="p-2 d-flex align-items-center justify-content-between border rounded" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#delete">
                                                <i data-feather="trash-2" class="feather-trash-2"></i>
                                            </a>
                                        </div>
                                        
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <label class="checkboxs">
                                            <input type="checkbox">
                                            <span class="checkmarks"></span>
                                        </label>
                                    </td>
                                    <td>{{ __('Overflow Warehouse') }}</td>
                                    <td>{{ __('Quaint Warehouse') }}</td>
                                    <td>
                                        30												
                                    </td>
                                    <td>
                                        20
                                    </td>
                                    <td>#366713</td>
                                    <td>{{ __('06 Nov 2024') }}</td>
                                    <td class="d-flex">
                                        <div class="edit-delete-action d-flex align-items-center justify-content-center">
                                            <a class="me-2 p-2 d-flex align-items-center justify-content-between border rounded" href="#" data-bs-toggle="modal" data-bs-target="#edit-stock-transfer">
                                                <i data-feather="edit" class="feather-edit"></i>
                                            </a>
                                            <a class="p-2 d-flex align-items-center justify-content-between border rounded" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#delete">
                                                <i data-feather="trash-2" class="feather-trash-2"></i>
                                            </a>
                                        </div>
                                        
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <label class="checkboxs">
                                            <input type="checkbox">
                                            <span class="checkmarks"></span>
                                        </label>
                                    </td>
                                    <td>{{ __('Nova Storage Hub') }}</td>
                                    <td>{{ __('Traditional Warehouse') }}</td>
                                    <td>
                                        10												
                                    </td>
                                    <td>
                                        06
                                    </td>
                                    <td>#327814</td>
                                    <td>{{ __('25 Oct 2024') }}</td>
                                    <td class="d-flex">
                                        <div class="edit-delete-action d-flex align-items-center justify-content-center">
                                            <a class="me-2 p-2 d-flex align-items-center justify-content-between border rounded" href="#" data-bs-toggle="modal" data-bs-target="#edit-stock-transfer">
                                                <i data-feather="edit" class="feather-edit"></i>
                                            </a>
                                            <a class="p-2 d-flex align-items-center justify-content-between border rounded" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#delete">
                                                <i data-feather="trash-2" class="feather-trash-2"></i>
                                            </a>
                                        </div>
                                        
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <label class="checkboxs">
                                            <input type="checkbox">
                                            <span class="checkmarks"></span>
                                        </label>
                                    </td>
                                    <td>{{ __('Retail Supply Hub') }}</td>
                                    <td>{{ __('Overflow Warehouse') }}</td>
                                    <td>
                                        70												
                                    </td>
                                    <td>
                                        60
                                    </td>
                                    <td>#274509</td>
                                    <td>{{ __('14 Oct 2024') }}</td>
                                    <td class="d-flex">
                                        <div class="edit-delete-action d-flex align-items-center justify-content-center">
                                            <a class="me-2 p-2 d-flex align-items-center justify-content-between border rounded" href="#" data-bs-toggle="modal" data-bs-target="#edit-stock-transfer">
                                                <i data-feather="edit" class="feather-edit"></i>
                                            </a>
                                            <a class="p-2 d-flex align-items-center justify-content-between border rounded" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#delete">
                                                <i data-feather="trash-2" class="feather-trash-2"></i>
                                            </a>
                                        </div>
                                        
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <label class="checkboxs">
                                            <input type="checkbox">
                                            <span class="checkmarks"></span>
                                        </label>
                                    </td>
                                    <td>{{ __('EdgeWare Solutions') }}</td>
                                    <td>{{ __('Lavish Warehouse') }}</td>
                                    <td>
                                        35												
                                    </td>
                                    <td>
                                        30
                                    </td>
                                    <td>#239073</td>
                                    <td>{{ __('03 Oct 2024') }}</td>
                                    <td class="d-flex">
                                        <div class="edit-delete-action d-flex align-items-center justify-content-center">
                                            <a class="me-2 p-2 d-flex align-items-center justify-content-between border rounded" href="#" data-bs-toggle="modal" data-bs-target="#edit-stock-transfer">
                                                <i data-feather="edit" class="feather-edit"></i>
                                            </a>
                                            <a class="p-2 d-flex align-items-center justify-content-between border rounded" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#delete">
                                                <i data-feather="trash-2" class="feather-trash-2"></i>
                                            </a>
                                        </div>
                                        
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <label class="checkboxs">
                                            <input type="checkbox">
                                            <span class="checkmarks"></span>
                                        </label>
                                    </td>
                                    <td>{{ __('North Zone Warehouse') }}</td>
                                    <td>{{ __('Fulfillment Hub') }}</td>
                                    <td>
                                        15												
                                    </td>
                                    <td>
                                        10
                                    </td>
                                    <td>#187204</td>
                                    <td>{{ __('20 Sep 2024') }}</td>
                                    <td class="d-flex">
                                        <div class="edit-delete-action d-flex align-items-center justify-content-center">
                                            <a class="me-2 p-2 d-flex align-items-center justify-content-between border rounded" href="#" data-bs-toggle="modal" data-bs-target="#edit-stock-transfer">
                                                <i data-feather="edit" class="feather-edit"></i>
                                            </a>
                                            <a class="p-2 d-flex align-items-center justify-content-between border rounded" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#delete">
                                                <i data-feather="trash-2" class="feather-trash-2"></i>
                                            </a>
                                        </div>
                                        
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <label class="checkboxs">
                                            <input type="checkbox">
                                            <span class="checkmarks"></span>
                                        </label>
                                    </td>
                                    <td>{{ __('Fulfillment Hub') }}</td>
                                    <td>{{ __('EdgeWare Solutions') }}</td>
                                    <td>
                                        45												
                                    </td>
                                    <td>
                                        35
                                    </td>
                                    <td>#139064</td>
                                    <td>{{ __('10 Sep 2024') }}</td>
                                    <td class="d-flex">
                                        <div class="edit-delete-action d-flex align-items-center justify-content-center">
                                            <a class="me-2 p-2 d-flex align-items-center justify-content-between border rounded" href="#" data-bs-toggle="modal" data-bs-target="#edit-stock-transfer">
                                                <i data-feather="edit" class="feather-edit"></i>
                                            </a>
                                            <a class="d-flex align-items-center justify-content-between border rounded p-2" href="javascript:void(0);">
                                                <i data-feather="trash-2" class="feather-trash-2"></i>
                                            </a>
                                        </div>
                                        
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <label class="checkboxs">
                                            <input type="checkbox">
                                            <span class="checkmarks"></span>
                                        </label>
                                    </td>
                                    <td>{{ __('Retail Supply Hub') }}</td>
                                    <td>{{ __('Overflow Warehouse') }}</td>
                                    <td>
                                        70												
                                    </td>
                                    <td>
                                        60
                                    </td>
                                    <td>#274509</td>
                                    <td>{{ __('14 Oct 2024') }}</td>
                                    <td class="d-flex">
                                        <div class="edit-delete-action d-flex align-items-center justify-content-center">
                                            <a class="me-2 d-flex align-items-center justify-content-between border rounded p-2" href="#" data-bs-toggle="modal" data-bs-target="#edit-stock-transfer">
                                                <i data-feather="edit" class="feather-edit"></i>
                                            </a>
                                            <a class="d-flex align-items-center justify-content-between border rounded p-2" href="javascript:void(0);">
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
            <!-- /product list -->

</x-dashboard::layouts.master>
