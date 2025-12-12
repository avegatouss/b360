<?php $page = 'blog-comments'; ?>
@extends('layout.mainlayout')
@section('content')
    <div class="page-wrapper">
        <div class="content">
            <div class="page-header">
                <div class="add-item d-flex">
                    <div class="page-title">
                        <h4 class="fw-bold">Blog Comments</h4>
                        <h6>Manage your blog tags</h6>
                    </div>
                </div>
                <ul class="table-top-head">
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
                                Sort By : Latest
                            </a>
                            <ul class="dropdown-menu  dropdown-menu-end p-3">
                                <li>
                                    <a href="javascript:void(0);" class="dropdown-item rounded-1">Latest</a>
                                </li>
                                <li>
                                    <a href="javascript:void(0);" class="dropdown-item rounded-1">Ascending</a>
                                </li>
                                <li>
                                    <a href="javascript:void(0);" class="dropdown-item rounded-1">Desending</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">	
                    <div class="table-responsive table-comments">
                        <table class="table datatable">
                            <thead class="thead-light">
                                <tr>
                                    <th class="no-sort">
                                        <label class="checkboxs">
                                            <input type="checkbox" id="select-all">
                                            <span class="checkmarks"></span>
                                        </label>
                                    </th>
                                    <th>Comments</th>
                                    <th>Created Date</th>
                                    <th>Ratings</th>
                                    <th>Blog</th>
                                    <th>By</th>
                                    <th></th>
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
                                    <td class="text-gray-9">Thanks for the detailed guide on POS System</td>										
                                    <td>24 Dec 2024</td>
                                    <td>
                                        <i class="ti ti-star-filled text-warning"></i>
                                        <i class="ti ti-star-filled text-warning"></i>
                                        <i class="ti ti-star-filled text-warning"></i>
                                        <i class="ti ti-star-filled text-warning"></i>
                                        <i class="ti ti-star-filled text-warning"></i>
                                    </td>
                                    <td>What is a POS System? A Beginner’s Guide</td>
                                    <td>Gertrude</td>
                                    <td>
                                        <select class="select">
                                            <option>Unpublish</option>
                                            <option>Publish</option>
                                        </select>
                                    </td>
                                    <td>
                                        <div class="edit-delete-action d-flex align-items-center">
                                            <a data-bs-toggle="modal" data-bs-target="#delete-modal" class="confirm-text p-2 d-flex align-items-center border rounded" href="javascript:void(0);">
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
                                    <td class="text-gray-9">Thanks for sharing these insights!</td>										
                                    <td>10 Dec 2024</td>
                                    <td>
                                        <i class="ti ti-star-filled text-warning"></i>
                                        <i class="ti ti-star-filled text-warning"></i>
                                        <i class="ti ti-star-filled text-warning"></i>
                                        <i class="ti ti-star-filled text-warning"></i>
                                        <i class="ti ti-star-filled text-warning"></i>
                                    </td>
                                    <td>Best POS Systems for Retail Businesses</td>
                                    <td>Edward</td>
                                    <td>
                                        <select class="select">
                                            <option>Unpublish</option>
                                            <option>Publish</option>
                                        </select>
                                    </td>
                                    <td>
                                        <div class="edit-delete-action d-flex align-items-center">
                                            <a data-bs-toggle="modal" data-bs-target="#delete-modal" class="confirm-text p-2 d-flex align-items-center border rounded" href="javascript:void(0);">
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
                                    <td class="text-gray-9">Helpful info on POS features - thank you!</td>										
                                    <td>27 Nov 2024</td>
                                    <td>
                                        <i class="ti ti-star-filled text-warning"></i>
                                        <i class="ti ti-star-filled text-warning"></i>
                                        <i class="ti ti-star-filled text-warning"></i>
                                        <i class="ti ti-star-filled text-warning"></i>
                                        <i class="ti ti-star-filled text-warning"></i>
                                    </td>
                                    <td>Key Features of a Modern POS</td>
                                    <td>Mark</td>
                                    <td>
                                        <select class="select">
                                            <option>Unpublish</option>
                                            <option>Publish</option>
                                        </select>
                                    </td>
                                    <td>
                                        <div class="edit-delete-action d-flex align-items-center">
                                            <a data-bs-toggle="modal" data-bs-target="#delete-modal" class="confirm-text p-2 d-flex align-items-center border rounded" href="javascript:void(0);">
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
                                    <td class="text-gray-9">Fantastic content, thank you for sharing!</td>										
                                    <td>18 Nov 2024</td>
                                    <td>
                                        <i class="ti ti-star-filled text-warning"></i>
                                        <i class="ti ti-star-filled text-warning"></i>
                                        <i class="ti ti-star-filled text-warning"></i>
                                        <i class="ti ti-star-filled text-warning"></i>
                                        <i class="ti ti-star-filled text-warning"></i>
                                    </td>
                                    <td>Integrating POS with E-Commerce</td>
                                    <td>Nidia</td>
                                    <td>
                                        <select class="select">
                                            <option>Unpublish</option>
                                            <option>Publish</option>
                                        </select>
                                    </td>
                                    <td>
                                        <div class="edit-delete-action d-flex align-items-center">
                                            <a data-bs-toggle="modal" data-bs-target="#delete-modal" class="confirm-text p-2 d-flex align-items-center border rounded" href="javascript:void(0);">
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
                                    <td class="text-gray-9">This really cleared things up, I appreciate it</td>										
                                    <td>06 Nov 2024</td>
                                    <td>
                                        <i class="ti ti-star-filled text-warning"></i>
                                        <i class="ti ti-star-filled text-warning"></i>
                                        <i class="ti ti-star-filled text-warning"></i>
                                        <i class="ti ti-star-filled text-warning"></i>
                                        <i class="ti ti-star-filled text-warning"></i>
                                    </td>
                                    <td>AI & the Future of POS Systems</td>
                                    <td>Rebecca</td>
                                    <td>
                                        <select class="select">
                                            <option>Unpublish</option>
                                            <option>Publish</option>
                                        </select>
                                    </td>
                                    <td>
                                        <div class="edit-delete-action d-flex align-items-center">
                                            <a data-bs-toggle="modal" data-bs-target="#delete-modal" class="confirm-text p-2 d-flex align-items-center border rounded" href="javascript:void(0);">
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
                                    <td class="text-gray-9">Awesome post, thanks for sharing!</td>										
                                    <td>25 Oct 2024</td>
                                    <td>
                                        <i class="ti ti-star-filled text-warning"></i>
                                        <i class="ti ti-star-filled text-warning"></i>
                                        <i class="ti ti-star-filled text-warning"></i>
                                        <i class="ti ti-star-filled text-warning"></i>
                                        <i class="ti ti-star-filled text-warning"></i>
                                    </td>
                                    <td>Retail vs Restaurant POS: Key Differences</td>
                                    <td>Jimmy</td>
                                    <td>
                                        <select class="select">
                                            <option>Unpublish</option>
                                            <option>Publish</option>
                                        </select>
                                    </td>
                                    <td>
                                        <div class="edit-delete-action d-flex align-items-center">
                                            <a data-bs-toggle="modal" data-bs-target="#delete-modal" class="confirm-text p-2 d-flex align-items-center border rounded" href="javascript:void(0);">
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
                                    <td class="text-gray-9">I learned a lot from thi - thanks!</td>										
                                    <td>14 Oct 2024</td>
                                    <td>
                                        <i class="ti ti-star-filled text-warning"></i>
                                        <i class="ti ti-star-filled text-warning"></i>
                                        <i class="ti ti-star-filled text-warning"></i>
                                        <i class="ti ti-star-filled text-warning"></i>
                                        <i class="ti ti-star-filled text-warning"></i>
                                    </td>
                                    <td>Understanding PCI Compliance for POS </td>
                                    <td>Richard</td>
                                    <td>
                                        <select class="select">
                                            <option>Unpublish</option>
                                            <option>Publish</option>
                                        </select>
                                    </td>
                                    <td>
                                        <div class="edit-delete-action d-flex align-items-center">
                                            <a data-bs-toggle="modal" data-bs-target="#delete-modal" class="confirm-text p-2 d-flex align-items-center border rounded" href="javascript:void(0);">
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