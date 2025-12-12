<?php $page="expense-list"?>
@extends('layout.mainlayout')
@section('content')

<div class="page-wrapper">
    <div class="content">
        <div class="page-header">
            <div class="add-item d-flex">
                <div class="page-title">
                    <h4 class="fw-bold">Expenses</h4>
                    <h6>Manage Your Expenses</h6>
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
                <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#add-expense"><i class="ti ti-circle-plus me-1"></i>Add Expense</a>
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
                        Category
                        </a>
                        <ul class="dropdown-menu  dropdown-menu-end p-3">
                            <li>
                                <a href="javascript:void(0);" class="dropdown-item rounded-1">Utilities</a>
                            </li>
                            <li>
                                <a href="javascript:void(0);" class="dropdown-item rounded-1">Office Supplies</a>
                            </li>
                        </ul>
                    </div>				
                    <div class="dropdown">
                        <a href="javascript:void(0);" class="dropdown-toggle btn btn-white btn-md d-inline-flex align-items-center" data-bs-toggle="dropdown">
                            Status
                        </a>
                        <ul class="dropdown-menu  dropdown-menu-end p-3">
                            <li>
                                <a href="javascript:void(0);" class="dropdown-item rounded-1">Approved</a>
                            </li>
                            <li>
                                <a href="javascript:void(0);" class="dropdown-item rounded-1">Pending</a>
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
                                <th>Reference</th>
                                <th>Expense Name</th>
                                <th>Category</th>
                                <th>Description</th>
                                <th>Date</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th class="no-sort"></th>
                            </tr>
                        </thead>
                        <tbody class="Expense-list-blk">
                            <tr>
                                <td>
                                    <label class="checkboxs">
                                        <input type="checkbox">
                                        <span class="checkmarks"></span>
                                    </label>
                                </td>
                                <td>EX849</td>
                                <td class="text-gray-9 p-2">Electricity Payment</td>
                                <td>Utilities</td>
                                <td>Electricity Bill</td>
                                <td>24 Dec 2024</td>
                                <td>$200</td>
                                <td><span class="badges status-badge fs-10 p-1 px-2 rounded-1">Approved</span></td>
                                <td class="action-table-data p-2">
                                    <div class="edit-delete-action">
                                        <a class="me-2 p-2 mb-0" href="javascript:void(0);">
                                            <i data-feather="eye" class="action-eye"></i>
                                        </a>
                                        <a class="me-2 p-2 mb-0" data-bs-toggle="modal" data-bs-target="#edit-expense">
                                            <i data-feather="edit" class="feather-edit"></i>
                                        </a>
                                        <a data-bs-toggle="modal" data-bs-target="#delete-modal" class="me-3 confirm-text p-2 mb-0" href="javascript:void(0);">
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
                                <td>EX848</td>
                                <td class="text-gray-9 p-2">Stationery Purchase</td>
                                <td>Office Supplies</td>
                                <td>Stationery items for office</td>
                                <td>10 Dec 2024</td>
                                <td>$50</td>
                                <td><span class="badges status-badge badge-pending fs-10 p-1 px-2 rounded-1">Pending</span></td>
                                <td class="action-table-data p-2">
                                    <div class="edit-delete-action">
                                        <a class="me-2 p-2 mb-0" href="javascript:void(0);">
                                            <i data-feather="eye" class="action-eye"></i>
                                        </a>
                                        <a class="me-2 p-2 mb-0" data-bs-toggle="modal" data-bs-target="#edit-expense">
                                            <i data-feather="edit" class="feather-edit"></i>
                                        </a>
                                        <a data-bs-toggle="modal" data-bs-target="#delete-modal" class="me-3 confirm-text p-2 mb-0" href="javascript:void(0);">
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
                                <td>EX847</td>
                                <td class="text-gray-9 p-2">AC Repair Service</td>
                                <td>Repairs & Maintenance</td>
                                <td>AC Repair for Office</td>
                                <td>27 Nov 2024</td>
                                <td>$800</td>
                                <td><span class="badges status-badge fs-10 p-1 px-2 rounded-1">Approved</span></td>
                                <td class="action-table-data p-2">
                                    <div class="edit-delete-action">
                                        <a class="me-2 p-2 mb-0" href="javascript:void(0);">
                                            <i data-feather="eye" class="action-eye"></i>
                                        </a>
                                        <a class="me-2 p-2 mb-0" data-bs-toggle="modal" data-bs-target="#edit-expense">
                                            <i data-feather="edit" class="feather-edit"></i>
                                        </a>
                                        <a data-bs-toggle="modal" data-bs-target="#delete-modal" class="me-3 confirm-text p-2 mb-0" href="javascript:void(0);">
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
                                <td>EX846</td>
                                <td class="text-gray-9 p-2">Social Media Promotion</td>
                                <td>Marketing</td>
                                <td>Social Media Ads Campaign</td>
                                <td>18 Nov 2024</td>
                                <td>$100</td>
                                <td><span class="badges status-badge fs-10 p-1 px-2 rounded-1">Approved</span></td>
                                <td class="action-table-data p-2">
                                    <div class="edit-delete-action">
                                        <a class="me-2 p-2 mb-0" href="javascript:void(0);">
                                            <i data-feather="eye" class="action-eye"></i>
                                        </a>
                                        <a class="me-2 p-2 mb-0" data-bs-toggle="modal" data-bs-target="#edit-expense">
                                            <i data-feather="edit" class="feather-edit"></i>
                                        </a>
                                        <a data-bs-toggle="modal" data-bs-target="#delete-modal" class="me-3 confirm-text p-2 mb-0" href="javascript:void(0);">
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
                                <td>EX845</td>
                                <td class="text-gray-9 p-2">Client Meeting</td>
                                <td>Travel Expenses</td>
                                <td>Travel fare for client meeting</td>
                                <td>06 Nov 2024</td>
                                <td>$700</td>
                                <td><span class="badges status-badge fs-10 p-1 px-2 rounded-1">Approved</span></td>
                                <td class="action-table-data p-2">
                                    <div class="edit-delete-action">
                                        <a class="me-2 p-2 mb-0" href="javascript:void(0);">
                                            <i data-feather="eye" class="action-eye"></i>
                                        </a>
                                        <a class="me-2 p-2 mb-0" data-bs-toggle="modal" data-bs-target="#edit-expense">
                                            <i data-feather="edit" class="feather-edit"></i>
                                        </a>
                                        <a data-bs-toggle="modal" data-bs-target="#delete-modal" class="me-3 confirm-text p-2 mb-0" href="javascript:void(0);">
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
                                <td>EX844</td>
                                <td class="text-gray-9 p-2">Team Lunch</td>
                                <td>Employee Benefits</td>
                                <td>Team Lunch at Restaurant</td>
                                <td>25 Oct 2024</td>
                                <td>$1000</td>
                                <td><span class="badges status-badge badge-pending fs-10 p-1 px-2 rounded-1">Pending</span></td>
                                <td class="action-table-data p-2">
                                    <div class="edit-delete-action">
                                        <a class="me-2 p-2 mb-0" href="javascript:void(0);">
                                            <i data-feather="eye" class="action-eye"></i>
                                        </a>
                                        <a class="me-2 p-2 mb-0" data-bs-toggle="modal" data-bs-target="#edit-expense">
                                            <i data-feather="edit" class="feather-edit"></i>
                                        </a>
                                        <a data-bs-toggle="modal" data-bs-target="#delete-modal" class="me-3 confirm-text p-2 mb-0" href="javascript:void(0);">
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
                                <td>EX843</td>
                                <td class="text-gray-9 p-2">Business Flight Ticket</td>
                                <td>Travel Expenses</td>
                                <td>Flight tickets for meetings</td>
                                <td>14 Oct 2024</td>
                                <td>$1200</td>
                                <td><span class="badges status-badge fs-10 p-1 px-2 rounded-1">Approved</span></td>
                                <td class="action-table-data p-2">
                                    <div class="edit-delete-action">
                                        <a class="me-2 p-2 mb-0" href="javascript:void(0);">
                                            <i data-feather="eye" class="action-eye"></i>
                                        </a>
                                        <a class="me-2 p-2 mb-0" data-bs-toggle="modal" data-bs-target="#edit-expense">
                                            <i data-feather="edit" class="feather-edit"></i>
                                        </a>
                                        <a data-bs-toggle="modal" data-bs-target="#delete-modal" class="me-3 confirm-text p-2 mb-0" href="javascript:void(0);">
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
                                <td>EX842</td>
                                <td class="text-gray-9 p-2">Chair Purchase</td>
                                <td>Office Supplies</td>
                                <td>Ergonomic chairs for staff</td>
                                <td>03 Oct 2024</td>
                                <td>$750</td>
                                <td><span class="badges status-badge fs-10 p-1 px-2 rounded-1">Approved</span></td>
                                <td class="action-table-data p-2">
                                    <div class="edit-delete-action">
                                        <a class="me-2 p-2 mb-0" href="javascript:void(0);">
                                            <i data-feather="eye" class="action-eye"></i>
                                        </a>
                                        <a class="me-2 p-2 mb-0" data-bs-toggle="modal" data-bs-target="#edit-expense">
                                            <i data-feather="edit" class="feather-edit"></i>
                                        </a>
                                        <a data-bs-toggle="modal" data-bs-target="#delete-modal" class="me-3 confirm-text p-2 mb-0" href="javascript:void(0);">
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
                                <td>EX841</td>
                                <td class="text-gray-9 p-2">Plumbing Service</td>
                                <td>Repairs & Maintenance</td>
                                <td>Plumbing repairs in office</td>
                                <td>20 Sep 2024</td>
                                <td>$450</td>
                                <td><span class="badges status-badge fs-10 p-1 px-2 rounded-1">Approved</span></td>
                                <td class="action-table-data p-2">
                                    <div class="edit-delete-action">
                                        <a class="me-2 p-2 mb-0" href="javascript:void(0);">
                                            <i data-feather="eye" class="action-eye"></i>
                                        </a>
                                        <a class="me-2 p-2 mb-0" data-bs-toggle="modal" data-bs-target="#edit-expense">
                                            <i data-feather="edit" class="feather-edit"></i>
                                        </a>
                                        <a data-bs-toggle="modal" data-bs-target="#delete-modal" class="me-3 confirm-text p-2 mb-0" href="javascript:void(0);">
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
                                <td>EX840</td>
                                <td class="text-gray-9 p-2">Internet Bill Payment</td>
                                <td>Utilities</td>
                                <td>Monthly internet subscription</td>
                                <td>10 Sep 2024</td>
                                <td>$300</td>
                                <td><span class="badges status-badge badge-pending fs-10 p-1 px-2 rounded-1">Pending</span></td>
                                <td class="action-table-data p-2">
                                    <div class="edit-delete-action">
                                        <a class="me-2 p-2 mb-0" href="javascript:void(0);">
                                            <i data-feather="eye" class="action-eye"></i>
                                        </a>
                                        <a class="me-2 p-2 mb-0" data-bs-toggle="modal" data-bs-target="#edit-expense">
                                            <i data-feather="edit" class="feather-edit"></i>
                                        </a>
                                        <a data-bs-toggle="modal" data-bs-target="#delete-modal" class="me-3 confirm-text p-2 mb-0" href="javascript:void(0);">
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