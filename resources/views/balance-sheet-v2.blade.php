<?php $page = 'balance-sheet-v2'; ?>
@extends('layout.mainlayout')
@section('content')
   
<div class="page-wrapper">
    <div class="content">
        <div class="page-header">
            <div class="add-item d-flex">
                <div class="page-title">
                    <h4 class="fw-bold">Balance Sheet</h4>
                    <h6>View Your Balance Sheet </h6>
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
        </div>
        
        <div class="card">
            <div class="card-body">
                <div class="row row-gap-2 align-items-end">
                      <div class="col-md-3">
                        <label class="form-label">Choose Your Date</label>				
                        <div class="input-groupicon calender-input balance-sheet-date">
                            <i data-feather="calendar" class="info-img"></i><input type="text" class="datetimepicker w-100" placeholder="01-Jan-2025 - 12-Dec-2025">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Store</label>
                        <div class="dropdown">
                            <button class="btn btn-white dropdown-toggle w-100" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                All Store
                            </button>
                            <ul class="dropdown-menu p-2">
                                <li><a class="dropdown-item" href="#">Store 1</a></li>
                                <li><a class="dropdown-item" href="#">Store 2</a></li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <button class="btn btn-primary">Submit</button>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h4>Jan 2025 - Balance Sheet</h4>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table">
                        <thead class="thead-light">
                            <tr>						
                                <th>Assets</th>
                                <th>Amount</th>
                                <th>Liabilities & Equity</th>
                                <th>Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                              <td class="text-gray-9 fw-bold" colspan="2">Current Assets</td>
                              <td class="text-gray-9 fw-bold" colspan="2">Current Liabilities</td>
                            </tr>   
                            <tr>
                                <td>Cash in register</td>
                                <td>$4565</td>
                                <td>Cash in register</td>
                                <td>$4565</td>
                              </tr>  
                              <tr>
                                <td>Bank Accounts</td>
                                <td>$4494</td>
                                <td>Bank Accounts</td>
                                <td>$4494</td>
                              </tr>
                              <tr>
                                <td>Accounts Receivable</td>
                                <td>$65945</td>
                                <td>Accounts Receivable</td>
                                <td>$65945</td>
                              </tr>
                              <tr>
                                <td>Inventory (POS stock)</td>
                                <td>$1948</td>
                                <td>Inventory (POS stock)</td>
                                <td>$1948</td>
                              </tr>
                              <tr>
                                <td>Prepaid Expenses</td>
                                <td>$1686</td>
                                <td>Prepaid Expenses</td>
                                <td>$1686</td>
                              </tr>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td class="bg-light fw-bold p-3 fs-16">Total Current Assets</td>
                                <td class="bg-light fw-bold p-3 fs-16">$31,000</td>
                                <td class="bg-light fw-bold p-3 fs-16">Total Liability</td>
                                <td class="bg-light fw-bold p-3 fs-16">$78,000</td>
                            </tr>
                        </tfoot>
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