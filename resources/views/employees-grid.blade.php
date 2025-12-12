<?php $page = 'employees-grid'; ?>
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
                        <a href="{{url('employees-list')}}" class="btn-list me-2"><i data-feather="list" class="feather-user"></i></a>
                        <a href="{{url('employees-grid')}}" class="btn-grid active bg-primary me-2"><i data-feather="grid" class="feather-user text-white"></i></a>
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
        <!-- /product list -->
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between flex-wrap row-gap-3">
                    <div class="search-set mb-0">
                        <div class="search-input">
                            <span class="btn-searchset"><i class="ti ti-search fs-14 feather-search"></i></span>
                            <input type="search" class="form-control" placeholder="Search">
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
            </div>
        </div>
        <!-- /product list -->
        
        <div class="employee-grid-widget">
            <div class="row">
                <div class="col-xxl-3 col-xl-4 col-lg-6 col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-start justify-content-between mb-2">
                                <div class="form-check form-check-md">
                                    <input class="form-check-input" type="checkbox">
                                </div>
                                <div>
                                    <a href="{{url('employee-details')}}" class="avatar avatar-xl avatar-rounded border p-1 rounded-circle">
                                        <img src="{{URL::asset('build/img/users/user-32.jpg')}}" class="img-fluid h-auto w-auto" alt="img">
                                    </a>
                                </div>
                                <div class="dropdown">
                                    <a href="#" class="action-icon border-0" data-bs-toggle="dropdown" aria-expanded="false"><i data-feather="more-vertical" class="feather-user"></i></a>
                                    <ul class="dropdown-menu dropdown-menu-end ">
                                        <li>
                                            <a href="{{url('edit-employee')}}" class="dropdown-item"><i data-feather="edit" class="me-2"></i>Edit</a>
                                        </li>
                                        <li>
                                            <a href="javascript:void(0);" class="dropdown-item confirm-text mb-0"><i data-feather="trash-2" class="me-2"></i>Delete</a>
                                        </li>								
                                    </ul>
                                </div>
                            </div>
                            <div class="text-center">
                                <p class="text-primary mb-2">EMP ID : POS001</p>
                            </div>
                            <div class="text-center mb-3">
                                <h6 class="mb-1"><a href="{{url('employee-details')}}">Anthony Lewis</a></h6>
                                <span class="badge bg-secondary-transparent text-gray-9 fs-10 fw-medium">System Admin</span>
                            </div>
                            <div class="d-flex align-items-center justify-content-between bg-light rounded p-3">
                                <div class="text-start">
                                    <h6 class="mb-1">Joined</h6>
                                    <p>30 May 2023</p>
                                </div>
                                <div class="text-start">
                                    <h6 class="mb-1">Department</h6>
                                    <p>HR</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xxl-3 col-xl-4 col-lg-6 col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-start justify-content-between mb-2">
                                <div class="form-check form-check-md">
                                    <input class="form-check-input" type="checkbox">
                                </div>
                                <div>
                                    <a href="{{url('employee-details')}}" class="avatar avatar-xl avatar-rounded border p-1 rounded-circle">
                                        <img src="{{URL::asset('build/img/users/user-02.jpg')}}" class="img-fluid h-auto w-auto" alt="img">
                                    </a>
                                </div>
                                <div class="dropdown">
                                    <a href="#" class="action-icon border-0" data-bs-toggle="dropdown" aria-expanded="false"><i data-feather="more-vertical" class="feather-user"></i></a>
                                    <ul class="dropdown-menu dropdown-menu-end ">
                                        <li>
                                            <a href="{{url('edit-employee')}}" class="dropdown-item"><i data-feather="edit" class="me-2"></i>Edit</a>
                                        </li>
                                        <li>
                                            <a href="javascript:void(0);" class="dropdown-item confirm-text mb-0"><i data-feather="trash-2" class="me-2"></i>Delete</a>
                                        </li>								
                                    </ul>
                                </div>
                            </div>
                            <div class="text-center">
                                <p class="text-primary mb-2">EMP ID : POS002</p>
                            </div>
                            <div class="text-center mb-3">
                                <h6 class="mb-1"><a href="{{url('employee-details')}}">Brian Villalobos</a></h6>
                                <span class="badge bg-secondary-transparent text-gray-9 fs-10 fw-medium">Software Developer</span>
                            </div>
                            <div class="d-flex align-items-center justify-content-between bg-light rounded p-3">
                                <div class="text-start">
                                    <h6 class="mb-1">Joined</h6>
                                    <p>30 May 2023</p>
                                </div>
                                <div class="text-start">
                                    <h6 class="mb-1">Department</h6>
                                    <p>UI/IX</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xxl-3 col-xl-4 col-lg-6 col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-start justify-content-between mb-2">
                                <div class="form-check form-check-md">
                                    <input class="form-check-input" type="checkbox">
                                </div>
                                <div>
                                    <a href="{{url('employee-details')}}" class="avatar avatar-xl avatar-rounded border p-1 rounded-circle">
                                        <img src="{{URL::asset('build/img/users/user-03.jpg')}}" class="img-fluid h-auto w-auto" alt="img">
                                    </a>
                                </div>
                                <div class="dropdown">
                                    <a href="#" class="action-icon border-0" data-bs-toggle="dropdown" aria-expanded="false"><i data-feather="more-vertical" class="feather-user"></i></a>
                                    <ul class="dropdown-menu dropdown-menu-end ">
                                        <li>
                                            <a href="{{url('edit-employee')}}" class="dropdown-item"><i data-feather="edit" class="me-2"></i>Edit</a>
                                        </li>
                                        <li>
                                            <a href="javascript:void(0);" class="dropdown-item confirm-text mb-0"><i data-feather="trash-2" class="me-2"></i>Delete</a>
                                        </li>								
                                    </ul>
                                </div>
                            </div>
                            <div class="text-center">
                                <p class="text-primary mb-2">EMP ID : POS003</p>
                            </div>
                            <div class="text-center mb-3">
                                <h6 class="mb-1"><a href="{{url('employee-details')}}">Harvey Smith</a></h6>
                                <span class="badge bg-secondary-transparent text-gray-9 fs-10 fw-medium">System Admin</span>
                            </div>
                            <div class="d-flex align-items-center justify-content-between bg-light rounded p-3">
                                <div class="text-start">
                                    <h6 class="mb-1">Joined</h6>
                                    <p>30 May 2023</p>
                                </div>
                                <div class="text-start">
                                    <h6 class="mb-1">Department</h6>
                                    <p>Admin</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xxl-3 col-xl-4 col-lg-6 col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-start justify-content-between mb-2">
                                <div class="form-check form-check-md">
                                    <input class="form-check-input" type="checkbox">
                                </div>
                                <div>
                                    <a href="{{url('employee-details')}}" class="avatar avatar-xl avatar-rounded border p-1 rounded-circle">
                                        <img src="{{URL::asset('build/img/users/user-06.jpg')}}" class="img-fluid h-auto w-auto" alt="img">
                                    </a>
                                </div>
                                <div class="dropdown">
                                    <a href="#" class="action-icon border-0" data-bs-toggle="dropdown" aria-expanded="false"><i data-feather="more-vertical" class="feather-user"></i></a>
                                    <ul class="dropdown-menu dropdown-menu-end ">
                                        <li>
                                            <a href="{{url('edit-employee')}}" class="dropdown-item"><i data-feather="edit" class="me-2"></i>Edit</a>
                                        </li>
                                        <li>
                                            <a href="javascript:void(0);" class="dropdown-item confirm-text mb-0"><i data-feather="trash-2" class="me-2"></i>Delete</a>
                                        </li>								
                                    </ul>
                                </div>
                            </div>
                            <div class="text-center">
                                <p class="text-primary mb-2">EMP ID : POS004</p>
                            </div>
                            <div class="text-center mb-3">
                                <h6 class="mb-1"><a href="{{url('employee-details')}}">Stephan Peralt</a></h6>
                                <span class="badge bg-secondary-transparent text-gray-9 fs-10 fw-medium">System Admin</span>
                            </div>
                            <div class="d-flex align-items-center justify-content-between bg-light rounded p-3">
                                <div class="text-start">
                                    <h6 class="mb-1">Joined</h6>
                                    <p>30 May 2023</p>
                                </div>
                                <div class="text-start">
                                    <h6 class="mb-1">Department</h6>
                                    <p>Admin</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xxl-3 col-xl-4 col-lg-6 col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-start justify-content-between mb-2">
                                <div class="form-check form-check-md">
                                    <input class="form-check-input" type="checkbox">
                                </div>
                                <div>
                                    <a href="{{url('employee-details')}}" class="avatar avatar-xl avatar-rounded border p-1 rounded-circle">
                                        <img src="{{URL::asset('build/img/users/user-08.jpg')}}" class="img-fluid h-auto w-auto" alt="img">
                                    </a>
                                </div>
                                <div class="dropdown">
                                    <a href="#" class="action-icon border-0" data-bs-toggle="dropdown" aria-expanded="false"><i data-feather="more-vertical" class="feather-user"></i></a>
                                    <ul class="dropdown-menu dropdown-menu-end ">
                                        <li>
                                            <a href="{{url('edit-employee')}}" class="dropdown-item"><i data-feather="edit" class="me-2"></i>Edit</a>
                                        </li>
                                        <li>
                                            <a href="javascript:void(0);" class="dropdown-item confirm-text mb-0"><i data-feather="trash-2" class="me-2"></i>Delete</a>
                                        </li>								
                                    </ul>
                                </div>
                            </div>
                            <div class="text-center">
                                <p class="text-primary mb-2">EMP ID : POS005</p>
                            </div>
                            <div class="text-center mb-3">
                                <h6 class="mb-1"><a href="{{url('employee-details')}}">Doglas Martini</a></h6>
                                <span class="badge bg-secondary-transparent text-gray-9 fs-10 fw-medium">System Admin</span>
                            </div>
                            <div class="d-flex align-items-center justify-content-between bg-light rounded p-3">
                                <div class="text-start">
                                    <h6 class="mb-1">Joined</h6>
                                    <p>30 May 2023</p>
                                </div>
                                <div class="text-start">
                                    <h6 class="mb-1">Department</h6>
                                    <p>IT</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xxl-3 col-xl-4 col-lg-6 col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-start justify-content-between mb-2">
                                <div class="form-check form-check-md">
                                    <input class="form-check-input" type="checkbox">
                                </div>
                                <div>
                                    <a href="{{url('employee-details')}}" class="avatar avatar-xl avatar-rounded border p-1 rounded-circle">
                                        <img src="{{URL::asset('build/img/users/user-19.jpg')}}" class="img-fluid h-auto w-auto" alt="img">
                                    </a>
                                </div>
                                <div class="dropdown">
                                    <a href="#" class="action-icon border-0" data-bs-toggle="dropdown" aria-expanded="false"><i data-feather="more-vertical" class="feather-user"></i></a>
                                    <ul class="dropdown-menu dropdown-menu-end ">
                                        <li>
                                            <a href="{{url('edit-employee')}}" class="dropdown-item"><i data-feather="edit" class="me-2"></i>Edit</a>
                                        </li>
                                        <li>
                                            <a href="javascript:void(0);" class="dropdown-item confirm-text mb-0"><i data-feather="trash-2" class="me-2"></i>Delete</a>
                                        </li>								
                                    </ul>
                                </div>
                            </div>
                            <div class="text-center">
                                <p class="text-primary mb-2">EMP ID : POS006</p>
                            </div>
                            <div class="text-center mb-3">
                                <h6 class="mb-1"><a href="{{url('employee-details')}}">Linda Ray</a></h6>
                                <span class="badge bg-secondary-transparent text-gray-9 fs-10 fw-medium">System Admin</span>
                            </div>
                            <div class="d-flex align-items-center justify-content-between bg-light rounded p-3">
                                <div class="text-start">
                                    <h6 class="mb-1">Joined</h6>
                                    <p>30 May 2023</p>
                                </div>
                                <div class="text-start">
                                    <h6 class="mb-1">Department</h6>
                                    <p>Support</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xxl-3 col-xl-4 col-lg-6 col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-start justify-content-between mb-2">
                                <div class="form-check form-check-md">
                                    <input class="form-check-input" type="checkbox">
                                </div>
                                <div>
                                    <a href="{{url('employee-details')}}" class="avatar avatar-xl avatar-rounded border p-1 rounded-circle">
                                        <img src="{{URL::asset('build/img/users/user-28.jpg')}}" class="img-fluid h-auto w-auto" alt="img">
                                    </a>
                                </div>
                                <div class="dropdown">
                                    <a href="#" class="action-icon border-0" data-bs-toggle="dropdown" aria-expanded="false"><i data-feather="more-vertical" class="feather-user"></i></a>
                                    <ul class="dropdown-menu dropdown-menu-end ">
                                        <li>
                                            <a href="{{url('edit-employee')}}" class="dropdown-item"><i data-feather="edit" class="me-2"></i>Edit</a>
                                        </li>
                                        <li>
                                            <a href="javascript:void(0);" class="dropdown-item confirm-text mb-0"><i data-feather="trash-2" class="me-2"></i>Delete</a>
                                        </li>								
                                    </ul>
                                </div>
                            </div>
                            <div class="text-center">
                                <p class="text-primary mb-2">EMP ID : POS007</p>
                            </div>
                            <div class="text-center mb-3">
                                <h6 class="mb-1"><a href="{{url('employee-details')}}">Elliot Murray</a></h6>
                                <span class="badge bg-secondary-transparent text-gray-9 fs-10 fw-medium">System Admin</span>
                            </div>
                            <div class="d-flex align-items-center justify-content-between bg-light rounded p-3">
                                <div class="text-start">
                                    <h6 class="mb-1">Joined</h6>
                                    <p>30 May 2023</p>
                                </div>
                                <div class="text-start">
                                    <h6 class="mb-1">Department</h6>
                                    <p>UI/UX</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xxl-3 col-xl-4 col-lg-6 col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-start justify-content-between mb-2">
                                <div class="form-check form-check-md">
                                    <input class="form-check-input" type="checkbox">
                                </div>
                                <div>
                                    <a href="{{url('employee-details')}}" class="avatar avatar-xl avatar-rounded border p-1 rounded-circle">
                                        <img src="{{URL::asset('build/img/users/user-17.jpg')}}" class="img-fluid h-auto w-auto" alt="img">
                                    </a>
                                </div>
                                <div class="dropdown">
                                    <a href="#" class="action-icon border-0" data-bs-toggle="dropdown" aria-expanded="false"><i data-feather="more-vertical" class="feather-user"></i></a>
                                    <ul class="dropdown-menu dropdown-menu-end ">
                                        <li>
                                            <a href="{{url('edit-employee')}}" class="dropdown-item"><i data-feather="edit" class="me-2"></i>Edit</a>
                                        </li>
                                        <li>
                                            <a href="javascript:void(0);" class="dropdown-item confirm-text mb-0"><i data-feather="trash-2" class="me-2"></i>Delete</a>
                                        </li>								
                                    </ul>
                                </div>
                            </div>
                            <div class="text-center">
                                <p class="text-primary mb-2">EMP ID : POS008</p>
                            </div>
                            <div class="text-center mb-3">
                                <h6 class="mb-1"><a href="{{url('employee-details')}}">Rebecca Smtih</a></h6>
                                <span class="badge bg-secondary-transparent text-gray-9 fs-10 fw-medium">System Admin</span>
                            </div>
                            <div class="d-flex align-items-center justify-content-between bg-light rounded p-3">
                                <div class="text-start">
                                    <h6 class="mb-1">Joined</h6>
                                    <p>30 May 2023</p>
                                </div>
                                <div class="text-start">
                                    <h6 class="mb-1">Department</h6>
                                    <p>HR</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xxl-3 col-xl-4 col-lg-6 col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-start justify-content-between mb-2">
                                <div class="form-check form-check-md">
                                    <input class="form-check-input" type="checkbox">
                                </div>
                                <div>
                                    <a href="{{url('employee-details')}}" class="avatar avatar-xl avatar-rounded border p-1 rounded-circle">
                                        <img src="{{URL::asset('build/img/users/user-30.jpg')}}" class="img-fluid h-auto w-auto" alt="img">
                                    </a>
                                </div>
                                <div class="dropdown">
                                    <a href="#" class="action-icon border-0" data-bs-toggle="dropdown" aria-expanded="false"><i data-feather="more-vertical" class="feather-user"></i></a>
                                    <ul class="dropdown-menu dropdown-menu-end ">
                                        <li>
                                            <a href="{{url('edit-employee')}}" class="dropdown-item"><i data-feather="edit" class="me-2"></i>Edit</a>
                                        </li>
                                        <li>
                                            <a href="javascript:void(0);" class="dropdown-item confirm-text mb-0"><i data-feather="trash-2" class="me-2"></i>Delete</a>
                                        </li>								
                                    </ul>
                                </div>
                            </div>
                            <div class="text-center">
                                <p class="text-primary mb-2">EMP ID : POS009</p>
                            </div>
                            <div class="text-center mb-3">
                                <h6 class="mb-1"><a href="{{url('employee-details')}}">Connie Waters</a></h6>
                                <span class="badge bg-secondary-transparent text-gray-9 fs-10 fw-medium">System Admin</span>
                            </div>
                            <div class="d-flex align-items-center justify-content-between bg-light rounded p-3">
                                <div class="text-start">
                                    <h6 class="mb-1">Joined</h6>
                                    <p>30 May 2023</p>
                                </div>
                                <div class="text-start">
                                    <h6 class="mb-1">Department</h6>
                                    <p>Admin</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xxl-3 col-xl-4 col-lg-6 col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-start justify-content-between mb-2">
                                <div class="form-check form-check-md">
                                    <input class="form-check-input" type="checkbox">
                                </div>
                                <div>
                                    <a href="{{url('employee-details')}}" class="avatar avatar-xl avatar-rounded border p-1 rounded-circle">
                                        <img src="{{URL::asset('build/img/users/user-26.jpg')}}" class="img-fluid h-auto w-auto" alt="img">
                                    </a>
                                </div>
                                <div class="dropdown">
                                    <a href="#" class="action-icon border-0" data-bs-toggle="dropdown" aria-expanded="false"><i data-feather="more-vertical" class="feather-user"></i></a>
                                    <ul class="dropdown-menu dropdown-menu-end ">
                                        <li>
                                            <a href="{{url('edit-employee')}}" class="dropdown-item"><i data-feather="edit" class="me-2"></i>Edit</a>
                                        </li>
                                        <li>
                                            <a href="javascript:void(0);" class="dropdown-item confirm-text mb-0"><i data-feather="trash-2" class="me-2"></i>Delete</a>
                                        </li>								
                                    </ul>
                                </div>
                            </div>
                            <div class="text-center">
                                <p class="text-primary mb-2">EMP ID : POS010</p>
                            </div>
                            <div class="text-center mb-3">
                                <h6 class="mb-1"><a href="{{url('employee-details')}}">Lori Broaddus</a></h6>
                                <span class="badge bg-secondary-transparent text-gray-9 fs-10 fw-medium">System Admin</span>
                            </div>
                            <div class="d-flex align-items-center justify-content-between bg-light rounded p-3">
                                <div class="text-start">
                                    <h6 class="mb-1">Joined</h6>
                                    <p>30 May 2023</p>
                                </div>
                                <div class="text-start">
                                    <h6 class="mb-1">Department</h6>
                                    <p>React JS</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xxl-3 col-xl-4 col-lg-6 col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-start justify-content-between mb-2">
                                <div class="form-check form-check-md">
                                    <input class="form-check-input" type="checkbox">
                                </div>
                                <div>
                                    <a href="{{url('employee-details')}}" class="avatar avatar-xl avatar-rounded border p-1 rounded-circle">
                                        <img src="{{URL::asset('build/img/users/user-11.jpg')}}" class="img-fluid h-auto w-auto" alt="img">
                                    </a>
                                </div>
                                <div class="dropdown">
                                    <a href="#" class="action-icon border-0" data-bs-toggle="dropdown" aria-expanded="false"><i data-feather="more-vertical" class="feather-user"></i></a>
                                    <ul class="dropdown-menu dropdown-menu-end ">
                                        <li>
                                            <a href="{{url('edit-employee')}}" class="dropdown-item"><i data-feather="edit" class="me-2"></i>Edit</a>
                                        </li>
                                        <li>
                                            <a href="javascript:void(0);" class="dropdown-item confirm-text mb-0"><i data-feather="trash-2" class="me-2"></i>Delete</a>
                                        </li>								
                                    </ul>
                                </div>
                            </div>
                            <div class="text-center">
                                <p class="text-primary mb-2">EMP ID : POS011</p>
                            </div>
                            <div class="text-center mb-3">
                                <h6 class="mb-1"><a href="{{url('employee-details')}}">Trent Frazier</a></h6>
                                <span class="badge bg-secondary-transparent text-gray-9 fs-10 fw-medium">System Admin</span>
                            </div>
                            <div class="d-flex align-items-center justify-content-between bg-light rounded p-3">
                                <div class="text-start">
                                    <h6 class="mb-1">Joined</h6>
                                    <p>30 May 2023</p>
                                </div>
                                <div class="text-start">
                                    <h6 class="mb-1">Department</h6>
                                    <p>Support</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xxl-3 col-xl-4 col-lg-6 col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-start justify-content-between mb-2">
                                <div class="form-check form-check-md">
                                    <input class="form-check-input" type="checkbox">
                                </div>
                                <div>
                                    <a href="{{url('employee-details')}}" class="avatar avatar-xl avatar-rounded border p-1 rounded-circle">
                                        <img src="{{URL::asset('build/img/users/user-04.jpg')}}" class="img-fluid h-auto w-auto" alt="img">
                                    </a>
                                </div>
                                <div class="dropdown">
                                    <a href="#" class="action-icon border-0" data-bs-toggle="dropdown" aria-expanded="false"><i data-feather="more-vertical" class="feather-user"></i></a>
                                    <ul class="dropdown-menu dropdown-menu-end ">
                                        <li>
                                            <a href="{{url('edit-employee')}}" class="dropdown-item"><i data-feather="edit" class="me-2"></i>Edit</a>
                                        </li>
                                        <li>
                                            <a href="javascript:void(0);" class="dropdown-item confirm-text mb-0"><i data-feather="trash-2" class="me-2"></i>Delete</a>
                                        </li>								
                                    </ul>
                                </div>
                            </div>
                            <div class="text-center">
                                <p class="text-primary mb-2">EMP ID : POS012</p>
                            </div>
                            <div class="text-center mb-3">
                                <h6 class="mb-1"><a href="{{url('employee-details')}}">Norene Valle</a></h6>
                                <span class="badge bg-secondary-transparent text-gray-9 fs-10 fw-medium">System Admin</span>
                            </div>
                            <div class="d-flex align-items-center justify-content-between bg-light rounded p-3">
                                <div class="text-start">
                                    <h6 class="mb-1">Joined</h6>
                                    <p>30 May 2023</p>
                                </div>
                                <div class="text-start">
                                    <h6 class="mb-1">Department</h6>
                                    <p>Admin</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="footer d-sm-flex align-items-center justify-content-between border-top bg-white p-3">
        <p class="mb-0">2014 - 2025 &copy; DreamsPOS. All Right Reserved</p>
        <p>Designed &amp; Developed by <a href="javascript:void(0);" class="text-primary">Dreams</a></p>
    </div>
</div>

@endsection
