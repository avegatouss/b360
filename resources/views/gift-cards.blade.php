<?php $page = 'gift-cards'; ?>
@extends('layout.mainlayout')
@section('content')

<div class="page-wrapper">
    <div class="content">
        <div class="page-header">
            <div class="add-item d-flex">
                <div class="page-title">
                    <h4 class="fw-bold">Gift Cards</h4>
                    <h6>Manage your gift cards</h6>
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
                <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#add-gift-card"><i class="ti ti-circle-plus me-1"></i>Add Gift Card</a>
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
                            Status
                        </a>
                        <ul class="dropdown-menu  dropdown-menu-end p-3">
                            <li>
                                <a href="javascript:void(0);" class="dropdown-item rounded-1">Active</a>
                            </li>
                            <li>
                                <a href="javascript:void(0);" class="dropdown-item rounded-1">Redeemed</a>
                            </li>
                            <li>
                                <a href="javascript:void(0);" class="dropdown-item rounded-1">Inactive</a>
                            </li>
                            <li>
                                <a href="javascript:void(0);" class="dropdown-item rounded-1">Expired</a>
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
                                    <label class="checkboxs">
                                        <input type="checkbox" id="select-all">
                                        <span class="checkmarks"></span>
                                    </label>
                                </th>
                                <th>Gift Card</th>
                                <th>Customer</th>
                                <th>Issued Date</th>
                                <th>Expiry Date</th>
                                <th>Amount</th>
                                <th>Balance</th>
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
                                <td class="text-gray-9">GFT1110</td>
                                <td>
                                    <div class="userimgname">
                                        <span class="avatar avatar-md me-2">
                                        <a href="javascript:void(0);">
                                            <img src="{{URL::asset('build/img/users/user-33.png')}}" alt="user">
                                        </a>
                                    </span>
                                        <a href="javascript:void(0);">Carl Evans</a>
                                    </div>
                                </td>											
                                <td>24 Dec 2024</td>
                                <td>24 Jan 2025</td>
                                <td>$200</td>
                                <td>$100</td>
                                <td><span class="badge table-badge bg-success fw-medium fs-10">Active</span></td>
                                <td class="action-table-data">
                                    <div class="edit-delete-action">
                                        <a data-bs-toggle="modal" data-bs-target="#gift-card-details" class="me-2 edit-icon  p-2" href="#">
                                            <i data-feather="eye" class="feather-eye"></i>
                                        </a>
                                        <a class="me-2 p-2" href="#" data-bs-toggle="modal" data-bs-target="#edit-gift-card">
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
                                <td class="text-gray-9">GFT1109</td>
                                <td>
                                    <div class="userimgname">
                                        <span class="avatar avatar-md me-2">
                                        <a href="javascript:void(0);">
                                            <img src="{{URL::asset('build/img/users/user-02.jpg')}}" alt="user">
                                        </a>
                                    </span>
                                        <a href="javascript:void(0);">Minerva Rameriz</a>
                                    </div>
                                </td>											
                                <td>10 Dec 2024</td>
                                <td>10 Jan 2025</td>
                                <td>$300</td>
                                <td>$200</td>
                                <td><span class="badge table-badge bg-success fw-medium fs-10">Active</span></td>
                                <td class="action-table-data">
                                    <div class="edit-delete-action">
                                        <a data-bs-toggle="modal" data-bs-target="#gift-card-details" class="me-2 edit-icon  p-2" href="#">
                                            <i data-feather="eye" class="feather-eye"></i>
                                        </a>
                                        <a class="me-2 p-2" href="#" data-bs-toggle="modal" data-bs-target="#edit-gift-card">
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
                                <td class="text-gray-9">GFT1108</td>
                                <td>
                                    <div class="userimgname">
                                        <span class="avatar avatar-md me-2">
                                        <a href="javascript:void(0);">
                                            <img src="{{URL::asset('build/img/users/user-34.jpg')}}" alt="user">
                                        </a>
                                    </span>
                                        <a href="javascript:void(0);">Robert Lamon</a>
                                    </div>
                                </td>											
                                <td>27 Nov 2024</td>
                                <td>27 Dec 2024</td>
                                <td>$200</td>
                                <td>$150</td>
                                <td><span class="badge table-badge bg-success fw-medium fs-10">Active</span></td>
                                <td class="action-table-data">
                                    <div class="edit-delete-action">
                                        <a data-bs-toggle="modal" data-bs-target="#gift-card-details" class="me-2 edit-icon  p-2" href="#">
                                            <i data-feather="eye" class="feather-eye"></i>
                                        </a>
                                        <a class="me-2 p-2" href="#" data-bs-toggle="modal" data-bs-target="#edit-gift-card">
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
                                <td class="text-gray-9">GFT1107</td>
                                <td>
                                    <div class="userimgname">
                                        <span class="avatar avatar-md me-2">
                                        <a href="javascript:void(0);">
                                            <img src="{{URL::asset('build/img/users/user-35.jpg')}}" alt="user">
                                        </a>
                                    </span>
                                        <a href="javascript:void(0);">Patricia Lewis</a>
                                    </div>
                                </td>											
                                <td>18 Nov 2024</td>
                                <td>18 Dec 2024</td>
                                <td>$120</td>
                                <td>$0</td>
                                <td><span class="badge table-badge bg-pink fw-medium fs-10">Redeemed</span></td>
                                <td class="action-table-data">
                                    <div class="edit-delete-action">
                                        <a data-bs-toggle="modal" data-bs-target="#gift-card-details" class="me-2 edit-icon  p-2" href="#">
                                            <i data-feather="eye" class="feather-eye"></i>
                                        </a>
                                        <a class="me-2 p-2" href="#" data-bs-toggle="modal" data-bs-target="#edit-gift-card">
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
                                <td class="text-gray-9">GFT1106</td>
                                <td>
                                    <div class="userimgname">
                                        <span class="avatar avatar-md me-2">
                                        <a href="javascript:void(0);">
                                            <img src="{{URL::asset('build/img/users/user-36.jpg')}}" alt="user">
                                        </a>
                                    </span>
                                        <a href="javascript:void(0);">Mark Joslyn</a>
                                    </div>
                                </td>											
                                <td>06 Nov 2024</td>
                                <td>06 Dec 2024</td>
                                <td>$350</td>
                                <td>$300</td>
                                <td><span class="badge table-badge bg-success fw-medium fs-10">Active</span></td>
                                <td class="action-table-data">
                                    <div class="edit-delete-action">
                                        <a data-bs-toggle="modal" data-bs-target="#gift-card-details" class="me-2 edit-icon  p-2" href="#">
                                            <i data-feather="eye" class="feather-eye"></i>
                                        </a>
                                        <a class="me-2 p-2" href="#" data-bs-toggle="modal" data-bs-target="#edit-gift-card">
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
                                <td class="text-gray-9">GFT1105</td>
                                <td>
                                    <div class="userimgname">
                                        <span class="avatar avatar-md me-2">
                                        <a href="javascript:void(0);">
                                            <img src="{{URL::asset('build/img/users/user-37.jpg')}}" alt="user">
                                        </a>
                                    </span>
                                        <a href="javascript:void(0);">Marsha Betts</a>
                                    </div>
                                </td>											
                                <td>25 Oct 2024</td>
                                <td>25 Nov 2024</td>
                                <td>$500</td>
                                <td>$400</td>
                                <td><span class="badge table-badge bg-success fw-medium fs-10">Active</span></td>
                                <td class="action-table-data">
                                    <div class="edit-delete-action">
                                        <a data-bs-toggle="modal" data-bs-target="#gift-card-details" class="me-2 edit-icon  p-2" href="#">
                                            <i data-feather="eye" class="feather-eye"></i>
                                        </a>
                                        <a class="me-2 p-2" href="#" data-bs-toggle="modal" data-bs-target="#edit-gift-card">
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
                                <td class="text-gray-9">GFT1104</td>
                                <td>
                                    <div class="userimgname">
                                        <span class="avatar avatar-md me-2">
                                        <a href="javascript:void(0);">
                                            <img src="{{URL::asset('build/img/users/user-28.jpg')}}" alt="user">
                                        </a>
                                    </span>
                                        <a href="javascript:void(0);">Daniel Jude</a>
                                    </div>
                                </td>											
                                <td>14 Oct 2024</td>
                                <td>14 Nov 2024</td>
                                <td>$220</td>
                                <td>$150</td>
                                <td><span class="badge table-badge bg-success fw-medium fs-10">Active</span></td>
                                <td class="action-table-data">
                                    <div class="edit-delete-action">
                                        <a data-bs-toggle="modal" data-bs-target="#gift-card-details" class="me-2 edit-icon  p-2" href="#">
                                            <i data-feather="eye" class="feather-eye"></i>
                                        </a>
                                        <a class="me-2 p-2" href="#" data-bs-toggle="modal" data-bs-target="#edit-gift-card">
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
                                <td class="text-gray-9">GFT1103</td>
                                <td>
                                    <div class="userimgname">
                                        <span class="avatar avatar-md me-2">
                                        <a href="javascript:void(0);">
                                            <img src="{{URL::asset('build/img/users/user-38.jpg')}}" alt="user">
                                        </a>
                                    </span>
                                        <a href="javascript:void(0);">Emma Bates</a>
                                    </div>
                                </td>											
                                <td>03 Oct 2024</td>
                                <td>03 Nov 2024</td>
                                <td>$260</td>
                                <td>$220</td>
                                <td><span class="badge table-badge bg-danger fw-medium fs-10">Inactive</span></td>
                                <td class="action-table-data">
                                    <div class="edit-delete-action">
                                        <a data-bs-toggle="modal" data-bs-target="#gift-card-details" class="me-2 edit-icon  p-2" href="#">
                                            <i data-feather="eye" class="feather-eye"></i>
                                        </a>
                                        <a class="me-2 p-2" href="#" data-bs-toggle="modal" data-bs-target="#edit-gift-card">
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
                                <td class="text-gray-9">GFT1102</td>
                                <td>
                                    <div class="userimgname">
                                        <span class="avatar avatar-md me-2">
                                        <a href="javascript:void(0);">
                                            <img src="{{URL::asset('build/img/users/user-39.jpg')}}" alt="user">
                                        </a>
                                    </span>
                                        <a href="javascript:void(0);">Richard Fralick</a>
                                    </div>
                                </td>											
                                <td>20 Sep 2024</td>
                                <td>20 Oct 2024</td>
                                <td>$200</td>
                                <td>$160</td>
                                <td><span class="badge table-badge bg-success fw-medium fs-10">Active</span></td>
                                <td class="action-table-data">
                                    <div class="edit-delete-action">
                                        <a data-bs-toggle="modal" data-bs-target="#gift-card-details" class="me-2 edit-icon  p-2" href="#">
                                            <i data-feather="eye" class="feather-eye"></i>
                                        </a>
                                        <a class="me-2 p-2" href="#" data-bs-toggle="modal" data-bs-target="#edit-gift-card">
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
                                <td class="text-gray-9">GFT1101</td>
                                <td>
                                    <div class="userimgname">
                                        <span class="avatar avatar-md me-2">
                                        <a href="javascript:void(0);">
                                            <img src="{{URL::asset('build/img/users/user-40.jpg')}}" alt="user">
                                        </a>
                                    </span>
                                        <a href="javascript:void(0);">Michelle Robison</a>
                                    </div>
                                </td>											
                                <td>10 Sep 2024</td>
                                <td>10 Oct 2024</td>
                                <td>$400</td>
                                <td>$350</td>
                                <td><span class="badge table-badge bg-light fw-medium fs-10 text-gray-9">Expired</span></td>
                                <td class="action-table-data">
                                    <div class="edit-delete-action">
                                        <a data-bs-toggle="modal" data-bs-target="#gift-card-details" class="me-2 edit-icon  p-2" href="#">
                                            <i data-feather="eye" class="feather-eye"></i>
                                        </a>
                                        <a class="me-2 p-2" href="#" data-bs-toggle="modal" data-bs-target="#edit-gift-card">
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
    </div>
    <div class="footer d-sm-flex align-items-center justify-content-between border-top bg-white p-3">
        <p class="mb-0 text-gray-9">2014 - 2025 &copy; DreamsPOS. All Right Reserved</p>
        <p>Designed &amp; Developed by <a href="javascript:void(0);" class="text-primary">Dreams</a></p>
    </div>
</div>

@endsection