<x-dashboard::layouts.master
    :title="__('Sub Categories') . ' —' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance"
    :pageTitle="__('Sub Categories')">

<div class="page-header">
            <div class="add-item d-flex">
                <div class="page-title">
                    <h4 class="fw-bold">{{ __('Sub Category') }}</h4>
                    <h6>{{ __('Manage your sub categories') }}</h6>
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
                <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#add-category"><i class="ti ti-circle-plus me-1"></i>{{ __('Add Sub Category') }}</a>
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
                            Category
                        </a>
                        <ul class="dropdown-menu  dropdown-menu-end p-3">
                            <li>
                                <a href="javascript:void(0);" class="dropdown-item rounded-1">{{ __('Computers') }}</a>
                            </li>
                            <li>
                                <a href="javascript:void(0);" class="dropdown-item rounded-1">{{ __('Electronics') }}</a>
                            </li>
                            <li>
                                <a href="javascript:void(0);" class="dropdown-item rounded-1">{{ __('Shoe') }}</a>
                            </li>
                            <li>
                                <a href="javascript:void(0);" class="dropdown-item rounded-1">{{ __('Electronics') }}</a>
                            </li>
                        </ul>
                    </div>
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
                                <th>{{ __('Image') }}</th>
                                <th>{{ __('Sub Category') }}</th>
                                <th>{{ __('Category') }}</th>
                                <th>{{ __('Category Code') }}</th>
                                <th>{{ __('Description') }}</th>
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
                                <td>
                                    <a class="avatar avatar-md me-2">
                                        <img src="{{URL::asset('build/img/products/stock-img-01.png')}}" alt="product">
                                    </a>
                                </td>
                                <td>{{ __('Laptop') }}</td>
                                <td>{{ __('Computers') }}</td>
                                <td>{{ __('CT001') }}</td>
                                <td>{{ __('Efficient Productivity') }}</td>
                                <td><span class="badge bg-success fw-medium fs-10">{{ __('Active') }}</span></td>
                                <td class="action-table-data">
                                    <div class="edit-delete-action">
                                        <a class="me-2 p-2" href="#" data-bs-toggle="modal" data-bs-target="#edit-category">
                                            <i data-feather="edit" class="feather-edit"></i>
                                        </a>
                                        <a data-bs-toggle="modal" data-bs-target="#delete-modal" class="p-2" href="javascript:void(0);">
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
                                <td>
                                    <a class="avatar avatar-md me-2">
                                        <img src="{{URL::asset('build/img/products/stock-img-07.png')}}" alt="product">
                                    </a>
                                </td>
                                <td>{{ __('Desktop') }}</td>
                                <td>{{ __('Computers') }}</td>
                                <td>{{ __('CT002') }}</td>
                                <td>{{ __('Compact Design') }}</td>
                                <td><span class="badge bg-success fw-medium fs-10">{{ __('Active') }}</span></td>
                                <td class="action-table-data">
                                    <div class="edit-delete-action">
                                        <a class="me-2 p-2" href="#" data-bs-toggle="modal" data-bs-target="#edit-category">
                                            <i data-feather="edit" class="feather-edit"></i>
                                        </a>
                                        <a data-bs-toggle="modal" data-bs-target="#delete-modal" class="p-2" href="javascript:void(0);">
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
                                <td>
                                    <a class="avatar avatar-md me-2">
                                        <img src="{{URL::asset('build/img/products/stock-img-02.png')}}" alt="product">
                                    </a>
                                </td>
                                <td>{{ __('Sneakers') }}</td>
                                <td>{{ __('Shoe') }}</td>
                                <td>{{ __('CT003') }}</td>
                                <td>{{ __('Dynamic Grip') }}</td>
                                <td><span class="badge bg-success fw-medium fs-10">{{ __('Active') }}</span></td>
                                <td class="action-table-data">
                                    <div class="edit-delete-action">
                                        <a class="me-2 p-2" href="#" data-bs-toggle="modal" data-bs-target="#edit-category">
                                            <i data-feather="edit" class="feather-edit"></i>
                                        </a>
                                        <a data-bs-toggle="modal" data-bs-target="#delete-modal" class="p-2" href="javascript:void(0);">
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
                                <td>
                                    <a class="avatar avatar-md me-2">
                                        <img src="{{URL::asset('build/img/products/stock-img-08.png')}}" alt="product">
                                    </a>
                                </td>
                                <td>{{ __('Formals') }}</td>
                                <td>{{ __('Shoe') }}</td>
                                <td>{{ __('CT004') }}</td>
                                <td>{{ __('Stylish Comfort') }}</td>
                                <td><span class="badge bg-success fw-medium fs-10">{{ __('Active') }}</span></td>
                                <td class="action-table-data">
                                    <div class="edit-delete-action">
                                        <a class="me-2 p-2" href="#" data-bs-toggle="modal" data-bs-target="#edit-category">
                                            <i data-feather="edit" class="feather-edit"></i>
                                        </a>
                                        <a data-bs-toggle="modal" data-bs-target="#delete-modal" class="p-2" href="javascript:void(0);">
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
                                <td>
                                    <a class="avatar avatar-md me-2">
                                        <img src="{{URL::asset('build/img/products/stock-img-06.png')}}" alt="product">
                                    </a>
                                </td>
                                <td>{{ __('Wearables') }}</td>
                                <td>{{ __('Electronics') }}</td>
                                <td>{{ __('CT005') }}</td>
                                <td>{{ __('Seamless Connectivity') }}</td>
                                <td><span class="badge bg-success fw-medium fs-10">{{ __('Active') }}</span></td>
                                <td class="action-table-data">
                                    <div class="edit-delete-action">
                                        <a class="me-2 p-2" href="#" data-bs-toggle="modal" data-bs-target="#edit-category">
                                            <i data-feather="edit" class="feather-edit"></i>
                                        </a>
                                        <a data-bs-toggle="modal" data-bs-target="#delete-modal" class="p-2" href="javascript:void(0);">
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
                                <td>
                                    <a class="avatar avatar-md me-2">
                                        <img src="{{URL::asset('build/img/products/stock-img-04.png')}}" alt="product">
                                    </a>
                                </td>
                                <td>{{ __('Speakers') }}</td>
                                <td>{{ __('Electronics') }}</td>
                                <td>{{ __('CT006') }}</td>
                                <td>{{ __('Reliable Sound') }}</td>
                                <td><span class="badge bg-success fw-medium fs-10">{{ __('Active') }}</span></td>
                                <td class="action-table-data">
                                    <div class="edit-delete-action">
                                        <a class="me-2 p-2" href="#" data-bs-toggle="modal" data-bs-target="#edit-category">
                                            <i data-feather="edit" class="feather-edit"></i>
                                        </a>
                                        <a data-bs-toggle="modal" data-bs-target="#delete-modal" class="p-2" href="javascript:void(0);">
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
                                <td>
                                    <a class="avatar avatar-md me-2">
                                        <img src="{{URL::asset('build/img/products/expire-product-01.png')}}" alt="product">
                                    </a>
                                </td>
                                <td>{{ __('Handbags') }}</td>
                                <td>{{ __('Bags') }}</td>
                                <td>{{ __('CT007') }}</td>
                                <td>{{ __('Compact Carry') }}</td>
                                <td><span class="badge bg-success fw-medium fs-10">{{ __('Active') }}</span></td>
                                <td class="action-table-data">
                                    <div class="edit-delete-action">
                                        <a class="me-2 p-2" href="#" data-bs-toggle="modal" data-bs-target="#edit-category">
                                            <i data-feather="edit" class="feather-edit"></i>
                                        </a>
                                        <a data-bs-toggle="modal" data-bs-target="#delete-modal" class="p-2" href="javascript:void(0);">
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
                                <td>
                                    <a class="avatar avatar-md me-2">
                                        <img src="{{URL::asset('build/img/products/expire-product-04.png')}}" alt="product">
                                    </a>
                                </td>
                                <td>{{ __('Travel') }}</td>
                                <td>{{ __('Bags') }}</td>
                                <td>{{ __('CT008') }}</td>
                                <td>{{ __('Travel Ready') }}</td>
                                <td><span class="badge bg-success fw-medium fs-10">{{ __('Active') }}</span></td>
                                <td class="action-table-data">
                                    <div class="edit-delete-action">
                                        <a class="me-2 p-2" href="#" data-bs-toggle="modal" data-bs-target="#edit-category">
                                            <i data-feather="edit" class="feather-edit"></i>
                                        </a>
                                        <a data-bs-toggle="modal" data-bs-target="#delete-modal" class="p-2" href="javascript:void(0);">
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
                                <td>
                                    <a class="avatar avatar-md me-2">
                                        <img src="{{URL::asset('build/img/products/stock-img-05.png')}}" alt="product">
                                    </a>
                                </td>
                                <td>{{ __('Sofa') }}</td>
                                <td>{{ __('Furniture') }}</td>
                                <td>{{ __('CT009') }}</td>
                                <td>{{ __('Cozy Comfort') }}</td>
                                <td><span class="badge bg-success fw-medium fs-10">{{ __('Active') }}</span></td>
                                <td class="action-table-data">
                                    <div class="edit-delete-action">
                                        <a class="me-2 p-2" href="#" data-bs-toggle="modal" data-bs-target="#edit-category">
                                            <i data-feather="edit" class="feather-edit"></i>
                                        </a>
                                        <a data-bs-toggle="modal" data-bs-target="#delete-modal" class="p-2" href="javascript:void(0);">
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
                                <td>
                                    <a class="avatar avatar-md me-2">
                                        <img src="{{URL::asset('build/img/products/expire-product-03.png')}}" alt="product">
                                    </a>
                                </td>
                                <td>{{ __('Chair') }}</td>
                                <td>{{ __('Furniture') }}</td>
                                <td>{{ __('CT0010') }}</td>
                                <td>{{ __('Stylish Comfort') }}</td>
                                <td><span class="badge bg-success fw-medium fs-10">{{ __('Active') }}</span></td>
                                <td class="action-table-data">
                                    <div class="edit-delete-action">
                                        <a class="me-2 p-2" href="#" data-bs-toggle="modal" data-bs-target="#edit-category">
                                            <i data-feather="edit" class="feather-edit"></i>
                                        </a>
                                        <a data-bs-toggle="modal" data-bs-target="#delete-modal" class="p-2" href="javascript:void(0);">
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
                                <td>
                                    <a class="avatar avatar-md me-2">
                                        <img src="{{URL::asset('build/img/products/product4.jpg')}}" alt="product">
                                    </a>
                                </td>
                                <td>{{ __('Fruits') }}</td>
                                <td>{{ __('Fruits') }}</td>
                                <td>{{ __('CT004') }}</td>
                                <td>{{ __('Fruits Description') }}</td>
                                <td><span class="badge bg-success fw-medium fs-10">{{ __('Active') }}</span></td>
                                <td class="action-table-data">
                                    <div class="edit-delete-action">
                                        <a class="me-2 p-2" href="#" data-bs-toggle="modal" data-bs-target="#edit-category">
                                            <i data-feather="edit" class="feather-edit"></i>
                                        </a>
                                        <a data-bs-toggle="modal" data-bs-target="#delete-modal" class="p-2" href="javascript:void(0);">
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
                                <td>
                                    <a class="avatar avatar-md me-2">
                                        <img src="{{URL::asset('build/img/products/product5.jpg')}}" alt="product">
                                    </a>
                                </td>
                                <td>{{ __('Accessories') }}</td>
                                <td>{{ __('Accessories') }}</td>
                                <td>{{ __('CT005') }}</td>
                                <td>{{ __('Accessories Description') }}</td>
                                <td><span class="badge bg-success fw-medium fs-10">{{ __('Active') }}</span></td>
                                <td class="action-table-data">
                                    <div class="edit-delete-action">
                                        <a class="me-2 p-2" href="#" data-bs-toggle="modal" data-bs-target="#edit-category">
                                            <i data-feather="edit" class="feather-edit"></i>
                                        </a>
                                        <a data-bs-toggle="modal" data-bs-target="#delete-modal" class="p-2" href="javascript:void(0);">
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
                                <td>
                                    <a class="avatar avatar-md me-2">
                                        <img src="{{URL::asset('build/img/products/product6.jpg')}}" alt="product">
                                    </a>
                                </td>
                                <td>{{ __('Shoes') }}</td>
                                <td>{{ __('Shoes') }}</td>
                                <td>{{ __('CT006') }}</td>
                                <td>{{ __('Shoes Description') }}</td>
                                <td><span class="badge bg-success fw-medium fs-10">{{ __('Active') }}</span></td>
                                <td class="action-table-data">
                                    <div class="edit-delete-action">
                                        <a class="me-2 p-2" href="#" data-bs-toggle="modal" data-bs-target="#edit-category">
                                            <i data-feather="edit" class="feather-edit"></i>
                                        </a>
                                        <a data-bs-toggle="modal" data-bs-target="#delete-modal" class="p-2" href="javascript:void(0);">
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
                                <td>
                                    <a class="avatar avatar-md me-2">
                                        <img src="{{URL::asset('build/img/products/product7.jpg')}}" alt="product">
                                    </a>
                                </td>
                                <td>{{ __('Fruits') }}</td>
                                <td>{{ __('Fruits') }}</td>
                                <td>{{ __('CT007') }}</td>
                                <td>{{ __('Fruits Description') }}</td>
                                <td><span class="badge bg-success fw-medium fs-10">{{ __('Active') }}</span></td>
                                <td class="action-table-data">
                                    <div class="edit-delete-action">
                                        <a class="me-2 p-2" href="#" data-bs-toggle="modal" data-bs-target="#edit-category">
                                            <i data-feather="edit" class="feather-edit"></i>
                                        </a>
                                        <a data-bs-toggle="modal" data-bs-target="#delete-modal" class="p-2" href="javascript:void(0);">
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
                                <td>
                                    <a class="avatar avatar-md me-2">
                                        <img src="{{URL::asset('build/img/products/product8.jpg')}}" alt="product">
                                    </a>
                                </td>
                                <td>{{ __('Fruits') }}</td>
                                <td>{{ __('Fruits') }}</td>
                                <td>{{ __('CT008') }}</td>
                                <td>{{ __('Fruits Description') }}</td>
                                <td><span class="badge bg-success fw-medium fs-10">{{ __('Active') }}</span></td>
                                <td class="action-table-data">
                                    <div class="edit-delete-action">
                                        <a class="me-2 p-2" href="#" data-bs-toggle="modal" data-bs-target="#edit-category">
                                            <i data-feather="edit" class="feather-edit"></i>
                                        </a>
                                        <a data-bs-toggle="modal" data-bs-target="#delete-modal" class="p-2" href="javascript:void(0);">
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
                                <td>
                                    <a class="avatar avatar-md me-2">
                                        <img src="{{URL::asset('build/img/products/product9.jpg')}}" alt="product">
                                    </a>
                                </td>
                                <td>{{ __('Computers') }}</td>
                                <td>{{ __('Computers') }}</td>
                                <td>{{ __('CT009') }}</td>
                                <td>{{ __('Computers Description') }}</td>
                                <td><span class="badge bg-success fw-medium fs-10">{{ __('Active') }}</span></td>
                                <td class="action-table-data">
                                    <div class="edit-delete-action">
                                        <a class="me-2 p-2" href="#" data-bs-toggle="modal" data-bs-target="#edit-category">
                                            <i data-feather="edit" class="feather-edit"></i>
                                        </a>
                                        <a data-bs-toggle="modal" data-bs-target="#delete-modal" class="p-2" href="javascript:void(0);">
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
                                <td>
                                    <a class="avatar avatar-md me-2">
                                        <img src="{{URL::asset('build/img/products/product10.jpg')}}" alt="product">
                                    </a>
                                </td>
                                <td>{{ __('Health Care') }}</td>
                                <td>{{ __('Health Care') }}</td>
                                <td>{{ __('CT0010') }}</td>
                                <td>{{ __('Health Care Description') }}</td>
                                <td><span class="badge bg-success fw-medium fs-10">{{ __('Active') }}</span></td>
                                <td class="action-table-data">
                                    <div class="edit-delete-action">
                                        <a class="me-2 p-2" href="#" data-bs-toggle="modal" data-bs-target="#edit-category">
                                            <i data-feather="edit" class="feather-edit"></i>
                                        </a>
                                        <a data-bs-toggle="modal" data-bs-target="#delete-modal" class="p-2" href="javascript:void(0);">
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
