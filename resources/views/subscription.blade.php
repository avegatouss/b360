<?php $page = 'subscription'; ?>
@extends('layout.mainlayout')
@section('content')

<div class="page-wrapper">
	<div class="content">
		<div class="page-header">
			<div class="add-item d-flex">
				<div class="page-title">
					<h4>Subscriptions</h4>
					<h6>Manage your subscriptions</h6>
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

		<div class="row">
			<div class="col-xl-3 col-md-6 d-flex">
				<div class="card flex-fill">
					<div class="card-body ">
						<div class="border-bottom pb-3 mb-3">
							<div class="row align-items-center">
								<div class="col-7">
									<div>
										<p class="text-truncate mb-1">Total Transaction</p>
										<h5>$5,340</h5>
									</div>
								</div>
								<div class="col-5">
									<div class="peity-chart">
										<span class="subscription-line-1" data-width="100%">6,2,8,4,3,8,1,3,6,5,9,2,8,1,4,8,9,8,2,1</span>
									</div>
								</div>
							</div>
						</div>
						<div class="d-flex">
							<p class="d-flex align-items-center text-truncate">
								<span class="text-primary fs-12 d-flex align-items-center me-1">
								<i class="ti ti-arrow-wave-right-up me-1"></i>+19.01%</span>from
								last week
							</p>
						</div>
					</div>
				</div>
			</div>
			<div class="col-xl-3 col-md-6 d-flex">
				<div class="card flex-fill">
					<div class="card-body ">
						<div class="border-bottom pb-3 mb-3">
							<div class="row align-items-center">
								<div class="col-7">
									<div>
										<p class="text-truncate mb-1">Total Subscribers</p>
										<h5>600</h5>
									</div>
								</div>
								<div class="col-5">
									<div class="peity-chart">
										<span class="subscription-line-2" data-width="100%">6,2,8,4,3,8,1,3,6,5,9,2,8,1,4,8,9,8,2,1</span>
									</div>
								</div>
							</div>
						</div>
						<div class="d-flex">
							<p class="d-flex align-items-center text-truncate">
								<span class="text-primary fs-12 d-flex align-items-center me-1">
								<i class="ti ti-arrow-wave-right-up me-1"></i>+19.01%</span>from
								last week
							</p>
						</div>
					</div>
				</div>
			</div>
			<div class="col-xl-3 col-md-6 d-flex">
				<div class="card flex-fill">
					<div class="card-body ">
						<div class="border-bottom pb-3 mb-3">
							<div class="row align-items-center">
								<div class="col-7">
									<div>
										<p class="text-truncate mb-1">Active Subscribers</p>
										<h5>560</h5>
									</div>
								</div>
								<div class="col-5">
									<div class="peity-chart">
										<span class="subscription-line-3" data-width="100%">6,2,8,4,3,8,1,3,6,5,9,2,8,1,4,8,9,8,2,1</span>
									</div>
								</div>
							</div>
						</div>
						<div class="d-flex">
							<p class="d-flex align-items-center text-truncate">
								<span class="text-primary fs-12 d-flex align-items-center me-1">
								<i class="ti ti-arrow-wave-right-up me-1"></i>+19.01%</span>from
								last week
							</p>
						</div>
					</div>
				</div>
			</div>
			<div class="col-xl-3 col-md-6 d-flex">
				<div class="card flex-fill">
					<div class="card-body ">
						<div class="border-bottom pb-3 mb-3">
							<div class="row align-items-center">
								<div class="col-7">
									<div>
										<p class="text-truncate mb-1">Expired Subscribers</p>
										<h5>40</h5>
									</div>
								</div>
								<div class="col-5">
									<div class="peity-chart">
										<span class="subscription-line-4" data-width="100%">6,2,8,4,3,8,1,3,6,5,9,2,8,1,4,8,9,8,2,1</span>
									</div>
								</div>
							</div>
						</div>
						<div class="d-flex">
							<p class="d-flex align-items-center text-truncate">
								<span class="text-primary fs-12 d-flex align-items-center me-1">
								<i class="ti ti-arrow-wave-right-up me-1"></i>+19.01%</span>from
								last week
							</p>
						</div>
					</div>
				</div>
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
				<div class="d-flex my-xl-auto right-content align-items-center flex-wrap row-gap-3">
					<div class="dropdown me-2">
						<a href="javascript:void(0);" class="dropdown-toggle btn btn-white btn-md d-inline-flex align-items-center" data-bs-toggle="dropdown">
							Select Plan
						</a>
						<ul class="dropdown-menu  dropdown-menu-end p-3">
							<li>
								<a href="javascript:void(0);" class="dropdown-item rounded-1">Advanced (Monthly)</a>
							</li>
							<li>
								<a href="javascript:void(0);" class="dropdown-item rounded-1">Basic (Yearly)</a>
							</li>
							<li>
								<a href="javascript:void(0);" class="dropdown-item rounded-1">Enterprise (Monthly)</a>
							</li>
						</ul>
					</div>
					<div class="dropdown me-2">
						<a href="javascript:void(0);" class="dropdown-toggle btn btn-white btn-md d-inline-flex align-items-center" data-bs-toggle="dropdown">
							Status
						</a>
						<ul class="dropdown-menu  dropdown-menu-end p-3">
							<li>
								<a href="javascript:void(0);" class="dropdown-item rounded-1">Paid</a>
							</li>
							<li>
								<a href="javascript:void(0);" class="dropdown-item rounded-1">Unpaid</a>
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
									<div class="form-check form-check-md">
										<input class="form-check-input" type="checkbox" id="select-all">
									</div>
								</th>
								<th>Subscriber</th>
								<th>Plan</th>
								<th>Billing Cycle</th>
								<th>Payment Method</th>
								<th>Amount</th>
								<th>Created Date</th>
								<th>Expiring On</th>
								<th>Status</th>
								<th></th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td>
									<div class="form-check form-check-md">
										<input class="form-check-input" type="checkbox">
									</div>
								</td>
								<td>
									<div class="d-flex align-items-center file-name-icon">
										<a href="#" class="avatar avatar-md border rounded-circle">
											<img src="{{URL::asset('build/img/company/company-01.svg')}}" class="img-fluid" alt="img">
										</a>
										<div class="ms-2">
											<h6 class="fw-medium"><a href="#">BrightWave Innovations</a></h6>
										</div>
									</div>
								</td>
								<td>Advanced (Monthly)</td>
								<td>30 Days</td>
								<td>Credit Card</td>
								<td>$200</td>
								<td>12 Sep 2024</td>
								<td>11 Oct 2024</td>
								<td>
									<span class="badge badge-success d-flex align-items-center badge-xs">
										<i class="ti ti-point-filled me-1"></i>Paid
									</span>
								</td>
								<td>
									<div class="action-icon d-inline-flex">
										<a href="#" class="me-2 p-2 d-flex align-items-center border rounded" data-bs-toggle="modal" data-bs-target="#view_invoice"><i class="ti ti-file-invoice"></i></a>
										<a href="#" class="me-2 d-flex align-items-center border rounded p-2"><i class="ti ti-download"></i></a>
										<a href="#" data-bs-toggle="modal" data-bs-target="#delete_modal" class="d-flex align-items-center p-2 border rounded"><i class="ti ti-trash"></i></a>
									</div>
								</td>
							</tr>
							<tr>
								<td>
									<div class="form-check form-check-md">
										<input class="form-check-input" type="checkbox">
									</div>
								</td>
								<td>
									<div class="d-flex align-items-center file-name-icon">
										<a href="#" class="avatar avatar-md border rounded-circle">
											<img src="{{URL::asset('build/img/company/company-02.svg')}}" class="img-fluid" alt="img">
										</a>
										<div class="ms-2">
											<h6 class="fw-medium"><a href="#">Stellar Dynamics</a></h6>
										</div>
									</div>
								</td>
								<td>Basic (Yearly)</td>
								<td>365 Days</td>
								<td>Paypal</td>
								<td>$600</td>
								<td>24 Oct 2024</td>
								<td>23 Oct 2025</td>
								<td>
									<span class="badge badge-success d-flex align-items-center badge-xs">
										<i class="ti ti-point-filled me-1"></i>Paid
									</span>
								</td>
								<td>
									<div class="action-icon d-inline-flex">
										<a href="#" class="me-2 p-2 d-flex align-items-center border rounded" data-bs-toggle="modal" data-bs-target="#view_invoice"><i class="ti ti-file-invoice"></i></a>
										<a href="#" class="me-2 d-flex align-items-center border rounded p-2"><i class="ti ti-download"></i></a>
										<a href="#" data-bs-toggle="modal" data-bs-target="#delete_modal" class="d-flex align-items-center p-2 border rounded"><i class="ti ti-trash"></i></a>
									</div>
								</td>
							</tr>
							<tr>
								<td>
									<div class="form-check form-check-md">
										<input class="form-check-input" type="checkbox">
									</div>
								</td>
								<td>
									<div class="d-flex align-items-center file-name-icon">
										<a href="#" class="avatar avatar-md border rounded-circle">
											<img src="{{URL::asset('build/img/company/company-03.svg')}}" class="img-fluid" alt="img">
										</a>
										<div class="ms-2">
											<h6 class="fw-medium"><a href="#">Quantum Nexus</a></h6>
										</div>
									</div>
								</td>
								<td>Advanced (Monthly)</td>
								<td>30 Days</td>
								<td>Debit Card</td>
								<td>$200</td>
								<td>18 Feb 2024</td>
								<td>17 Mar 2024</td>
								<td>
									<span class="badge badge-success d-flex align-items-center badge-xs">
										<i class="ti ti-point-filled me-1"></i>Paid
									</span>
								</td>
								<td>
									<div class="action-icon d-inline-flex">
										<a href="#" class="me-2 p-2 d-flex align-items-center border rounded" data-bs-toggle="modal" data-bs-target="#view_invoice"><i class="ti ti-file-invoice"></i></a>
										<a href="#" class="me-2 d-flex align-items-center border rounded p-2"><i class="ti ti-download"></i></a>
										<a href="#" data-bs-toggle="modal" data-bs-target="#delete_modal" class="d-flex align-items-center p-2 border rounded"><i class="ti ti-trash"></i></a>
									</div>
								</td>
							</tr>
							<tr>
								<td>
									<div class="form-check form-check-md">
										<input class="form-check-input" type="checkbox">
									</div>
								</td>
								<td>
									<div class="d-flex align-items-center file-name-icon">
										<a href="#" class="avatar avatar-md border rounded-circle">
											<img src="{{URL::asset('build/img/company/company-04.svg')}}" class="img-fluid" alt="img">
										</a>
										<div class="ms-2">
											<h6 class="fw-medium"><a href="#">EcoVision Enterprises</a></h6>
										</div>
									</div>
								</td>
								<td>Advanced (Monthly)</td>
								<td>30 Days</td>
								<td>Paypal</td>
								<td>$200</td>
								<td>17 Oct 2024</td>
								<td>16 Nov 2024</td>
								<td>
									<span class="badge badge-success d-flex align-items-center badge-xs">
										<i class="ti ti-point-filled me-1"></i>Paid
									</span>
								</td>
								<td>
									<div class="action-icon d-inline-flex">
										<a href="#" class="me-2 p-2 d-flex align-items-center border rounded" data-bs-toggle="modal" data-bs-target="#view_invoice"><i class="ti ti-file-invoice"></i></a>
										<a href="#" class="me-2 d-flex align-items-center border rounded p-2"><i class="ti ti-download"></i></a>
										<a href="#" data-bs-toggle="modal" data-bs-target="#delete_modal" class="d-flex align-items-center p-2 border rounded"><i class="ti ti-trash"></i></a>
									</div>
								</td>
							</tr>
							<tr>
								<td>
									<div class="form-check form-check-md">
										<input class="form-check-input" type="checkbox">
									</div>
								</td>
								<td>
									<div class="d-flex align-items-center file-name-icon">
										<a href="#" class="avatar avatar-md border rounded-circle">
											<img src="{{URL::asset('build/img/company/company-05.svg')}}" class="img-fluid" alt="img">
										</a>
										<div class="ms-2">
											<h6 class="fw-medium"><a href="#">Aurora Technologies</a></h6>
										</div>
									</div>
								</td>
								<td>Enterprise (Monthly)</td>
								<td>30 Days</td>
								<td>Credit Card</td>
								<td>$400</td>
								<td>20 Jul 2024</td>
								<td>19 Aug 2024</td>
								<td>
									<span class="badge badge-success d-flex align-items-center badge-xs">
										<i class="ti ti-point-filled me-1"></i>Paid
									</span>
								</td>
								<td>
									<div class="action-icon d-inline-flex">
										<a href="#" class="me-2 p-2 d-flex align-items-center border rounded" data-bs-toggle="modal" data-bs-target="#view_invoice"><i class="ti ti-file-invoice"></i></a>
										<a href="#" class="me-2 d-flex align-items-center border rounded p-2"><i class="ti ti-download"></i></a>
										<a href="#" data-bs-toggle="modal" data-bs-target="#delete_modal" class="d-flex align-items-center p-2 border rounded"><i class="ti ti-trash"></i></a>
									</div>
								</td>
							</tr>
							<tr>
								<td>
									<div class="form-check form-check-md">
										<input class="form-check-input" type="checkbox">
									</div>
								</td>
								<td>
									<div class="d-flex align-items-center file-name-icon">
										<a href="#" class="avatar avatar-md border rounded-circle">
											<img src="{{URL::asset('build/img/company/company-06.svg')}}" class="img-fluid" alt="img">
										</a>
										<div class="ms-2">
											<h6 class="fw-medium"><a href="#">BlueSky Ventures</a></h6>
										</div>
									</div>
								</td>
								<td>Advanced (Monthly)</td>
								<td>30 Days</td>
								<td>Paypal</td>
								<td>$200</td>
								<td>10 Apr 2024</td>
								<td>19 Aug 2024</td>
								<td>
									<span class="badge badge-success d-flex align-items-center badge-xs">
										<i class="ti ti-point-filled me-1"></i>Paid
									</span>
								</td>
								<td>
									<div class="action-icon d-inline-flex">
										<a href="#" class="me-2 p-2 d-flex align-items-center border rounded" data-bs-toggle="modal" data-bs-target="#view_invoice"><i class="ti ti-file-invoice"></i></a>
										<a href="#" class="me-2 d-flex align-items-center border rounded p-2"><i class="ti ti-download"></i></a>
										<a href="#" data-bs-toggle="modal" data-bs-target="#delete_modal" class="d-flex align-items-center p-2 border rounded"><i class="ti ti-trash"></i></a>
									</div>
								</td>
							</tr>
							<tr>
								<td>
									<div class="form-check form-check-md">
										<input class="form-check-input" type="checkbox">
									</div>
								</td>
								<td>
									<div class="d-flex align-items-center file-name-icon">
										<a href="#" class="avatar avatar-md border rounded-circle">
											<img src="{{URL::asset('build/img/company/company-07.svg')}}" class="img-fluid" alt="img">
										</a>
										<div class="ms-2">
											<h6 class="fw-medium"><a href="#">TerraFusion Energy</a></h6>
										</div>
									</div>
								</td>
								<td>Enterprise (Yearly)</td>
								<td>365 Days</td>
								<td>Credit Card</td>
								<td>$4800</td>
								<td>29 Aug 2024</td>
								<td>28 Aug 2025</td>
								<td>
									<span class="badge badge-success d-flex align-items-center badge-xs">
										<i class="ti ti-point-filled me-1"></i>Paid
									</span>
								</td>
								<td>
									<div class="action-icon d-inline-flex">
										<a href="#" class="me-2 p-2 d-flex align-items-center border rounded" data-bs-toggle="modal" data-bs-target="#view_invoice"><i class="ti ti-file-invoice"></i></a>
										<a href="#" class="me-2 d-flex align-items-center border rounded p-2"><i class="ti ti-download"></i></a>
										<a href="#" data-bs-toggle="modal" data-bs-target="#delete_modal" class="d-flex align-items-center p-2 border rounded"><i class="ti ti-trash"></i></a>
									</div>
								</td>
							</tr>
							<tr>
								<td>
									<div class="form-check form-check-md">
										<input class="form-check-input" type="checkbox">
									</div>
								</td>
								<td>
									<div class="d-flex align-items-center file-name-icon">
										<a href="#" class="avatar avatar-md border rounded-circle">
											<img src="{{URL::asset('build/img/company/company-08.svg')}}" class="img-fluid" alt="img">
										</a>
										<div class="ms-2">
											<h6 class="fw-medium"><a href="#">UrbanPulse Design</a></h6>
										</div>
									</div>
								</td>
								<td>Basic (Monthly)</td>
								<td>30 Days</td>
								<td>Credit Card</td>
								<td>$50</td>
								<td>22 Feb 2024</td>
								<td>21 Mar 2024</td>
								<td>
									<span class="badge badge-danger d-flex align-items-center badge-xs">
										<i class="ti ti-point-filled me-1"></i>Unpaid
									</span>
								</td>
								<td>
									<div class="action-icon d-inline-flex">
										<a href="#" class="me-2 p-2 d-flex align-items-center border rounded" data-bs-toggle="modal" data-bs-target="#view_invoice"><i class="ti ti-file-invoice"></i></a>
										<a href="#" class="me-2 d-flex align-items-center border rounded p-2"><i class="ti ti-download"></i></a>
										<a href="#" data-bs-toggle="modal" data-bs-target="#delete_modal" class="d-flex align-items-center p-2 border rounded"><i class="ti ti-trash"></i></a>
									</div>
								</td>
							</tr>
							<tr>
								<td>
									<div class="form-check form-check-md">
										<input class="form-check-input" type="checkbox">
									</div>
								</td>
								<td>
									<div class="d-flex align-items-center file-name-icon">
										<a href="#" class="avatar avatar-md border rounded-circle">
											<img src="{{URL::asset('build/img/company/company-09.svg')}}" class="img-fluid" alt="img">
										</a>
										<div class="ms-2">
											<h6 class="fw-medium"><a href="#">Nimbus Networks</a></h6>
										</div>
									</div>
								</td>
								<td>Basic (Yearly)</td>
								<td>365 Days</td>
								<td>Paypal</td>
								<td>$600</td>
								<td>03 Nov 2024</td>
								<td>02 Nov 2025</td>
								<td>
									<span class="badge badge-success d-flex align-items-center badge-xs">
										<i class="ti ti-point-filled me-1"></i>Paid
									</span>
								</td>
								<td>
									<div class="action-icon d-inline-flex">
										<a href="#" class="me-2 p-2 d-flex align-items-center border rounded" data-bs-toggle="modal" data-bs-target="#view_invoice"><i class="ti ti-file-invoice"></i></a>
										<a href="#" class="me-2 d-flex align-items-center border rounded p-2"><i class="ti ti-download"></i></a>
										<a href="#" data-bs-toggle="modal" data-bs-target="#delete_modal" class="d-flex align-items-center p-2 border rounded"><i class="ti ti-trash"></i></a>
									</div>
								</td>
							</tr>
							<tr>
								<td>
									<div class="form-check form-check-md">
										<input class="form-check-input" type="checkbox">
									</div>
								</td>
								<td>
									<div class="d-flex align-items-center file-name-icon">
										<a href="#" class="avatar avatar-md border rounded-circle">
											<img src="{{URL::asset('build/img/company/company-10.svg')}}" class="img-fluid" alt="img">
										</a>
										<div class="ms-2">
											<h6 class="fw-medium"><a href="#">Epicurean Delights</a></h6>
										</div>
									</div>
								</td>
								<td>Advanced (Monthly)</td>
								<td>30 Days</td>
								<td>Credit Card</td>
								<td>$200</td>
								<td>17 Dec 2024</td>
								<td>16 Jan 2024</td>
								<td>
									<span class="badge badge-success dlign-items-center badge-xs">
										<i class="ti ti-point-filled me-1"></i>Paid
									</span>
								</td>
								<td>
									<div class="action-icon d-inline-flex">
										<a href="#" class="me-2 p-2 d-flex align-items-center border rounded" data-bs-toggle="modal" data-bs-target="#view_invoice"><i class="ti ti-file-invoice"></i></a>
										<a href="#" class="me-2 d-flex align-items-center border rounded p-2"><i class="ti ti-download"></i></a>
										<a href="#" data-bs-toggle="modal" data-bs-target="#delete_modal" class="d-flex align-items-center p-2 border rounded"><i class="ti ti-trash"></i></a>
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