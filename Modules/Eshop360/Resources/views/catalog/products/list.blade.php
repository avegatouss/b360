<x-dashboard::layouts.master
    :title="__('Product List') . ' —' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance"
    :pageTitle="__('Product List')">

<div class="page-header">
            <div class="add-item d-flex">
                <div class="page-title">
                    <h4 class="fw-bold">{{ __('Product List') }}</h4>
                    <h6>{{ __('Manage your products') }}</h6>
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
                <a href="{{url('add-product')}}" class="btn btn-primary"><i class="ti ti-circle-plus me-1"></i>Add Product</a>
            </div>	
            <div class="page-btn import">
                <a href="#" class="btn btn-secondary color" data-bs-toggle="modal" data-bs-target="#view-notes"><i
                    data-feather="download" class="me-1"></i>{{ __('Import Product') }}</a>
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
                            Brand
                        </a>
                        <ul class="dropdown-menu  dropdown-menu-end p-3">
                            <li>
                                <a href="javascript:void(0);" class="dropdown-item rounded-1">{{ __('Lenovo') }}</a>
                            </li>
                            <li>
                                <a href="javascript:void(0);" class="dropdown-item rounded-1">{{ __('Beats') }}</a>
                            </li>
                            <li>
                                <a href="javascript:void(0);" class="dropdown-item rounded-1">{{ __('Nike') }}</a>
                            </li>
                            <li>
                                <a href="javascript:void(0);" class="dropdown-item rounded-1">{{ __('Apple') }}</a>
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
                                <th>{{ __('SKU') }}</th>
                                <th>{{ __('Product Name') }}</th>
                                <th>{{ __('Category') }}</th>
                                <th>{{ __('Brand') }}</th>
                                <th>{{ __('Price') }}</th>
                                <th>{{ __('Unit') }}</th>
                                <th>{{ __('Qty') }}</th>
                                <th>{{ __('Created By') }}</th>
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
                                <td>{{ __('PT001') }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <a href="javascript:void(0);" class="avatar avatar-md me-2">
                                            <img src="{{URL::asset('build/img/products/stock-img-01.png')}}" alt="product">
                                        </a>
                                        <a href="javascript:void(0);">{{ __('Lenovo IdeaPad 3') }}</a>
                                    </div>												
                                </td>							
                                <td>{{ __('Computers') }}</td>
                                <td>{{ __('Lenovo') }}</td>
                                <td>$600</td>
                                <td>{{ __('Pc') }}</td>
                                <td>100</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <a href="javascript:void(0);" class="avatar avatar-sm me-2">
                                            <img src="{{URL::asset('build/img/users/user-30.jpg')}}" alt="product">
                                        </a>
                                        <a href="javascript:void(0);">{{ __('James Kirwin') }}</a>
                                    </div>
                                </td>
                                <td class="action-table-data">
                                    <div class="edit-delete-action">
                                        <a class="me-2 edit-icon  p-2" href="{{url('product-details')}}">
                                            <i data-feather="eye" class="feather-eye"></i>
                                        </a>
                                        <a class="me-2 p-2" href="{{url('edit-product')}}" >
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
                                <td>{{ __('PT002') }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <a href="javascript:void(0);" class="avatar avatar-md me-2">
                                            <img src="{{URL::asset('build/img/products/stock-img-06.png')}}" alt="product">
                                        </a>
                                        <a href="javascript:void(0);">{{ __('Beats Pro') }}</a>
                                    </div>												
                                </td>
                                <td>{{ __('Electronics') }}</td>
                                <td>{{ __('Beats') }}</td>
                                <td>$160</td>
                                <td>{{ __('Pc') }}</td>
                                <td>140</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <a href="javascript:void(0);" class="avatar avatar-sm me-2">
                                            <img src="{{URL::asset('build/img/users/user-13.jpg')}}" alt="product">
                                        </a>
                                        <a href="javascript:void(0);">{{ __('Francis Chang') }}</a>
                                    </div>
                                </td>
                                <td class="action-table-data">
                                    <div class="edit-delete-action">
                                        <a class="me-2 edit-icon p-2" href="{{url('product-details')}}">
                                            <i data-feather="eye" class="action-eye"></i>
                                        </a>
                                        <a class="me-2 p-2" href="{{url('edit-product')}}">
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
                                <td>{{ __('PT003') }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <a href="javascript:void(0);" class="avatar avatar-md me-2">
                                            <img src="{{URL::asset('build/img/products/stock-img-02.png')}}" alt="product">
                                        </a>
                                        <a href="javascript:void(0);">{{ __('Nike Jordan') }}</a>
                                    </div>												
                                </td>											
                                <td>{{ __('Shoe') }}</td>
                                <td>{{ __('Nike') }}</td>
                                <td>$110</td>
                                <td>{{ __('Pc') }}</td>
                                <td>300</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <a href="javascript:void(0);" class="avatar avatar-sm me-2">
                                            <img src="{{URL::asset('build/img/users/user-11.jpg')}}" alt="product">
                                        </a>
                                        <a href="javascript:void(0);">{{ __('Antonio Engle') }}</a>
                                    </div>
                                </td>
                                <td class="action-table-data">
                                    <div class="edit-delete-action">
                                        <a class="me-2 edit-icon p-2" href="{{url('product-details')}}">
                                            <i data-feather="eye" class="action-eye"></i>
                                        </a>
                                        <a class="me-2 p-2" href="{{url('edit-product')}}">
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
                                <td>{{ __('PT004') }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <a href="javascript:void(0);" class="avatar avatar-md me-2">
                                            <img src="{{URL::asset('build/img/products/stock-img-03.png')}}" alt="product">
                                        </a>
                                        <a href="javascript:void(0);">{{ __('Apple Series 5 Watch') }}</a>
                                    </div>												
                                </td>											
                                <td>{{ __('Electronics') }}</td>
                                <td>{{ __('Apple') }}</td>
                                <td>$120</td>
                                <td>{{ __('Pc') }}</td>
                                <td>450</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <a href="javascript:void(0);" class="avatar avatar-sm me-2">
                                            <img src="{{URL::asset('build/img/users/user-32.jpg')}}" alt="product">
                                        </a>
                                        <a href="javascript:void(0);">{{ __('Leo Kelly') }}</a>
                                    </div>
                                </td>
                                <td class="action-table-data">
                                    <div class="edit-delete-action">
                                        <a class="me-2 edit-icon p-2" href="{{url('product-details')}}">
                                            <i data-feather="eye" class="action-eye"></i>
                                        </a>
                                        <a class="me-2 p-2" href="{{url('edit-product')}}">
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
                                <td>{{ __('PT005') }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <a href="javascript:void(0);" class="avatar avatar-md me-2">
                                            <img src="{{URL::asset('build/img/products/stock-img-04.png')}}" alt="product">
                                        </a>
                                        <a href="javascript:void(0);">{{ __('Amazon Echo Dot') }}</a>
                                    </div>												
                                </td>											
                                <td>{{ __('Electronics') }}</td>
                                <td>{{ __('Amazon') }}</td>
                                <td>$80</td>
                                <td>{{ __('Pc') }}</td>
                                <td>320</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <a href="javascript:void(0);" class="avatar avatar-sm me-2">
                                            <img src="{{URL::asset('build/img/users/user-02.jpg')}}" alt="product">
                                        </a>
                                        <a href="javascript:void(0);">{{ __('Annette Walker') }}</a>
                                    </div>
                                </td>
                                <td class="action-table-data">
                                    <div class="edit-delete-action">
                                        <a class="me-2 edit-icon p-2" href="{{url('product-details')}}">
                                            <i data-feather="eye" class="action-eye"></i>
                                        </a>
                                        <a class="me-2 p-2" href="{{url('edit-product')}}">
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
                                <td>{{ __('PT006') }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <a href="javascript:void(0);" class="avatar avatar-md me-2">
                                            <img src="{{URL::asset('build/img/products/stock-img-05.png')}}" alt="product">
                                        </a>
                                        <a href="javascript:void(0);">{{ __('Sanford Chair Sofa') }}</a>
                                    </div>												
                                </td>											
                                <td>{{ __('Furnitures') }}</td>
                                <td>{{ __('Modern Wave') }}</td>
                                <td>$320</td>
                                <td>{{ __('Pc') }}</td>
                                <td>650</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <a href="javascript:void(0);" class="avatar avatar-sm me-2">
                                            <img src="{{URL::asset('build/img/users/user-05.jpg')}}" alt="product">
                                        </a>
                                        <a href="javascript:void(0);">{{ __('John Weaver') }}</a>
                                    </div>
                                </td>
                                <td class="action-table-data">
                                    <div class="edit-delete-action">
                                        <a class="me-2 edit-icon p-2" href="{{url('product-details')}}">
                                            <i data-feather="eye" class="action-eye"></i>
                                        </a>
                                        <a class="me-2 p-2" href="{{url('edit-product')}}">
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
                                <td>{{ __('PT007') }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <a href="javascript:void(0);" class="avatar avatar-md me-2">
                                            <img src="{{URL::asset('build/img/products/expire-product-01.png')}}" alt="product">
                                        </a>
                                        <a href="javascript:void(0);">{{ __('Red Premium Satchel') }}</a>
                                    </div>												
                                </td>											
                                <td>{{ __('Bags') }}</td>
                                <td>{{ __('Dior') }}</td>
                                <td>$60</td>
                                <td>{{ __('Pc') }}</td>
                                <td>700</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <a href="javascript:void(0);" class="avatar avatar-sm me-2">
                                            <img src="{{URL::asset('build/img/users/user-08.jpg')}}" alt="product">
                                        </a>
                                        <a href="javascript:void(0);">{{ __('Gary Hennessy') }}</a>
                                    </div>
                                </td>
                                <td class="action-table-data">
                                    <div class="edit-delete-action">
                                        <a class="me-2 edit-icon p-2" href="{{url('product-details')}}">
                                            <i data-feather="eye" class="action-eye"></i>
                                        </a>
                                        <a class="me-2 p-2" href="{{url('edit-product')}}">
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
                                <td>{{ __('PT008') }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <a href="javascript:void(0);" class="avatar avatar-md me-2">
                                            <img src="{{URL::asset('build/img/products/expire-product-02.png')}}" alt="product">
                                        </a>
                                        <a href="javascript:void(0);">{{ __('Iphone 14 Pro') }}</a>
                                    </div>												
                                </td>											
                                <td>{{ __('Phone') }}</td>
                                <td>{{ __('Apple') }}</td>
                                <td>$540</td>
                                <td>{{ __('Pc') }}</td>
                                <td>630</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <a href="javascript:void(0);" class="avatar avatar-sm me-2">
                                            <img src="{{URL::asset('build/img/users/user-04.jpg')}}" alt="product">
                                        </a>
                                        <a href="javascript:void(0);">{{ __('Eleanor Panek') }}</a>
                                    </div>
                                </td>
                                <td class="action-table-data">
                                    <div class="edit-delete-action">
                                        <a class="me-2 edit-icon p-2" href="{{url('product-details')}}">
                                            <i data-feather="eye" class="action-eye"></i>
                                        </a>
                                        <a class="me-2 p-2" href="{{url('edit-product')}}">
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
                                <td>{{ __('PT009') }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <a href="javascript:void(0);" class="avatar avatar-md me-2">
                                            <img src="{{URL::asset('build/img/products/expire-product-03.png')}}" alt="product">
                                        </a>
                                        <a href="javascript:void(0);">{{ __('Gaming Chair') }}</a>
                                    </div>												
                                </td>											
                                <td>{{ __('Furniture') }}</td>
                                <td>{{ __('Arlime') }}</td>
                                <td>$200</td>
                                <td>{{ __('Pc') }}</td>
                                <td>410</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <a href="javascript:void(0);" class="avatar avatar-sm me-2">
                                            <img src="{{URL::asset('build/img/users/user-09.jpg')}}" alt="product">
                                        </a>
                                        <a href="javascript:void(0);">{{ __('William Levy') }}</a>
                                    </div>
                                </td>
                                <td class="action-table-data">
                                    <div class="edit-delete-action">
                                        <a class="me-2 edit-icon p-2" href="{{url('product-details')}}">
                                            <i data-feather="eye" class="action-eye"></i>
                                        </a>
                                        <a class="me-2 p-2" href="{{url('edit-product')}}">
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
                                <td>{{ __('PT010') }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <a href="javascript:void(0);" class="avatar avatar-md me-2">
                                            <img src="{{URL::asset('build/img/products/expire-product-04.png')}}" alt="product">
                                        </a>
                                        <a href="javascript:void(0);">{{ __('Borealis Backpack') }}</a>
                                    </div>												
                                </td>											
                                <td>{{ __('Bags') }}</td>
                                <td>{{ __('The North Face') }}</td>
                                <td>$45</td>
                                <td>{{ __('Pc') }}</td>
                                <td>550</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <a href="javascript:void(0);" class="avatar avatar-sm me-2">
                                            <img src="{{URL::asset('build/img/users/user-10.jpg')}}" alt="product">
                                        </a>
                                        <a href="javascript:void(0);">{{ __('Charlotte Klotz') }}</a>
                                    </div>
                                </td>
                                <td class="action-table-data">
                                    <div class="edit-delete-action">
                                        <a class="me-2 edit-icon p-2" href="{{url('product-details')}}">
                                            <i data-feather="eye" class="action-eye"></i>
                                        </a>
                                        <a class="me-2 p-2" href="{{url('edit-product')}}">
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
                                <td>{{ __('PT010') }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <a href="javascript:void(0);" class="avatar avatar-md me-2">
                                            <img src="{{URL::asset('build/img/products/expire-product-04.png')}}" alt="product">
                                        </a>
                                        <a href="javascript:void(0);">{{ __('Borealis Backpack') }}</a>
                                    </div>												
                                </td>											
                                <td>{{ __('Bags') }}</td>
                                <td>{{ __('The North Face') }}</td>
                                <td>$45</td>
                                <td>{{ __('Pc') }}</td>
                                <td>550</td>
                                <td>
                                    <div class="userimgname">
                                        <span class="avatar avatar-sm">
                                        <a href="javascript:void(0);">
                                            <img src="{{URL::asset('build/img/users/user-10.jpg')}}" alt="product">
                                        </a>
                                    </span>
                                            <a href="javascript:void(0);">{{ __('Charlotte Klotz') }}</a>
                                    </div>
                                </td>
                                <td class="action-table-data">
                                    <div class="edit-delete-action">
                                        <a class="me-2 edit-icon p-2" href="{{url('product-details')}}">
                                            <i data-feather="eye" class="action-eye"></i>
                                        </a>
                                        <a class="me-2 p-2" href="{{url('edit-product')}}">
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
