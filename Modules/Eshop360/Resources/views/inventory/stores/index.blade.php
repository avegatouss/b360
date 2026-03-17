<x-dashboard::layouts.master
    :title="__('Stores') . ' —' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance"
    :pageTitle="__('Stores')">

<div class="page-header">
            <div class="add-item d-flex">
                <div class="page-title">
                    <h4>{{ __('Stores') }}</h4>
                    <h6>{{ __('Manage your Store') }}</h6>
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
                    <a data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('Print') }}"><i data-feather="printer" class="feather-rotate-ccw"></i></a>
                </li>
                <li>
                    <a data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('Refresh') }}"><i class="ti ti-refresh"></i></a>
                </li>
                <li>
                    <a data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('Collapse') }}" id="collapse-header"><i class="ti ti-chevron-up"></i></a>
                </li>
            </ul>
            <div class="page-btn">
                <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#add-store"><i class="ti ti-circle-plus me-1"></i>{{ __('Add Store') }}</a>
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
                    <div class="dropdown">
                        <a href="javascript:void(0);" class="dropdown-toggle btn btn-white btn-md d-inline-flex align-items-center" data-bs-toggle="dropdown">
                            Status
                        </a>
                        <ul class="dropdown-menu  dropdown-menu-end p-3">
                            <li>
                                <a href="javascript:void(0);" class="dropdown-item rounded-1">{{ __('Active') }}</a>
                            </li>
                            <li>
                                <a href="javascript:void(0);" class="dropdown-item rounded-1">{{ __('Inactive') }}</a>
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
                                <th>{{ __('Store') }}</th>
                                <th>{{ __('User Name') }}</th>
                                <th>{{ __('Email') }}</th>
                                <th>{{ __('Phone') }}</th>
                                <th>{{ __('Status') }}</th>
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
                                <td class="text-gray-9">{{ __('Electro Mart') }}</td>
                                <td>
                                    johnsmith
                                </td>
                                <td>
                                    electromart@example.com						
                                </td>
                                <td>+12498345785</td>											
                                <td>
                                    <span class="badge badge-success d-inline-flex align-items-center badge-xs">
                                        <i class="ti ti-point-filled me-1"></i>Active
                                    </span>
                                </td>
                                <td class="action-table-data">
                                    <div class="edit-delete-action">
                                        <a class="me-2 p-2" href="#">
                                            <i data-feather="eye" class="feather-eye"></i>
                                        </a>
                                        <a class="me-2 p-2" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#edit-store">
                                            <i data-feather="edit" class="feather-edit"></i>
                                        </a>
                                        <a class="p-2" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#delete-modal">
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
                                <td class="text-gray-9">{{ __('Quantum Gadgets') }}</td>
                                <td>
                                    janedoe
                                </td>
                                <td>
                                    quantum@example.com								
                                </td>
                                <td>+13178964582 </td>											
                                <td>
                                    <span class="badge badge-success d-inline-flex align-items-center badge-xs">
                                        <i class="ti ti-point-filled me-1"></i>Active
                                    </span>
                                </td>
                                <td class="action-table-data">
                                    <div class="edit-delete-action">
                                        <a class="me-2 p-2" href="#">
                                            <i data-feather="eye" class="feather-eye"></i>
                                        </a>
                                        <a class="me-2 p-2" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#edit-store">
                                            <i data-feather="edit" class="feather-edit"></i>
                                        </a>
                                        <a class="p-2" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#delete-modal">
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
                                <td class="text-gray-9">{{ __('Prime Bazaar') }}</td>
                                <td>
                                    sarahlee
                                </td>											
                                <td>
                                    primebazaar@example.com								
                                </td>
                                <td>+12796183487 </td>											
                                <td>
                                    <span class="badge badge-success d-inline-flex align-items-center badge-xs">
                                        <i class="ti ti-point-filled me-1"></i>Active
                                    </span>
                                </td>
                                <td class="action-table-data">
                                    <div class="edit-delete-action">
                                        <a class="me-2 p-2" href="#">
                                            <i data-feather="eye" class="feather-eye"></i>
                                        </a>
                                        <a class="me-2 p-2" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#edit-store">
                                            <i data-feather="edit" class="feather-edit"></i>
                                        </a>
                                        <a class="p-2" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#delete-modal">
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
                                <td class="text-gray-9">{{ __('Gadget World') }}</td>
                                <td>
                                    alexbrown
                                </td>										
                                <td>
                                    gadgetworld@example.com								
                                </td>
                                <td>+17538647943 </td>											
                                <td>
                                    <span class="badge badge-success d-inline-flex align-items-center badge-xs">
                                        <i class="ti ti-point-filled me-1"></i>Active
                                    </span>
                                </td>
                                <td class="action-table-data">
                                    <div class="edit-delete-action">
                                        <a class="me-2 p-2" href="#">
                                            <i data-feather="eye" class="feather-eye"></i>
                                        </a>
                                        <a class="me-2 p-2" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#edit-store">
                                            <i data-feather="edit" class="feather-edit"></i>
                                        </a>
                                        <a class="p-2" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#delete-modal">
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
                                <td class="text-gray-9">{{ __('Volt Vault') }}</td>
                                <td>
                                    jesswhite
                                </td>											
                                <td>
                                    voltvault@example.com								
                                </td>
                                <td>+13798132475</td>
                                <td>
                                    <span class="badge badge-success d-inline-flex align-items-center badge-xs">
                                        <i class="ti ti-point-filled me-1"></i>Active
                                    </span>
                                </td>
                                <td class="action-table-data">
                                    <div class="edit-delete-action">
                                        <a class="me-2 p-2" href="#">
                                            <i data-feather="eye" class="feather-eye"></i>
                                        </a>
                                        <a class="me-2 p-2" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#edit-store">
                                            <i data-feather="edit" class="feather-edit"></i>
                                        </a>
                                        <a class="p-2" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#delete-modal">
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
                                <td class="text-gray-9">{{ __('Elite Retail') }}</td>
                                <td>
                                    emilydavis
                                </td>
                                <td>
                                    eliteretail@example.com								
                                </td>
                                <td>+17596341894</td>											
                                <td>
                                    <span class="badge badge-success d-inline-flex align-items-center badge-xs">
                                        <i class="ti ti-point-filled me-1"></i>Active
                                    </span>
                                </td>
                                <td class="action-table-data">
                                    <div class="edit-delete-action">
                                        <a class="me-2 p-2" href="#">
                                            <i data-feather="eye" class="feather-eye"></i>
                                        </a>
                                        <a class="me-2 p-2" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#edit-store">
                                            <i data-feather="edit" class="feather-edit"></i>
                                        </a>
                                        <a class="p-2" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#delete-modal">
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
                                <td class="text-gray-9">{{ __('Prime Mart') }}</td>
                                <td>
                                    tomharris
                                </td>											
                                <td>
                                    primemart@example.com							
                                </td>
                                <td>+12973548678</td>
                                <td>
                                    <span class="badge badge-success d-inline-flex align-items-center badge-xs">
                                        <i class="ti ti-point-filled me-1"></i>Active
                                    </span>
                                </td>
                                <td class="action-table-data">
                                    <div class="edit-delete-action">
                                        <a class="me-2 p-2" href="#">
                                            <i data-feather="eye" class="feather-eye"></i>
                                        </a>
                                        <a class="me-2 p-2" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#edit-store">
                                            <i data-feather="edit" class="feather-edit"></i>
                                        </a>
                                        <a class="p-2" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#delete-modal">
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
                                <td class="text-gray-9">{{ __('NeoTech Store') }}</td>
                                <td>
                                    sarahjohnson
                                </td>										
                                <td>
                                    neotech@example.com							
                                </td>
                                <td>+13147858357 </td>
                                <td>
                                    <span class="badge badge-success d-inline-flex align-items-center badge-xs">
                                        <i class="ti ti-point-filled me-1"></i>Active
                                    </span>
                                </td>
                                <td class="action-table-data">
                                    <div class="edit-delete-action">
                                        <a class="me-2 p-2" href="#">
                                            <i data-feather="eye" class="feather-eye"></i>
                                        </a>
                                        <a class="me-2 p-2" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#edit-store">
                                            <i data-feather="edit" class="feather-edit"></i>
                                        </a>
                                        <a class="p-2" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#delete-modal">
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
                                <td class="text-gray-9">{{ __('Urban Mart') }}</td>
                                <td>
                                    laurawilson
                                </td>
                                <td>
                                    urbanmart@example.com						
                                </td>
                                <td>+11978348626 </td>
                                <td>
                                    <span class="badge badge-success d-inline-flex align-items-center badge-xs">
                                        <i class="ti ti-point-filled me-1"></i>Active
                                    </span>
                                </td>
                                <td class="action-table-data">
                                    <div class="edit-delete-action">
                                        <a class="me-2 p-2" href="#">
                                            <i data-feather="eye" class="feather-eye"></i>
                                        </a>
                                        <a class="me-2 p-2" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#edit-store">
                                            <i data-feather="edit" class="feather-edit"></i>
                                        </a>
                                        <a class="p-2" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#delete-modal">
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
                                <td class="text-gray-9">{{ __('Travel Mart') }}</td>
                                <td>
                                    robertwhite
                                </td>
                                <td>
                                    travelmart@example.com					
                                </td>
                                <td>+12678934561 </td>
                                <td>
                                    <span class="badge badge-success d-inline-flex align-items-center badge-xs">
                                        <i class="ti ti-point-filled me-1"></i>Active
                                    </span>
                                </td>
                                <td class="action-table-data">
                                    <div class="edit-delete-action">
                                        <a class="me-2 p-2" href="#">
                                            <i data-feather="eye" class="feather-eye"></i>
                                        </a>
                                        <a class="me-2 p-2" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#edit-store">
                                            <i data-feather="edit" class="feather-edit"></i>
                                        </a>
                                        <a class="p-2" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#delete-modal">
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
