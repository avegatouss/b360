<?php $page = 'cash-flow'; ?>
@extends('layout.mainlayout')
@section('content')
            <div class="page-wrapper">
				<div class="content">
					<div class="page-header">
						<div class="add-item d-flex">
							<div class="page-title">
								<h4 class="fw-bold">Cash Flow</h4>
								<h6>View Your Cashflows </h6>
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
					
					<!-- /product list -->
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
										Payment Method
									</a>
									<ul class="dropdown-menu  dropdown-menu-end p-3">
										<li>
											<a href="javascript:void(0);" class="dropdown-item rounded-1">Stripe</a>
										</li>
										<li>
											<a href="javascript:void(0);" class="dropdown-item rounded-1">Cash</a>
										</li>	
										<li>
											<a href="javascript:void(0);" class="dropdown-item rounded-1">Paypal</a>
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
											<th>Date</th>
											<th>Bank & Account Number</th>
                                            <th>Description</th>
                                            <th>Credit</th>
                                            <th>Debit</th>
                                            <th>Account balance<i data-bs-toggle="tooltip" data-bs-placement="top" title="Total balance of the Particular account" class="ti ti-info-square-rounded-filled fs-14 fw-medium ms-1"></i></th>
                                            <th>Total Balance<i data-bs-toggle="tooltip" data-bs-placement="top" title="Total balance of all the Accounts" class="ti ti-info-square-rounded-filled fs-14 fw-medium ms-1"></i></th>
                                            <th>Payment Method</th>
                                        </tr>
									</thead>
									<tbody>
										<tr>
	                                      <td>24 Dec 2024</td>
                                          <td>HBSC - 3298784309485</td>
                                          <td>Cash receipts from sales</td>
                                          <td>$1000</td>
                                          <td>$0.00</td>
                                          <td>$1000</td>
                                          <td>$889898</td>
                                          <td>Stripe</td>
										</tr>   
                                        <tr>
                                            <td>10 Dec 2024</td>
                                            <td>SWIZ - 5475878970090</td>
                                            <td>Cash payments to employees</td>
                                            <td>$0.00</td>
                                            <td>$1500</td>
                                            <td>$1500</td>
                                            <td>$9899</td>
                                            <td>Paypal</td>
                                          </tr> 
										  <tr>
                                            <td>27 Nov 2024</td>
                                            <td>SWIZ - 3255465758698</td>
                                            <td>Purchase of POS equipment</td>
                                            <td>$1800</td>
                                            <td>$0.00</td>
                                            <td>$1800</td>
                                            <td>$35656</td>
                                            <td>Cash</td>
                                          </tr> 
										  <tr>
                                            <td>18 Nov 2024</td>
                                            <td>IBO - 4353689870544</td>
                                            <td>Sale of old equipment</td>
                                            <td>$1000</td>
                                            <td>$1000</td>
                                            <td>$1000</td>
                                            <td>$1562</td>
                                            <td>Paypal</td>
                                          </tr> 
										  <tr>
                                            <td>06 Nov 2024</td>
                                            <td>NBC - 4324356677889</td>
                                            <td>Loan received (short-term)</td>
                                            <td>$800</td>
                                            <td>$0.00</td>
                                            <td>$800</td>
                                            <td>$6896</td>
                                            <td>Cash</td>
                                          </tr> 
										  <tr>
                                            <td>25 Oct 2024</td>
                                            <td>NBC - 2343547586900</td>
                                            <td>Repayment of long-term loan</td>
                                            <td>$0.00</td>
                                            <td>$750</td>
                                            <td>$0.00</td>
                                            <td>$8963</td>
                                            <td>Cash</td>
                                          </tr>
										  <tr>
                                            <td>14 Oct 2024</td>
                                            <td>IBO - 3453647664889</td>
                                            <td>Owner’s equity contribution</td>
                                            <td>$1300</td>
                                            <td>$0.00</td>
                                            <td>$1300</td>
                                            <td>$4568</td>
                                            <td>Paypal</td>
                                          </tr>
										  <tr>
                                            <td>03 Oct 2024</td>
                                            <td>SWIZ - 3354456565687</td>
                                            <td>Cash payments for operating </td>
                                            <td>$1100</td>
                                            <td>$0.00</td>
                                            <td>$1100</td>
                                            <td>$5899</td>
                                            <td>Stripe</td>
                                          </tr>
										  <tr>
                                            <td>20 Sep 2024</td>
                                            <td>SWIZ - 3456565767787</td>
                                            <td>Cash payments to suppliers</td>
                                            <td>$2300</td>
                                            <td>$0.00</td>
                                            <td>$2300</td>
                                            <td>$4568</td>
                                            <td>Stripe</td>
                                          </tr>
										  <tr>
                                            <td>10 Sep 2024</td>
                                            <td>IBO - 3434565776768</td>
                                            <td>Cash receipts from sales</td>
                                            <td>$1700</td>
                                            <td>$0.00</td>
                                            <td>$1700</td>
                                            <td>$4568</td>
                                            <td>Cash</td>
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