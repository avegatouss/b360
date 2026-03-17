<x-dashboard::layouts.master
    :title="__('Stock Adjustment') . ' —' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance"
    :pageTitle="__('Stock Adjustment')">

<div class="page-header">
            <div class="add-item d-flex">
                <div class="page-title">
                    <h4>{{ __('Stock Adjustment') }}</h4>
                    <h6>{{ __('Manage your stock adjustment') }}</h6>
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
                <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#add-stock-adjustment"><i class="ti ti-circle-plus me-1"></i>{{ __('Add Adjustment') }}</a>
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
                            Warehouse
                        </a>
                        <ul class="dropdown-menu  dropdown-menu-end p-3">
                            <li>
                                <a href="javascript:void(0);" class="dropdown-item rounded-1">{{ __('Lavish Warehouse') }}</a>
                            </li>
                            <li>
                                <a href="javascript:void(0);" class="dropdown-item rounded-1">{{ __('Quaint Warehouse') }}</a>
                            </li>
                            <li>
                                <a href="javascript:void(0);" class="dropdown-item rounded-1">{{ __('Cool Warehouse') }}</a>
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
                                <th>{{ __('Warehouse') }}</th>
                                <th>{{ __('Store') }}</th>
                                <th>{{ __('Product') }}</th>
                                <th>{{ __('Date') }}</th>
                                <th>{{ __('Person') }}</th>
                                <th>{{ __('Qty') }}</th>
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
                                <td>{{ __('Electro Mart') }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <a href="javascript:void(0);" class="avatar avatar-md me-2">
                                            <img src="{{URL::asset('build/img/products/stock-img-01.png')}}" alt="product">
                                        </a>
                                        <a href="javascript:void(0);">{{ __('Lenovo IdeaPad 3') }}</a>
                                    </div>												
                                </td>
                                <td>{{ __('24 Dec 2024') }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <a href="javascript:void(0);" class="avatar avatar-md me-2">
                                            <img src="{{URL::asset('build/img/users/user-30.jpg')}}" alt="product">
                                        </a>
                                        <a href="javascript:void(0);">{{ __('James Kirwin') }}</a>
                                    </div>
                                </td>
                                <td>100</td>
                                <td class="d-flex">
                                    <div class="d-flex align-items-center edit-delete-action">
                                        <a class="me-2 border rounded d-flex align-items-center p-2" href="#" data-bs-toggle="modal" data-bs-target="#view-notes">
                                            <i data-feather="file-text" class="feather-file-text"></i>
                                        </a>
                                        <a class="me-2 border rounded d-flex align-items-center p-2" href="#" data-bs-toggle="modal" data-bs-target="#edit-stock-adjustment">
                                            <i data-feather="edit" class="feather-edit"></i>
                                        </a>
                                        <a class="p-2 border rounded d-flex align-items-center" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#delete">
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
                                <td>{{ __('Quantum Gadgets') }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <a href="javascript:void(0);" class="avatar avatar-md me-2">
                                            <img src="{{URL::asset('build/img/products/stock-img-06.png')}}" alt="product">
                                        </a>
                                        <a href="javascript:void(0);">{{ __('Beats Pro') }}</a>
                                    </div>												
                                </td>
                                <td>{{ __('10 Dec 2024') }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <a href="javascript:void(0);" class="avatar avatar-md me-2">
                                            <img src="{{URL::asset('build/img/users/user-13.jpg')}}" alt="product">
                                        </a>
                                        <a href="javascript:void(0);">{{ __('Francis Chang') }}</a>
                                    </div>
                                </td>
                                <td>140</td>
                                <td class="d-flex">
                                    <div class="d-flex align-items-center justify-content-between edit-delete-action">
                                        <a class="me-2 border rounded d-flex align-items-center p-2" href="#" data-bs-toggle="modal" data-bs-target="#view-notes">
                                            <i data-feather="file-text" class="feather-file-text"></i>
                                        </a>
                                        <a class="me-2 p-2 border rounded d-flex align-items-center" href="#" data-bs-toggle="modal" data-bs-target="#edit-stock-adjustment">
                                            <i data-feather="edit" class="feather-edit"></i>
                                        </a>
                                        <a class="p-2 border rounded d-flex align-items-center" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#delete">
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
                                <td>{{ __('Prime Bazaar') }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <a href="javascript:void(0);" class="avatar avatar-md me-2">
                                            <img src="{{URL::asset('build/img/products/stock-img-02.png')}}" alt="product">
                                        </a>
                                        <a href="javascript:void(0);">{{ __('Nike Jordan') }}</a>
                                    </div>												
                                </td>
                                <td>{{ __('25 Jul 2023') }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <a href="javascript:void(0);" class="avatar avatar-md me-2">
                                            <img src="{{URL::asset('build/img/users/user-08.jpg')}}" alt="product">
                                        </a>
                                        <a href="javascript:void(0);">{{ __('Antonio Engle') }}</a>
                                    </div>
                                </td>
                                <td>120</td>
                                <td class="d-flex">
                                    <div class="d-flex align-items-center justify-content-between edit-delete-action">
                                        <a class="me-2 border rounded d-flex align-items-center p-2" href="#" data-bs-toggle="modal" data-bs-target="#view-notes">
                                            <i data-feather="file-text" class="feather-file-text"></i>
                                        </a>
                                        <a class="me-2 p-2 border rounded d-flex align-items-center" href="#" data-bs-toggle="modal" data-bs-target="#edit-stock-adjustment">
                                            <i data-feather="edit" class="feather-edit"></i>
                                        </a>
                                        <a class="p-2 border rounded d-flex align-items-center" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#delete">
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
                                <td>{{ __('Gadget World') }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <a href="javascript:void(0);" class="avatar avatar-md me-2">
                                            <img src="{{URL::asset('build/img/products/stock-img-03.png')}}" alt="product">
                                        </a>
                                        <a href="javascript:void(0);">{{ __('Apple Series 5 Watch') }}</a>
                                    </div>												
                                </td>
                                <td>{{ __('28 Jul 2023') }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <a href="javascript:void(0);" class="avatar avatar-md me-2">
                                            <img src="{{URL::asset('build/img/users/user-04.jpg')}}" alt="product">
                                        </a>
                                        <a href="javascript:void(0);">{{ __('Leo Kelly') }}</a>
                                    </div>
                                </td>
                                <td>130</td>
                                <td class="d-flex">
                                    <div class="d-flex align-items-center justify-content-between edit-delete-action">
                                        <a class="me-2 border rounded d-flex align-items-center p-2" href="#" data-bs-toggle="modal" data-bs-target="#view-notes">
                                            <i data-feather="file-text" class="feather-file-text"></i>
                                        </a>
                                        <a class="me-2 p-2 border rounded d-flex align-items-center" href="#" data-bs-toggle="modal" data-bs-target="#edit-stock-adjustment">
                                            <i data-feather="edit" class="feather-edit"></i>
                                        </a>
                                        <a class="p-2 border rounded d-flex align-items-center" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#delete">
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
                                <td>{{ __('Volt Vault') }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <a href="javascript:void(0);" class="avatar avatar-md me-2">
                                            <img src="{{URL::asset('build/img/products/stock-img-04.png')}}" alt="product">
                                        </a>
                                        <a href="javascript:void(0);">{{ __('Amazon Echo Dot') }}</a>
                                    </div>												
                                </td>
                                <td>{{ __('24 Jul 2023') }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <a href="javascript:void(0);" class="avatar avatar-md me-2">
                                            <img src="{{URL::asset('build/img/users/user-09.jpg')}}" alt="product">
                                        </a>
                                        <a href="javascript:void(0);">{{ __('Annette Walker') }}</a>
                                    </div>
                                </td>
                                <td>140</td>
                                <td class="d-flex">
                                    <div class="d-flex align-items-center justify-content-between edit-delete-action">
                                        <a class="me-2 border rounded d-flex align-items-center p-2" href="#" data-bs-toggle="modal" data-bs-target="#view-notes">
                                            <i data-feather="file-text" class="feather-file-text"></i>
                                        </a>
                                        <a class="me-2 p-2 border rounded d-flex align-items-center" href="#" data-bs-toggle="modal" data-bs-target="#edit-stock-adjustment">
                                            <i data-feather="edit" class="feather-edit"></i>
                                        </a>
                                        <a class="p-2 border rounded d-flex align-items-center" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#delete">
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
                                <td>{{ __('Elite Retail') }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <a href="javascript:void(0);" class="avatar avatar-md me-2">
                                            <img src="{{URL::asset('build/img/products/stock-img-05.png')}}" alt="product">
                                        </a>
                                        <a href="javascript:void(0);">{{ __('Lobar Handy') }}</a>
                                    </div>												
                                </td>
                                <td>{{ __('15 Jul 2023') }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <a href="javascript:void(0);" class="avatar avatar-md me-2">
                                            <img src="{{URL::asset('build/img/users/user-10.jpg')}}" alt="product">
                                        </a>
                                        <a href="javascript:void(0);">{{ __('John Weaver') }}</a>
                                    </div>
                                </td>
                                <td>150</td>
                                <td class="d-flex">
                                    <div class="d-flex align-items-center justify-content-between edit-delete-action">
                                        <a class="me-2 border rounded d-flex align-items-center p-2" href="#" data-bs-toggle="modal" data-bs-target="#view-notes">
                                            <i data-feather="file-text" class="feather-file-text"></i>
                                        </a>
                                        <a class="me-2 p-2 border rounded d-flex align-items-center" href="#" data-bs-toggle="modal" data-bs-target="#edit-stock-adjustment">
                                            <i data-feather="edit" class="feather-edit"></i>
                                        </a>
                                        <a class="p-2 border rounded d-flex align-items-center" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#delete">
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
                                <td>{{ __('Prime Mart') }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <a href="javascript:void(0);" class="avatar avatar-md me-2">
                                            <img src="{{URL::asset('build/img/products/expire-product-01.png')}}" alt="product">
                                        </a>
                                        <a href="javascript:void(0);">{{ __('Red Premium Satchel') }}</a>
                                    </div>												
                                </td>
                                <td>{{ __('14 Oct 2024') }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <a href="javascript:void(0);" class="avatar avatar-md me-2">
                                            <img src="{{URL::asset('build/img/users/user-08.jpg')}}" alt="product">
                                        </a>
                                        <a href="javascript:void(0);">{{ __('Gary Hennessy') }}</a>
                                    </div>
                                </td>
                                <td>700</td>
                                <td class="d-flex">
                                    <div class="d-flex align-items-center justify-content-between edit-delete-action">
                                        <a class="me-2 border rounded d-flex align-items-center p-2" href="#" data-bs-toggle="modal" data-bs-target="#view-notes">
                                            <i data-feather="file-text" class="feather-file-text"></i>
                                        </a>
                                        <a class="me-2 p-2 border rounded d-flex align-items-center" href="#" data-bs-toggle="modal" data-bs-target="#edit-stock-adjustment">
                                            <i data-feather="edit" class="feather-edit"></i>
                                        </a>
                                        <a class="p-2 border rounded d-flex align-items-center" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#delete">
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
                                <td>{{ __('NeoTech Store') }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <a href="javascript:void(0);" class="avatar avatar-md me-2">
                                            <img src="{{URL::asset('build/img/products/expire-product-02.png')}}" alt="product">
                                        </a>
                                        <a href="javascript:void(0);">{{ __('Iphone 14 Pro') }}</a>
                                    </div>												
                                </td>
                                <td>{{ __('03 Oct 2024') }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <a href="javascript:void(0);" class="avatar avatar-md me-2">
                                            <img src="{{URL::asset('build/img/users/user-04.jpg')}}" alt="product">
                                        </a>
                                        <a href="javascript:void(0);">{{ __('Eleanor Panek') }}</a>
                                    </div>
                                </td>
                                <td>630</td>
                                <td class="d-flex">
                                    <div class="d-flex align-items-center justify-content-between edit-delete-action">
                                        <a class="me-2 border rounded d-flex align-items-center p-2" href="#" data-bs-toggle="modal" data-bs-target="#view-notes">
                                            <i data-feather="file-text" class="feather-file-text"></i>
                                        </a>
                                        <a class="me-2 p-2 border rounded d-flex align-items-center" href="#" data-bs-toggle="modal" data-bs-target="#edit-stock-adjustment">
                                            <i data-feather="edit" class="feather-edit"></i>
                                        </a>
                                        <a class="p-2 border rounded d-flex align-items-center" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#delete">
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
                                <td>{{ __('Urban Mart') }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <a href="javascript:void(0);" class="avatar avatar-md me-2">
                                            <img src="{{URL::asset('build/img/products/expire-product-03.png')}}" alt="product">
                                        </a>
                                        <a href="javascript:void(0);">{{ __('Gaming Chair') }}</a>
                                    </div>												
                                </td>
                                <td>{{ __('20 Sep 2024') }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <a href="javascript:void(0);" class="avatar avatar-md me-2">
                                            <img src="{{URL::asset('build/img/users/user-13.jpg')}}" alt="product">
                                        </a>
                                        <a href="javascript:void(0);">{{ __('William Levy') }}</a>
                                    </div>
                                </td>
                                <td>410</td>
                                <td class="d-flex">
                                    <div class="d-flex align-items-center justify-content-between edit-delete-action">
                                        <a class="me-2 border rounded d-flex align-items-center p-2" href="#" data-bs-toggle="modal" data-bs-target="#view-notes">
                                            <i data-feather="file-text" class="feather-file-text"></i>
                                        </a>
                                        <a class="me-2 p-2 border rounded d-flex align-items-center" href="#" data-bs-toggle="modal" data-bs-target="#edit-stock-adjustment">
                                            <i data-feather="edit" class="feather-edit"></i>
                                        </a>
                                        <a class="p-2 border rounded d-flex align-items-center" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#delete">
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
                                <td>{{ __('Travel Mart') }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <a href="javascript:void(0);" class="avatar avatar-md me-2">
                                            <img src="{{URL::asset('build/img/products/expire-product-04.png')}}" alt="product">
                                        </a>
                                        <a href="javascript:void(0);">{{ __('Borealis Backpack') }}</a>
                                    </div>												
                                </td>
                                <td>{{ __('10 Sep 2024') }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <a href="javascript:void(0);" class="avatar avatar-md me-2">
                                            <img src="{{URL::asset('build/img/users/user-16.jpg')}}" alt="product">
                                        </a>
                                        <a href="javascript:void(0);">{{ __('Charlotte Klotz') }}</a>
                                    </div>
                                </td>
                                <td>550</td>
                                <td class="d-flex">
                                    <div class="d-flex align-items-center justify-content-between edit-delete-action">
                                        <a class="me-2 border rounded d-flex align-items-center p-2" href="#" data-bs-toggle="modal" data-bs-target="#view-notes">
                                            <i data-feather="file-text" class="feather-file-text"></i>
                                        </a>
                                        <a class="me-2 p-2 border rounded d-flex align-items-center" href="#" data-bs-toggle="modal" data-bs-target="#edit-stock-adjustment">
                                            <i data-feather="edit" class="feather-edit"></i>
                                        </a>
                                        <a class="p-2 border rounded d-flex align-items-center" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#delete">
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
