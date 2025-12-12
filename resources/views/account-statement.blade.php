<?php $page = 'account-statement'; ?>
@extends('layout.mainlayout')
@section('content')
<div class="page-wrapper">
    <div class="content">
        <div class="page-header">
            <div class="add-item d-flex">
                <div class="page-title">
                    <h4 class="fw-bold">Account Statement</h4>
                    <h6>View Your Statement</h6>
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
                            <label class="form-label">Account</label>
                            <div class="dropdown">
                                <button class="btn btn-white dropdown-toggle justify-content-between w-100" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    Select
                                </button>
                                <ul class="dropdown-menu p-2">
                                    <li><a class="dropdown-item" href="#">Zephyr Indira</a></li>
                                    <li><a class="dropdown-item" href="#">Quillon Elysia</a></li>
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
                    <h4>Statement of Account : <span class="badge bg-soft-primary">HBSC - 3298784309485</span></h4>
                </div>
                <div class="table-responsive">
                    <table class="table	">
                        <thead class="thead-light">
                            <tr>						
                                <th>Reference Number</th>
                                <th>Date</th>
                                <th>Category</th>
                                <th>Description</th>
                                <th>Amount</th>
                                <th>Transaction Type</th>
                                <th>Balance</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="p-3">#AS842</td>
                                <td class="p-3">24 Dec 2024</td>
                                <td class="p-3">Sale</td>
                                <td class="p-3">Sale of goods</td>
                                <td class="p-3">+$200</td>
                                <td class="p-3"><span class="d-inline-flex align-items-center p-1 pe-2 rounded-1 text-white bg-success fs-10"><i class="ti ti-point-filled me-1 fs-11"></i>Credit</span></td>
                                <td class="p-3">$4365</td>
                            </tr>  
                            <tr>
                                <td class="p-3">#AS821</td>
                                <td class="p-3">10 Dec 2024</td>
                                <td class="p-3">Refund</td>
                                <td class="p-3">Refund Issued </td>
                                <td class="p-3">-$50</td>
                                <td class="p-3"><span class="d-inline-flex align-items-center p-1 pe-2 rounded-1 text-white bg-danger fs-10"><i class="ti ti-point-filled me-1 fs-11"></i>Debit</span></td>
                                <td class="p-3">$4444</td>
                            </tr>  
                            <tr>
                                <td class="p-3">#AS847</td>
                                <td class="p-3">27 Nov 2024</td>
                                <td class="p-3">Purchase</td>
                                <td class="p-3">Inventory restocking </td>
                                <td class="p-3">-$800</td>
                                <td class="p-3"><span class="d-inline-flex align-items-center p-1 pe-2 rounded-1 text-white bg-danger fs-10"><i class="ti ti-point-filled me-1 fs-11"></i>Debit</span></td>
                                <td class="p-3">$65145</td>
                            </tr> 
                            <tr>
                                <td class="p-3">#AS874</td>
                                <td class="p-3">18 Nov 2024</td>
                                <td class="p-3">Sale</td>
                                <td class="p-3">Sale of goods </td>
                                <td class="p-3">+$100</td>
                                <td class="p-3"><span class="d-inline-flex align-items-center p-1 pe-2 rounded-1 text-white bg-success fs-10"><i class="ti ti-point-filled me-1 fs-11"></i>Credit</span></td>
                                <td class="p-3">$1848</td>
                            </tr> 
                            <tr>
                                <td class="p-3">#AS887</td>
                                <td class="p-3">06 Nov 2024</td>
                                <td class="p-3">Purchase</td>
                                <td class="p-3">Inventory restocking </td>
                                <td class="p-3">-$700</td>
                                <td class="p-3"><span class="d-inline-flex align-items-center p-1 pe-2 rounded-1 text-white bg-danger fs-10"><i class="ti ti-point-filled me-1 fs-11"></i>Debit</span></td>
                                <td class="p-3">$986</td>
                            </tr> 
                            <tr>
                                <td class="p-3">#AS856</td>
                                <td class="p-3">25 Oct 2024</td>
                                <td class="p-3">Utility Payment</td>
                                <td class="p-3">Electricity Bill</td>
                                <td class="p-3">-$1000</td>
                                <td class="p-3"><span class="d-inline-flex align-items-center p-1 pe-2 rounded-1 text-white bg-danger fs-10"><i class="ti ti-point-filled me-1 fs-11"></i>Debit</span></td>
                                <td class="p-3">$15547</td>
                            </tr> 
                            <tr>
                                <td class="p-3">#AS822</td>
                                <td class="p-3">14 Oct 2024</td>
                                <td class="p-3">Equipment Purchase</td>
                                <td class="p-3">New POS terminal purchased</td>
                                <td class="p-3">-$1200</td>
                                <td class="p-3"><span class="d-inline-flex align-items-center p-1 pe-2 rounded-1 text-white bg-danger fs-10"><i class="ti ti-point-filled me-1 fs-11"></i>Debit</span></td>
                                <td class="p-3">$141645</td>
                            </tr>
                            <tr>
                                <td class="p-3">#AS844</td>
                                <td class="p-3">03 Oct 2024</td>
                                <td class="p-3">Refund</td>
                                <td class="p-3">Refund Issued</td>
                                <td class="p-3">-$750</td>
                                <td class="p-3"><span class="d-inline-flex align-items-center p-1 pe-2 rounded-1 text-white bg-danger fs-10"><i class="ti ti-point-filled me-1 fs-11"></i>Debit</span></td>
                                <td class="p-3">$4356</td>
                            </tr>
                            <tr>
                                <td class="p-3">#AS832</td>
                                <td class="p-3">20 Sep 2024</td>
                                <td class="p-3">Withdraw</td>
                                <td class="p-3">Withdraw by accountant</td>
                                <td class="p-3">-$450</td>
                                <td class="p-3"><span class="d-inline-flex align-items-center p-1 pe-2 rounded-1 text-white bg-danger fs-10"><i class="ti ti-point-filled me-1 fs-11"></i>Debit</span></td>
                                <td class="p-3">614389</td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td class="bg-light fw-bold p-3 fs-16" colspan="6">Total</td>
                                <td class="bg-light fw-bold p-3 fs-16">$33268.53</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
    </div>
    <div class="footer d-sm-flex align-items-center justify-content-between border-top bg-white p-3">
        <p class="mb-0 text-gray-9">2014 - 2025 &copy; DreamsPOS. All Right Reserved</p>
        <p>Designed &amp; Developed by <a href="javascript:void(0);" class="text-primary">Dreams</a></p>
    </div>
</div>  

@endsection 
