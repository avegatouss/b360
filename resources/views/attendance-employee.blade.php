<?php $page = 'attendance-employee'; ?>
@extends('layout.mainlayout')
@section('content')
    <div class="page-wrapper">
        <div class="content">
            <div class="attendance-header">
                <div class="attendance-content">
                <img src="{{URL::asset('build/img/icons/hand01.svg')}}" class="hand-img" alt="img">
                <h3>Good Morning, <span>John Smilga</span></h3>
                </div>
                <div>
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
            </div>
            <div class="row">
                <div class="col-xl-4 col-lg-12 d-flex">
                    <div class="card w-100">
                        <div class="card-body">
                            <h5 class="mb-3 pb-3 border-bottom d-flex justify-content-between align-items-center fs-18">Attendance<span class="text-purple fs-14">22 Aug 2023</span></h5>
                            <div class="d-flex align-items-center mb-3">
                                <div class="me-2">
                                    <img src="{{URL::asset('build/img/icons/time-big.svg')}}" alt="time-img">
                                </div>
                                <div>
                                    <h2>05:45:22</h2>
                                    <p>Current Time</p>
                                </div>
                            </div>
                            <div class="d-flex align-items-center">
                                <a href="javascript:void(0);" class="btn btn-primary w-100 me-2">Clock Out</a>
                                <a href="javascript:void(0);" class="btn btn-secondary me-2 w-100">Break</a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-8 col-lg-12 d-flex">
                    <div class="card w-100">
                        <div class="card-body">
                            <h5 class="border-bottom pb-3 mb-3">Days Overview This Month</h5>
                            <div class="row gy-3">
                                <div class="col-lg-2 col-md-3 col-sm-4 text-center">
                                    <div>
                                        <span class="d-flex align-items-center justify-content-center avatar avatar-xl bg-primary-transparent fw-bold fs-20 mb-2 mx-auto">31</span>
                                        <p class="fs-14">Total Working <br> Days</p>
                                    </div>
                                </div>
                                <div class="col-lg-2 col-md-3 col-sm-4 text-center">
                                    <div>
                                        <span class="d-flex align-items-center justify-content-center avatar avatar-xl bg-danger-transparent fw-bold fs-20 mb-2 mx-auto">05</span>
                                        <p class="fs-14">Abesent <br>Days</p>
                                    </div>
                                </div>
                                <div class="col-lg-2 col-md-3 col-sm-4 text-center">
                                    <div>
                                        <span class="d-flex align-items-center justify-content-center avatar avatar-xl bg-purple-transparent text-purple fw-bold fs-20 mb-2 mx-auto">28</span>
                                        <p class="fs-14">Present <br>Days</p>
                                    </div>
                                </div>
                                <div class="col-lg-2 col-md-3 col-sm-4 text-center">
                                    <div>
                                        <span class="d-flex align-items-center justify-content-center avatar avatar-xl bg-warning-transparent fw-bold fs-20 mb-2 mx-auto">02</span>
                                        <p class="fs-14">Half<br> Days</p>
                                    </div>
                                </div>
                                <div class="col-lg-2 col-md-3 col-sm-4 text-center">
                                    <div>
                                        <span class="d-flex align-items-center justify-content-center avatar avatar-xl bg-cyan-transparent text-cyan fw-bold fs-20 mb-2 mx-auto">01</span>
                                        <p class="fs-14">Late <br>Days</p>
                                    </div>
                                </div>
                                <div class="col-lg-2 col-md-3 col-sm-4 text-center">
                                    <div>
                                        <span class="d-flex align-items-center justify-content-center avatar avatar-xl bg-success-transparent text-success fw-bold fs-20 mb-2 mx-auto">02</span>
                                        <p class="fs-14">Holidays</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
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
                                Select Status
                            </a>
                            <ul class="dropdown-menu  dropdown-menu-end p-3">
                                <li>
                                    <a href="javascript:void(0);" class="dropdown-item rounded-1">Present</a>
                                </li>
                                <li>
                                    <a href="javascript:void(0);" class="dropdown-item rounded-1">Absent</a>
                                </li>
                                <li>
                                    <a href="javascript:void(0);" class="dropdown-item rounded-1">Holiday</a>
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
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Clock In</th>
                                    <th>Clock Out</th>
                                    <th>Production</th>
                                    <th>Break</th>
                                    <th>Overtime</th>
                                    <th>Progress</th>
                                    <th>Total Hours</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>01 Jan 2025</td>
                                    <td>
                                        <span class="badge badge-success d-inline-flex align-items-center badge-xs">
                                            <i class="ti ti-point-filled me-1"></i>Present
                                        </span>
                                    </td>
                                    <td>09:15 AM</td>
                                    <td>08:55 PM</td>
                                    <td>9h 00m</td>
                                    <td>1h 13m</td>
                                    <td>00h 50m</td>
                                    <td>
                                        <div class="progress attendance bg-secondary-transparent">
                                            <div class="progress-bar progress-bar-success" role="progressbar" style="width:60%">
                                            </div>
                                            <div class="progress-bar progress-bar-warning" role="progressbar" style="width:20%">
                                            </div>
                                            <div class="progress-bar progress-bar-danger" role="progressbar" style="width:10%">
                                            </div>
                                        </div>
                                    </td>
                                    <td>09h 50m</td>
                                </tr>
                                <tr>
                                    <td>02 Jan 2025</td>
                                    <td>
                                        <span class="badge badge-success d-inline-flex align-items-center badge-xs">
                                            <i class="ti ti-point-filled me-1"></i>Present
                                        </span>
                                    </td>
                                    <td>09:07 AM</td>
                                    <td>08:40 PM</td>
                                    <td>9h 10m</td>
                                    <td>1h 07m</td>
                                    <td>01h 13m</td>
                                    <td>
                                        <div class="progress attendance bg-secondary-transparent">
                                            <div class="progress-bar progress-bar-success" role="progressbar" style="width:60%">
                                            </div>
                                            <div class="progress-bar progress-bar-warning" role="progressbar" style="width:25%">
                                            </div>
                                            <div class="progress-bar progress-bar-danger" role="progressbar" style="width:5%">
                                            </div>
                                            </div>
                                    </td>
                                    <td>10h 23m</td>
                                </tr>
                                <tr>
                                    <td>03 Jan 2025</td>
                                    <td>
                                        <span class="badge badge-success d-inline-flex align-items-center badge-xs">
                                            <i class="ti ti-point-filled me-1"></i>Present
                                        </span>
                                    </td>
                                    <td>09:04 AM</td>
                                    <td>08:52 PM</td>
                                    <td>8h 47m</td>
                                    <td>1h 04m</td>
                                    <td>01h 07m</td>
                                    <td>
                                        <div class="progress attendance bg-secondary-transparent">
                                            <div class="progress-bar progress-bar-success" role="progressbar" style="width: 60%">
                                            </div>
                                            <div class="progress-bar progress-bar-warning" role="progressbar" style="width:20%">
                                            </div>
                                            </div>
                                    </td>
                                    <td>10h 04m</td>
                                </tr>
                                <tr>
                                    <td>04 Jan 2025</td>
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
                                    <td>
                                        <div class="progress attendance bg-secondary-transparent">
                                            <div class="progress-bar progress-bar-success" role="progressbar" style="width:60%">
                                            </div>
                                            <div class="progress-bar progress-bar-warning" role="progressbar" style="width:20%">
                                            </div>
                                            <div class="progress-bar progress-bar-danger" role="progressbar" style="width:10%">
                                            </div>
                                            </div>
                                    </td>
                                    <td>09h 14m</td>
                                </tr>
                                <tr>
                                    <td>06 Jan 2025</td>
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
                                    <td>
                                        <div class="progress attendance bg-secondary-transparent">	
                                        </div>
                                    </td>
                                    <td>-</td>
                                </tr>
                                <tr>
                                    <td>07 Jan 2023</td>
                                    <td>
                                        <span class="badge badge-success d-inline-flex align-items-center badge-xs">
                                            <i class="ti ti-point-filled me-1"></i>Prescent
                                        </span>
                                    </td>
                                    <td>09:03 AM</td>
                                    <td>08:57 PM</td>
                                    <td>8h 50m</td>
                                    <td>1h 26m</td>
                                    <td>0h 43m</td>
                                    <td>
                                        <div class="progress attendance bg-secondary-transparent">
                                            <div class="progress-bar progress-bar-success" role="progressbar" style="width:60%">
                                            </div>
                                            <div class="progress-bar progress-bar-warning" role="progressbar" style="width:20%">
                                            </div>
                                            <div class="progress-bar progress-bar-danger" role="progressbar" style="width:10%">
                                            </div>
                                        </div>
                                    </td>
                                    <td>08h 33m</td>
                                </tr>
                                <tr>
                                    <td>04 Jan 2023</td>
                                    <td>
                                        <span class="badge badge-purple d-inline-flex align-items-center badge-xs">
                                            <i class="ti ti-point-filled me-1"></i>Holiday
                                        </span>
                                    </td>
                                    <td>-</td>
                                    <td>-</td>
                                    <td>-</td>
                                    <td>-</td>
                                    <td>-</td>
                                    <td>
                                        <div class="progress attendance bg-secondary-transparent">
                                            </div>
                                    </td>
                                    <td>-</td>
                                </tr>
                                <tr>
                                    <td>07 Jan 2023</td>
                                    <td>
                                        <span class="badge badge-success d-inline-flex align-items-center badge-xs">
                                            <i class="ti ti-point-filled me-1"></i>Prescent
                                        </span>
                                    </td>
                                    <td>09:42 AM</td>
                                    <td>07:20 PM</td>
                                    <td>09h 17m</td>
                                    <td>01h 00m</td>
                                    <td>00h 17m</td>
                                    <td>
                                        <div class="progress attendance bg-secondary-transparent">
                                            <div class="progress-bar progress-bar-success" role="progressbar" style="width:60%">
                                            </div>
                                            <div class="progress-bar progress-bar-warning" role="progressbar" style="width:20%">
                                            </div>
                                            <div class="progress-bar progress-bar-danger" role="progressbar" style="width:10%">
                                            </div>
                                        </div>
                                    </td>
                                    <td>09h 17m</td>
                                </tr>
                                <tr>
                                    <td>07 Jan 2023</td>
                                    <td>
                                        <span class="badge badge-success d-inline-flex align-items-center badge-xs">
                                            <i class="ti ti-point-filled me-1"></i>Prescent
                                        </span>
                                    </td>
                                    <td>09:18 AM</td>
                                    <td>07:11 PM</td>
                                    <td>09h 32m</td>
                                    <td>01h 15m</td>
                                    <td>00h 32m</td>
                                    <td>
                                        <div class="progress attendance bg-secondary-transparent">
                                            <div class="progress-bar progress-bar-success" role="progressbar" style="width:60%">
                                            </div>
                                            <div class="progress-bar progress-bar-warning" role="progressbar" style="width:20%">
                                            </div>
                                            <div class="progress-bar progress-bar-danger" role="progressbar" style="width:10%">
                                            </div>
                                        </div>
                                    </td>
                                    <td>09h 32m</td>
                                </tr>
                                <tr>
                                    <td>07 Jan 2023</td>
                                    <td>
                                        <span class="badge badge-success d-inline-flex align-items-center badge-xs">
                                            <i class="ti ti-point-filled me-1"></i>Prescent
                                        </span>
                                    </td>
                                    <td>09:30 AM</td>
                                    <td>08:10 PM</td>
                                    <td>09h 00m</td>
                                    <td>00h 34m</td>
                                    <td>00h 20m</td>
                                    <td>
                                        <div class="progress attendance bg-secondary-transparent">
                                            <div class="progress-bar progress-bar-success" role="progressbar" style="width:60%">
                                            </div>
                                            <div class="progress-bar progress-bar-warning" role="progressbar" style="width:20%">
                                            </div>
                                            <div class="progress-bar progress-bar-danger" role="progressbar" style="width:10%">
                                            </div>
                                        </div>
                                    </td>
                                    <td>09h 32m</td>
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
