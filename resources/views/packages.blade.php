<?php $page = 'packages'; ?>
@extends('layout.mainlayout')
@section('content')

<div class="page-wrapper">
    <div class="content">
        <div class="page-header">
            <div class="add-item d-flex">
                <div class="page-title">
                    <h4>Packages</h4>
                    <h6>Manage your packages</h6>
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
                    <a data-bs-toggle="tooltip" data-bs-placement="top" title="Refresh"><i class="ti ti-refresh"></i></a>
                </li>
                <li>
                    <a data-bs-toggle="tooltip" data-bs-placement="top" title="Collapse" id="collapse-header"><i class="ti ti-chevron-up"></i></a>
                </li>
            </ul>
            <div class="page-btn">
                <a href="#" data-bs-toggle="modal" data-bs-target="#add_plans" class="btn btn-primary"><i class="ti ti-circle-plus me-1"></i>Add Packages</a>
            </div>
        </div>

        <div class="row">

            <!-- Total Plans -->
            <div class="col-lg-3 col-md-6 d-flex">
                <div class="card flex-fill">
                    <div class="card-body d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center overflow-hidden">
                            <div>
                                <p class="fs-12 fw-medium mb-1 text-truncate">Total Plans</p>
                                <h4>08</h4>
                            </div>
                        </div>
                        <div>                                    
                            <span class="avatar avatar-lg bg-primary flex-shrink-0">
                                <i class="ti ti-box fs-16"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /Total Plans -->

            <!-- Total Plans -->
            <div class="col-lg-3 col-md-6 d-flex">
                <div class="card flex-fill">
                    <div class="card-body d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center overflow-hidden">									
                            <div>
                                <p class="fs-12 fw-medium mb-1 text-truncate">Active Plans</p>
                                <h4>08</h4>
                            </div>
                        </div>
                        <div>
                            <span class="avatar avatar-lg bg-success flex-shrink-0">
                                <i class="ti ti-activity-heartbeat fs-16"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /Total Plans -->

            <!-- Inactive Plans -->
            <div class="col-lg-3 col-md-6 d-flex">
                <div class="card flex-fill">
                    <div class="card-body d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center overflow-hidden">
                            <div>
                                <p class="fs-12 fw-medium mb-1 text-truncate">Inactive Plans</p>
                                <h4>0</h4>
                            </div>
                        </div>
                        <div>                                    
                            <span class="avatar avatar-lg bg-danger flex-shrink-0">
                                <i class="ti ti-player-pause fs-16"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /Inactive Companies -->

            <!-- No of Plans  -->
            <div class="col-lg-3 col-md-6 d-flex">
                <div class="card flex-fill">
                    <div class="card-body d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center overflow-hidden">									
                            <div>
                                <p class="fs-12 fw-medium mb-1 text-truncate">No of Plan Types</p>
                                <h4>02</h4>
                            </div>
                        </div>
                        <div>
                            <span class="avatar avatar-lg bg-skyblue flex-shrink-0">
                                <i class="ti ti-mask fs-16"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /No of Plans -->

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
                            Status
                        </a>
                        <ul class="dropdown-menu  dropdown-menu-end p-3">
                            <li>
                                <a href="javascript:void(0);" class="dropdown-item rounded-1">Active</a>
                            </li>
                            <li>
                                <a href="javascript:void(0);" class="dropdown-item rounded-1">Inactive</a>
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
                                <th>Plan Name</th>
                                <th>Plan Type</th>
                                <th>Total Subscribers</th>
                                <th>Price</th>
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
                                    <h6 class="fw-medium"><a href="#">Basic</a></h6>
                                </td>
                                <td>Monthly</td>
                                <td class="text-center">56</td>
                                <td>$50</td>
                                <td>14 Jan 2024</td>
                                <td>
                                    <span class="badge badge-success d-inline-flex align-items-center badge-sm">
                                        <i class="ti ti-point-filled me-1"></i>Active
                                    </span>
                                </td>
                                <td>
                                    <div class="action-icon d-inline-flex align-items-center">
                                        <a href="#" class="p-2 d-flex align-items-center border rounded me-2" data-bs-toggle="modal" data-bs-target="#edit_plans"><i class="ti ti-edit"></i></a>
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
                                <td>
                                    <h6 class="fw-medium"><a href="#">Advanced</a></h6>
                                </td>
                                <td>Monthly</td>
                                <td class="text-center">99</td>
                                <td>$200</td>
                                <td>21 Jan 2024</td>
                                <td>
                                    <span class="badge badge-success d-inline-flex align-items-center badge-sm">
                                        <i class="ti ti-point-filled me-1"></i>Active
                                    </span>
                                </td>
                                <td>
                                    <div class="action-icon d-inline-flex align-items-center">
                                        <a href="#" class="p-2 d-flex align-items-center border rounded me-2" data-bs-toggle="modal" data-bs-target="#edit_plans"><i class="ti ti-edit"></i></a>
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
                                <td>
                                    <h6 class="fw-medium"><a href="#">Premium</a></h6>
                                </td>
                                <td>Monthly</td>
                                <td class="text-center">58</td>
                                <td>$300</td>
                                <td>10 Feb 2024</td>
                                <td>
                                    <span class="badge badge-success d-inline-flex align-items-center badge-sm">
                                        <i class="ti ti-point-filled me-1"></i>Active
                                    </span>
                                </td>
                                <td>
                                    <div class="action-icon d-inline-flex align-items-center">
                                        <a href="#" class="p-2 d-flex align-items-center border rounded me-2" data-bs-toggle="modal" data-bs-target="#edit_plans"><i class="ti ti-edit"></i></a>
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
                                <td>
                                    <h6 class="fw-medium"><a href="#">Enterprise</a></h6>
                                </td>
                                <td>Monthly</td>
                                <td class="text-center">67</td>
                                <td>$400</td>
                                <td>18 Feb 2024</td>
                                <td>
                                    <span class="badge badge-success d-inline-flex align-items-center badge-sm">
                                        <i class="ti ti-point-filled me-1"></i>Active
                                    </span>
                                </td>
                                <td>
                                    <div class="action-icon d-inline-flex align-items-center">
                                        <a href="#" class="p-2 d-flex align-items-center border rounded me-2" data-bs-toggle="modal" data-bs-target="#edit_plans"><i class="ti ti-edit"></i></a>
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
                                <td>
                                    <h6 class="fw-medium"><a href="#">Basic</a></h6>
                                </td>
                                <td>Yearly</td>
                                <td class="text-center">78</td>
                                <td>$600</td>
                                <td>15 Mar 2024</td>
                                <td>
                                    <span class="badge badge-success d-inline-flex align-items-center badge-sm">
                                        <i class="ti ti-point-filled me-1"></i>Active
                                    </span>
                                </td>
                                <td>
                                    <div class="action-icon d-inline-flex align-items-center">
                                        <a href="#" class="p-2 d-flex align-items-center border rounded me-2" data-bs-toggle="modal" data-bs-target="#edit_plans"><i class="ti ti-edit"></i></a>
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
                                <td>
                                    <h6 class="fw-medium"><a href="#">Advanced</a></h6>
                                </td>
                                <td>Yearly</td>
                                <td class="text-center">99</td>
                                <td>$2400</td>
                                <td>26 Mar 2024</td>
                                <td>
                                    <span class="badge badge-success d-inline-flex align-items-center badge-sm">
                                        <i class="ti ti-point-filled me-1"></i>Active
                                    </span>
                                </td>
                                <td>
                                    <div class="action-icon d-inline-flex align-items-center">
                                        <a href="#" class="p-2 d-flex align-items-center border rounded me-2" data-bs-toggle="modal" data-bs-target="#edit_plans"><i class="ti ti-edit"></i></a>
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
                                <td>
                                    <h6 class="fw-medium"><a href="#">Premium</a></h6>
                                </td>
                                <td>Yearly</td>
                                <td class="text-center">48</td>
                                <td>$3600</td>
                                <td>05 Apr 2024</td>
                                <td>
                                    <span class="badge badge-success d-inline-flex align-items-center badge-sm">
                                        <i class="ti ti-point-filled me-1"></i>Active
                                    </span>
                                </td>
                                <td>
                                    <div class="action-icon d-inline-flex align-items-center">
                                        <a href="#" class="p-2 d-flex align-items-center border rounded me-2" data-bs-toggle="modal" data-bs-target="#edit_plans"><i class="ti ti-edit"></i></a>
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
                                <td>
                                    <h6 class="fw-medium"><a href="#">Enterprise</a></h6>
                                </td>
                                <td>Yearly</td>
                                <td class="text-center">17</td>
                                <td>$4800</td>
                                <td>16 Apr 2024</td>
                                <td>
                                    <span class="badge badge-success d-inline-flex align-items-center badge-sm">
                                        <i class="ti ti-point-filled me-1"></i>Active
                                    </span>
                                </td>
                                <td>
                                    <div class="action-icon d-inline-flex align-items-center">
                                        <a href="#" class="p-2 d-flex align-items-center border rounded me-2" data-bs-toggle="modal" data-bs-target="#edit_plans"><i class="ti ti-edit"></i></a>
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
    </div>
    <div class="footer d-sm-flex align-items-center justify-content-between border-top bg-white p-3">
        <p class="mb-0 text-gray-9">2014 - 2025 &copy; DreamsPOS. All Right Reserved</p>
        <p>Designed &amp; Developed by <a href="javascript:void(0);" class="text-primary">Dreams</a></p>
    </div>
</div> 
@endsection