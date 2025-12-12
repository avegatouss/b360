<?php $page = 'call-history'; ?>
@extends('layout.mainlayout')
@section('content')
 
    <div class="page-wrapper">
        <div class="content">
            <div class="page-header">
            <div class="page-header menu">
                <div class="page-title">
                    <h4>Call History</h4>
                    <h6>Manage your call history</h6>
                </div>
            </div>
            <div>
                <ul class="table-top-head">
                    <li>
                        <a data-bs-toggle="tooltip" data-bs-placement="top" title="pdf"><img src="{{URL::asset('build/img/icons/pdf.svg')}}" alt="img"></a>
                    </li>
                    <li>
                        <a data-bs-toggle="tooltip" data-bs-placement="top" title="excel"><img src="{{URL::asset('build/img/icons/excel.svg')}}" alt="img"></a>
                    </li>
                    <li>
                        <a data-bs-toggle="tooltip" data-bs-placement="top" title="rotate"><i class="ti ti-refresh"></i></a>
                    </li>
                    <li>
                        <a id="collapse-header" data-bs-toggle="tooltip" data-bs-placement="top" title="up"><i class="ti ti-chevron-up"></i></a>
                    </li>
                </ul>
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
                                Call type
                            </a>
                            <ul class="dropdown-menu  dropdown-menu-end p-3">
                                <li>
                                    <a href="javascript:void(0);" class="dropdown-item rounded-1">Incoming</a>
                                </li>
                                <li>
                                    <a href="javascript:void(0);" class="dropdown-item rounded-1">Outgoing</a>
                                </li>
                                <li>
                                    <a href="javascript:void(0);" class="dropdown-item rounded-1">Missed Call</a>
                                </li>
                            </ul>
                        </div>
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
                                    <th>Username</th>
                                    <th>Phone Number</th>
                                    <th>Call Type</th>
                                    <th>Duration</th>
                                    <th>Date & Time</th>
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
                                            <a href="javascript:void(0);" class="avatar avatar-md me-1">
                                                <img src="{{URL::asset('build/img/users/user-01.jpg')}}" alt="product">
                                            </a>
                                            <a href="javascript:void(0);">Arroon</a>
                                        </div>
                                    </td>
                                    <td>+1 25182 94528</td>
                                    <td class="phone-call-icon"><i data-feather="phone-incoming" class="income-success me-2"></i>Incoming Call</td>
                                    <td>00.25</td>
                                    <td>19 Jan 2023 - 01:16 PM</td>
                                    <td class="d-flex">
                                        <div class="edit-delete-action d-flex align-items-center">
                                            <a class="me-3 p-2 d-flex align-items-center p-1 border rounded" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#user-profile-new">
                                                <i data-feather="eye" class="feather-eye"></i>
                                            </a>
                                            <a class="p-2 d-flex align-items-center p-1 border rounded" href="javascript:void(0);"  data-bs-toggle="modal" data-bs-target="#delete">
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
                                            <a href="javascript:void(0);" class="avatar avatar-md me-1">
                                                <img src="{{URL::asset('build/img/users/user-02.jpg')}}" alt="product">
                                            </a>
                                            <a href="javascript:void(0);">Rose</a>
                                        </div>
                                    </td>
                                    <td>+1 93016 81932</td>
                                    <td class="phone-call-icon"><i data-feather="phone-missed" class="income-success-missed me-2"></i>Missed Call</td>
                                    <td>00.00</td>
                                    <td>24 Jan 2023 - 02:50 PM</td>
                                    <td class="d-flex">
                                        <div class="edit-delete-action d-flex align-items-center">
                                            <a class="me-3 p-2 d-flex align-items-center p-1 border rounded" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#user-profile-new">
                                                <i data-feather="eye" class="feather-eye"></i>
                                            </a>
                                            <a class="p-2 d-flex align-items-center p-1 border rounded" href="javascript:void(0);"  data-bs-toggle="modal" data-bs-target="#delete">
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
                                            <a href="javascript:void(0);" class="avatar avatar-md me-1">
                                                <img src="{{URL::asset('build/img/users/user-03.jpg')}}" alt="product">
                                            </a>
                                            <a href="javascript:void(0);">Benjamin</a>
                                        </div>
                                    </td>
                                    <td>+1 25182 94528</td>
                                    <td class="phone-call-icon"><i data-feather="video" class="income-success me-2"></i>Incoming Call</td>
                                    <td>00.15</td>
                                    <td>03 Feb 2023 - 10:37 AM</td>
                                    <td class="d-flex">
                                        <div class="edit-delete-action d-flex align-items-center">
                                            <a class="me-3 p-2 d-flex align-items-center p-1 border rounded" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#user-profile-new">
                                                <i data-feather="eye" class="feather-eye"></i>
                                            </a>
                                            <a class="p-2 d-flex align-items-center p-1 border rounded" href="javascript:void(0);"  data-bs-toggle="modal" data-bs-target="#delete">
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
                                            <a href="javascript:void(0);" class="avatar avatar-md me-1">
                                                <img src="{{URL::asset('build/img/users/user-04.jpg')}}" alt="product">
                                            </a>
                                            <a href="javascript:void(0);">Kaitlin</a>
                                        </div>
                                    </td>
                                    <td>+1 70328 96042</td>
                                    <td class="phone-call-icon"><i data-feather="phone-missed" class="income-success-missed me-2"></i>Missed Call</td>
                                    <td>00.00</td>
                                    <td>17 Feb 2023 - 11:25 AM</td>
                                    <td class="d-flex">
                                        <div class="edit-delete-action d-flex align-items-center">
                                            <a class="me-3 p-2 d-flex align-items-center p-1 border rounded" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#user-profile-new">
                                                <i data-feather="eye" class="feather-eye"></i>
                                            </a>
                                            <a class="p-2 d-flex align-items-center p-1 border rounded" href="javascript:void(0);"  data-bs-toggle="modal" data-bs-target="#delete">
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
                                            <a href="javascript:void(0);" class="avatar avatar-md me-1">
                                                <img src="{{URL::asset('build/img/users/user-05.jpg')}}" alt="product">
                                            </a>
                                            <a href="javascript:void(0);">Lilly</a>
                                        </div>
                                    </td>
                                    <td>+1 83610 45175</td>
                                    <td class="phone-call-icon"><i data-feather="video" class="income-success me-2"></i>Outgoing Call</td>
                                    <td>00.45</td>
                                    <td>14 Mar 2023 - 09:12 AM</td>
                                    <td class="d-flex">
                                        <div class="edit-delete-action d-flex align-items-center">
                                            <a class="me-3 p-2 d-flex align-items-center p-1 border rounded" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#user-profile-new">
                                                <i data-feather="eye" class="feather-eye"></i>
                                            </a>
                                            <a class="p-2 d-flex align-items-center p-1 border rounded" href="javascript:void(0);"  data-bs-toggle="modal" data-bs-target="#delete">
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
                                            <a href="javascript:void(0);" class="avatar avatar-md me-1">
                                                <img src="{{URL::asset('build/img/users/user-08.jpg')}}" alt="product">
                                            </a>
                                            <a href="javascript:void(0);">Freda</a>
                                        </div>
                                    </td>
                                    <td>+1 46217 84294</td>
                                    <td class="phone-call-icon"><i data-feather="phone-incoming" class="income-success me-2"></i>Incoming Call</td>
                                    <td>00.30</td>
                                    <td>27 Mar 2023 - 04:32 PM</td>
                                    <td class="d-flex">
                                        <div class="edit-delete-action d-flex align-items-center">
                                            <a class="me-3 p-2 d-flex align-items-center p-1 border rounded" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#user-profile-new">
                                                <i data-feather="eye" class="feather-eye"></i>
                                            </a>
                                            <a class="p-2 d-flex align-items-center p-1 border rounded" href="javascript:void(0);"  data-bs-toggle="modal" data-bs-target="#delete">
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
                                            <a href="javascript:void(0);" class="avatar avatar-md me-1">
                                                <img src="{{URL::asset('build/img/users/user-06.jpg')}}" alt="product">
                                            </a>
                                            <a href="javascript:void(0);">Alwin</a>
                                        </div>
                                    </td>
                                    <td>+1 62573 84301</td>
                                    <td class="phone-call-icon"><i data-feather="phone-outgoing" class="income-success me-2"></i>Outgoing Call</td>
                                    <td>01.17</td>
                                    <td>13 Apr 2023 - 02:46 PM</td>
                                    <td class="d-flex">
                                        <div class="edit-delete-action d-flex align-items-center">
                                            <a class="me-3 p-2 d-flex align-items-center p-1 border rounded" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#user-profile-new">
                                                <i data-feather="eye" class="feather-eye"></i>
                                            </a>
                                            <a class="p-2 d-flex align-items-center p-1 border rounded" href="javascript:void(0);"  data-bs-toggle="modal" data-bs-target="#delete">
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
                                            <a href="javascript:void(0);" class="avatar avatar-md me-1">
                                                <img src="{{URL::asset('build/img/users/user-07.jpg')}}" alt="product">
                                            </a>
                                            <a href="javascript:void(0);">Maybelle</a>
                                        </div>
                                    </td>
                                    <td>+1 14693 63826</td>
                                    <td class="phone-call-icon"><i data-feather="video" class="income-success-missed me-2"></i>Missed Call</td>
                                    <td>00.00</td>
                                    <td>11 Apr 2023 - 10:29 AM</td>
                                    <td class="d-flex">
                                        <div class="edit-delete-action d-flex align-items-center">
                                            <a class="me-3 p-2 d-flex align-items-center p-1 border rounded" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#user-profile-new">
                                                <i data-feather="eye" class="feather-eye"></i>
                                            </a>
                                            <a class="p-2 d-flex align-items-center p-1 border rounded" href="javascript:void(0);"  data-bs-toggle="modal" data-bs-target="#delete">
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
                                            <a href="javascript:void(0);" class="avatar avatar-md me-1">
                                                <img src="{{URL::asset('build/img/users/user-10.jpg')}}" alt="product">
                                            </a>
                                            <a href="javascript:void(0);">Ellen</a>
                                        </div>
                                    </td>
                                    <td>+1 83710 43827</td>
                                    <td class="phone-call-icon"><i data-feather="phone-incoming" class="income-success me-2"></i>Incoming Call</td>
                                    <td>00.50</td>
                                    <td>14 May 2023 - 03:06 PM</td>
                                    <td class="d-flex">
                                        <div class="edit-delete-action d-flex align-items-center">
                                            <a class="me-3 p-2 d-flex align-items-center p-1 border rounded" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#user-profile-new">
                                                <i data-feather="eye" class="feather-eye"></i>
                                            </a>
                                            <a class="p-2 d-flex align-items-center p-1 border rounded" href="javascript:void(0);"  data-bs-toggle="modal" data-bs-target="#delete">
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
                                            <a href="javascript:void(0);" class="avatar avatar-md me-1">
                                                <img src="{{URL::asset('build/img/users/user-12.jpg')}}" alt="product">
                                            </a>
                                            <a href="javascript:void(0);">Grace</a>
                                        </div>
                                    </td>
                                    <td>+1 42695 07322</td>
                                    <td class="phone-call-icon"><i data-feather="video" class="income-success-missed me-2"></i>Missed Call</td>
                                    <td>00.00</td>
                                    <td>29 May 2023 - 11:23 AM</td>
                                    <td class="d-flex">
                                        <div class="edit-delete-action d-flex align-items-center">
                                            <a class="me-3 p-2 d-flex align-items-center p-1 border rounded" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#user-profile-new">
                                                <i data-feather="eye" class="feather-eye"></i>
                                            </a>
                                            <a class="p-2 d-flex align-items-center p-1 border rounded" href="javascript:void(0);"  data-bs-toggle="modal" data-bs-target="#delete">
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
    </div>
@endsection
