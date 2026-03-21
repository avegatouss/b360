<x-dashboard::layouts.master
    :title="__('Purchase Transactions') . ' —' . ($instance->name ?? $instance->slug ?? 'B360')"
    :instance="$instance"
    :pageTitle="__('Purchase Transactions')">

<div class="page-header">
                <div class="add-item d-flex">
                    <div class="page-title">
                        <h4>{{ __('Purchase Transaction') }}</h4>
                        <h6>{{ __('Manage your purchase transaction') }}</h6>
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
            </div>
            
            <!-- /product list -->
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
                    <div class="search-set">
                        <div class="search-input">
                            <span class="btn-searchset"><i class="ti ti-search fs-14 feather-search"></i></span>
                        </div>
                    </div>
                    <div class="d-flex my-xl-auto right-content align-items-center flex-wrap row-gap-3">
                        
                        <div class="dropdown me-2">
                            <a href="javascript:void(0);" class="dropdown-toggle btn btn-white btn-md d-inline-flex align-items-center" data-bs-toggle="dropdown">
                                Payment Method
                            </a>
                            <ul class="dropdown-menu  dropdown-menu-end p-3">
                                <li>
                                    <a href="javascript:void(0);" class="dropdown-item rounded-1">{{ __('Credit card') }}</a>
                                </li>
                                <li>
                                    <a href="javascript:void(0);" class="dropdown-item rounded-1">{{ __('Paypal') }}</a>
                                </li>
                                <li>
                                    <a href="javascript:void(0);" class="dropdown-item rounded-1">{{ __('Debit Card') }}</a>
                                </li>
                            </ul>
                        </div>
                        <div class="dropdown me-2">
                            <a href="javascript:void(0);" class="dropdown-toggle btn btn-white btn-md d-inline-flex align-items-center" data-bs-toggle="dropdown">
                                Select Status
                            </a>
                            <ul class="dropdown-menu  dropdown-menu-end p-3">
                                <li>
                                    <a href="javascript:void(0);" class="dropdown-item rounded-1">{{ __('Paid') }}</a>
                                </li>
                                <li>
                                    <a href="javascript:void(0);" class="dropdown-item rounded-1">{{ __('Unpaid') }}</a>
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
                                        <div class="form-check form-check-md">
                                            <input class="form-check-input" type="checkbox" id="select-all">
                                        </div>
                                    </th>
                                    <th>{{ __('Invoice ID') }}</th>
                                    <th>{{ __('Customer') }}</th>
                                    <th>{{ __('Email') }}</th>
                                    <th>{{ __('Created Date') }}</th>
                                    <th>{{ __('Amount') }}</th>
                                    <th>{{ __('Payment Method') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <div class="form-check form-check-md">
                                            <input class="form-check-input" type="checkbox">
                                        </div>
                                    </td>
                                    <td><a href="javascript:void(0);" class="link-default">{{ __('INV001') }}</a></td>
                                    <td>
                                        <div class="d-flex align-items-center file-name-icon">
                                            <a href="#" class="avatar avatar-md border rounded-circle">
                                                <img src="{{URL::asset('build/img/company/company-01.svg')}}" class="img-fluid" alt="img">
                                            </a>
                                            <div class="ms-2">
                                                <h6 class="fw-medium"><a href="#">{{ __('BrightWave Innovations') }}</a></h6>
                                            </div>
                                        </div>
                                    </td>
                                    <td>michael@example.com</td>
                                    <td>{{ __('12 Sep 2024') }}</td>
                                    <td>200 {{ $eshopCurrency ?? 'FCFA' }}</td>
                                    <td>{{ __('Credit Card') }}</td>
                                    <td>
                                        <span class="badge badge-success d-flex align-items-center badge-xs">
                                            <i class="ti ti-point-filled me-1"></i>Paid
                                        </span>
                                    </td>
                                    <td>
                                        <div class="action-icon d-inline-flex align-items-center">
                                            <a href="#" class="p-2 d-flex align-items-center border rounded me-2" data-bs-toggle="modal" data-bs-target="#view_invoice"><i class="ti ti-file-invoice"></i></a>
                                            <a href="#" class="p-2 d-flex align-items-center border rounded me-2"><i class="ti ti-download"></i></a>
                                            <a href="#" data-bs-toggle="modal" data-bs-target="#delete_modal" class="p-2 d-flex align-items-center border rounded"><i class="ti ti-trash"></i></a>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="form-check form-check-md">
                                            <input class="form-check-input" type="checkbox">
                                        </div>
                                    </td>
                                    <td><a href="javascript:void(0);" class="link-default">{{ __('INV002') }}</a></td>
                                    <td>
                                        <div class="d-flex align-items-center file-name-icon">
                                            <a href="#" class="avatar avatar-md border rounded-circle">
                                                <img src="{{URL::asset('build/img/company/company-02.svg')}}" class="img-fluid" alt="img">
                                            </a>
                                            <div class="ms-2">
                                                <h6 class="fw-medium"><a href="#">{{ __('Stellar Dynamics') }}</a></h6>
                                            </div>
                                        </div>
                                    </td>
                                    <td>sophie@example.com</td>
                                    <td>{{ __('24 Oct 2024') }}</td>
                                    <td>600 {{ $eshopCurrency ?? 'FCFA' }}</td>
                                    <td>{{ __('Paypal') }}</td>
                                    <td>
                                        <span class="badge badge-success d-flex align-items-center badge-xs">
                                            <i class="ti ti-point-filled me-1"></i>Paid
                                        </span>
                                    </td>
                                    <td>
                                        <div class="action-icon d-inline-flex align-items-center">
                                            <a href="#" class="p-2 d-flex align-items-center border rounded me-2" data-bs-toggle="modal" data-bs-target="#view_invoice"><i class="ti ti-file-invoice"></i></a>
                                            <a href="#" class="p-2 d-flex align-items-center border rounded me-2"><i class="ti ti-download"></i></a>
                                            <a href="#" data-bs-toggle="modal" data-bs-target="#delete_modal" class="p-2 d-flex align-items-center border rounded"><i class="ti ti-trash"></i></a>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="form-check form-check-md">
                                            <input class="form-check-input" type="checkbox">
                                        </div>
                                    </td>
                                    <td><a href="javascript:void(0);" class="link-default">{{ __('INV003') }}</a></td>
                                    <td>
                                        <div class="d-flex align-items-center file-name-icon">
                                            <a href="#" class="avatar avatar-md border rounded-circle">
                                                <img src="{{URL::asset('build/img/company/company-03.svg')}}" class="img-fluid" alt="img">
                                            </a>
                                            <div class="ms-2">
                                                <h6 class="fw-medium"><a href="#">{{ __('Quantum Nexus') }}</a></h6>
                                            </div>
                                        </div>
                                    </td>
                                    <td>cameron@example.com</td>
                                    <td>{{ __('18 Feb 2024') }}</td>
                                    <td>200 {{ $eshopCurrency ?? 'FCFA' }}</td>
                                    <td>{{ __('Debit Card') }}</td>
                                    <td>
                                        <span class="badge badge-success d-flex align-items-center badge-xs">
                                            <i class="ti ti-point-filled me-1"></i>Paid
                                        </span>
                                    </td>
                                    <td>
                                        <div class="action-icon d-inline-flex align-items-center">
                                            <a href="#" class="p-2 d-flex align-items-center border rounded me-2" data-bs-toggle="modal" data-bs-target="#view_invoice"><i class="ti ti-file-invoice"></i></a>
                                            <a href="#" class="p-2 d-flex align-items-center border rounded me-2"><i class="ti ti-download"></i></a>
                                            <a href="#" data-bs-toggle="modal" data-bs-target="#delete_modal" class="p-2 d-flex align-items-center border rounded"><i class="ti ti-trash"></i></a>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="form-check form-check-md">
                                            <input class="form-check-input" type="checkbox">
                                        </div>
                                    </td>
                                    <td><a href="javascript:void(0);" class="link-default">{{ __('INV004') }}</a></td>
                                    <td>
                                        <div class="d-flex align-items-center file-name-icon">
                                            <a href="#" class="avatar avatar-md border rounded-circle">
                                                <img src="{{URL::asset('build/img/company/company-04.svg')}}" class="img-fluid" alt="img">
                                            </a>
                                            <div class="ms-2">
                                                <h6 class="fw-medium"><a href="#">{{ __('EcoVision Enterprises') }}</a></h6>
                                            </div>
                                        </div>
                                    </td>
                                    <td>doris@example.com</td>
                                    <td>{{ __('17 Oct 2024') }}</td>
                                    <td>200 {{ $eshopCurrency ?? 'FCFA' }}</td>
                                    <td>{{ __('Paypal') }}</td>
                                    <td>
                                        <span class="badge badge-success d-flex align-items-center badge-xs">
                                            <i class="ti ti-point-filled me-1"></i>Paid
                                        </span>
                                    </td>
                                    <td>
                                        <div class="action-icon d-inline-flex align-items-center">
                                            <a href="#" class="p-2 d-flex align-items-center border rounded me-2" data-bs-toggle="modal" data-bs-target="#view_invoice"><i class="ti ti-file-invoice"></i></a>
                                            <a href="#" class="p-2 d-flex align-items-center border rounded me-2"><i class="ti ti-download"></i></a>
                                            <a href="#" data-bs-toggle="modal" data-bs-target="#delete_modal" class="p-2 d-flex align-items-center border rounded"><i class="ti ti-trash"></i></a>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="form-check form-check-md">
                                            <input class="form-check-input" type="checkbox">
                                        </div>
                                    </td>
                                    <td><a href="javascript:void(0);" class="link-default">{{ __('INV005') }}</a></td>
                                    <td>
                                        <div class="d-flex align-items-center file-name-icon">
                                            <a href="#" class="avatar avatar-md border rounded-circle">
                                                <img src="{{URL::asset('build/img/company/company-05.svg')}}" class="img-fluid" alt="img">
                                            </a>
                                            <div class="ms-2">
                                                <h6 class="fw-medium"><a href="#">{{ __('Aurora Technologies') }}</a></h6>
                                            </div>
                                        </div>
                                    </td>
                                    <td>thomas@example.com</td>
                                    <td>{{ __('20 Jul 2024') }}</td>
                                    <td>400 {{ $eshopCurrency ?? 'FCFA' }}</td>
                                    <td>{{ __('Credit Card') }}</td>
                                    <td>
                                        <span class="badge badge-success d-flex align-items-center badge-xs">
                                            <i class="ti ti-point-filled me-1"></i>Paid
                                        </span>
                                    </td>
                                    <td>
                                        <div class="action-icon d-inline-flex align-items-center">
                                            <a href="#" class="p-2 d-flex align-items-center border rounded me-2" data-bs-toggle="modal" data-bs-target="#view_invoice"><i class="ti ti-file-invoice"></i></a>
                                            <a href="#" class="p-2 d-flex align-items-center border rounded me-2"><i class="ti ti-download"></i></a>
                                            <a href="#" data-bs-toggle="modal" data-bs-target="#delete_modal" class="p-2 d-flex align-items-center border rounded"><i class="ti ti-trash"></i></a>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="form-check form-check-md">
                                            <input class="form-check-input" type="checkbox">
                                        </div>
                                    </td>
                                    <td><a href="javascript:void(0);" class="link-default">{{ __('INV006') }}</a></td>
                                    <td>
                                        <div class="d-flex align-items-center file-name-icon">
                                            <a href="#" class="avatar avatar-md border rounded-circle">
                                                <img src="{{URL::asset('build/img/company/company-06.svg')}}" class="img-fluid" alt="img">
                                            </a>
                                            <div class="ms-2">
                                                <h6 class="fw-medium"><a href="#">{{ __('BlueSky Ventures') }}</a></h6>
                                            </div>
                                        </div>
                                    </td>
                                    <td>kathleen@example.com</td>
                                    <td>{{ __('10 Apr 2024') }}</td>
                                    <td>200 {{ $eshopCurrency ?? 'FCFA' }}</td>
                                    <td>{{ __('Paypal') }}</td>
                                    <td>
                                        <span class="badge badge-success d-flex align-items-center badge-xs">
                                            <i class="ti ti-point-filled me-1"></i>Paid
                                        </span>
                                    </td>
                                    <td>
                                        <div class="action-icon d-inline-flex align-items-center">
                                            <a href="#" class="p-2 d-flex align-items-center border rounded me-2" data-bs-toggle="modal" data-bs-target="#view_invoice"><i class="ti ti-file-invoice"></i></a>
                                            <a href="#" class="p-2 d-flex align-items-center border rounded me-2"><i class="ti ti-download"></i></a>
                                            <a href="#" data-bs-toggle="modal" data-bs-target="#delete_modal" class="p-2 d-flex align-items-center border rounded"><i class="ti ti-trash"></i></a>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="form-check form-check-md">
                                            <input class="form-check-input" type="checkbox">
                                        </div>
                                    </td>
                                    <td><a href="javascript:void(0);" class="link-default">{{ __('INV007') }}</a></td>
                                    <td>
                                        <div class="d-flex align-items-center file-name-icon">
                                            <a href="#" class="avatar avatar-md border rounded-circle">
                                                <img src="{{URL::asset('build/img/company/company-07.svg')}}" class="img-fluid" alt="img">
                                            </a>
                                            <div class="ms-2">
                                                <h6 class="fw-medium"><a href="#">{{ __('TerraFusion Energy') }}</a></h6>
                                            </div>
                                        </div>
                                    </td>
                                    <td>bruce@example.com</td>
                                    <td>{{ __('29 Aug 2024') }}</td>
                                    <td>4800 {{ $eshopCurrency ?? 'FCFA' }}</td>
                                    <td>{{ __('Credit Card') }}</td>
                                    <td>
                                        <span class="badge badge-success d-flex align-items-center badge-xs">
                                            <i class="ti ti-point-filled me-1"></i>Paid
                                        </span>
                                    </td>
                                    <td>
                                        <div class="action-icon d-inline-flex align-items-center">
                                            <a href="#" class="p-2 d-flex align-items-center border rounded me-2" data-bs-toggle="modal" data-bs-target="#view_invoice"><i class="ti ti-file-invoice"></i></a>
                                            <a href="#" class="p-2 d-flex align-items-center border rounded me-2"><i class="ti ti-download"></i></a>
                                            <a href="#" data-bs-toggle="modal" data-bs-target="#delete_modal" class="p-2 d-flex align-items-center border rounded"><i class="ti ti-trash"></i></a>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="form-check form-check-md">
                                            <input class="form-check-input" type="checkbox">
                                        </div>
                                    </td>
                                    <td><a href="javascript:void(0);" class="link-default">{{ __('INV008') }}</a></td>
                                    <td>
                                        <div class="d-flex align-items-center file-name-icon">
                                            <a href="#" class="avatar avatar-md border rounded-circle">
                                                <img src="{{URL::asset('build/img/company/company-08.svg')}}" class="img-fluid" alt="img">
                                            </a>
                                            <div class="ms-2">
                                                <h6 class="fw-medium"><a href="#">{{ __('UrbanPulse Design') }}</a></h6>
                                            </div>
                                        </div>
                                    </td>
                                    <td>estelle@example.com</td>
                                    <td>{{ __('22 Feb 2024') }}</td>
                                    <td>50 {{ $eshopCurrency ?? 'FCFA' }}</td>
                                    <td>{{ __('Credit Card') }}</td>
                                    <td>
                                        <span class="badge badge-danger d-flex align-items-center badge-xs">
                                            <i class="ti ti-point-filled me-1"></i>Unpaid
                                        </span>
                                    </td>
                                    <td>
                                        <div class="action-icon d-inline-flex align-items-center">
                                            <a href="#" class="p-2 d-flex align-items-center border rounded me-2" data-bs-toggle="modal" data-bs-target="#view_invoice"><i class="ti ti-file-invoice"></i></a>
                                            <a href="#" class="p-2 d-flex align-items-center border rounded me-2"><i class="ti ti-download"></i></a>
                                            <a href="#" data-bs-toggle="modal" data-bs-target="#delete_modal" class="p-2 d-flex align-items-center border rounded"><i class="ti ti-trash"></i></a>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="form-check form-check-md">
                                            <input class="form-check-input" type="checkbox">
                                        </div>
                                    </td>
                                    <td><a href="javascript:void(0);" class="link-default">{{ __('INV009') }}</a></td>
                                    <td>
                                        <div class="d-flex align-items-center file-name-icon">
                                            <a href="#" class="avatar avatar-md border rounded-circle">
                                                <img src="{{URL::asset('build/img/company/company-09.svg')}}" class="img-fluid" alt="img">
                                            </a>
                                            <div class="ms-2">
                                                <h6 class="fw-medium"><a href="#">{{ __('Nimbus Networks') }}</a></h6>
                                            </div>
                                        </div>
                                    </td>
                                    <td>stephen@example.com</td>
                                    <td>{{ __('03 Nov 2024') }}</td>
                                    <td>600 {{ $eshopCurrency ?? 'FCFA' }}</td>
                                    <td>{{ __('Paypal') }}</td>
                                    <td>
                                        <span class="badge badge-success d-flex align-items-center badge-xs">
                                            <i class="ti ti-point-filled me-1"></i>Paid
                                        </span>
                                    </td>
                                    <td>
                                        <div class="action-icon d-inline-flex align-items-center">
                                            <a href="#" class="p-2 d-flex align-items-center border rounded me-2" data-bs-toggle="modal" data-bs-target="#view_invoice"><i class="ti ti-file-invoice"></i></a>
                                            <a href="#" class="p-2 d-flex align-items-center border rounded me-2"><i class="ti ti-download"></i></a>
                                            <a href="#" data-bs-toggle="modal" data-bs-target="#delete_modal" class="p-2 d-flex align-items-center border rounded"><i class="ti ti-trash"></i></a>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="form-check form-check-md">
                                            <input class="form-check-input" type="checkbox">
                                        </div>
                                    </td>
                                    <td><a href="javascript:void(0);" class="link-default">{{ __('INV010') }}</a></td>
                                    <td>
                                        <div class="d-flex align-items-center file-name-icon">
                                            <a href="#" class="avatar avatar-md border rounded-circle">
                                                <img src="{{URL::asset('build/img/company/company-10.svg')}}" class="img-fluid" alt="img">
                                            </a>
                                            <div class="ms-2">
                                                <h6 class="fw-medium"><a href="#">{{ __('Epicurean Delights') }}</a></h6>
                                            </div>
                                        </div>
                                    </td>
                                    <td>angela@example.com</td>
                                    <td>{{ __('17 Dec 2024') }}</td>
                                    <td>200 {{ $eshopCurrency ?? 'FCFA' }}</td>
                                    <td>{{ __('Credit Card') }}</td>
                                    <td>
                                        <span class="badge badge-success d-flex align-items-center badge-xs">
                                            <i class="ti ti-point-filled me-1"></i>Paid
                                        </span>
                                    </td>
                                    <td>
                                        <div class="action-icon d-inline-flex align-items-center">
                                            <a href="#" class="p-2 d-flex align-items-center border rounded me-2" data-bs-toggle="modal" data-bs-target="#view_invoice"><i class="ti ti-file-invoice"></i></a>
                                            <a href="#" class="p-2 d-flex align-items-center border rounded me-2"><i class="ti ti-download"></i></a>
                                            <a href="#" data-bs-toggle="modal" data-bs-target="#delete_modal" class="p-2 d-flex align-items-center border rounded"><i class="ti ti-trash"></i></a>
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
