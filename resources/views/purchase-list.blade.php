<?php $page = 'purchase-list'; ?>
@extends('layout.mainlayout')
@section('content')
<div class="page-wrapper">
    <div class="content">
        <div class="page-header transfer">
            <div class="add-item d-flex">
                <div class="page-title">
                    <h4 class="fw-bold">Purchase</h4>
                    <h6>Manage your purchases</h6>
                </div>
            </div>
            <ul class="table-top-head">
                <li>
                    <a data-bs-toggle="tooltip" data-bs-placement="top" title="Pdf"><img
                            src="{{URL::asset('build/img/icons/pdf.svg')}}" alt="img"></a>
                </li>
                <li>
                    <a data-bs-toggle="tooltip" data-bs-placement="top" title="Excel"><img
                            src="{{URL::asset('build/img/icons/excel.svg')}}" alt="img"></a>
                </li>
                <li>
                    <a data-bs-toggle="tooltip" data-bs-placement="top" title="Refresh"><i
                            data-feather="rotate-ccw" class="feather-rotate-ccw"></i></a>
                </li>		
                <li>
                    <a data-bs-toggle="tooltip" data-bs-placement="top" title="Collapse" id="collapse-header"><i class="ti ti-chevron-up"></i></a>
                </li>
            </ul>
            <div class="d-flex purchase-pg-btn">
                <div class="page-btn">
                    <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#add-purchase"><i
                            data-feather="plus-circle" class="me-1"></i>Add Purchase</a>
                </div>
                <div class="page-btn import">
                    <a href="#" class="btn btn-secondary color" data-bs-toggle="modal" data-bs-target="#view-notes"><i
                            data-feather="download" class="me-2"></i>Import Purchase</a>
                </div>
            </div>
            
        </div>

        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
                <div class="search-set">
                    <div class="search-input">
                        <span class="btn-searchset"><i class="ti ti-search fs-14 feather-search"></i></span>
                    </div>
                </div>
                <div class="d-flex table-dropdown my-xl-auto right-content align-items-center flex-wrap row-gap-3">
                    <div class="dropdown">
                        <a href="javascript:void(0);" class="dropdown-toggle btn btn-white btn-md d-inline-flex align-items-center" data-bs-toggle="dropdown">
                        Payment Status
                        </a>
                        <ul class="dropdown-menu  dropdown-menu-end p-3">
                            <li>
                                <a href="javascript:void(0);" class="dropdown-item rounded-1">Paid</a>
                            </li>
                            <li>
                                <a href="javascript:void(0);" class="dropdown-item rounded-1">Unpaid</a>
                            </li>
                            <li>
                                <a href="javascript:void(0);" class="dropdown-item rounded-1">Overdue</a>
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
                                <th>Supplier Name</th>
                                <th>Reference</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Total</th>
                                <th>Paid</th>
                                <th>Due</th>
                                <th>Payment Status</th>
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
                                <td>Electro Mart</td>
                                <td>PT001 </td>
                                <td>24 Dec 2024</td>
                                <td><span class="badges status-badge fs-10 p-1 px-2 rounded-1">Received</span></td>
                                <td>$1000</td>
                                <td>$1000</td>
                                <td>$0.00</td>
                                <td><span class="p-1 pe-2 rounded-1 text-success bg-success-transparent fs-10"><i class="ti ti-point-filled me-1 fs-11"></i>Paid</span></td>
                                <td class="action-table-data">
                                    <div class="edit-delete-action">
                                        <a class="me-2 p-2" href="javascript:void(0);">
                                            <i data-feather="eye" class="action-eye"></i>
                                        </a>
                                        <a class="me-2 p-2" data-bs-toggle="modal" data-bs-target="#edit-purchase">
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
                                <td>Quantum Gadgets</td>
                                <td>PT002</td>
                                <td>10 Dec 2024</td>
                                <td><span class="badges status-badge badge-pending fs-10 p-1 px-2 rounded-1">Pending</span></td>
                                <td>$1500</td>
                                <td>$0.00</td>
                                <td>$1500</td>
                                <td><span class="p-1 pe-2 rounded-1 text-danger bg-danger-transparent fs-10"><i class="ti ti-point-filled me-1 fs-11"></i>Unpaid</span></td>
                                <td class="action-table-data">
                                    <div class="edit-delete-action">
                                        <a class="me-2 p-2" href="javascript:void(0);">
                                            <i data-feather="eye" class="action-eye"></i>
                                        </a>
                                        <a class="me-2 p-2" data-bs-toggle="modal" data-bs-target="#edit-purchase">
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
                                <td>Prime Bazaar</td>
                                <td>PT003</td>
                                <td>27 Nov 2024</td>
                                <td><span class="badges status-badge fs-10 p-1 px-2 rounded-1">Received</span></td>
                                <td>$1500</td>
                                <td>$1800</td>
                                <td>$0.00</td>
                                <td><span class="p-1 pe-2 rounded-1 text-success bg-success-transparent fs-10"><i class="ti ti-point-filled me-1 fs-11"></i>Paid</span></td>
                                <td class="action-table-data">
                                    <div class="edit-delete-action">
                                        <a class="me-2 p-2" href="javascript:void(0);">
                                            <i data-feather="eye" class="action-eye"></i>
                                        </a>
                                        <a class="me-2 p-2" data-bs-toggle="modal" data-bs-target="#edit-purchase">
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
                                <td>Gadget World</td>
                                <td>PT004</td>
                                <td>18 Nov 2024</td>
                                <td><span class="badges status-badge bg-warning fs-10 p-1 px-2 rounded-1">Ordered</span></td>
                                <td>$2000</td>
                                <td>$1000</td>
                                <td>$1000</td>
                                <td><span class="p-1 pe-2 rounded-1 text-warning bg-warning-transparent fs-10"><i class="ti ti-point-filled me-1 fs-11"></i>Overdue</span></td>
                                <td class="action-table-data">
                                    <div class="edit-delete-action">
                                        <a class="me-2 p-2" href="javascript:void(0);">
                                            <i data-feather="eye" class="action-eye"></i>
                                        </a>
                                        <a class="me-2 p-2" data-bs-toggle="modal" data-bs-target="#edit-purchase">
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
                                <td>Volt Vault</td>
                                <td>PT005</td>
                                <td>06 Nov 2024</td>
                                <td><span class="badges status-badge fs-10 p-1 px-2 rounded-1">Received</span></td>
                                <td>$800</td>
                                <td>$800</td>
                                <td>$0.00</td>
                                <td><span class="p-1 pe-2 rounded-1 text-success bg-success-transparent fs-10"><i class="ti ti-point-filled me-1 fs-11"></i>Paid</span></td>
                                <td class="action-table-data">
                                    <div class="edit-delete-action">
                                        <a class="me-2 p-2" href="javascript:void(0);">
                                            <i data-feather="eye" class="action-eye"></i>
                                        </a>
                                        <a class="me-2 p-2" data-bs-toggle="modal" data-bs-target="#edit-purchase">
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
                                <td>Elite Retail</td>
                                <td>PT006</td>
                                <td>25 Oct 2024</td>
                                <td><span class="badges status-badge badge-pending fs-10 p-1 px-2 rounded-1">Pending</span></td>
                                <td>$750</td>
                                <td>$0.00</td>
                                <td>$750</td>
                                <td><span class="p-1 pe-2 rounded-1 text-danger bg-danger-transparent fs-10"><i class="ti ti-point-filled me-1 fs-11"></i>Unpaid</span></td>
                                <td class="action-table-data">
                                    <div class="edit-delete-action">
                                        <a class="me-2 p-2" href="javascript:void(0);">
                                            <i data-feather="eye" class="action-eye"></i>
                                        </a>
                                        <a class="me-2 p-2" data-bs-toggle="modal" data-bs-target="#edit-purchase">
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
                                <td>Prime Mart</td>
                                <td>PT007</td>
                                <td>14 Oct 2024</td>
                                <td><span class="badges status-badge fs-10 p-1 px-2 rounded-1">Received</span></td>
                                <td>$1300</td>
                                <td>$1300</td>
                                <td>$0.00</td>
                                <td><span class="p-1 pe-2 rounded-1 text-success bg-success-transparent fs-10"><i class="ti ti-point-filled me-1 fs-11"></i>Paid</span></td>
                                <td class="action-table-data">
                                    <div class="edit-delete-action">
                                        <a class="me-2 p-2" href="javascript:void(0);">
                                            <i data-feather="eye" class="action-eye"></i>
                                        </a>
                                        <a class="me-2 p-2" data-bs-toggle="modal" data-bs-target="#edit-purchase">
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
                                <td>NeoTech Store</td>
                                <td>PT008</td>
                                <td>03 Oct 2024</td>
                                <td><span class="badges status-badge fs-10 p-1 px-2 rounded-1">Received</span></td>
                                <td>$1100</td>
                                <td>$1100</td>
                                <td>$0.00</td>
                                <td><span class="p-1 pe-2 rounded-1 text-success bg-success-transparent fs-10"><i class="ti ti-point-filled me-1 fs-11"></i>Paid</span></td>
                                <td class="action-table-data">
                                    <div class="edit-delete-action">
                                        <a class="me-2 p-2" href="javascript:void(0);">
                                            <i data-feather="eye" class="action-eye"></i>
                                        </a>
                                        <a class="me-2 p-2" data-bs-toggle="modal" data-bs-target="#edit-purchase">
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
                                <td>Urban Mart</td>
                                <td>PT009</td>
                                <td>20 Sep 2024</td>
                                <td><span class="badges status-badge bg-warning fs-10 p-1 px-2 rounded-1">Ordered</span></td>
                                <td>$2300</td>
                                <td>$2300</td>
                                <td>$0.00</td>
                                <td><span class="p-1 pe-2 rounded-1 text-success bg-success-transparent fs-10"><i class="ti ti-point-filled me-1 fs-11"></i>Paid</span></td>
                                <td class="action-table-data">
                                    <div class="edit-delete-action">
                                        <a class="me-2 p-2" href="javascript:void(0);">
                                            <i data-feather="eye" class="action-eye"></i>
                                        </a>
                                        <a class="me-2 p-2" data-bs-toggle="modal" data-bs-target="#edit-purchase">
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
                                <td>Travel Mart</td>
                                <td>PT010</td>
                                <td>10 Sep 2024</td>
                                <td><span class="badges status-badge badge-pending fs-10 p-1 px-2 rounded-1">Pending</span></td>
                                <td>$1700</td>
                                <td>$1700</td>
                                <td>$0.00</td>
                                <td><span class="p-1 pe-2 rounded-1 text-success bg-success-transparent fs-10"><i class="ti ti-point-filled me-1 fs-11"></i>Paid</span></td>
                                <td class="action-table-data">
                                    <div class="edit-delete-action">
                                        <a class="me-2 p-2" href="javascript:void(0);">
                                            <i data-feather="eye" class="action-eye"></i>
                                        </a>
                                        <a class="me-2 p-2" data-bs-toggle="modal" data-bs-target="#edit-purchase">
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
