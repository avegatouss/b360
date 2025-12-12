<?php $page = 'domain'; ?>
@extends('layout.mainlayout')
@section('content')

    <div class="page-wrapper">
        <div class="content">
            <div class="page-header">
                <div class="add-item d-flex">
                    <div class="page-title">
                        <h4>Domain</h4>
                        <h6>Manage your domain</h6>
                    </div>
                </div>
                <ul class="table-top-head">
                    <li>
                        <a data-bs-toggle="tooltip" data-bs-placement="top" title="Pdf"><img src="{{URL::asset('/build/img/icons/pdf.svg')}}" alt="img"></a>
                    </li>
                    <li>
                        <a data-bs-toggle="tooltip" data-bs-placement="top" title="Excel"><img src="{{URL::asset('/build/img/icons/excel.svg')}}" alt="img"></a>
                    </li>
                    <li>
                        <a data-bs-toggle="tooltip" data-bs-placement="top" title="Refresh"><i class="ti ti-refresh"></i></a>
                    </li>
                    <li>
                        <a data-bs-toggle="tooltip" data-bs-placement="top" title="Collapse" id="collapse-header"><i class="ti ti-chevron-up"></i></a>
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
                                Select Plan
                            </a>
                            <ul class="dropdown-menu  dropdown-menu-end p-3">
                                <li>
                                    <a href="javascript:void(0);" class="dropdown-item rounded-1">Monthly</a>
                                </li>
                                <li>
                                    <a href="javascript:void(0);" class="dropdown-item rounded-1">Yearly</a>
                                </li>
                            </ul>
                        </div>
                        <div class="dropdown me-2">
                            <a href="javascript:void(0);" class="dropdown-toggle btn btn-white btn-md d-inline-flex align-items-center" data-bs-toggle="dropdown">
                                Select Status
                            </a>
                            <ul class="dropdown-menu  dropdown-menu-end p-3">
                                <li>
                                    <a href="javascript:void(0);" class="dropdown-item rounded-1">Approve</a>
                                </li>
                                <li>
                                    <a href="javascript:void(0);" class="dropdown-item rounded-1">Pending</a>
                                </li>
                                <li>
                                    <a href="javascript:void(0);" class="dropdown-item rounded-1">Rejected</a>
                                </li>
                            </ul>
                        </div>
                        <div class="dropdown">
                            <a href="javascript:void(0);" class="dropdown-toggle btn btn-white btn-md d-inline-flex align-items-center" data-bs-toggle="dropdown">
                                Sort By : Last 7 Days
                            </a>
                            <ul class="dropdown-menu  dropdown-menu-end p-3">
                                <li>
                                    <a href="javascript:void(0);" class="dropdown-item rounded-1">Recently Added</a>
                                </li>
                                <li>
                                    <a href="javascript:void(0);" class="dropdown-item rounded-1">Ascending</a>
                                </li>
                                <li>
                                    <a href="javascript:void(0);" class="dropdown-item rounded-1">Desending</a>
                                </li>
                                <li>
                                    <a href="javascript:void(0);" class="dropdown-item rounded-1">Last Month</a>
                                </li>
                                <li>
                                    <a href="javascript:void(0);" class="dropdown-item rounded-1">Last 7 Days</a>
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
                                    <th>Name</th>
                                    <th>Domain URL</th>
                                    <th>Plan</th>
                                    <th>Created Date</th>
                                    <th>Status</th>
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
                                    <td>
                                        <div class="d-flex align-items-center file-name-icon">
                                            <a href="#" class="avatar avatar-md border rounded-circle">
                                                <img src="{{URL::asset('build/img/company/company-01.svg')}}" class="img-fluid" alt="img">
                                            </a>
                                            <div class="ms-2">
                                                <h6 class="fw-medium"><a href="#">BrightWave Innovations</a></h6>
                                            </div>
                                        </div>
                                    </td>
                                    <td>bwi.example.com</td>
                                    <td>Advanced (Monthly)</td>
                                    <td>12 Sep 2024</td>
                                    <td>
                                        <a href="#" class="badge badge-soft-success d-inline-flex align-items-center badge-xs shadow-none">
                                            <i class="ti ti-checks me-1"></i>Approved
                                        </a>
                                    </td>
                                    <td>
                                        <div class="action-icon d-inline-flex align-items-center">
                                            <a href="#" class="p-2 d-flex align-items-center border rounded me-2" data-bs-toggle="modal" data-bs-target="#domain_approved"><i class="ti ti-eye"></i></a>
                                            <a href="javascript:void(0);" class="p-2 d-flex align-items-center border rounded" data-bs-toggle="modal" data-bs-target="#delete_modal"><i class="ti ti-trash"></i></a>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="form-check form-check-md">
                                            <input class="form-check-input" type="checkbox">
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center file-name-icon">
                                            <a href="#" class="avatar avatar-md border rounded-circle">
                                                <img src="{{URL::asset('build/img/company/company-02.svg')}}" class="img-fluid" alt="img">
                                            </a>
                                            <div class="ms-2">
                                                <h6 class="fw-medium"><a href="#">Stellar Dynamics</a></h6>
                                            </div>
                                        </div>
                                    </td>
                                    <td>sd.example.com</td>
                                    <td>Basic (Yearly)</td>
                                    <td>24 Oct 2024</td>
                                    <td>
                                        <a href="#" class="badge badge-soft-skyblue shadow-none d-inline-flex align-items-center badge-xs">
                                            <i class="ti ti-clock me-1"></i>Pending
                                        </a>
                                    </td>
                                    <td>
                                        <div class="action-icon d-inline-flex">
                                            <a href="#" class="p-2 d-flex align-items-center border rounded me-2" data-bs-toggle="modal" data-bs-target="#domain_pending"><i class="ti ti-eye"></i></a>
                                            <a href="javascript:void(0);" class="p-2 d-flex align-items-center border rounded" data-bs-toggle="modal" data-bs-target="#delete_modal"><i class="ti ti-trash"></i></a>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="form-check form-check-md">
                                            <input class="form-check-input" type="checkbox">
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center file-name-icon">
                                            <a href="#" class="avatar avatar-md border rounded-circle">
                                                <img src="{{URL::asset('build/img/company/company-03.svg')}}" class="img-fluid" alt="img">
                                            </a>
                                            <div class="ms-2">
                                                <h6 class="fw-medium"><a href="#">Quantum Nexus</a></h6>
                                            </div>
                                        </div>
                                    </td>
                                    <td>qn.example.com</td>
                                    <td>Advanced (Monthly)</td>
                                    <td>18 Feb 2024</td>
                                    <td>
                                        <a href="#" class="badge badge-soft-danger d-inline-flex align-items-center badge-xs shadow-none">
                                            <i class="ti ti-x me-1"></i>Rejected
                                        </a>
                                    </td>
                                    <td>
                                        <div class="action-icon d-inline-flex">
                                            <a href="#" class="p-2 d-flex align-items-center border rounded me-2" data-bs-toggle="modal" data-bs-target="#domain_rejected"><i class="ti ti-eye"></i></a>
                                            <a href="javascript:void(0);" class="p-2 d-flex align-items-center border rounded" data-bs-toggle="modal" data-bs-target="#delete_modal"><i class="ti ti-trash"></i></a>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="form-check form-check-md">
                                            <input class="form-check-input" type="checkbox">
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center file-name-icon">
                                            <a href="#" class="avatar avatar-md border rounded-circle">
                                                <img src="{{URL::asset('build/img/company/company-04.svg')}}" class="img-fluid" alt="img">
                                            </a>
                                            <div class="ms-2">
                                                <h6 class="fw-medium"><a href="#">EcoVision Enterprises</a></h6>
                                            </div>
                                        </div>
                                    </td>
                                    <td>eve.example.com</td>
                                    <td>Advanced (Monthly)</td>
                                    <td>17 Oct 2024</td>
                                    <td>
                                        <a href="#" class="badge badge-soft-success d-inline-flex align-items-center badge-xs shadow-none">
                                            <i class="ti ti-checks me-1"></i>Approved
                                        </a>
                                    </td>
                                    <td>
                                        <div class="action-icon d-inline-flex align-items-center">
                                            <a href="#" class="p-2 d-flex align-items-center border rounded me-2" data-bs-toggle="modal" data-bs-target="#domain_approved"><i class="ti ti-eye"></i></a>
                                            <a href="javascript:void(0);" class="p-2 d-flex align-items-center border rounded" data-bs-toggle="modal" data-bs-target="#delete_modal"><i class="ti ti-trash"></i></a>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="form-check form-check-md">
                                            <input class="form-check-input" type="checkbox">
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center file-name-icon">
                                            <a href="#" class="avatar avatar-md border rounded-circle">
                                                <img src="{{URL::asset('build/img/company/company-05.svg')}}" class="img-fluid" alt="img">
                                            </a>
                                            <div class="ms-2">
                                                <h6 class="fw-medium"><a href="#">Aurora Technologies</a></h6>
                                            </div>
                                        </div>
                                    </td>
                                    <td>at.example.com</td>
                                    <td>Enterprise (Monthly)</td>
                                    <td>20 Jul 2024</td>
                                    <td>
                                        <a href="#" class="badge badge-soft-success d-inline-flex align-items-center badge-xs shadow-none">
                                            <i class="ti ti-checks me-1"></i>Approved
                                        </a>
                                    </td>
                                    <td>
                                        <div class="action-icon d-inline-flex align-items-center">
                                            <a href="#" class="p-2 d-flex align-items-center border rounded me-2" data-bs-toggle="modal" data-bs-target="#domain_approved"><i class="ti ti-eye"></i></a>
                                            <a href="javascript:void(0);" class="p-2 d-flex align-items-center border rounded" data-bs-toggle="modal" data-bs-target="#delete_modal"><i class="ti ti-trash"></i></a>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="form-check form-check-md">
                                            <input class="form-check-input" type="checkbox">
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center file-name-icon">
                                            <a href="#" class="avatar avatar-md border rounded-circle">
                                                <img src="{{URL::asset('build/img/company/company-06.svg')}}" class="img-fluid" alt="img">
                                            </a>
                                            <div class="ms-2">
                                                <h6 class="fw-medium"><a href="#">BlueSky Ventures</a></h6>
                                            </div>
                                        </div>
                                    </td>
                                    <td>bsv.example.com</td>
                                    <td>Advanced (Monthly)</td>
                                    <td>10 Apr 2024</td>
                                    <td>
                                        <a href="#" class="badge badge-soft-skyblue d-inline-flex align-items-center badge-xs">
                                            <i class="ti ti-clock me-1"></i>Pending
                                        </a>
                                    </td>
                                    <td>
                                        <div class="action-icon d-inline-flex">
                                            <a href="#" class="p-2 d-flex align-items-center border rounded me-2" data-bs-toggle="modal" data-bs-target="#domain_pending"><i class="ti ti-eye"></i></a>
                                            <a href="javascript:void(0);" class="p-2 d-flex align-items-center border rounded" data-bs-toggle="modal" data-bs-target="#delete_modal"><i class="ti ti-trash"></i></a>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="form-check form-check-md">
                                            <input class="form-check-input" type="checkbox">
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center file-name-icon">
                                            <a href="#" class="avatar avatar-md border rounded-circle">
                                                <img src="{{URL::asset('build/img/company/company-07.svg')}}" class="img-fluid" alt="img">
                                            </a>
                                            <div class="ms-2">
                                                <h6 class="fw-medium"><a href="#">TerraFusion Energy</a></h6>
                                            </div>
                                        </div>
                                    </td>
                                    <td>tfe.example.com</td>
                                    <td>Enterprise (Yearly)</td>
                                    <td>29 Aug 2024</td>
                                    <td>
                                        <a href="#" class="badge badge-soft-success d-inline-flex align-items-center badge-xs shadow-none">
                                            <i class="ti ti-checks me-1"></i>Approved
                                        </a>
                                    </td>
                                    <td>
                                        <div class="action-icon d-inline-flex align-items-center">
                                            <a href="#" class="p-2 d-flex align-items-center border rounded me-2" data-bs-toggle="modal" data-bs-target="#domain_approved"><i class="ti ti-eye"></i></a>
                                            <a href="javascript:void(0);" class="p-2 d-flex align-items-center border rounded" data-bs-toggle="modal" data-bs-target="#delete_modal"><i class="ti ti-trash"></i></a>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="form-check form-check-md">
                                            <input class="form-check-input" type="checkbox">
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center file-name-icon">
                                            <a href="#" class="avatar avatar-md border rounded-circle">
                                                <img src="{{URL::asset('build/img/company/company-08.svg')}}" class="img-fluid" alt="img">
                                            </a>
                                            <div class="ms-2">
                                                <h6 class="fw-medium"><a href="#">UrbanPulse Design</a></h6>
                                            </div>
                                        </div>
                                    </td>
                                    <td>upd.example.com</td>
                                    <td>Basic (Monthly)</td>
                                    <td>22 Feb 2024</td>
                                    <td>
                                        <a href="#" class="badge badge-soft-danger d-inline-flex align-items-center badge-xs shadow-none">
                                            <i class="ti ti-x me-1"></i>Rejected
                                        </a>
                                    </td>
                                    <td>
                                        <div class="action-icon d-inline-flex">
                                            <a href="#" class="p-2 d-flex align-items-center border rounded me-2" data-bs-toggle="modal" data-bs-target="#domain_rejected"><i class="ti ti-eye"></i></a>
                                            <a href="javascript:void(0);" class="p-2 d-flex align-items-center border rounded" data-bs-toggle="modal" data-bs-target="#delete_modal"><i class="ti ti-trash"></i></a>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="form-check form-check-md">
                                            <input class="form-check-input" type="checkbox">
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center file-name-icon">
                                            <a href="#" class="avatar avatar-md border rounded-circle">
                                                <img src="{{URL::asset('build/img/company/company-09.svg')}}" class="img-fluid" alt="img">
                                            </a>
                                            <div class="ms-2">
                                                <h6 class="fw-medium"><a href="#">Nimbus Networks</a></h6>
                                            </div>
                                        </div>
                                    </td>
                                    <td>nn.example.com</td>
                                    <td>Basic (Yearly)</td>
                                    <td>03 Nov 2024</td>
                                    <td>
                                        <a href="#" class="badge badge-soft-success d-inline-flex align-items-center badge-xs shadow-none">
                                            <i class="ti ti-checks me-1"></i>Approved
                                        </a>
                                    </td>
                                    <td>
                                        <div class="action-icon d-inline-flex align-items-center">
                                            <a href="#" class="p-2 d-flex align-items-center border rounded me-2" data-bs-toggle="modal" data-bs-target="#domain_approved"><i class="ti ti-eye"></i></a>
                                            <a href="javascript:void(0);" class="p-2 d-flex align-items-center border rounded" data-bs-toggle="modal" data-bs-target="#delete_modal"><i class="ti ti-trash"></i></a>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="form-check form-check-md">
                                            <input class="form-check-input" type="checkbox">
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center file-name-icon">
                                            <a href="#" class="avatar avatar-md border rounded-circle">
                                                <img src="{{URL::asset('build/img/company/company-10.svg')}}" class="img-fluid" alt="img">
                                            </a>
                                            <div class="ms-2">
                                                <h6 class="fw-medium"><a href="#">Epicurean Delights</a></h6>
                                            </div>
                                        </div>
                                    </td>
                                    <td>ed.example.com</td>
                                    <td>Advanced (Monthly)</td>
                                    <td>17 Dec 2024</td>
                                    <td>
                                        <a href="#" class="badge badge-soft-success d-inline-flex align-items-center badge-xs shadow-none">
                                            <i class="ti ti-checks me-1"></i>Approved
                                        </a>
                                    </td>
                                    <td>
                                        <div class="action-icon d-inline-flex align-items-center">
                                            <a href="#" class="p-2 d-flex align-items-center border rounded me-2" data-bs-toggle="modal" data-bs-target="#domain_approved"><i class="ti ti-eye"></i></a>
                                            <a href="javascript:void(0);" class="p-2 d-flex align-items-center border rounded" data-bs-toggle="modal" data-bs-target="#delete_modal"><i class="ti ti-trash"></i></a>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <!-- /product list -->
        </div>
        <div class="footer d-sm-flex align-items-center justify-content-between border-top bg-white p-3">
            <p class="mb-0 text-gray-9">2014 - 2025 &copy; DreamsPOS. All Right Reserved</p>
            <p>Designed &amp; Developed by <a href="javascript:void(0);" class="text-primary">Dreams</a></p>
        </div>
    </div>
@endsection