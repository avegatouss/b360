<?php $page = 'attendance-admin'; ?>
@extends('layout.mainlayout')
@section('content')
    <div class="page-wrapper">
        <div class="content">
            <div class="page-header">
                <div class="add-item d-flex">
                    <div class="page-title">
                        <h4>Attendance</h4>
                        <h6>Manage your Attendance</h6>
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
                                    <a href="javascript:void(0);" class="dropdown-item rounded-1">Present</a>
                                </li>
                                <li>
                                    <a href="javascript:void(0);" class="dropdown-item rounded-1">Absent</a>
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
                                    <th>Employee</th>
                                    <th>Status</th>
                                    <th>Clock In</th>
                                    <th>Clock Out</th>
                                    <th>Production</th>
                                    <th>Break</th>
                                    <th>Overtime</th>
                                    <th>Total Hours</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
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
                                    <td>
                                        <span class="badge badge-success d-inline-flex align-items-center badge-xs">
                                            <i class="ti ti-point-filled me-1"></i>Present
                                        </span>
                                    </td>
                                    <td>09:00 AM</td>
                                    <td>07:15 PM</td>
                                    <td>09h 00m</td>
                                    <td>0h 45m</td>
                                    <td>0h 20m</td>
                                    <td>09h 20m</td>
                                </tr>
                                <tr>
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
                                    <td>
                                        <span class="badge badge-success d-inline-flex align-items-center badge-xs">
                                            <i class="ti ti-point-filled me-1"></i>Present
                                        </span>
                                    </td>
                                    <td>09:15 AM</td>
                                    <td>07:12 PM</td>
                                    <td>09h 00m</td>
                                    <td>01h 15m</td>
                                    <td>0h 12m</td>
                                    <td>09h 12m</td>
                                </tr>
                                <tr>
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
                                    <td>
                                        <span class="badge badge-success d-inline-flex align-items-center badge-xs">
                                            <i class="ti ti-point-filled me-1"></i>Present
                                        </span>
                                    </td>
                                    <td>09:40 AM</td>
                                    <td>07:00 PM</td>
                                    <td>08h 45m</td>
                                    <td>01h 00m</td>
                                    <td>00h 00m</td>
                                    <td>08h 45m</td>
                                </tr>	
                                <tr>
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
                                    <td>
                                        <span class="badge badge-success d-inline-flex align-items-center badge-xs">
                                            <i class="ti ti-point-filled me-1"></i>Present
                                        </span>
                                    </td>
                                    <td>09:45 AM</td>
                                    <td>08:10 PM</td>
                                    <td>09h 12m</td>
                                    <td>00h 50m</td>
                                    <td>00 14m</td>
                                    <td>09h 14m</td>
                                </tr>	
                                <tr>
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
                                    <td>
                                        <span class="badge badge-danger d-inline-flex align-items-center badge-xs">
                                            <i class="ti ti-point-filled me-1"></i>Absent
                                        </span>
                                    </td>
                                    <td>-</td>
                                    <td>-</td>
                                    <td>-</td>
                                    <td>-</td>
                                    <td>-</td>
                                    <td>-</td>
                                </tr>
                                <tr>
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
                                    <td>
                                        <span class="badge badge-success d-inline-flex align-items-center badge-xs">
                                            <i class="ti ti-point-filled me-1"></i>Present
                                        </span>
                                    </td>
                                    <td>09:17 AM</td>
                                    <td>07:34 PM</td>
                                    <td>09h 26m</td>
                                    <td>01h 20m</td>
                                    <td>00h 26m</td>
                                    <td>09h 26m</td>
                                </tr>		
                                <tr>
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
                                    <td>
                                        <span class="badge badge-danger d-inline-flex align-items-center badge-xs">
                                            <i class="ti ti-point-filled me-1"></i>Absent
                                        </span>
                                    </td>
                                    <td>-</td>
                                    <td>-</td>
                                    <td>-</td>
                                    <td>-</td>
                                    <td>-</td>
                                    <td>-</td>
                                </tr>
                                <tr>
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
                                    <td>
                                        <span class="badge badge-success d-inline-flex align-items-center badge-xs">
                                            <i class="ti ti-point-filled me-1"></i>Present
                                        </span>
                                    </td>
                                    <td>09:42 AM</td>
                                    <td>07:20 PM</td>
                                    <td>09h 17m</td>
                                    <td>01h 00m</td>
                                    <td>00h 17m</td>
                                    <td>09h 17m</td>
                                </tr>		
                                <tr>
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
                                    <td>
                                        <span class="badge badge-success d-inline-flex align-items-center badge-xs">
                                            <i class="ti ti-point-filled me-1"></i>Present
                                        </span>
                                    </td>
                                    <td>09:18 AM</td>
                                    <td>07:11 PM</td>
                                    <td>09h 32m</td>
                                    <td>01h 15m</td>
                                    <td>00h 32m</td>
                                    <td>09h 32m</td>
                                </tr>		
                                <tr>
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
                                    <td>
                                        <span class="badge badge-success d-inline-flex align-items-center badge-xs">
                                            <i class="ti ti-point-filled me-1"></i>Present
                                        </span>
                                    </td>
                                    <td>09:30 AM</td>
                                    <td>08:10 PM</td>
                                    <td>09h 00m</td>
                                    <td>00h 34m</td>
                                    <td>00h 20m</td>
                                    <td>09h 20m</td>
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
