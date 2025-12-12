<?php $page = 'invoice-report'; ?>
@extends('layout.mainlayout')
@section('content')
    <div class="page-wrapper">
        <div class="content">
            <div class="page-header">
                <div class="add-item d-flex">
                    <div class="page-title">
                        <h4>Invoice Report	</h4>
                        <h6>Manage Your Invoice Report</h6>
                    </div>
                </div>
                <ul class="table-top-head">
                    <li class="me-2">
                        <a data-bs-toggle="tooltip" data-bs-placement="top" title="Refresh"><i class="ti ti-refresh"></i></a>
                    </li>
                    <li class="me-2">
                        <a data-bs-toggle="tooltip" data-bs-placement="top" title="Collapse" id="collapse-header"><i class="ti ti-chevron-up"></i></a>
                    </li>
                </ul>
            </div>
            <div class="row">
                <div class="col-xl-3 col-sm-6 col-12 d-flex">
                    <div class="card border border-success sale-widget flex-fill">
                        <div class="card-body d-flex align-items-center">
                            <span class="sale-icon bg-success text-white">
                                <i class="ti ti-align-box-bottom-left-filled fs-24"></i>
                            </span>
                            <div class="ms-2">
                                <p class="fw-medium mb-1">Total Amount</p>
                                <div>
                                    <h3>$4,56,000</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-sm-6 col-12 d-flex">
                    <div class="card border border-info sale-widget flex-fill">
                        <div class="card-body d-flex align-items-center">
                            <span class="sale-icon bg-info text-white">
                                <i class="ti ti-align-box-bottom-left-filled fs-24"></i>
                            </span>
                            <div class="ms-2">
                                <p class="fw-medium mb-1">Total Paid</p>
                                <div>
                                    <h3>$2,56,42</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-sm-6 col-12 d-flex">
                    <div class="card border border-orange sale-widget flex-fill">
                        <div class="card-body d-flex align-items-center">
                            <span class="sale-icon bg-orange text-white">
                                <i class="ti ti-moneybag fs-24"></i>
                            </span>
                            <div class="ms-2">
                                <p class="fw-medium mb-1">Total Unpaid</p>
                                <div>
                                    <h3>$1,52,45</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-sm-6 col-12 d-flex">
                    <div class="card border border-danger sale-widget flex-fill">
                        <div class="card-body d-flex align-items-center">
                            <span class="sale-icon bg-danger text-white">
                                <i class="ti ti-alert-circle-filled fs-24"></i>
                            </span>
                            <div class="ms-2">
                                <p class="fw-medium mb-1">Overdue</p>
                                <div>
                                    <h3>$2,56,12</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="card-body pb-1">
                    <form action="{{url('invoice-report')}}">
                        <div class="row align-items-end">
                            <div class="col-lg-10">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label class="form-label">Choose Date </label>
                                            <div class="input-icon-start position-relative">
                                                <input type="text" class="form-control date-range bookingrange" placeholder="dd/mm/yyyy - dd/mm/yyyy">
                                                <span class="input-icon-left">
                                                    <i class="ti ti-calendar"></i>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label class="form-label">Customer</label>
                                            <select class="select">
                                                <option>All</option>
                                                <option>Carl Evans</option>
                                                <option>Minerva Rameriz</option>
                                                <option>Robert Lamon</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label class="form-label">Status</label>
                                            <select class="select">
                                                <option>All</option>
                                                <option>Paid</option>
                                                <option>Unpaid</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-2">
                                <div class="mb-3">
                                    <button class="btn btn-primary w-100" type="submit">Generate Report</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="card no-search">
                <div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
                    <div>
                        <h4>Invoice Report</h4>
                    </div>
                    <ul class="table-top-head">
                        <li class="me-2">
                            <a data-bs-toggle="tooltip" data-bs-placement="top" title="Pdf"><img src="{{URL::asset('build/img/icons/pdf.svg')}}" alt="img"></a>
                        </li>
                        <li class="me-2">
                            <a data-bs-toggle="tooltip" data-bs-placement="top" title="Excel"><img src="{{URL::asset('build/img/icons/excel.svg')}}" alt="img"></a>
                        </li>
                        <li>
                            <a data-bs-toggle="tooltip" data-bs-placement="top" title="Print"><i class="ti ti-printer"></i></a>
                        </li>
                    </ul>
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
                                    <th>Invoice No</th>
                                    <th>Customer</th>
                                    <th>Due Date</th>
                                    <th>Amount</th>
                                    <th>Paid</th>
                                    <th>Amount Due</th>
                                    <th>Status</th>
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
                                    <td><a href="#">INV001</a></td>
                                    <td>Carl Evans</td>
                                    <td>24 Dec 2024</td>
                                    <td>$500</td>
                                    <td>$500</td>
                                    <td>$500</td>
                                    <td>
                                        <span class="badge badge-success d-inline-flex align-items-center badge-xs">
                                            <i class="ti ti-point-filled me-1"></i>Paid
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <label class="checkboxs">
                                            <input type="checkbox">
                                            <span class="checkmarks"></span>
                                        </label>
                                    </td>
                                    <td><a href="#">INV002</a></td>
                                    <td>Minerva Rameriz</td>
                                    <td>10 Dec 2024</td>
                                    <td>$1500</td>
                                    <td>$1500</td>
                                    <td>$1500</td>
                                    <td>
                                        <span class="badge badge-success d-inline-flex align-items-center badge-xs">
                                            <i class="ti ti-point-filled me-1"></i>Paid
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <label class="checkboxs">
                                            <input type="checkbox">
                                            <span class="checkmarks"></span>
                                        </label>
                                    </td>
                                    <td><a href="#">INV003</a></td>
                                    <td>Robert Lamon</td>
                                    <td>27 Nov 2024</td>
                                    <td>$600</td>
                                    <td>$600</td>
                                    <td>$600</td>
                                    <td>
                                        <span class="badge badge-success d-inline-flex align-items-center badge-xs">
                                            <i class="ti ti-point-filled me-1"></i>Paid
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <label class="checkboxs">
                                            <input type="checkbox">
                                            <span class="checkmarks"></span>
                                        </label>
                                    </td>
                                    <td><a href="#">INV004</a></td>
                                    <td>Patricia Lewis</td>
                                    <td>18 Nov 2024</td>
                                    <td>$1000</td>
                                    <td>$1000</td>
                                    <td>$1000</td>
                                    <td>
                                        <span class="badge badge-success d-inline-flex align-items-center badge-xs">
                                            <i class="ti ti-point-filled me-1"></i>Paid
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <label class="checkboxs">
                                            <input type="checkbox">
                                            <span class="checkmarks"></span>
                                        </label>
                                    </td>
                                    <td><a href="#">INV005</a></td>
                                    <td>Mark Joslyn</td>
                                    <td>06 Nov 2024</td>
                                    <td>$1200</td>
                                    <td>$1200</td>
                                    <td>$1200</td>
                                    <td>
                                        <span class="badge badge-success d-inline-flex align-items-center badge-xs">
                                            <i class="ti ti-point-filled me-1"></i>Paid
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <label class="checkboxs">
                                            <input type="checkbox">
                                            <span class="checkmarks"></span>
                                        </label>
                                    </td>
                                    <td><a href="#">INV006</a></td>
                                    <td>Marsha Betts</td>
                                    <td>25 Oct 2024</td>
                                    <td>$800</td>
                                    <td>$800</td>
                                    <td>$800</td>
                                    <td>
                                        <span class="badge badge-success d-inline-flex align-items-center badge-xs">
                                            <i class="ti ti-point-filled me-1"></i>Paid
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <label class="checkboxs">
                                            <input type="checkbox">
                                            <span class="checkmarks"></span>
                                        </label>
                                    </td>
                                    <td><a href="#">INV007</a></td>
                                    <td>Daniel Jude</td>
                                    <td>14 Oct 2024</td>
                                    <td>$2000</td>
                                    <td>$2000</td>
                                    <td>$2000</td>
                                    <td>
                                        <span class="badge badge-success d-inline-flex align-items-center badge-xs">
                                            <i class="ti ti-point-filled me-1"></i>Paid
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <label class="checkboxs">
                                            <input type="checkbox">
                                            <span class="checkmarks"></span>
                                        </label>
                                    </td>
                                    <td><a href="#">INV008</a></td>
                                    <td>Emma Bates</td>
                                    <td>03 Oct 2024</td>
                                    <td>$100</td>
                                    <td>$100</td>
                                    <td>$100</td>
                                    <td>
                                        <span class="badge badge-success d-inline-flex align-items-center badge-xs">
                                            <i class="ti ti-point-filled me-1"></i>Paid
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <label class="checkboxs">
                                            <input type="checkbox">
                                            <span class="checkmarks"></span>
                                        </label>
                                    </td>
                                    <td><a href="#">INV009</a></td>
                                    <td>Richard Fralick</td>
                                    <td>20 Sep 2024</td>
                                    <td>$300</td>
                                    <td>$300</td>
                                    <td>$300</td>
                                    <td>
                                        <span class="badge badge-success d-inline-flex align-items-center badge-xs">
                                            <i class="ti ti-point-filled me-1"></i>Paid
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <label class="checkboxs">
                                            <input type="checkbox">
                                            <span class="checkmarks"></span>
                                        </label>
                                    </td>
                                    <td><a href="#">INV010</a></td>
                                    <td>Michelle Robison</td>
                                    <td>10 Sep 2024</td>
                                    <td>$5000</td>
                                    <td>$5000</td>
                                    <td>$5000</td>
                                    <td>
                                        <span class="badge badge-danger d-inline-flex align-items-center badge-xs">
                                            <i class="ti ti-point-filled me-1"></i>Unpaid
                                        </span>
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
