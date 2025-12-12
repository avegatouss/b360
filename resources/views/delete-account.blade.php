<?php $page = 'delete-account'; ?>
@extends('layout.mainlayout')
@section('content')
    <div class="page-wrapper">
        <div class="content">
            <div class="page-header">
                <div class="add-item d-flex">
                    <div class="page-title">
                        <h4>Delete Account Request</h4>
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
                        <a data-bs-toggle="tooltip" data-bs-placement="top" title="Print"><i data-feather="printer" class="feather-rotate-ccw"></i></a>
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
                                    <th>User Name</th>
                                    <th>Requisition Date</th>
                                    <th>Delete Request Date</th>
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
                                                <img src="{{URL::asset('build/img/users/user-01.jpg')}}" alt="user">
                                            </a>
                                            <div>
                                                <a href="javascript:void(0);">Steven</a>
                                            </div>
                                            
                                        </div>
                                    </td>
                                    <td>25 Sep 2023</td>
                                    <td>
                                        01 Oct 2023
                                    </td>
                                    <td>
                                        <div class="edit-delete-action d-flex align-items-center">
                                            <a class="p-2 d-flex align-items-center p-1 border rounded" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#delete_modal">
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
                                                <img src="{{URL::asset('build/img/users/user-02.jpg')}}" alt="user">
                                            </a>
                                            <div>
                                                <a href="javascript:void(0);">Susan Lopez</a>
                                            </div>
                                            
                                        </div>
                                    </td>
                                    <td>30 Sep 2023</td>
                                    <td>
                                        05 Oct 2023
                                    </td>
                                    <td>
                                        <div class="edit-delete-action d-flex align-items-center">
                                            <a class="p-2 d-flex align-items-center p-1 border rounded" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#delete_modal">
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
                                                <img src="{{URL::asset('build/img/users/user-03.jpg')}}" alt="user">
                                            </a>
                                            <div>
                                                <a href="javascript:void(0);">Robert Grossman</a>
                                            </div>
                                            
                                        </div>
                                    </td>
                                    <td>10 Sep 2023</td>
                                    <td>
                                        25 Sep 2023
                                    </td>
                                    <td>
                                        <div class="edit-delete-action d-flex align-items-center">
                                            <a class="p-2 d-flex align-items-center p-1 border rounded" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#delete_modal">
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
                                                <img src="{{URL::asset('build/img/users/user-06.jpg')}}" alt="user">
                                            </a>
                                            <div>
                                                <a href="javascript:void(0);">Janet Hembre</a>
                                            </div>
                                            
                                        </div>
                                    </td>
                                    <td>15 Sep 2023</td>
                                    <td>
                                        20 Sep 2023
                                    </td>
                                    <td>
                                        <div class="edit-delete-action d-flex align-items-center">
                                            <a class="p-2 d-flex align-items-center p-1 border rounded" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#delete_modal">
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
                                                <img src="{{URL::asset('build/img/users/user-04.jpg')}}" alt="user">
                                            </a>
                                            <div>
                                                <a href="javascript:void(0);">Russell Belle</a>
                                            </div>
                                            
                                        </div>
                                    </td>
                                    <td>15 Aug 2023</td>
                                    <td>
                                        01 Sep 2023
                                    </td>
                                    <td>
                                        <div class="edit-delete-action d-flex align-items-center">
                                            <a class="p-2 d-flex align-items-center p-1 border rounded" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#delete_modal">
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
                                                <img src="{{URL::asset('build/img/users/user-08.jpg')}}" alt="user">
                                            </a>
                                            <div>
                                                <a href="javascript:void(0);">Henry Bryant</a>
                                            </div>
                                            
                                        </div>
                                    </td>
                                    <td>12 Aug 2023</td>
                                    <td>
                                        01 Sep 2023
                                    </td>
                                    <td>
                                        <div class="edit-delete-action d-flex align-items-center">
                                            <a class="p-2 d-flex align-items-center p-1 border rounded" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#delete_modal">
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
                                                <img src="{{URL::asset('build/img/users/user-10.jpg')}}" alt="user">
                                            </a>
                                            <div>
                                                <a href="javascript:void(0);">Michael Dawson</a>
                                            </div>
                                            
                                        </div>
                                    </td>
                                    <td>15 Sep 2023</td>
                                    <td>
                                        01 Oct 2023
                                    </td>
                                    <td>
                                        <div class="edit-delete-action d-flex align-items-center">
                                            <a class="p-2 d-flex align-items-center p-1 border rounded" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#delete_modal">
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
                                                <img src="{{URL::asset('build/img/users/user-09.jpg')}}" alt="user">
                                            </a>
                                            <div>
                                                <a href="javascript:void(0);">Thomas Ward</a>
                                            </div>
                                            
                                        </div>
                                    </td>
                                    <td>01 Jan 2023</td>
                                    <td>
                                        01 Feb 2023
                                    </td>
                                    <td>
                                        <div class="edit-delete-action d-flex align-items-center">
                                            <a class="p-2 d-flex align-items-center p-1 border rounded" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#delete_modal">
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
                                                <img src="{{URL::asset('build/img/users/user-20.jpg')}}" alt="user">
                                            </a>
                                            <div>
                                                <a href="javascript:void(0);">Jada Robinson</a>
                                            </div>
                                            
                                        </div>
                                    </td>
                                    <td>22 Oct 2023</td>
                                    <td>
                                        15 Nov 2023
                                    </td>
                                    <td>
                                        <div class="edit-delete-action d-flex align-items-center">
                                            <a class="p-2 d-flex align-items-center p-1 border rounded" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#delete_modal">
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
                                                <img src="{{URL::asset('build/img/users/user-04.jpg')}}" alt="user">
                                            </a>
                                            <div>
                                                <a href="javascript:void(0);">Aliza Duncan</a>
                                            </div>
                                            
                                        </div>
                                    </td>
                                    <td>02 Nov 2023</td>
                                    <td>
                                        01 Dec 2023
                                    </td>
                                    <td>
                                        <div class="edit-delete-action d-flex align-items-center">
                                            <a class="p-2 d-flex align-items-center p-1 border rounded" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#delete_modal">
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
