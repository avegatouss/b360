<?php $page = 'department-grid'; ?>
@extends('layout.mainlayout')
@section('content')
   
<div class="page-wrapper">
    <div class="content">
        <div class="page-header">
            <div class="add-item d-flex">
                <div class="page-title">
                    <h4>Departments</h4>
                    <h6>Manage your departments</h6>
                </div>
            </div>
            <ul class="table-top-head">
                <li>
                    <div class="d-flex me-2 pe-2 border-end">
                        <a href="{{url('department-list')}}" class="btn-list me-2"><i data-feather="list" class="feather-user"></i></a>
                        <a href="{{url('department-grid')}}" class="btn-grid active bg-primary me-2"><i data-feather="grid" class="feather-user text-white"></i></a>
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
                <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#add-department"><i class="ti ti-circle-plus me-1"></i>Add Department</a>
            </div>
        </div>

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
                                Select Status
                            </a>
                            <ul class="dropdown-menu  dropdown-menu-end p-3">
                                <li>
                                    <a href="javascript:void(0);" class="dropdown-item rounded-1">Active</a>
                                </li>
                                <li>
                                    <a href="javascript:void(0);" class="dropdown-item rounded-1">Inactive</a>
                                </li>
                                <li>
                                    <a href="javascript:void(0);" class="dropdown-item rounded-1">New Joiners</a>
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
            </div>
        </div>

        <div class="employee-grid-widget">
            <div class="row">
                <div class="col-xxl-3 col-xl-4 col-lg-6 col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between mb-4">
                                <h5 class="d-inline-flex align-items-center"><i class="ti ti-point-filled text-success fs-20"></i>Inventory</h5>
                                <div class="dropdown">
                                    <a href="#" class="action-icon border-0" data-bs-toggle="dropdown" aria-expanded="false"><i data-feather="more-vertical" class="feather-user"></i></a>
                                    <ul class="dropdown-menu dropdown-menu-end ">
                                        <li>
                                            <a href="javascript:void(0);" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#edit-department"><i data-feather="edit" class="info-img me-2"></i>Edit</a>
                                        </li>
                                        <li>
                                            <a href="javascript:void(0);" class="dropdown-item mb-0" data-bs-toggle="modal" data-bs-target="#delete-modal"><i data-feather="trash-2" class="info-img me-2"></i>Delete</a>
                                        </li>								
                                    </ul>
                                </div>
                            </div>
                            <div class="bg-light rounded p-3 text-center mb-4">
                                <div class="avatar avatar-lg mb-2">
                                    <img src="{{URL::asset('build/img/users/user-01.jpg')}}" alt="Img">
                                </div>
                                <h4>Mitchum Daniel</h4>
                            </div>
                            <div class="d-flex align-items-center justify-content-between">
                                <p class="mb-0">Total Members: 08</p>
                                <div class="avatar-list-stacked avatar-group-sm">
                                    <span class="avatar avatar-rounded">
                                        <img class="border border-white" src="{{URL::asset('build/img/profiles/avatar-15.jpg')}}" alt="img">
                                    </span>
                                    <span class="avatar avatar-rounded">
                                        <img class="border border-white" src="{{URL::asset('build/img/profiles/avatar-16.jpg')}}" alt="img">
                                    </span>
                                    <span class="avatar avatar-rounded">
                                        <img class="border border-white" src="{{URL::asset('build/img/profiles/avatar-18.jpg')}}" alt="img">
                                    </span>
                                    <a class="avatar avatar-rounded text-fixed-white fs-10 fw-medium position-relative" href="javascript:void(0);">
                                        <img src="{{URL::asset('build/img/profiles/avatar-17.jpg')}}" alt="img">
                                        <span class="position-absolute top-50 start-50 translate-middle text-center">+2</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xxl-3 col-xl-4 col-lg-6 col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between mb-4">
                                <h5 class="d-inline-flex align-items-center"><i class="ti ti-point-filled text-success fs-20"></i>Human Resources</h5>
                                <div class="dropdown">
                                    <a href="#" class="action-icon border-0" data-bs-toggle="dropdown" aria-expanded="false"><i data-feather="more-vertical" class="feather-user"></i></a>
                                    <ul class="dropdown-menu dropdown-menu-end ">
                                        <li>
                                            <a href="javascript:void(0);" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#edit-department"><i data-feather="edit" class="info-img me-2"></i>Edit</a>
                                        </li>
                                        <li>
                                            <a href="javascript:void(0);" class="dropdown-item mb-0" data-bs-toggle="modal" data-bs-target="#delete-modal"><i data-feather="trash-2" class="info-img me-2"></i>Delete</a>
                                        </li>								
                                    </ul>
                                </div>
                            </div>
                            <div class="bg-light rounded p-3 text-center mb-4">
                                <div class="avatar avatar-lg mb-2">
                                    <img src="{{URL::asset('build/img/users/user-02.jpg')}}" alt="Img">
                                </div>
                                <h4>Susan Lopez</h4>
                            </div>
                            <div class="d-flex align-items-center justify-content-between">
                                <p class="mb-0">Total Members: 10</p>
                                <div class="avatar-list-stacked avatar-group-sm">
                                    <span class="avatar avatar-rounded">
                                        <img class="border border-white" src="{{URL::asset('build/img/profiles/avatar-15.jpg')}}" alt="img">
                                    </span>
                                    <span class="avatar avatar-rounded">
                                        <img class="border border-white" src="{{URL::asset('build/img/profiles/avatar-16.jpg')}}" alt="img">
                                    </span>
                                    <span class="avatar avatar-rounded">
                                        <img class="border border-white" src="{{URL::asset('build/img/profiles/avatar-18.jpg')}}" alt="img">
                                    </span>
                                    <a class="avatar avatar-rounded text-fixed-white fs-10 fw-medium position-relative" href="javascript:void(0);">
                                        <img src="{{URL::asset('build/img/profiles/avatar-17.jpg')}}" alt="img">
                                        <span class="position-absolute top-50 start-50 translate-middle text-center">+2</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xxl-3 col-xl-4 col-lg-6 col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between mb-4">
                                <h5 class="d-inline-flex align-items-center"><i class="ti ti-point-filled text-success fs-20"></i>Admin</h5>
                                <div class="dropdown">
                                    <a href="#" class="action-icon border-0" data-bs-toggle="dropdown" aria-expanded="false"><i data-feather="more-vertical" class="feather-user"></i></a>
                                    <ul class="dropdown-menu dropdown-menu-end ">
                                        <li>
                                            <a href="javascript:void(0);" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#edit-department"><i data-feather="edit" class="info-img me-2"></i>Edit</a>
                                        </li>
                                        <li>
                                            <a href="javascript:void(0);" class="dropdown-item mb-0" data-bs-toggle="modal" data-bs-target="#delete-modal"><i data-feather="trash-2" class="info-img me-2"></i>Delete</a>
                                        </li>								
                                    </ul>
                                </div>
                            </div>
                            <div class="bg-light rounded p-3 text-center mb-4">
                                <div class="avatar avatar-lg mb-2">
                                    <img src="{{URL::asset('build/img/users/user-03.jpg')}}" alt="Img">
                                </div>
                                <h4>Robert Grossman</h4>
                            </div>
                            <div class="d-flex align-items-center justify-content-between">
                                <p class="mb-0">Total Members: 05</p>
                                <div class="avatar-list-stacked avatar-group-sm">
                                    <span class="avatar avatar-rounded">
                                        <img class="border border-white" src="{{URL::asset('build/img/profiles/avatar-15.jpg')}}" alt="img">
                                    </span>
                                    <span class="avatar avatar-rounded">
                                        <img class="border border-white" src="{{URL::asset('build/img/profiles/avatar-16.jpg')}}" alt="img">
                                    </span>
                                    <span class="avatar avatar-rounded">
                                        <img class="border border-white" src="{{URL::asset('build/img/profiles/avatar-18.jpg')}}" alt="img">
                                    </span>
                                    <a class="avatar avatar-rounded text-fixed-white fs-10 fw-medium position-relative" href="javascript:void(0);">
                                        <img src="{{URL::asset('build/img/profiles/avatar-17.jpg')}}" alt="img">
                                        <span class="position-absolute top-50 start-50 translate-middle text-center">+2</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xxl-3 col-xl-4 col-lg-6 col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between mb-4">
                                <h5 class="d-inline-flex align-items-center"><i class="ti ti-point-filled text-success fs-20"></i>Sales</h5>
                                <div class="dropdown">
                                    <a href="#" class="action-icon border-0" data-bs-toggle="dropdown" aria-expanded="false"><i data-feather="more-vertical" class="feather-user"></i></a>
                                    <ul class="dropdown-menu dropdown-menu-end ">
                                        <li>
                                            <a href="javascript:void(0);" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#edit-department"><i data-feather="edit" class="info-img me-2"></i>Edit</a>
                                        </li>
                                        <li>
                                            <a href="javascript:void(0);" class="dropdown-item mb-0" data-bs-toggle="modal" data-bs-target="#delete-modal"><i data-feather="trash-2" class="info-img me-2"></i>Delete</a>
                                        </li>								
                                    </ul>
                                </div>
                            </div>
                            <div class="bg-light rounded p-3 text-center mb-4">
                                <div class="avatar avatar-lg mb-2">
                                    <img src="{{URL::asset('build/img/users/user-06.jpg')}}" alt="Img">
                                </div>
                                <h4>Janet Hembre</h4>
                            </div>
                            <div class="d-flex align-items-center justify-content-between">
                                <p class="mb-0">Total Members: 10</p>
                                <div class="avatar-list-stacked avatar-group-sm">
                                    <span class="avatar avatar-rounded">
                                        <img class="border border-white" src="{{URL::asset('build/img/profiles/avatar-15.jpg')}}" alt="img">
                                    </span>
                                    <span class="avatar avatar-rounded">
                                        <img class="border border-white" src="{{URL::asset('build/img/profiles/avatar-16.jpg')}}" alt="img">
                                    </span>
                                    <span class="avatar avatar-rounded">
                                        <img class="border border-white" src="{{URL::asset('build/img/profiles/avatar-18.jpg')}}" alt="img">
                                    </span>
                                    <a class="avatar avatar-rounded text-fixed-white fs-10 fw-medium position-relative" href="javascript:void(0);">
                                        <img src="{{URL::asset('build/img/profiles/avatar-17.jpg')}}" alt="img">
                                        <span class="position-absolute top-50 start-50 translate-middle text-center">+2</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xxl-3 col-xl-4 col-lg-6 col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between mb-4">
                                <h5 class="d-inline-flex align-items-center"><i class="ti ti-point-filled text-success fs-20"></i>Marketing</h5>
                                <div class="dropdown">
                                    <a href="#" class="action-icon border-0" data-bs-toggle="dropdown" aria-expanded="false"><i data-feather="more-vertical" class="feather-user"></i></a>
                                    <ul class="dropdown-menu dropdown-menu-end ">
                                        <li>
                                            <a href="javascript:void(0);" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#edit-department"><i data-feather="edit" class="info-img me-2"></i>Edit</a>
                                        </li>
                                        <li>
                                            <a href="javascript:void(0);" class="dropdown-item mb-0" data-bs-toggle="modal" data-bs-target="#delete-modal"><i data-feather="trash-2" class="info-img me-2"></i>Delete</a>
                                        </li>								
                                    </ul>
                                </div>
                            </div>
                            <div class="bg-light rounded p-3 text-center mb-4">
                                <div class="avatar avatar-lg mb-2">
                                    <img src="{{URL::asset('build/img/users/user-04.jpg')}}" alt="Img">
                                </div>
                                <h4>Russell Belle</h4>
                            </div>
                            <div class="d-flex align-items-center justify-content-between">
                                <p class="mb-0">Total Members: 06</p>
                                <div class="avatar-list-stacked avatar-group-sm">
                                    <span class="avatar avatar-rounded">
                                        <img class="border border-white" src="{{URL::asset('build/img/profiles/avatar-15.jpg')}}" alt="img">
                                    </span>
                                    <span class="avatar avatar-rounded">
                                        <img class="border border-white" src="{{URL::asset('build/img/profiles/avatar-16.jpg')}}" alt="img">
                                    </span>
                                    <span class="avatar avatar-rounded">
                                        <img class="border border-white" src="{{URL::asset('build/img/profiles/avatar-18.jpg')}}" alt="img">
                                    </span>
                                    <a class="avatar avatar-rounded text-fixed-white fs-10 fw-medium position-relative" href="javascript:void(0);">
                                        <img src="{{URL::asset('build/img/profiles/avatar-17.jpg')}}" alt="img">
                                        <span class="position-absolute top-50 start-50 translate-middle text-center">+2</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xxl-3 col-xl-4 col-lg-6 col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between mb-4">
                                <h5 class="d-inline-flex align-items-center"><i class="ti ti-point-filled text-success fs-20"></i>Quality Assurance</h5>
                                <div class="dropdown">
                                    <a href="#" class="action-icon border-0" data-bs-toggle="dropdown" aria-expanded="false"><i data-feather="more-vertical" class="feather-user"></i></a>
                                    <ul class="dropdown-menu dropdown-menu-end ">
                                        <li>
                                            <a href="javascript:void(0);" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#edit-department"><i data-feather="edit" class="info-img me-2"></i>Edit</a>
                                        </li>
                                        <li>
                                            <a href="javascript:void(0);" class="dropdown-item mb-0" data-bs-toggle="modal" data-bs-target="#delete-modal"><i data-feather="trash-2" class="info-img me-2"></i>Delete</a>
                                        </li>								
                                    </ul>
                                </div>
                            </div>
                            <div class="bg-light rounded p-3 text-center mb-4">
                                <div class="avatar avatar-lg mb-2">
                                    <img src="{{URL::asset('build/img/users/user-05.jpg')}}" alt="Img">
                                </div>
                                <h4>Edward Muniz</h4>
                            </div>
                            <div class="d-flex align-items-center justify-content-between">
                                <p class="mb-0">Total Members: 06</p>
                                <div class="avatar-list-stacked avatar-group-sm">
                                    <span class="avatar avatar-rounded">
                                        <img class="border border-white" src="{{URL::asset('build/img/profiles/avatar-15.jpg')}}" alt="img">
                                    </span>
                                    <span class="avatar avatar-rounded">
                                        <img class="border border-white" src="{{URL::asset('build/img/profiles/avatar-16.jpg')}}" alt="img">
                                    </span>
                                    <span class="avatar avatar-rounded">
                                        <img class="border border-white" src="{{URL::asset('build/img/profiles/avatar-18.jpg')}}" alt="img">
                                    </span>
                                    <a class="avatar avatar-rounded text-fixed-white fs-10 fw-medium position-relative" href="javascript:void(0);">
                                        <img src="{{URL::asset('build/img/profiles/avatar-17.jpg')}}" alt="img">
                                        <span class="position-absolute top-50 start-50 translate-middle text-center">+2</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xxl-3 col-xl-4 col-lg-6 col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between mb-4">
                                <h5 class="d-inline-flex align-items-center"><i class="ti ti-point-filled text-success fs-20"></i>Finance</h5>
                                <div class="dropdown">
                                    <a href="#" class="action-icon border-0" data-bs-toggle="dropdown" aria-expanded="false"><i data-feather="more-vertical" class="feather-user"></i></a>
                                    <ul class="dropdown-menu dropdown-menu-end ">
                                        <li>
                                            <a href="javascript:void(0);" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#edit-department"><i data-feather="edit" class="info-img me-2"></i>Edit</a>
                                        </li>
                                        <li>
                                            <a href="javascript:void(0);" class="dropdown-item mb-0" data-bs-toggle="modal" data-bs-target="#delete-modal"><i data-feather="trash-2" class="info-img me-2"></i>Delete</a>
                                        </li>								
                                    </ul>
                                </div>
                            </div>
                            <div class="bg-light rounded p-3 text-center mb-4">
                                <div class="avatar avatar-lg mb-2">
                                    <img src="{{URL::asset('build/img/users/user-07.jpg')}}" alt="Img">
                                </div>
                                <h4>Susan Moore</h4>
                            </div>
                            <div class="d-flex align-items-center justify-content-between">
                                <p class="mb-0">Total Members: 08</p>
                                <div class="avatar-list-stacked avatar-group-sm">
                                    <span class="avatar avatar-rounded">
                                        <img class="border border-white" src="{{URL::asset('build/img/profiles/avatar-15.jpg')}}" alt="img">
                                    </span>
                                    <span class="avatar avatar-rounded">
                                        <img class="border border-white" src="{{URL::asset('build/img/profiles/avatar-16.jpg')}}" alt="img">
                                    </span>
                                    <span class="avatar avatar-rounded">
                                        <img class="border border-white" src="{{URL::asset('build/img/profiles/avatar-18.jpg')}}" alt="img">
                                    </span>
                                    <a class="avatar avatar-rounded text-fixed-white fs-10 fw-medium position-relative" href="javascript:void(0);">
                                        <img src="{{URL::asset('build/img/profiles/avatar-17.jpg')}}" alt="img">
                                        <span class="position-absolute top-50 start-50 translate-middle text-center">+2</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xxl-3 col-xl-4 col-lg-6 col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between mb-4">
                                <h5 class="d-inline-flex align-items-center"><i class="ti ti-point-filled text-success fs-20"></i>Maintenance</h5>
                                <div class="dropdown">
                                    <a href="#" class="action-icon border-0" data-bs-toggle="dropdown" aria-expanded="false"><i data-feather="more-vertical" class="feather-user"></i></a>
                                    <ul class="dropdown-menu dropdown-menu-end ">
                                        <li>
                                            <a href="javascript:void(0);" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#edit-department"><i data-feather="edit" class="info-img me-2"></i>Edit</a>
                                        </li>
                                        <li>
                                            <a href="javascript:void(0);" class="dropdown-item mb-0" data-bs-toggle="modal" data-bs-target="#delete-modal"><i data-feather="trash-2" class="info-img me-2"></i>Delete</a>
                                        </li>								
                                    </ul>
                                </div>
                            </div>
                            <div class="bg-light rounded p-3 text-center mb-4">
                                <div class="avatar avatar-lg mb-2">
                                    <img src="{{URL::asset('build/img/users/user-08.jpg')}}" alt="Img">
                                </div>
                                <h4>Lance Jackson</h4>
                            </div>
                            <div class="d-flex align-items-center justify-content-between">
                                <p class="mb-0">Total Members: 07</p>
                                <div class="avatar-list-stacked avatar-group-sm">
                                    <span class="avatar avatar-rounded">
                                        <img class="border border-white" src="{{URL::asset('build/img/profiles/avatar-15.jpg')}}" alt="img">
                                    </span>
                                    <span class="avatar avatar-rounded">
                                        <img class="border border-white" src="{{URL::asset('build/img/profiles/avatar-16.jpg')}}" alt="img">
                                    </span>
                                    <span class="avatar avatar-rounded">
                                        <img class="border border-white" src="{{URL::asset('build/img/profiles/avatar-18.jpg')}}" alt="img">
                                    </span>
                                    <a class="avatar avatar-rounded text-fixed-white fs-10 fw-medium position-relative" href="javascript:void(0);">
                                        <img src="{{URL::asset('build/img/profiles/avatar-17.jpg')}}" alt="img">
                                        <span class="position-absolute top-50 start-50 translate-middle text-center">+2</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xxl-3 col-xl-4 col-lg-6 col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between mb-4">
                                <h5 class="d-inline-flex align-items-center"><i class="ti ti-point-filled text-success fs-20"></i>R&D</h5>
                                <div class="dropdown">
                                    <a href="#" class="action-icon border-0" data-bs-toggle="dropdown" aria-expanded="false"><i data-feather="more-vertical" class="feather-user"></i></a>
                                    <ul class="dropdown-menu dropdown-menu-end ">
                                        <li>
                                            <a href="javascript:void(0);" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#edit-department"><i data-feather="edit" class="info-img me-2"></i>Edit</a>
                                        </li>
                                        <li>
                                            <a href="javascript:void(0);" class="dropdown-item mb-0" data-bs-toggle="modal" data-bs-target="#delete-modal"><i data-feather="trash-2" class="info-img me-2"></i>Delete</a>
                                        </li>								
                                    </ul>
                                </div>
                            </div>
                            <div class="bg-light rounded p-3 text-center mb-4">
                                <div class="avatar avatar-lg mb-2">
                                    <img src="{{URL::asset('build/img/users/user-11.jpg')}}" alt="Img">
                                </div>
                                <h4>Travis Marcotte</h4>
                            </div>
                            <div class="d-flex align-items-center justify-content-between">
                                <p class="mb-0">Total Members: 10</p>
                                <div class="avatar-list-stacked avatar-group-sm">
                                    <span class="avatar avatar-rounded">
                                        <img class="border border-white" src="{{URL::asset('build/img/profiles/avatar-15.jpg')}}" alt="img">
                                    </span>
                                    <span class="avatar avatar-rounded">
                                        <img class="border border-white" src="{{URL::asset('build/img/profiles/avatar-16.jpg')}}" alt="img">
                                    </span>
                                    <span class="avatar avatar-rounded">
                                        <img class="border border-white" src="{{URL::asset('build/img/profiles/avatar-18.jpg')}}" alt="img">
                                    </span>
                                    <a class="avatar avatar-rounded text-fixed-white fs-10 fw-medium position-relative" href="javascript:void(0);">
                                        <img src="{{URL::asset('build/img/profiles/avatar-17.jpg')}}" alt="img">
                                        <span class="position-absolute top-50 start-50 translate-middle text-center">+2</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xxl-3 col-xl-4 col-lg-6 col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between mb-4">
                                <h5 class="d-inline-flex align-items-center"><i class="ti ti-point-filled text-success fs-20"></i>Content Creation</h5>
                                <div class="dropdown">
                                    <a href="#" class="action-icon border-0" data-bs-toggle="dropdown" aria-expanded="false"><i data-feather="more-vertical" class="feather-user"></i></a>
                                    <ul class="dropdown-menu dropdown-menu-end ">
                                        <li>
                                            <a href="javascript:void(0);" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#edit-department"><i data-feather="edit" class="info-img me-2"></i>Edit</a>
                                        </li>
                                        <li>
                                            <a href="javascript:void(0);" class="dropdown-item mb-0" data-bs-toggle="modal" data-bs-target="#delete-modal"><i data-feather="trash-2" class="info-img me-2"></i>Delete</a>
                                        </li>								
                                    </ul>
                                </div>
                            </div>
                            <div class="bg-light rounded p-3 text-center mb-4">
                                <div class="avatar avatar-lg mb-2">
                                    <img src="{{URL::asset('build/img/users/user-12.jpg')}}" alt="Img">
                                </div>
                                <h4>Malinda Ruiz</h4>
                            </div>
                            <div class="d-flex align-items-center justify-content-between">
                                <p class="mb-0">Total Members: 08</p>
                                <div class="avatar-list-stacked avatar-group-sm">
                                    <span class="avatar avatar-rounded">
                                        <img class="border border-white" src="{{URL::asset('build/img/profiles/avatar-15.jpg')}}" alt="img">
                                    </span>
                                    <span class="avatar avatar-rounded">
                                        <img class="border border-white" src="{{URL::asset('build/img/profiles/avatar-16.jpg')}}" alt="img">
                                    </span>
                                    <span class="avatar avatar-rounded">
                                        <img class="border border-white" src="{{URL::asset('build/img/profiles/avatar-18.jpg')}}" alt="img">
                                    </span>
                                    <a class="avatar avatar-rounded text-fixed-white fs-10 fw-medium position-relative" href="javascript:void(0);">
                                        <img src="{{URL::asset('build/img/profiles/avatar-17.jpg')}}" alt="img">
                                        <span class="position-absolute top-50 start-50 translate-middle text-center">+2</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xxl-3 col-xl-4 col-lg-6 col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between mb-4">
                                <h5 class="d-inline-flex align-items-center"><i class="ti ti-point-filled text-success fs-20"></i>Social Media</h5>
                                <div class="dropdown">
                                    <a href="#" class="action-icon border-0" data-bs-toggle="dropdown" aria-expanded="false"><i data-feather="more-vertical" class="feather-user"></i></a>
                                    <ul class="dropdown-menu dropdown-menu-end ">
                                        <li>
                                            <a href="javascript:void(0);" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#edit-department"><i data-feather="edit" class="info-img me-2"></i>Edit</a>
                                        </li>
                                        <li>
                                            <a href="javascript:void(0);" class="dropdown-item mb-0" data-bs-toggle="modal" data-bs-target="#delete-modal"><i data-feather="trash-2" class="info-img me-2"></i>Delete</a>
                                        </li>								
                                    </ul>
                                </div>
                            </div>
                            <div class="bg-light rounded p-3 text-center mb-4">
                                <div class="avatar avatar-lg mb-2">
                                    <img src="{{URL::asset('build/img/users/user-09.jpg')}}" alt="Img">
                                </div>
                                <h4>David Slater</h4>
                            </div>
                            <div class="d-flex align-items-center justify-content-between">
                                <p class="mb-0">Total Members: 06</p>
                                <div class="avatar-list-stacked avatar-group-sm">
                                    <span class="avatar avatar-rounded">
                                        <img class="border border-white" src="{{URL::asset('build/img/profiles/avatar-15.jpg')}}" alt="img">
                                    </span>
                                    <span class="avatar avatar-rounded">
                                        <img class="border border-white" src="{{URL::asset('build/img/profiles/avatar-16.jpg')}}" alt="img">
                                    </span>
                                    <span class="avatar avatar-rounded">
                                        <img class="border border-white" src="{{URL::asset('build/img/profiles/avatar-18.jpg')}}" alt="img">
                                    </span>
                                    <a class="avatar avatar-rounded text-fixed-white fs-10 fw-medium position-relative" href="javascript:void(0);">
                                        <img src="{{URL::asset('build/img/profiles/avatar-17.jpg')}}" alt="img">
                                        <span class="position-absolute top-50 start-50 translate-middle text-center">+2</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xxl-3 col-xl-4 col-lg-6 col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between mb-4">
                                <h5 class="d-inline-flex align-items-center"><i class="ti ti-point-filled text-success fs-20"></i>IT Support</h5>
                                <div class="dropdown">
                                    <a href="#" class="action-icon border-0" data-bs-toggle="dropdown" aria-expanded="false"><i data-feather="more-vertical" class="feather-user"></i></a>
                                    <ul class="dropdown-menu dropdown-menu-end ">
                                        <li>
                                            <a href="javascript:void(0);" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#edit-department"><i data-feather="edit" class="info-img me-2"></i>Edit</a>
                                        </li>
                                        <li>
                                            <a href="javascript:void(0);" class="dropdown-item mb-0" data-bs-toggle="modal" data-bs-target="#delete-modal"><i data-feather="trash-2" class="info-img me-2"></i>Delete</a>
                                        </li>								
                                    </ul>
                                </div>
                            </div>
                            <div class="bg-light rounded p-3 text-center mb-4">
                                <div class="avatar avatar-lg mb-2">
                                    <img src="{{URL::asset('build/img/users/user-13.jpg')}}" alt="Img">
                                </div>
                                <h4>Michele Kim</h4>
                            </div>
                            <div class="d-flex align-items-center justify-content-between">
                                <p class="mb-0">Total Members: 04</p>
                                <div class="avatar-list-stacked avatar-group-sm">
                                    <span class="avatar avatar-rounded">
                                        <img class="border border-white" src="{{URL::asset('build/img/profiles/avatar-15.jpg')}}" alt="img">
                                    </span>
                                    <span class="avatar avatar-rounded">
                                        <img class="border border-white" src="{{URL::asset('build/img/profiles/avatar-16.jpg')}}" alt="img">
                                    </span>
                                    <span class="avatar avatar-rounded">
                                        <img class="border border-white" src="{{URL::asset('build/img/profiles/avatar-18.jpg')}}" alt="img">
                                    </span>
                                    <a class="avatar avatar-rounded text-fixed-white fs-10 fw-medium position-relative" href="javascript:void(0);">
                                        <img src="{{URL::asset('build/img/profiles/avatar-17.jpg')}}" alt="img">
                                        <span class="position-absolute top-50 start-50 translate-middle text-center">+2</span>
                                    </a>
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
