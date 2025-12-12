<?php $page = 'employees-list'; ?>
@extends('layout.mainlayout')
@section('content')
    <div class="page-wrapper">
        <div class="content">
            <div class="page-header">
                <div class="add-item d-flex">
                    <div class="page-title">
                        <h4>Employees</h4>
                        <h6>Manage your employees</h6>
                    </div>
                </div>
                <ul class="table-top-head">
                    <li>
                        <div class="d-flex me-2 pe-2 border-end">
                            <a href="{{url('employees-list')}}" class="btn-list active bg-primary me-2"><i data-feather="list" class="feather-user text-white"></i></a>
                            <a href="{{url('employees-grid')}}" class="btn-grid me-2"><i data-feather="grid" class="feather-user"></i></a>
                        </div>
                    </li>
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
                    <a href="{{url('add-employee')}}" class="btn btn-primary"><i class="ti ti-circle-plus me-1"></i>Add Employee</a>
                </div>
            </div>
            <div class="row">
                <div class="col-xl-3 col-md-6">
                    <div class="card bg-purple border-0">
                        <div class="card-body d-flex align-items-center justify-content-between">
                            <div>
                                <p class="mb-1 text-white">Total Employee</p>
                                <h4 class="text-white">1007</h4>
                            </div>
                            <div>
                                <span class="avatar avatar-lg bg-purple-900"><i class="ti ti-users-group"></i></span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card bg-teal border-0">
                        <div class="card-body d-flex align-items-center justify-content-between">
                            <div>
                                <p class="mb-1 text-white">Active</p>
                                <h4 class="text-white">1007</h4>
                            </div>
                            <div>
                                <span class="avatar avatar-lg bg-teal-900"><i class="ti ti-user-star"></i></span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card bg-secondary border-0">
                        <div class="card-body d-flex align-items-center justify-content-between">
                            <div>
                                <p class="mb-1 text-white">Inactive</p>
                                <h4 class="text-white">1007</h4>
                            </div>
                            <div>
                                <span class="avatar avatar-lg bg-secondary-900"><i class="ti ti-user-exclamation"></i></span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card bg-info border-0">
                        <div class="card-body d-flex align-items-center justify-content-between">
                            <div>
                                <p class="mb-1 text-white">New Joiners</p>
                                <h4 class="text-white">67</h4>
                            </div>
                            <div>
                                <span class="avatar avatar-lg bg-info-900"><i class="ti ti-user-check"></i></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- product list -->
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
                                Select Employees 
                            </a>
                            <ul class="dropdown-menu  dropdown-menu-end p-3">
                                <li>
                                    <a href="javascript:void(0);" class="dropdown-item rounded-1">Anthony Lewis</a>
                                </li>
                                <li>
                                    <a href="javascript:void(0);" class="dropdown-item rounded-1">Brian Villalobos</a>
                                </li>
                                <li>
                                    <a href="javascript:void(0);" class="dropdown-item rounded-1">Harvey Smith</a>
                                </li>
                                <li>
                                    <a href="javascript:void(0);" class="dropdown-item rounded-1">Stephan Peralt</a>
                                </li>
                            </ul>
                        </div>
                        <div class="dropdown">
                            <a href="javascript:void(0);" class="dropdown-toggle btn btn-white btn-md d-inline-flex align-items-center" data-bs-toggle="dropdown">
                                Designation
                            </a>
                            <ul class="dropdown-menu  dropdown-menu-end p-3">
                                <li>
                                    <a href="javascript:void(0);" class="dropdown-item rounded-1">System Admin</a>
                                </li>
                                <li>
                                    <a href="javascript:void(0);" class="dropdown-item rounded-1">Designer</a>
                                </li>
                                <li>
                                    <a href="javascript:void(0);" class="dropdown-item rounded-1">Tech Lead</a>
                                </li>
                                <li>
                                    <a href="javascript:void(0);" class="dropdown-item rounded-1">Database administrator</a>
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
                                    <th>Designation</th>
                                    <th>Email</th>
                                    <th>Phone</th>
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
                                    <td>
                                        <a href="{{url('employee-details')}}">EMP001</a>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <a href="{{url('employee-details')}}" class="avatar avatar-md"><img src="{{URL::asset('build/img/users/user-32.jpg')}}" class="img-fluid" alt="img"></a>
                                            <div class="ms-2">
                                                <p class="text-dark mb-0"><a href="{{url('employee-details')}}">Carl Evans</a></p>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        Designer
                                    </td>
                                    <td>
                                        carlevans@example.com									
                                    </td>
                                    <td>+12163547758 </td>
                                    <td>
                                        Regular
                                    </td>
                                    <td>
                                        <span class="badge badge-success d-inline-flex align-items-center badge-xs">
                                            <i class="ti ti-point-filled me-1"></i>Active
                                        </span>
                                    </td>
                                    <td>
                                        <div class="edit-delete-action d-flex align-items-center">
                                            <a class="me-2 d-flex align-items-center border rounded p-2" href="{{url('employee-details')}}">
                                                <i data-feather="eye" class="feather-eye"></i>
                                            </a>
                                            <a class="me-2 p-2 d-flex align-items-center border rounded" href="{{url('edit-employee')}}">
                                                <i data-feather="edit" class="feather-edit"></i>
                                            </a>
                                            <a data-bs-toggle="modal" data-bs-target="#delete-modal" class="p-2 d-flex align-items-center border rounded" href="javascript:void(0);">
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
                                        <a href="{{url('employee-details')}}">EMP002</a>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <a href="{{url('employee-details')}}" class="avatar avatar-md"><img src="{{URL::asset('build/img/users/user-02.jpg')}}" class="img-fluid" alt="img"></a>
                                            <div class="ms-2">
                                                <p class="text-dark mb-0"><a href="{{url('employee-details')}}">Minerva Rameriz</a></p>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        Administrator
                                    </td>
                                    <td>
                                        rameriz@example.com								
                                    </td>
                                    <td>+11367529510 </td>
                                    <td>
                                        Regular
                                    </td>
                                    <td>
                                        <span class="badge badge-success d-inline-flex align-items-center badge-xs">
                                            <i class="ti ti-point-filled me-1"></i>Active
                                        </span>
                                    </td>
                                    <td>
                                        <div class="edit-delete-action d-flex align-items-center">
                                            <a class="me-2 d-flex align-items-center border rounded p-2" href="{{url('employee-details')}}">
                                                <i data-feather="eye" class="feather-eye"></i>
                                            </a>
                                            <a class="me-2 p-2 d-flex align-items-center border rounded" href="{{url('edit-employee')}}">
                                                <i data-feather="edit" class="feather-edit"></i>
                                            </a>
                                            <a data-bs-toggle="modal" data-bs-target="#delete-modal" class="p-2 d-flex align-items-center border rounded" href="javascript:void(0);">
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
                                        <a href="{{url('employee-details')}}">EMP003</a>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <a href="{{url('employee-details')}}" class="avatar avatar-md"><img src="{{URL::asset('build/img/users/user-01.jpg')}}" class="img-fluid" alt="img"></a>
                                            <div class="ms-2">
                                                <p class="text-dark mb-0"><a href="{{url('employee-details')}}">Robert Lamon</a></p>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        Developer
                                    </td>
                                    <td>
                                        robert@example.com						
                                    </td>
                                    <td>+15362789414</td>
                                    <td>
                                        Regular
                                    </td>
                                    <td>
                                        <span class="badge badge-success d-inline-flex align-items-center badge-xs">
                                            <i class="ti ti-point-filled me-1"></i>Active
                                        </span>
                                    </td>
                                    <td>
                                        <div class="edit-delete-action d-flex align-items-center">
                                            <a class="me-2 d-flex align-items-center border rounded p-2" href="{{url('employee-details')}}">
                                                <i data-feather="eye" class="feather-eye"></i>
                                            </a>
                                            <a class="me-2 p-2 d-flex align-items-center border rounded" href="{{url('edit-employee')}}">
                                                <i data-feather="edit" class="feather-edit"></i>
                                            </a>
                                            <a data-bs-toggle="modal" data-bs-target="#delete-modal" class="p-2 d-flex align-items-center border rounded" href="javascript:void(0);">
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
                                        <a href="{{url('employee-details')}}">EMP004</a>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <a href="{{url('employee-details')}}" class="avatar avatar-md"><img src="{{URL::asset('build/img/users/user-04.jpg')}}" class="img-fluid" alt="img"></a>
                                            <div class="ms-2">
                                                <p class="text-dark mb-0"><a href="{{url('employee-details')}}">Patricia Lewis</a></p>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        HR Manager
                                    </td>
                                    <td>
                                        patricia@example.com					
                                    </td>
                                    <td>+18513094627</td>
                                    <td>
                                        Night Shift
                                    </td>
                                    <td>
                                        <span class="badge badge-success d-inline-flex align-items-center badge-xs">
                                            <i class="ti ti-point-filled me-1"></i>Active
                                        </span>
                                    </td>
                                    <td>
                                        <div class="edit-delete-action d-flex align-items-center">
                                            <a class="me-2 d-flex align-items-center border rounded p-2" href="{{url('employee-details')}}">
                                                <i data-feather="eye" class="feather-eye"></i>
                                            </a>
                                            <a class="me-2 p-2 d-flex align-items-center border rounded" href="{{url('edit-employee')}}">
                                                <i data-feather="edit" class="feather-edit"></i>
                                            </a>
                                            <a data-bs-toggle="modal" data-bs-target="#delete-modal" class="p-2 d-flex align-items-center border rounded" href="javascript:void(0);">
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
                                        <a href="{{url('employee-details')}}">EMP005</a>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <a href="{{url('employee-details')}}" class="avatar avatar-md"><img src="{{URL::asset('build/img/users/user-08.jpg')}}" class="img-fluid" alt="img"></a>
                                            <div class="ms-2">
                                                <p class="text-dark mb-0"><a href="{{url('employee-details')}}">Mark Joslyn</a></p>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        Designer
                                    </td>
                                    <td>
                                        markjoslyn@example.com				
                                    </td>
                                    <td>+14678219025</td>
                                    <td>
                                        Mid Shift
                                    </td>
                                    <td>
                                        <span class="badge badge-success d-inline-flex align-items-center badge-xs">
                                            <i class="ti ti-point-filled me-1"></i>Active
                                        </span>
                                    </td>
                                    <td>
                                        <div class="edit-delete-action d-flex align-items-center">
                                            <a class="me-2 d-flex align-items-center border rounded p-2" href="{{url('employee-details')}}">
                                                <i data-feather="eye" class="feather-eye"></i>
                                            </a>
                                            <a class="me-2 p-2 d-flex align-items-center border rounded" href="{{url('edit-employee')}}">
                                                <i data-feather="edit" class="feather-edit"></i>
                                            </a>
                                            <a data-bs-toggle="modal" data-bs-target="#delete-modal" class="p-2 d-flex align-items-center border rounded" href="javascript:void(0);">
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
                                        <a href="{{url('employee-details')}}">EMP006</a>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <a href="{{url('employee-details')}}" class="avatar avatar-md"><img src="{{URL::asset('build/img/users/user-10.jpg')}}" class="img-fluid" alt="img"></a>
                                            <div class="ms-2">
                                                <p class="text-dark mb-0"><a href="{{url('employee-details')}}">Marsha Betts</a></p>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        Developer
                                    </td>
                                    <td>
                                        marshabetts@example.com
                                    </td>
                                    <td>+10913278319</td>
                                    <td>
                                        Mid Shift
                                    </td>
                                    <td>
                                        <span class="badge badge-success d-inline-flex align-items-center badge-xs">
                                            <i class="ti ti-point-filled me-1"></i>Active
                                        </span>
                                    </td>
                                    <td>
                                        <div class="edit-delete-action d-flex align-items-center">
                                            <a class="me-2 d-flex align-items-center border rounded p-2" href="{{url('employee-details')}}">
                                                <i data-feather="eye" class="feather-eye"></i>
                                            </a>
                                            <a class="me-2 p-2 d-flex align-items-center border rounded" href="{{url('edit-employee')}}">
                                                <i data-feather="edit" class="feather-edit"></i>
                                            </a>
                                            <a data-bs-toggle="modal" data-bs-target="#delete-modal" class="p-2 d-flex align-items-center border rounded" href="javascript:void(0);">
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
                                        <a href="{{url('employee-details')}}">EMP007</a>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <a href="{{url('employee-details')}}" class="avatar avatar-md"><img src="{{URL::asset('build/img/users/user-28.jpg')}}" class="img-fluid" alt="img"></a>
                                            <div class="ms-2">
                                                <p class="text-dark mb-0"><a href="{{url('employee-details')}}">Daniel Jude</a></p>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        Administrator
                                    </td>
                                    <td>
                                        daieljude@example.com
                                    </td>
                                    <td>+19125852947</td>
                                    <td>
                                        Regular
                                    </td>
                                    <td>
                                        <span class="badge badge-success d-inline-flex align-items-center badge-xs">
                                            <i class="ti ti-point-filled me-1"></i>Active
                                        </span>
                                    </td>
                                    <td>
                                        <div class="edit-delete-action d-flex align-items-center">
                                            <a class="me-2 d-flex align-items-center border rounded p-2" href="{{url('employee-details')}}">
                                                <i data-feather="eye" class="feather-eye"></i>
                                            </a>
                                            <a class="me-2 p-2 d-flex align-items-center border rounded" href="{{url('edit-employee')}}">
                                                <i data-feather="edit" class="feather-edit"></i>
                                            </a>
                                            <a data-bs-toggle="modal" data-bs-target="#delete-modal" class="p-2 d-flex align-items-center border rounded" href="javascript:void(0);">
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
                                        <a href="{{url('employee-details')}}">EMP008</a>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <a href="{{url('employee-details')}}" class="avatar avatar-md"><img src="{{URL::asset('build/img/users/user-17.jpg')}}" class="img-fluid" alt="img"></a>
                                            <div class="ms-2">
                                                <p class="text-dark mb-0"><a href="{{url('employee-details')}}">Emma Bates</a></p>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        HR Assistant
                                    </td>
                                    <td>
                                        emmabates@example.com
                                    </td>
                                    <td>+13671835209</td>
                                    <td>
                                        Regular
                                    </td>
                                    <td>
                                        <span class="badge badge-success d-inline-flex align-items-center badge-xs">
                                            <i class="ti ti-point-filled me-1"></i>Active
                                        </span>
                                    </td>
                                    <td>
                                        <div class="edit-delete-action d-flex align-items-center">
                                            <a class="me-2 d-flex align-items-center border rounded p-2" href="{{url('employee-details')}}">
                                                <i data-feather="eye" class="feather-eye"></i>
                                            </a>
                                            <a class="me-2 p-2 d-flex align-items-center border rounded" href="{{url('edit-employee')}}">
                                                <i data-feather="edit" class="feather-edit"></i>
                                            </a>
                                            <a data-bs-toggle="modal" data-bs-target="#delete-modal" class="p-2 d-flex align-items-center border rounded" href="javascript:void(0);">
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
                                        <a href="{{url('employee-details')}}">EMP009</a>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <a href="{{url('employee-details')}}" class="avatar avatar-md"><img src="{{URL::asset('build/img/users/user-20.jpg')}}" class="img-fluid" alt="img"></a>
                                            <div class="ms-2">
                                                <p class="text-dark mb-0"><a href="{{url('employee-details')}}">Richard Fralick</a></p>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        Designer
                                    </td>
                                    <td>
                                        richard@example.com
                                    </td>
                                    <td>+19756194733</td>
                                    <td>
                                        Regular
                                    </td>
                                    <td>
                                        <span class="badge badge-success d-inline-flex align-items-center badge-xs">
                                            <i class="ti ti-point-filled me-1"></i>Active
                                        </span>
                                    </td>
                                    <td>
                                        <div class="edit-delete-action d-flex align-items-center">
                                            <a class="me-2 d-flex align-items-center border rounded p-2" href="{{url('employee-details')}}">
                                                <i data-feather="eye" class="feather-eye"></i>
                                            </a>
                                            <a class="me-2 p-2 d-flex align-items-center border rounded" href="{{url('edit-employee')}}">
                                                <i data-feather="edit" class="feather-edit"></i>
                                            </a>
                                            <a data-bs-toggle="modal" data-bs-target="#delete-modal" class="p-2 d-flex align-items-center border rounded" href="javascript:void(0);">
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
                                        <a href="{{url('employee-details')}}">EMP010</a>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <a href="{{url('employee-details')}}" class="avatar avatar-md"><img src="{{URL::asset('build/img/users/user-29.jpg')}}" class="img-fluid" alt="img"></a>
                                            <div class="ms-2">
                                                <p class="text-dark mb-0"><a href="{{url('employee-details')}}">Michelle Robison</a></p>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        HR Manager
                                    </td>
                                    <td>
                                        robinson@example.com
                                    </td>
                                    <td>+19167850925</td>
                                    <td>
                                        Regular
                                    </td>
                                    <td>
                                        <span class="badge badge-danger d-inline-flex align-items-center badge-xs">
                                            <i class="ti ti-point-filled me-1"></i>Inactive
                                        </span>
                                    </td>
                                    <td>
                                        <div class="edit-delete-action d-flex align-items-center">
                                            <a class="me-2 d-flex align-items-center border rounded p-2" href="{{url('employee-details')}}">
                                                <i data-feather="eye" class="feather-eye"></i>
                                            </a>
                                            <a class="me-2 p-2 d-flex align-items-center border rounded" href="{{url('edit-employee')}}">
                                                <i data-feather="edit" class="feather-edit"></i>
                                            </a>
                                            <a data-bs-toggle="modal" data-bs-target="#delete-modal" class="p-2 d-flex align-items-center border rounded" href="javascript:void(0);">
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
