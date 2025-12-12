<?php $page = 'reviews'; ?>
@extends('layout.mainlayout')
@section('content')
            <div class="page-wrapper">
				<div class="content">
					<div class="page-header">
						<div class="add-item d-flex">
							<div class="page-title">
								<h4 class="fw-bold">Review</h4>
								<h6>Manage your reviews</h6>
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
							<a href="javascript:void(0);" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#add-review"><i class="ti ti-circle-plus me-1"></i>Add Product</a>
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
										Rating
									</a>
									<ul class="dropdown-menu  dropdown-menu-end p-3">
										<li>
											<a href="javascript:void(0);" class="dropdown-item rounded-1">5 </a>
										</li>
										<li>
											<a href="javascript:void(0);" class="dropdown-item rounded-1">4</a>
										</li>
										<li>
											<a href="javascript:void(0);" class="dropdown-item rounded-1">3</a>
										</li>
										<li>
											<a href="javascript:void(0);" class="dropdown-item rounded-1">2</a>
										</li>
										<li>
											<a href="javascript:void(0);" class="dropdown-item rounded-1">1	</a>
										</li>
									</ul>
								</div>
								<div class="dropdown me-2">
									<a href="javascript:void(0);" class="dropdown-toggle btn btn-white btn-md d-inline-flex align-items-center" data-bs-toggle="dropdown">
										Status
									</a>
									<ul class="dropdown-menu  dropdown-menu-end p-3">
										<li>
											<a href="javascript:void(0);" class="dropdown-item rounded-1">Published</a>
										</li>
										<li>
											<a href="javascript:void(0);" class="dropdown-item rounded-1">Pending</a>
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
											<th>Product Name</th>
											<th>Author</th>
											<th>Review</th>
											<th>Ratings</th>
											<th>Date</th>
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
											<td>
												<div class="d-flex align-items-center">
													<a href="javascript:void(0);" class="avatar avatar-md me-2">
														<img src="{{URL::asset('build/img/products/stock-img-01.png')}}" alt="product">
													</a>
													<a href="javascript:void(0);">Lenovo IdeaPad 3 </a>
												</div>												
											</td>
											<td>
												<div class="d-flex align-items-center">
													<span class="avatar avatar-md me-2">
														<a href="javascript:void(0);">
															<img src="{{URL::asset('build/img/users/user-30.jpg')}}" alt="product">
														</a>
													</span>
													<a href="javascript:void(0);">James Kirwin</a>
												</div>
											</td>
											<td>Sleek design and excellent battery life</td>
											<td>
												<div class="d-flex align-items-center">
												<span><i class="ti ti-star-filled text-warning me-1"></i></span>
												<span><i class="ti ti-star-filled text-warning me-1"></i></span>
												<span><i class="ti ti-star-filled text-warning me-1"></i></span>
												<span><i class="ti ti-star-filled text-warning me-1"></i></span>
												<span><i class="ti ti-star-filled text-warning me-1"></i></span>
												</div>
											</td>
											<td>24 Dec 2024</td>
											<td>
												<span class="badge badge-success"><i class="ti ti-point-filled"></i>Published</span>
											</td>
											<td class="action-table-data">
												<div class="edit-delete-action">
													<a class="me-2 p-2" href="#" data-bs-toggle="modal" data-bs-target="#edit-review">
														<i data-feather="edit" class="feather-edit"></i>
													</a>
													<a class="p-2" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#delete_review">
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
											<td>
												<div class="d-flex align-items-center">
													<a href="javascript:void(0);" class="avatar avatar-md me-2">
														<img src="{{URL::asset('build/img/products/stock-img-06.png')}}" alt="product">
													</a>
													<a href="javascript:void(0);">Beats Pro</a>
												</div>												
											</td>
											<td>
												<div class="d-flex align-items-center">
													<span class="avatar avatar-md me-2">
													<a href="javascript:void(0);">
														<img src="{{URL::asset('build/img/users/user-13.jpg')}}" alt="product">
													</a>
												  </span>
														<a href="javascript:void(0);">Francis Chang</a>
												</div>
											</td>
											<td>Crystal-clear sound and deep bass</td>
											<td>
												<div class="d-flex align-items-center">
												<span><i class="ti ti-star-filled text-warning me-1"></i></span>
												<span><i class="ti ti-star-filled text-warning me-1"></i></span>
												<span><i class="ti ti-star-filled text-warning me-1"></i></span>
												<span><i class="ti ti-star-filled text-warning me-1"></i></span>
												<span><i class="ti ti-star-filled text-warning me-1"></i></span>
												</div>
											</td>
											<td>10 Dec 2024</td>
											<td>
												<span class="badge badge-success"><i class="ti ti-point-filled"></i>Published</span>
											</td>
											<td class="action-table-data">
												<div class="edit-delete-action">
													<a class="me-2 p-2" href="#" data-bs-toggle="modal" data-bs-target="#edit-review">
														<i data-feather="edit" class="feather-edit"></i>
													</a>
													<a class="p-2" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#delete_review">
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
											<td>
												<div class="d-flex align-items-center">
													<a href="javascript:void(0);" class="avatar avatar-md me-2">
														<img src="{{URL::asset('build/img/products/stock-img-02.png')}}" alt="product">
													</a>
													<a href="javascript:void(0);">Nike Jordan</a>
												</div>												
											</td>
											<td>
												<div class="d-flex align-items-center">
													<span class="avatar avatar-md me-2">
													<a href="javascript:void(0);">
														<img src="{{URL::asset('build/img/users/user-11.jpg')}}" alt="product">
													</a>
												</span>
														<a href="javascript:void(0);">Antonio Engle</a>
												</div>
											</td>
											<td>Stylish, durable, and incredibly comfortable</td>
											<td>
												<div class="d-flex align-items-center">
												<span><i class="ti ti-star-filled text-warning me-1"></i></span>
												<span><i class="ti ti-star-filled text-warning me-1"></i></span>
												<span><i class="ti ti-star-filled text-warning me-1"></i></span>
												<span><i class="ti ti-star-filled text-warning me-1"></i></span>
												<span><i class="ti ti-star-filled text-warning me-1"></i></span>
												</div>
											</td>
											<td>27 Nov 2024</td>
											<td>
												<span class="badge badge-success"><i class="ti ti-point-filled"></i>Published</span>
											</td>
											<td class="action-table-data">
												<div class="edit-delete-action">
													<a class="me-2 p-2" href="#" data-bs-toggle="modal" data-bs-target="#edit-review">
														<i data-feather="edit" class="feather-edit"></i>
													</a>
													<a class="p-2" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#delete_review">
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
											<td>
												<div class="d-flex align-items-center">
													<a href="javascript:void(0);" class="avatar avatar-md me-2">
														<img src="{{URL::asset('build/img/products/stock-img-03.png')}}" alt="product">
													</a>
													<a href="javascript:void(0);">Apple Series 5 Watch</a>
												</div>												
											</td>
											<td>
												<div class="d-flex align-items-center">
													<span class="avatar avatar-md me-2">
													<a href="javascript:void(0);">
														<img src="{{URL::asset('build/img/users/user-32.jpg')}}" alt="product">
													</a>
												</span>
														<a href="javascript:void(0);">Leo Kelly</a>
												</div>
											</td>
											<td>Ultimate fitness and lifestyle companion!</td>
											<td>
												<div class="d-flex align-items-center">
												<span><i class="ti ti-star-filled text-warning me-1"></i></span>
												<span><i class="ti ti-star-filled text-warning me-1"></i></span>
												<span><i class="ti ti-star-filled text-warning me-1"></i></span>
												<span><i class="ti ti-star-filled text-warning me-1"></i></span>
												<span><i class="ti ti-star-filled text-warning me-1"></i></span>
												</div>
											</td>
											<td>18 Nov 2024</td>
											<td>
												<span class="badge badge-success"><i class="ti ti-point-filled"></i>Published</span>
											</td>
											<td class="action-table-data">
												<div class="edit-delete-action">
													<a class="me-2 p-2" href="#" data-bs-toggle="modal" data-bs-target="#edit-review">
														<i data-feather="edit" class="feather-edit"></i>
													</a>
													<a class="p-2" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#delete_review">
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
											<td>
												<div class="d-flex align-items-center">
													<a href="javascript:void(0);" class="avatar avatar-md me-2">
														<img src="{{URL::asset('build/img/products/stock-img-04.png')}}" alt="product">
													</a>
													<a href="javascript:void(0);">Amazon Echo Dot</a>
												</div>												
											</td>
											<td>
												<div class="d-flex align-items-center">
													<span class="avatar avatar-md me-2">
													<a href="javascript:void(0);">
														<img src="{{URL::asset('build/img/users/user-02.jpg')}}" alt="product">
													</a>
												</span>
														<a href="javascript:void(0);">Annette Walker</a>
												</div>
											</td>
											<td>Perfect voice-controlled home assistant!</td>
											<td>
												<div class="d-flex align-items-center">
												<span><i class="ti ti-star-filled text-warning me-1"></i></span>
												<span><i class="ti ti-star-filled text-warning me-1"></i></span>
												<span><i class="ti ti-star-filled text-warning me-1"></i></span>
												<span><i class="ti ti-star-filled text-warning me-1"></i></span>
												<span><i class="ti ti-star-filled text-warning me-1"></i></span>
												</div>
											</td>
											<td>06 Nov 2024</td>
											<td>
												<span class="badge badge-success"><i class="ti ti-point-filled"></i>Published</span>
											</td>
											<td class="action-table-data">
												<div class="edit-delete-action">
													<a class="me-2 p-2" href="#" data-bs-toggle="modal" data-bs-target="#edit-review">
														<i data-feather="edit" class="feather-edit"></i>
													</a>
													<a class="p-2" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#delete_review">
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
											<td>
												<div class="d-flex align-items-center">
													<a href="javascript:void(0);" class="avatar avatar-md me-2">
														<img src="{{URL::asset('build/img/products/stock-img-05.png')}}" alt="product">
													</a>
													<a href="javascript:void(0);">Sanford Chair Sofa</a>
												</div>												
											</td>
											<td>
												<div class="d-flex align-items-center">
													<span class="avatar avatar-md me-2">
													<a href="javascript:void(0);">
														<img src="{{URL::asset('build/img/users/user-05.jpg')}}" alt="product">
													</a>
												</span>
														<a href="javascript:void(0);">John Weaver</a>
												</div>
											</td>
											<td>Elegant design and superb comfort</td>
											<td>
												<div class="d-flex align-items-center">
												<span><i class="ti ti-star-filled text-warning me-1"></i></span>
												<span><i class="ti ti-star-filled text-warning me-1"></i></span>
												<span><i class="ti ti-star-filled text-warning me-1"></i></span>
												<span><i class="ti ti-star-filled text-warning me-1"></i></span>
												<span><i class="ti ti-star-filled text-warning me-1"></i></span>
												</div>
											</td>
											<td>25 Oct 2024</td>
											<td>
												<span class="badge badge-success"><i class="ti ti-point-filled"></i>Published</span>
											</td>
											<td class="action-table-data">
												<div class="edit-delete-action">
													<a class="me-2 p-2" href="#" data-bs-toggle="modal" data-bs-target="#edit-review">
														<i data-feather="edit" class="feather-edit"></i>
													</a>
													<a class="p-2" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#delete_review">
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
											<td>
												<div class="d-flex align-items-center">
													<a href="javascript:void(0);" class="avatar avatar-md me-2">
														<img src="{{URL::asset('build/img/products/expire-product-01.png')}}" alt="product">
													</a>
													<a href="javascript:void(0);">Red Premium Satchel</a>
												</div>												
											</td>
											<td>
												<div class="d-flex align-items-center">
													<span class="avatar avatar-md me-2">
													<a href="javascript:void(0);">
														<img src="{{URL::asset('build/img/users/user-08.jpg')}}" alt="product">
													</a>
											    	</span>
														<a href="javascript:void(0);">Gary Hennessy</a>
												</div>
											</td>
											<td>perfect for work, travel, and everyday use</td>
											<td>
												<div class="d-flex align-items-center">
												<span><i class="ti ti-star-filled text-warning me-1"></i></span>
												<span><i class="ti ti-star-filled text-warning me-1"></i></span>
												<span><i class="ti ti-star-filled text-warning me-1"></i></span>
												<span><i class="ti ti-star-filled text-warning me-1"></i></span>
												<span><i class="ti ti-star-filled text-warning me-1"></i></span>
												</div>
											</td>
											<td>14 Oct 2024</td>
											<td>
												<span class="badge badge-success"><i class="ti ti-point-filled"></i>Published</span>
											</td>
											<td class="action-table-data">
												<div class="edit-delete-action">
													<a class="me-2 p-2" href="#" data-bs-toggle="modal" data-bs-target="#edit-review">
														<i data-feather="edit" class="feather-edit"></i>
													</a>
													<a class="p-2" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#delete_review">
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
											<td>
												<div class="d-flex align-items-center">
													<a href="javascript:void(0);" class="avatar avatar-md me-2">
														<img src="{{URL::asset('build/img/products/expire-product-02.png')}}" alt="product">
													</a>
													<a href="javascript:void(0);">Iphone 14 Pro</a>
												</div>												
											</td>
											<td>
												<div class="d-flex align-items-center">
													<span class="avatar avatar-md me-2">
													<a href="javascript:void(0);">
														<img src="{{URL::asset('build/img/users/user-04.jpg')}}" alt="product">
													</a>
												</span>
														<a href="javascript:void(0);">Eleanor Panek</a>
												</div>
											</td>
											<td>Powerful performance and stunning display</td>
											<td>
												<div class="d-flex align-items-center">
												<span><i class="ti ti-star-filled text-warning me-1"></i></span>
												<span><i class="ti ti-star-filled text-warning me-1"></i></span>
												<span><i class="ti ti-star-filled text-warning me-1"></i></span>
												<span><i class="ti ti-star-filled text-warning me-1"></i></span>
												<span><i class="ti ti-star-filled text-warning me-1"></i></span>
												</div>
											</td>
											<td>03 Oct 2024</td>
											<td>
												<span class="badge badge-success"><i class="ti ti-point-filled"></i>Published</span>
											</td>
											<td class="action-table-data">
												<div class="edit-delete-action">
													<a class="me-2 p-2" href="#" data-bs-toggle="modal" data-bs-target="#edit-review">
														<i data-feather="edit" class="feather-edit"></i>
													</a>
													<a class="p-2" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#delete_review">
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
											<td>
												<div class="d-flex align-items-center">
													<a href="javascript:void(0);" class="avatar avatar-md me-2">
														<img src="{{URL::asset('build/img/products/expire-product-03.png')}}" alt="product">
													</a>
													<a href="javascript:void(0);">Gaming Chair</a>
												</div>												
											</td>
											<td>
												<div class="d-flex align-items-center">
													<span class="avatar avatar-md me-2">
													<a href="javascript:void(0);">
														<img src="{{URL::asset('build/img/users/user-09.jpg')}}" alt="product">
													</a>
												</span>
														<a href="javascript:void(0);">William Levy</a>
												</div>
											</td>
											<td>Ergonomic and incredibly comfortable</td>
											<td>
												<div class="d-flex align-items-center">
												<span><i class="ti ti-star-filled text-warning me-1"></i></span>
												<span><i class="ti ti-star-filled text-warning me-1"></i></span>
												<span><i class="ti ti-star-filled text-warning me-1"></i></span>
												<span><i class="ti ti-star-filled text-warning me-1"></i></span>
												<span><i class="ti ti-star-filled text-warning me-1"></i></span>
												</div>
											</td>
											<td>20 Sep 2024</td>
											<td>
												<span class="badge badge-success"><i class="ti ti-point-filled"></i>Published</span>
											</td>
											<td class="action-table-data">
												<div class="edit-delete-action">
													<a class="me-2 p-2" href="#" data-bs-toggle="modal" data-bs-target="#edit-review">
														<i data-feather="edit" class="feather-edit"></i>
													</a>
													<a class="p-2" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#delete_review">
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
											<td>
												<div class="d-flex align-items-center">
													<a href="javascript:void(0);" class="avatar avatar-md me-2">
														<img src="{{URL::asset('build/img/products/expire-product-04.png')}}" alt="product">
													</a>
													<a href="javascript:void(0);">Borealis Backpack</a>
												</div>												
											</td>
											<td>
												<div class="d-flex align-items-center">
													<span class="avatar avatar-md me-2">
													<a href="javascript:void(0);">
														<img src="{{URL::asset('build/img/users/user-10.jpg')}}" alt="product">
													</a>
												</span>
														<a href="javascript:void(0);">Charlotte Klotz</a>
												</div>
											</td>
											<td>Perfect for work, school and travel!</td>
											<td>
												<div class="d-flex align-items-center">
												<span><i class="ti ti-star-filled text-warning me-1"></i></span>
												<span><i class="ti ti-star-filled text-warning me-1"></i></span>
												<span><i class="ti ti-star-filled text-warning me-1"></i></span>
												<span><i class="ti ti-star-filled text-warning me-1"></i></span>
												<span><i class="ti ti-star-filled text-warning me-1"></i></span>
												</div>
											</td>
											<td>10 Sep 2024</td>
											<td>
												<span class="badge badge-success"><i class="ti ti-point-filled"></i>Published</span>
											</td>
											<td class="action-table-data">
												<div class="edit-delete-action">
													<a class="me-2 p-2" href="#" data-bs-toggle="modal" data-bs-target="#edit-review">
														<i data-feather="edit" class="feather-edit"></i>
													</a>
													<a class="p-2" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#delete_review">
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
											<td>
												<div class="d-flex align-items-center">
													<a href="javascript:void(0);" class="avatar avatar-md me-2">
														<img src="{{URL::asset('build/img/products/expire-product-04.png')}}" alt="product">
													</a>
													<a href="javascript:void(0);">Borealis Backpack</a>
												</div>												
											</td>
											<td>
												<div class="d-flex align-items-center">
													<span class="avatar avatar-md me-2">
													<a href="javascript:void(0);">
														<img src="{{URL::asset('build/img/users/user-10.jpg')}}" alt="product">
													</a>
												</span>
														<a href="javascript:void(0);">Charlotte Klotz</a>
												</div>
											</td>
											<td>perfect for work, travel, and everyday use</td>
											<td>
												<div class="d-flex align-items-center">
												<span><i class="ti ti-star-filled text-warning me-1"></i></span>
												<span><i class="ti ti-star-filled text-warning me-1"></i></span>
												<span><i class="ti ti-star-filled text-warning me-1"></i></span>
												<span><i class="ti ti-star-filled text-warning me-1"></i></span>
												<span><i class="ti ti-star-filled text-warning me-1"></i></span>
												</div>
											</td>
											<td>03 Oct 2024</td>
											<td>
												<span class="badge badge-success"><i class="ti ti-point-filled"></i>Published</span>
											</td>
											<td class="action-table-data">
												<div class="edit-delete-action">
													<a class="me-2 p-2" href="#" data-bs-toggle="modal" data-bs-target="#edit-review">
														<i data-feather="edit" class="feather-edit"></i>
													</a>
													<a class="p-2" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#delete_review">
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