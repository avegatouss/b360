<x-dashboard::layouts.master
    :title="__('Coupons') . ' —' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance"
    :pageTitle="__('Coupons')">

<div class="page-header">
            <div class="add-item d-flex">
                <div class="page-title">
                    <h4 class="fw-bold">{{ __('Coupons') }}</h4>
                    <h6>{{ __('Manage Your Coupons') }}</h6>
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
                <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#add-coupon"><i class="ti ti-circle-plus me-1"></i>{{ __('Add Coupons') }}</a>
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
                        Type
                        </a>
                        <ul class="dropdown-menu  dropdown-menu-end p-3">
                            <li>
                                <a href="javascript:void(0);" class="dropdown-item rounded-1">{{ __('Fixed') }}</a>
                            </li>
                            <li>
                                <a href="javascript:void(0);" class="dropdown-item rounded-1">{{ __('Percentage') }}</a>
                            </li>
                        </ul>
                    </div>				
                    <div class="dropdown me-2">
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
                                <th>{{ __('Name') }}</th>
                                <th>{{ __('Code') }}</th>
                                <th>{{ __('Description') }}</th>
                                <th>{{ __('Type') }}</th>
                                <th>{{ __('Discount') }}</th>
                                <th>{{ __('Limit') }}</th>
                                <th>{{ __('Valid') }}</th>
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
                                <td class="text-gray-9">{{ __('New Year Blast') }}</td>
                                <td><span class="badge purple-badge">{{ __('NEWYEAR30') }}</span></td>
                                <td>
                                    30% off on New Year 									
                                </td>
                                <td>{{ __('Percentage') }}</td>
                                <td>
                                    30%
                                </td>
                                <td>01</td>
                                <td>{{ __('04 Jan 2025') }}</td>
                                <td><span class="badge table-badge bg-success fw-medium fs-10">{{ __('Active') }}</span></td>
                                <td class="action-table-data">
                                    <div class="edit-delete-action">
                                        <a class="me-2 p-2" href="#" data-bs-toggle="modal" data-bs-target="#edit-coupon">
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
                                <td class="text-gray-9">{{ __('Christmas Cheer') }}</td>
                                <td><span class="badge purple-badge">{{ __('CHRISTMAS100') }}</span></td>
                                <td>
                                    $100 off holiday packages									
                                </td>
                                <td>{{ __('Fixed Amount') }}</td>
                                <td>
                                    $100
                                </td>
                                <td>01</td>
                                <td>{{ __('27 Dec 2024') }}</td>
                                <td><span class="badge table-badge bg-success fw-medium fs-10">{{ __('Active') }}</span></td>
                                <td class="action-table-data">
                                    <div class="edit-delete-action">
                                        <a class="me-2 p-2" href="#" data-bs-toggle="modal" data-bs-target="#edit-coupon">
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
                                <td class="text-gray-9">{{ __('Spooky Savings') }}</td>
                                <td><span class="badge purple-badge">{{ __('HALLOWEEN20') }}</span></td>
                                <td>
                                    20% off on Halloween items									
                                </td>
                                <td>{{ __('Percentage') }}</td>
                                <td>
                                    20%
                                </td>
                                <td>02</td>
                                <td>{{ __('28 Nov 2024') }}</td>
                                <td><span class="badge table-badge bg-success fw-medium fs-10">{{ __('Active') }}</span></td>
                                <td class="action-table-data">
                                    <div class="edit-delete-action">
                                        <a class="me-2 p-2" href="#" data-bs-toggle="modal" data-bs-target="#edit-coupon">
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
                                <td class="text-gray-9">{{ __('Black Friday') }}</td>
                                <td><span class="badge purple-badge">{{ __('BLACKFRIDAY50') }}</span></td>
                                <td>
                                    50% off electronics								
                                </td>
                                <td>{{ __('Percentage') }}</td>
                                <td>
                                    50%
                                </td>
                                <td>04</td>
                                <td>{{ __('18 Nov 2024') }}</td>
                                <td><span class="badge table-badge bg-danger fw-medium fs-10">{{ __('Inactive') }}</span></td>
                                <td class="action-table-data">
                                    <div class="edit-delete-action">
                                        <a class="me-2 p-2" href="#" data-bs-toggle="modal" data-bs-target="#edit-coupon">
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
                                <td class="text-gray-9">{{ __('Golden Years Deal') }}</td>
                                <td><span class="badge purple-badge">{{ __('SENIOR20') }}</span></td>
                                <td>
                                    20% off for senior citizens							
                                </td>
                                <td>{{ __('Percentage') }}</td>
                                <td>
                                    20%
                                </td>
                                <td>03</td>
                                <td>{{ __('06 Nov 2024') }}</td>
                                <td><span class="badge table-badge bg-success fw-medium fs-10">{{ __('Active') }}</span></td>
                                <td class="action-table-data">
                                    <div class="edit-delete-action">
                                        <a class="me-2 p-2" href="#" data-bs-toggle="modal" data-bs-target="#edit-coupon">
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
                                <td class="text-gray-9">{{ __('Thanksgiving Special') }}</td>
                                <td><span class="badge purple-badge">{{ __('THANKS10') }}</span></td>
                                <td>
                                    10% off for Thanksgiving						
                                </td>
                                <td>{{ __('Percentage') }}</td>
                                <td>
                                    10%
                                </td>
                                <td>01</td>
                                <td>{{ __('31 Oct 2024') }}</td>
                                <td><span class="badge table-badge bg-success fw-medium fs-10">{{ __('Active') }}</span></td>
                                <td class="action-table-data">
                                    <div class="edit-delete-action">
                                        <a class="me-2 p-2" href="#" data-bs-toggle="modal" data-bs-target="#edit-coupon">
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
                                <td class="text-gray-9">{{ __('New Year Blast') }}</td>
                                <td><span class="badge purple-badge">{{ __('STUDENT10') }}</span></td>
                                <td>
                                    10% off for students						
                                </td>
                                <td>{{ __('Percentage') }}</td>
                                <td>
                                    10%
                                </td>
                                <td>02</td>
                                <td>{{ __('14 Oct 2024') }}</td>
                                <td><span class="badge table-badge bg-success fw-medium fs-10">{{ __('Active') }}</span></td>
                                <td class="action-table-data">
                                    <div class="edit-delete-action">
                                        <a class="me-2 p-2" href="#" data-bs-toggle="modal" data-bs-target="#edit-coupon">
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
                                <td class="text-gray-9">{{ __('Big Saver Deal') }}</td>
                                <td><span class="badge purple-badge">{{ __('SAVE50') }}</span></td>
                                <td>
                                    $50 off orders over $300						
                                </td>
                                <td>{{ __('Fixed Amount') }}</td>
                                <td>
                                    $50
                                </td>
                                <td>03</td>
                                <td>{{ __('03 Oct 2024') }}</td>
                                <td><span class="badge table-badge bg-danger fw-medium fs-10">{{ __('Inactive') }}</span></td>
                                <td class="action-table-data">
                                    <div class="edit-delete-action">
                                        <a class="me-2 p-2" href="#" data-bs-toggle="modal" data-bs-target="#edit-coupon">
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
                                <td class="text-gray-9">{{ __('Weekend Exclusive') }}</td>
                                <td><span class="badge purple-badge">{{ __('WEEKENDSALE') }}</span></td>
                                <td>
                                    Exclusive15% off on weekends						
                                </td>
                                <td>{{ __('Percentage') }}</td>
                                <td>
                                    15%
                                </td>
                                <td>04</td>
                                <td>{{ __('29 Sep 2024') }}</td>
                                <td><span class="badge table-badge bg-success fw-medium fs-10">{{ __('Active') }}</span></td>
                                <td class="action-table-data">
                                    <div class="edit-delete-action">
                                        <a class="me-2 p-2" href="#" data-bs-toggle="modal" data-bs-target="#edit-coupon">
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
                                <td class="text-gray-9">{{ __('Welcome Delight') }}</td>
                                <td><span class="badge purple-badge">{{ __('WELCOME10') }}</span></td>
                                <td>
                                    10% off for first-time users						
                                </td>
                                <td>{{ __('Percentage') }}</td>
                                <td>
                                    10%
                                </td>
                                <td>01</td>
                                <td>{{ __('10 Sep 2024') }}</td>
                                <td><span class="badge table-badge bg-success fw-medium fs-10">{{ __('Active') }}</span></td>
                                <td class="action-table-data">
                                    <div class="edit-delete-action">
                                        <a class="me-2 p-2" href="#" data-bs-toggle="modal" data-bs-target="#edit-coupon">
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
                                <td class="text-gray-9">{{ __('New Year Blast') }}</td>
                                <td><span class="badge purple-badge">{{ __('NEWYEAR30') }}</span></td>
                                <td>
                                    30% off on New Year 									
                                </td>
                                <td>{{ __('Percentage') }}</td>
                                <td>
                                    30%
                                </td>
                                <td>01</td>
                                <td>{{ __('04 Jan 2025') }}</td>
                                <td><span class="badge table-badge bg-success fw-medium fs-10">{{ __('Active') }}</span></td>
                                <td class="action-table-data">
                                    <div class="edit-delete-action">
                                        <a class="me-2 p-2" href="#" data-bs-toggle="modal" data-bs-target="#edit-coupon">
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
                                <td class="text-gray-9">{{ __('Christmas Cheer') }}</td>
                                <td><span class="badge purple-badge">{{ __('CHRISTMAS100') }}</span></td>
                                <td>
                                    $100 off holiday packages									
                                </td>
                                <td>{{ __('Fixed Amount') }}</td>
                                <td>
                                    $100
                                </td>
                                <td>01</td>
                                <td>{{ __('27 Dec 2024') }}</td>
                                <td><span class="badge table-badge bg-success fw-medium fs-10">{{ __('Active') }}</span></td>
                                <td class="action-table-data">
                                    <div class="edit-delete-action">
                                        <a class="me-2 p-2" href="#" data-bs-toggle="modal" data-bs-target="#edit-coupon">
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
                                <td class="text-gray-9">{{ __('Spooky Savings') }}</td>
                                <td><span class="badge purple-badge">{{ __('HALLOWEEN20') }}</span></td>
                                <td>
                                    20% off on Halloween items									
                                </td>
                                <td>{{ __('Percentage') }}</td>
                                <td>
                                    20%
                                </td>
                                <td>02</td>
                                <td>{{ __('28 Nov 2024') }}</td>
                                <td><span class="badge table-badge bg-success fw-medium fs-10">{{ __('Active') }}</span></td>
                                <td class="action-table-data">
                                    <div class="edit-delete-action">
                                        <a class="me-2 p-2" href="#" data-bs-toggle="modal" data-bs-target="#edit-coupon">
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
                                <td class="text-gray-9">{{ __('Black Friday') }}</td>
                                <td><span class="badge purple-badge">{{ __('BLACKFRIDAY50') }}</span></td>
                                <td>
                                    50% off electronics								
                                </td>
                                <td>{{ __('Percentage') }}</td>
                                <td>
                                    50%
                                </td>
                                <td>04</td>
                                <td>{{ __('18 Nov 2024') }}</td>
                                <td><span class="badge table-badge bg-danger fw-medium fs-10">{{ __('Inactive') }}</span></td>
                                <td class="action-table-data">
                                    <div class="edit-delete-action">
                                        <a class="me-2 p-2" href="#" data-bs-toggle="modal" data-bs-target="#edit-coupon">
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
                                <td class="text-gray-9">{{ __('Golden Years Deal') }}</td>
                                <td><span class="badge purple-badge">{{ __('SENIOR20') }}</span></td>
                                <td>
                                    20% off for senior citizens							
                                </td>
                                <td>{{ __('Percentage') }}</td>
                                <td>
                                    20%
                                </td>
                                <td>03</td>
                                <td>{{ __('06 Nov 2024') }}</td>
                                <td><span class="badge table-badge bg-success fw-medium fs-10">{{ __('Active') }}</span></td>
                                <td class="action-table-data">
                                    <div class="edit-delete-action">
                                        <a class="me-2 p-2" href="#" data-bs-toggle="modal" data-bs-target="#edit-coupon">
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
                                <td class="text-gray-9">{{ __('Thanksgiving Special') }}</td>
                                <td><span class="badge purple-badge">{{ __('THANKS10') }}</span></td>
                                <td>
                                    10% off for Thanksgiving						
                                </td>
                                <td>{{ __('Percentage') }}</td>
                                <td>
                                    10%
                                </td>
                                <td>01</td>
                                <td>{{ __('31 Oct 2024') }}</td>
                                <td><span class="badge table-badge bg-success fw-medium fs-10">{{ __('Active') }}</span></td>
                                <td class="action-table-data">
                                    <div class="edit-delete-action">
                                        <a class="me-2 p-2" href="#" data-bs-toggle="modal" data-bs-target="#edit-coupon">
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
