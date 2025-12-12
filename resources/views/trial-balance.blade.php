<?php $page = 'trial-balance'; ?>
@extends('layout.mainlayout')
@section('content')

<div class="page-wrapper">
  <div class="content">
    <div class="page-header">
      <div class="add-item d-flex">
        <div class="page-title">
          <h4 class="fw-bold">Trial Balance</h4>
          <h6>View Your Balance Sheet</h6>
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
        <div class="row align-items-end">
              <div class="col-sm-3">
              <div class="dropdown me-2">
                <label class="form-label">Choose Your Date</label>				
              <div class="input-groupicon calender-input balance-sheet-date">
              <i data-feather="calendar" class="info-img"></i><input type="text" class="datetimepicker w-100" placeholder="01-Jan-2025 - 12-Dec-2025">
              </div>
              </div>
            </div>
            <div class="col-sm-3">
              <div class="dropdown">
                <label class="form-label">Store</label>
                <a href="javascript:void(0);" class="dropdown-toggle btn btn-white justify-content-between btn-md w-100" data-bs-toggle="dropdown">
                  Select
                </a>
                <ul class="dropdown-menu  dropdown-menu-end p-3">
                  <li>
                    <a href="javascript:void(0);" class="dropdown-item rounded-1">Zephyr Indira</a>
                  </li>
                  <li>
                    <a href="javascript:void(0);" class="dropdown-item rounded-1">Quillon Elysia</a>
                  </li>										
                </ul>
              </div>
            </div>
            <div class="col-sm-3">
              <button class="btn btn-primary shadow-none">Submit</button>
            </div>
          </div>
      </div>
    </div>
          
    <div class="card">
      <div class="card-body">
        <div class="table-responsive">
          <table class="table border">
            <thead class="thead-light">
              <tr>						
                <th>Account Name</th>
                <th>Debit</th>
                                      <th>Credit</th>
              </tr>
            </thead>
            <tbody>
             
                                  <tr>
                                      <td class="fw-bold text-gray-9">Assets</td>
                                      <td></td>
                                      <td></td>
                                    </tr>  
                                    <tr>
                                      <td>Cash in register</td>
                                      <td>$5,000</td>
                                      <td></td>
                                    </tr>  
                                    <tr>
                                      <td>Bank Accounts</td>
                                      <td>$12,000</td>
                                      <td></td>
                                    </tr> 
                                    <tr>
                                      <td>Accounts Receivable</td>
                                      <td>$3,000</td>
                                      <td></td>
                                    </tr> 
                                    <tr>
                                      <td>Inventory (POS stock)</td>
                                      <td>$10,000</td>
                                      <td></td>
                                    </tr> 
                                    <tr class="border-bottom">
                                      <td class="fw-bold text-gray-9">Total Assets</td>
                                      <td class="fw-bold text-gray-9">$37,000</td>
                                      <td></td>
                                    </tr> 
                                    <tr>
                                      <td class="fw-bold text-gray-9">Liabilities</td>
                                      <td></td>
                                      <td></td>
                                    </tr> 
                                    <tr>
                                      <td>Accounts Payable</td>
                                      <td></td>
                                      <td>$2,000</td>
                                    </tr> 
                                    <tr>
                                      <td>Short-term Loans</td>
                                      <td></td>
                                      <td>$4,000</td>
                                    </tr> 
                                    <tr>
                                      <td>Sales Tax Payable</td>
                                      <td></td>
                                      <td>$500</td>
                                    </tr> 
                                    <tr>
                                      <td>Wages Payable</td>
                                      <td></td>
                                      <td>$1,200</td>
                                    </tr> 
                                    <tr>
                                      <td class="fw-bold text-gray-9">Total Assets</td>
                                      <td></td>
                                      <td class="fw-bold text-gray-9">$20,700</td>
                                    </tr> 
            </tbody>
            <tfoot>
              <td class="bg-light fw-bold p-3 fs-16">Total</td>
              <td class="bg-light fw-bold p-3 fs-16">$37,000</td>
              <td class="bg-light fw-bold p-3 fs-16">$37,000</td>
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