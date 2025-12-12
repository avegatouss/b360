<?php $page = 'leaves-admin'; ?>
@extends('layout.mainlayout')
@section('content')


<div class="page-wrapper">
    <div class="content">
        <div class="page-header">
            <div class="add-item d-flex">
                <div class="page-title">
                    <h4>Leaves</h4>
                    <h6>Manage your Leaves</h6>
                </div>
            </div>
            <ul class="table-top-head">
                <li class="me-2">
                    <a data-bs-toggle="tooltip" data-bs-placement="top" title="Pdf"><img src="{{URL::asset('build/img/icons/pdf.svg')}}" alt="img"></a>
                </li>
                <li class="me-2">
                    <a data-bs-toggle="tooltip" data-bs-placement="top" title="Excel"><img src="{{URL::asset('build/img/icons/excel.svg')}}" alt="img"></a>
                </li>
                <li class="me-2">
                    <a data-bs-toggle="tooltip" data-bs-placement="top" title="Refresh"><i class="ti ti-refresh"></i></a>
                </li>
                <li class="me-2">
                    <a data-bs-toggle="tooltip" data-bs-placement="top" title="Collapse" id="collapse-header"><i class="ti ti-chevron-up"></i></a>
                </li>
            </ul>
            <div class="page-btn">
                <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#add-leave">Apply Leave</a>
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
                    <div class="me-2 date-select-small">
                        <div class="input-addon-left position-relative">
                            <input type="text" class="form-control datetimepicker" placeholder="Select Date">
                            <span class="cus-icon"><i data-feather="calendar" class="feather-clock"></i></span>
                        </div>
                    </div>
                    <div class="dropdown">
                        <a href="javascript:void(0);" class="dropdown-toggle btn btn-white btn-md d-inline-flex align-items-center" data-bs-toggle="dropdown">
                            Select Status
                        </a>
                        <ul class="dropdown-menu  dropdown-menu-end p-3">
                            <li>
                                <a href="javascript:void(0);" class="dropdown-item rounded-1">Approved</a>
                            </li>
                            <li>
                                <a href="javascript:void(0);" class="dropdown-item rounded-1">Rejected</a>
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
                                <th>ID</th>
                                <th>Employee</th>
                                <th>Type</th>
                                <th>From Date</th>
                                <th>To Date</th>
                                <th>Days/Hours</th>
                                <th>Applied  On</th>
                                <th>Shift</th>
                                <th>Status</th>
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
                                <td>EMP001</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <a href="#" class="avatar avatar-md me-2">
                                            <img src="{{URL::asset('build/img/users/user-01.jpg')}}" alt="product">
                                        </a>
                                        <div>
                                            <h6><a href="#">Carl Evans</a></h6>
                                            <span>Designer</span>
                                        </div>
                                    </div>
                                </td>
                                <td>Sick Leave</td>
                                <td>24 Dec 2024</td>
                                <td>24 Dec 2024</td>
                                <td>01 Day</td>
                                <td>23 Dec 2024</td>
                                <td>Regular</td>
                                <td>
                                    <span class="badge badge-success d-inline-flex align-items-center badge-xs">
                                        <i class="ti ti-point-filled me-1"></i>Approved
                                    </span>
                                </td>
                                <td class="action-table-data">
                                    <div class="edit-delete-action">
                                        <a class="me-2 p-2" href="#" data-bs-toggle="modal" data-bs-target="#edit-leave">
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
                                <td>EMP002</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <a href="#" class="avatar avatar-md me-2">
                                            <img src="{{URL::asset('build/img/users/user-06.jpg')}}" alt="product">
                                        </a>
                                        <div>
                                            <h6><a href="#">Minerva Rameriz</a></h6>
                                            <span>Administrator</span>
                                        </div>
                                    </div>
                                </td>
                                <td>Casual Leave</td>
                                <td>10 Dec 2024</td>
                                <td>10 Dec 2024</td>
                                <td>01 Day</td>
                                <td>09 Dec 2024</td>
                                <td>Regular</td>
                                <td>
                                    <span class="badge badge-success d-inline-flex align-items-center badge-xs">
                                        <i class="ti ti-point-filled me-1"></i>Approved
                                    </span>
                                </td>
                                <td class="action-table-data">
                                    <div class="edit-delete-action">
                                        <a class="me-2 p-2" href="#" data-bs-toggle="modal" data-bs-target="#edit-leave">
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
                                <td>EMP003</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <a href="#" class="avatar avatar-md me-2">
                                            <img src="{{URL::asset('build/img/users/user-04.jpg')}}" alt="product">
                                        </a>
                                        <div>
                                            <h6><a href="#">Robert Lamon</a></h6>
                                            <span>Developer</span>
                                        </div>
                                    </div>
                                </td>
                                <td>Casual Leave</td>
                                <td>27 Nov 2024</td>
                                <td>28 Nov 2024</td>
                                <td>02 Day</td>
                                <td>26 Nov 2024</td>
                                <td>Regular</td>
                                <td>
                                    <span class="badge badge-success d-inline-flex align-items-center badge-xs">
                                        <i class="ti ti-point-filled me-1"></i>Approved
                                    </span>
                                </td>
                                <td class="action-table-data">
                                    <div class="edit-delete-action">
                                        <a class="me-2 p-2" href="#" data-bs-toggle="modal" data-bs-target="#edit-leave">
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
                                <td>EMP004</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <a href="#" class="avatar avatar-md me-2">
                                            <img src="{{URL::asset('build/img/users/user-03.jpg')}}" alt="product">
                                        </a>
                                        <div>
                                            <h6><a href="#">Patricia Lewis</a></h6>
                                            <span>HR Manager</span>
                                        </div>
                                    </div>
                                </td>
                                <td>Sick Leave</td>
                                <td>18 Nov 2024</td>
                                <td>18 Nov 2024</td>
                                <td>02 hrs</td>
                                <td>18 Nov 2024</td>
                                <td>Regular</td>
                                <td>
                                    <span class="badge badge-success d-inline-flex align-items-center badge-xs">
                                        <i class="ti ti-point-filled me-1"></i>Approved
                                    </span>
                                </td>
                                <td class="action-table-data">
                                    <div class="edit-delete-action">
                                        <a class="me-2 p-2" href="#" data-bs-toggle="modal" data-bs-target="#edit-leave">
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
                                <td>EMP005</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <a href="#" class="avatar avatar-md me-2">
                                            <img src="{{URL::asset('build/img/users/user-05.jpg')}}" alt="product">
                                        </a>
                                        <div>
                                            <h6><a href="#">Mark Joslyn</a></h6>
                                            <span>Designer</span>
                                        </div>
                                    </div>
                                </td>
                                <td>Casual Leave</td>
                                <td>06 Nov 2024</td>
                                <td>08 Nov 2024</td>
                                <td>03 Days</td>
                                <td>05 Nov 2024</td>
                                <td>Regular</td>
                                <td>
                                    <span class="badge badge-success d-inline-flex align-items-center badge-xs">
                                        <i class="ti ti-point-filled me-1"></i>Approved
                                    </span>
                                </td>
                                <td class="action-table-data">
                                    <div class="edit-delete-action">
                                        <a class="me-2 p-2" href="#" data-bs-toggle="modal" data-bs-target="#edit-leave">
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
                                <td>EMP006</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <a href="#" class="avatar avatar-md me-2">
                                            <img src="{{URL::asset('build/img/users/user-05.jpg')}}" alt="product">
                                        </a>
                                        <div>
                                            <h6><a href="#">Marsha Betts</a></h6>
                                            <span>Developer</span>
                                        </div>
                                    </div>
                                </td>
                                <td>Sick Leave</td>
                                <td>25 Oct 2024</td>
                                <td>25 Oct 2024</td>
                                <td>01 Days</td>
                                <td>24 Oct 2024</td>
                                <td>Regular</td>
                                <td>
                                    <span class="badge badge-danger d-inline-flex align-items-center badge-xs">
                                        <i class="ti ti-point-filled me-1"></i>Rejected
                                    </span>
                                </td>
                                <td class="action-table-data">
                                    <div class="edit-delete-action">
                                        <a class="me-2 p-2" href="#" data-bs-toggle="modal" data-bs-target="#edit-leave">
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
                                <td>EMP007</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <a href="#" class="avatar avatar-md me-2">
                                            <img src="{{URL::asset('build/img/users/user-28.jpg')}}" alt="product">
                                        </a>
                                        <div>
                                            <h6><a href="#">Daniel Jude</a></h6>
                                            <span>Administrator</span>
                                        </div>
                                    </div>
                                </td>
                                <td>Casual Leave</td>
                                <td>14 Oct 2024</td>
                                <td>15 Oct 2024</td>
                                <td>02 Days</td>
                                <td>13 Oct 2024</td>
                                <td>Regular</td>
                                <td>
                                    <span class="badge badge-success d-inline-flex align-items-center badge-xs">
                                        <i class="ti ti-point-filled me-1"></i>Approved
                                    </span>
                                </td>
                                <td class="action-table-data">
                                    <div class="edit-delete-action">
                                        <a class="me-2 p-2" href="#" data-bs-toggle="modal" data-bs-target="#edit-leave">
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
                                <td>EMP008</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <a href="#" class="avatar avatar-md me-2">
                                            <img src="{{URL::asset('build/img/users/user-12.jpg')}}" alt="product">
                                        </a>
                                        <div>
                                            <h6><a href="#">Emma Bates</a></h6>
                                            <span>HR Assistant</span>
                                        </div>
                                    </div>
                                </td>
                                <td>Casual Leave</td>
                                <td>03 Oct 2024</td>
                                <td>03 Oct 2024</td>
                                <td>01 Days</td>
                                <td>02 Oct 2024</td>
                                <td>Regular</td>
                                <td>
                                    <span class="badge badge-success d-inline-flex align-items-center badge-xs">
                                        <i class="ti ti-point-filled me-1"></i>Approved
                                    </span>
                                </td>
                                <td class="action-table-data">
                                    <div class="edit-delete-action">
                                        <a class="me-2 p-2" href="#" data-bs-toggle="modal" data-bs-target="#edit-leave">
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
                                <td>EMP009</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <a href="#" class="avatar avatar-md me-2">
                                            <img src="{{URL::asset('build/img/users/user-30.jpg')}}" alt="product">
                                        </a>
                                        <div>
                                            <h6><a href="#">Richard Fralick </a></h6>
                                            <span>Designer</span>
                                        </div>
                                    </div>
                                </td>
                                <td>Sick Leave</td>
                                <td>20 Sep 2024</td>
                                <td>21 Sep 2024</td>
                                <td>02 Days</td>
                                <td>19 Sep 2024</td>
                                <td>Regular</td>
                                <td>
                                    <span class="badge badge-success d-inline-flex align-items-center badge-xs">
                                        <i class="ti ti-point-filled me-1"></i>Approved
                                    </span>
                                </td>
                                <td class="action-table-data">
                                    <div class="edit-delete-action">
                                        <a class="me-2 p-2" href="#" data-bs-toggle="modal" data-bs-target="#edit-leave">
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
                                <td>EMP010</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <a href="#" class="avatar avatar-md me-2">
                                            <img src="{{URL::asset('build/img/users/user-26.jpg')}}" alt="product">
                                        </a>
                                        <div>
                                            <h6><a href="#">Michelle Robison</a></h6>
                                            <span>HR Manager</span>
                                        </div>
                                    </div>
                                </td>
                                <td>Casual Leave</td>
                                <td>10 Sep 2024</td>
                                <td>10 Sep 2024</td>
                                <td>02 hrs</td>
                                <td>09 Sep 2024</td>
                                <td>Regular</td>
                                <td>
                                    <span class="badge badge-danger d-inline-flex align-items-center badge-xs">
                                        <i class="ti ti-point-filled me-1"></i>Rejected
                                    </span>
                                </td>
                                <td class="action-table-data">
                                    <div class="edit-delete-action">
                                        <a class="me-2 p-2" href="#" data-bs-toggle="modal" data-bs-target="#edit-leave">
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
    </div>
    <div class="footer d-sm-flex align-items-center justify-content-between border-top bg-white p-3">
        <p class="mb-0">2014 - 2025 &copy; DreamsPOS. All Right Reserved</p>
        <p>Designed &amp; Developed by <a href="javascript:void(0);" class="text-primary">Dreams</a></p>
    </div>
</div>

@endsection
