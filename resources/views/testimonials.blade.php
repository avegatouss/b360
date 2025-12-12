<?php $page = 'testimonials'; ?>
@extends('layout.mainlayout')
@section('content')
    <div class="page-wrapper">
        <div class="content">
            <div class="page-header">
                <div class="add-item d-flex">
                    <div class="page-title">
                        <h4>Testimonials</h4>
                        <h6>Manage your testimonials</h6>
                    </div>
                </div>
                <ul class="table-top-head">
                    <li class="me-2">
                        <a data-bs-toggle="tooltip" data-bs-placement="top" title="Pdf"><img src="{{URL::asset('build/img/icons/pdf.svg')}}" alt="img"></a>
                    </li>
                    <li class="me-2">
                        <a data-bs-toggle="tooltip" data-bs-placement="top" title="Excel"><img src="{{URL::asset('build/img/icons/excel.svg')}}" alt="img"></a>
                    </li>
                    <li class="me-2">
                        <a data-bs-toggle="tooltip" data-bs-placement="top" title="Refresh"><i class="ti ti-refresh"></i></a>
                    </li>
                    <li class="me-2">
                        <a data-bs-toggle="tooltip" data-bs-placement="top" title="Collapse" id="collapse-header"><i class="ti ti-chevron-up"></i></a>
                    </li>
                </ul>
                <div class="page-btn">
                    <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#add-testimonial"><i class="ti ti-circle-plus me-1"></i>Add Testimonial</a>
                </div>
            </div>
            <!-- product list -->
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
                                    <th>Author</th>
                                    <th>Role</th>
                                    <th>Content</th>
                                    <th>Ratings</th>
                                    <th>Created Date</th>
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
                                            <a href="#" class="avatar avatar-md"><img src="{{URL::asset('build/img/users/user-32.jpg')}}" class="img-fluid" alt="img"></a>
                                            <div class="ms-2">
                                                <p class="text-dark mb-0"><a href="#">Carl Evans</a></p>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        Manager
                                    </td>
                                    <td>
                                        This POS system has streamlined our operations and improved efficiency!							
                                    </td>
                                    <td>
                                        <div>
                                            <span><i class="ti ti-star-filled text-warning"></i></span>
                                            <span><i class="ti ti-star-filled text-warning"></i></span>
                                            <span><i class="ti ti-star-filled text-warning"></i></span>
                                            <span><i class="ti ti-star-filled text-warning"></i></span>
                                            <span><i class="ti ti-star-filled text-warning"></i></span>
                                        </div>
                                    </td>
                                    <td>
                                        24 Dec 2024
                                    </td>
                                    <td class="action-table-data">
                                        <div class="edit-delete-action">
                                            <a class="me-2 p-2" href="#" data-bs-toggle="modal" data-bs-target="#edit-testimonial">
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
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <a href="#" class="avatar avatar-md"><img src="{{URL::asset('build/img/users/user-02.jpg')}}" class="img-fluid" alt="img"></a>
                                            <div class="ms-2">
                                                <p class="text-dark mb-0"><a href="#">Minerva Rameriz</a></p>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        Salesman
                                    </td>
                                    <td>
                                        The POS system makes processing sales fast and effortless				
                                    </td>
                                    <td>
                                        <div>
                                            <span><i class="ti ti-star-filled text-warning"></i></span>
                                            <span><i class="ti ti-star-filled text-warning"></i></span>
                                            <span><i class="ti ti-star-filled text-warning"></i></span>
                                            <span><i class="ti ti-star-filled text-warning"></i></span>
                                            <span><i class="ti ti-star-filled text-warning"></i></span>
                                        </div>
                                    </td>
                                    <td>
                                        25 Dec 2024
                                    </td>
                                    <td class="action-table-data">
                                        <div class="edit-delete-action">
                                            <a class="me-2 p-2" href="#" data-bs-toggle="modal" data-bs-target="#edit-testimonial">
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
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <a href="#" class="avatar avatar-md"><img src="{{URL::asset('build/img/users/user-01.jpg')}}" class="img-fluid" alt="img"></a>
                                            <div class="ms-2">
                                                <p class="text-dark mb-0"><a href="#">Robert Lamon</a></p>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        Supervisor
                                    </td>
                                    <td>
                                        Easy to track sales and team performance in real-time					
                                    </td>
                                    <td>
                                        <div>
                                            <span><i class="ti ti-star-filled text-warning"></i></span>
                                            <span><i class="ti ti-star-filled text-warning"></i></span>
                                            <span><i class="ti ti-star-filled text-warning"></i></span>
                                            <span><i class="ti ti-star-filled text-warning"></i></span>
                                            <span><i class="ti ti-star-filled text-warning"></i></span>
                                        </div>
                                    </td>
                                    <td>
                                        24 Dec 2024
                                    </td>
                                    <td class="action-table-data">
                                        <div class="edit-delete-action">
                                            <a class="me-2 p-2" href="#" data-bs-toggle="modal" data-bs-target="#edit-testimonial">
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
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <a href="#" class="avatar avatar-md"><img src="{{URL::asset('build/img/users/user-04.jpg')}}" class="img-fluid" alt="img"></a>
                                            <div class="ms-2">
                                                <p class="text-dark mb-0"><a href="#">Patricia Lewis</a></p>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        Store Keeper
                                    </td>
                                    <td>
                                        This system saves me time by automating many inventory tasks					
                                    </td>
                                    <td>
                                        <div>
                                            <span><i class="ti ti-star-filled text-warning"></i></span>
                                            <span><i class="ti ti-star-filled text-warning"></i></span>
                                            <span><i class="ti ti-star-filled text-warning"></i></span>
                                            <span><i class="ti ti-star-filled text-warning"></i></span>
                                            <span><i class="ti ti-star-filled text-warning"></i></span>
                                        </div>
                                    </td>
                                    <td>
                                        27 Dec 2024
                                    </td>
                                    <td class="action-table-data">
                                        <div class="edit-delete-action">
                                            <a class="me-2 p-2" href="#" data-bs-toggle="modal" data-bs-target="#edit-testimonial">
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
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <a href="#" class="avatar avatar-md"><img src="{{URL::asset('build/img/users/user-08.jpg')}}" class="img-fluid" alt="img"></a>
                                            <div class="ms-2">
                                                <p class="text-dark mb-0"><a href="#">Mark Joslyn</a></p>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        Manager
                                    </td>
                                    <td>
                                        It’s easy to manage sales, inventory, and staff with this POS system!							
                                    </td>
                                    <td>
                                        <div>
                                            <span><i class="ti ti-star-filled text-warning"></i></span>
                                            <span><i class="ti ti-star-filled text-warning"></i></span>
                                            <span><i class="ti ti-star-filled text-warning"></i></span>
                                            <span><i class="ti ti-star-filled text-warning"></i></span>
                                            <span><i class="ti ti-star-filled text-warning"></i></span>
                                        </div>
                                    </td>
                                    <td>
                                        28 Dec 2024
                                    </td>
                                    <td class="action-table-data">
                                        <div class="edit-delete-action">
                                            <a class="me-2 p-2" href="#" data-bs-toggle="modal" data-bs-target="#edit-testimonial">
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
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <a href="#" class="avatar avatar-md"><img src="{{URL::asset('build/img/users/user-10.jpg')}}" class="img-fluid" alt="img"></a>
                                            <div class="ms-2">
                                                <p class="text-dark mb-0"><a href="#">Marsha Betts</a></p>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        Inventory Manager
                                    </td>
                                    <td>
                                        Real-time inventory updates make stock management a breeze				
                                    </td>
                                    <td>
                                        <div>
                                            <span><i class="ti ti-star-filled text-warning"></i></span>
                                            <span><i class="ti ti-star-filled text-warning"></i></span>
                                            <span><i class="ti ti-star-filled text-warning"></i></span>
                                            <span><i class="ti ti-star-filled text-warning"></i></span>
                                            <span><i class="ti ti-star-filled text-warning"></i></span>
                                        </div>
                                    </td>
                                    <td>
                                        30 Dec 2024
                                    </td>
                                    <td class="action-table-data">
                                        <div class="edit-delete-action">
                                            <a class="me-2 p-2" href="#" data-bs-toggle="modal" data-bs-target="#edit-testimonial">
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
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <a href="#" class="avatar avatar-md"><img src="{{URL::asset('build/img/users/user-28.jpg')}}" class="img-fluid" alt="img"></a>
                                            <div class="ms-2">
                                                <p class="text-dark mb-0"><a href="#">Daniel Jude</a></p>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        Delivery Biker
                                    </td>
                                    <td>
                                        POS integration makes tracking deliveries easy!					
                                    </td>
                                    <td>
                                        <div>
                                            <span><i class="ti ti-star-filled text-warning"></i></span>
                                            <span><i class="ti ti-star-filled text-warning"></i></span>
                                            <span><i class="ti ti-star-filled text-warning"></i></span>
                                            <span><i class="ti ti-star-filled text-warning"></i></span>
                                            <span><i class="ti ti-star-filled text-warning"></i></span>
                                        </div>
                                    </td>
                                    <td>
                                        14 Dec 2024
                                    </td>
                                    <td class="action-table-data">
                                        <div class="edit-delete-action">
                                            <a class="me-2 p-2" href="#" data-bs-toggle="modal" data-bs-target="#edit-testimonial">
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
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <a href="#" class="avatar avatar-md"><img src="{{URL::asset('build/img/users/user-17.jpg')}}" class="img-fluid" alt="img"></a>
                                            <div class="ms-2">
                                                <p class="text-dark mb-0"><a href="#">Emma Bates</a></p>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        Cashier
                                    </td>
                                    <td>
                                        Quick and easy to use - checkouts have never been faster!						
                                    </td>
                                    <td>
                                        <div>
                                            <span><i class="ti ti-star-filled text-warning"></i></span>
                                            <span><i class="ti ti-star-filled text-warning"></i></span>
                                            <span><i class="ti ti-star-filled text-warning"></i></span>
                                            <span><i class="ti ti-star-filled text-warning"></i></span>
                                            <span><i class="ti ti-star-filled text-warning"></i></span>
                                        </div>
                                    </td>
                                    <td>
                                        24 Dec 2024
                                    </td>
                                    <td class="action-table-data">
                                        <div class="edit-delete-action">
                                            <a class="me-2 p-2" href="#" data-bs-toggle="modal" data-bs-target="#edit-testimonial">
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
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <a href="#" class="avatar avatar-md"><img src="{{URL::asset('build/img/users/user-20.jpg')}}" class="img-fluid" alt="img"></a>
                                            <div class="ms-2">
                                                <p class="text-dark mb-0"><a href="#">Richard Fralick</a></p>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        Accountant
                                    </td>
                                    <td>
                                        Transaction tracking is simplified, making reconciliation faster.					
                                    </td>
                                    <td>
                                        <div>
                                            <span><i class="ti ti-star-filled text-warning"></i></span>
                                            <span><i class="ti ti-star-filled text-warning"></i></span>
                                            <span><i class="ti ti-star-filled text-warning"></i></span>
                                            <span><i class="ti ti-star-filled text-warning"></i></span>
                                            <span><i class="ti ti-star-filled text-warning"></i></span>
                                        </div>
                                    </td>
                                    <td>
                                        10 Dec 2024
                                    </td>
                                    <td class="action-table-data">
                                        <div class="edit-delete-action">
                                            <a class="me-2 p-2" href="#" data-bs-toggle="modal" data-bs-target="#edit-testimonial">
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
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <a href="#" class="avatar avatar-md"><img src="{{URL::asset('build/img/users/user-29.jpg')}}" class="img-fluid" alt="img"></a>
                                            <div class="ms-2">
                                                <p class="text-dark mb-0"><a href="#">Michelle Robison</a></p>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        Manager
                                    </td>
                                    <td>
                                        Our team is more organized, and customer checkouts are faster than ever.					
                                    </td>
                                    <td>
                                        <div>
                                            <span><i class="ti ti-star-filled text-warning"></i></span>
                                            <span><i class="ti ti-star-filled text-warning"></i></span>
                                            <span><i class="ti ti-star-filled text-warning"></i></span>
                                            <span><i class="ti ti-star-filled text-warning"></i></span>
                                            <span><i class="ti ti-star-filled text-warning"></i></span>
                                        </div>
                                    </td>
                                    <td>
                                        16 Dec 2024
                                    </td>
                                    <td class="action-table-data">
                                        <div class="edit-delete-action">
                                            <a class="me-2 p-2" href="#" data-bs-toggle="modal" data-bs-target="#edit-testimonial">
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
            <p class="mb-0">2014 - 2025 &copy; DreamsPOS. All Right Reserved</p>
            <p>Designed &amp; Developed by <a href="javascript:void(0);" class="text-primary">Dreams</a></p>
        </div>
    </div>
@endsection            