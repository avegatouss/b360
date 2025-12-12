<?php $page = 'notes'; ?>
@extends('layout.mainlayout')
@section('content')
    <div class="page-wrapper notes-page-wrapper">
        <div class="content">

            <div class="page-header page-add-notes border-0 flex-sm-row flex-column">
                <div class="add-item d-flex">
                    <div class="page-title">
                        <h4>Notes</h4>
                        <h6 class="mb-0">Manage your notes</h6>
                    </div>
                </div>
                <div class="d-flex flex-sm-row flex-column align-items-sm-center align-items-start">
                    <ul class="table-top-head me-2">
                        <li>
                            <a href="{{url('notes')}}" data-bs-toggle="tooltip" data-bs-placement="top" title="Refresh"><i class="ti ti-refresh"></i></a>
                        </li>
                        <li>
                            <a data-bs-toggle="tooltip" data-bs-placement="top" title="Collapse" id="collapse-header"><i class="ti ti-chevron-up"></i></a>
                        </li>
                    </ul>
                    <div class="search-set">
                        <div class="search-input">
                            <span class="btn-searchset"><i class="ti ti-search fs-14 feather-search"></i></span>
                        <div class="dataTables_filter">
                            <label> <input type="search" class="form-control form-control-sm py-0" placeholder="Search"></label>
                        </div></div>
                    </div>
                    <div class="page-btn">
                        <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#note-units"><i class="ti ti-circle-plus me-1"></i>Add Note</a>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-xl-3 col-md-12 sidebars-right theiaStickySidebar section-bulk-widget">
                    <div class="border rounded-3 bg-white p-3">
                        <div class="mb-3 pb-3 border-bottom">
                            <h4 class="d-flex align-items-center"><i class="ti ti-file-text me-2"></i>Notes List</h4>
                        </div>
                        <div class="border-bottom pb-3 ">
                            <div class="nav flex-column nav-pills" id="v-pills-tab" role="tablist"
                                aria-orientation="vertical">
                                <button class="d-flex text-start align-items-center fw-medium fs-15 nav-link active mb-1" id="v-pills-profile-tab" data-bs-toggle="pill" data-bs-target="#v-pills-profile" type="button" role="tab" aria-controls="v-pills-profile" aria-selected="true">
                                    <i class="ti ti-inbox me-2"></i>All Notes<span class="ms-2">1</span>
                                </button>
                                <button class="d-flex text-start align-items-center fw-medium fs-15 nav-link mb-1" id="v-pills-messages-tab" data-bs-toggle="pill" data-bs-target="#v-pills-messages" type="button" role="tab" aria-controls="v-pills-messages" aria-selected="false">
                                    <i class="ti ti-star me-2"></i>
                                    Important
                                </button>
                                <button class="d-flex text-start align-items-center fw-medium fs-15 nav-link mb-0" id="v-pills-settings-tab" data-bs-toggle="pill" data-bs-target="#v-pills-settings" type="button" role="tab" aria-controls="v-pills-settings" aria-selected="false">
                                    <i class="ti ti-trash me-2"></i>
                                    Trash
                                </button>
                            </div>
                        </div>
                        <div class="mt-3">
                            <div class="border-bottom px-2 pb-3 mb-3">
                                <h5 class="mb-2">Tags</h5>
                                <div class="d-flex flex-column mt-2">
                                    <a href="javascript:void(0);" class="text-info mb-2">
                                        <span class="text-info me-2">
                                            <i class="fas fa-square square-rotate fs-10"></i>
                                        </span>
                                        Pending
                                    </a>
                                    <a href="javascript:void(0);" class="text-danger mb-2">
                                        <span class="text-danger me-2">
                                            <i class="fas fa-square square-rotate fs-10"></i>
                                        </span>
                                        Onhold
                                    </a>
                                    <a href="javascript:void(0);" class="text-warning mb-2">
                                        <span class="text-warning me-2">
                                            <i class="fas fa-square square-rotate fs-10"></i>
                                        </span>
                                        Inprogress
                                    </a>
                                    <a href="javascript:void(0);" class="text-success">
                                        <span class="text-success me-2">
                                            <i class="fas fa-square square-rotate fs-10"></i>
                                        </span>
                                        Done
                                    </a>
                                </div>
                            </div>
                            <div class="px-2">
                                <h5 class="mb-2">Priority</h5>
                                <div class="d-flex flex-column mt-2">
                                    <a href="javascript:void(0);" class="text-warning mb-2">
                                        <span class="text-warning me-2">
                                            <i class="fas fa-square square-rotate fs-10"></i>
                                        </span>
                                        Medium
                                    </a>
                                    <a href="javascript:void(0);" class="text-success mb-2">
                                        <span class="text-success me-2">
                                            <i class="fas fa-square square-rotate fs-10"></i>
                                        </span>
                                        High
                                    </a>
                                    <a href="javascript:void(0);" class="text-danger">
                                        <span class="text-danger me-2">
                                            <i class="fas fa-square square-rotate fs-10"></i>
                                        </span>
                                        Low
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-9 budget-role-notes">
                    <div class="bg-white rounded-3 d-flex align-items-center justify-content-between flex-wrap mb-4 p-3 pb-0">
                        <div class="form-sort me-2 mb-3">
                            <i data-feather="sliders" class="info-img"></i>
                            <select class="select">
                                <option>Sort by A-Z</option>
                                <option>Ascending </option>
                                <option>Descending</option>
                                <option>Recently Viewed </option>
                                <option>Recently Added</option>
                            </select>
                        </div>
                        <div class="d-flex align-items-center mb-3">
                            <div class="input-icon-start me-2 position-relative">
                                <span class="icon-addon">
                                    <i class="ti ti-calendar"></i>
                                </span>
                                <input type="text" class="form-control date-range bookingrange" placeholder="dd/mm/yyyy - dd/mm/yyyy">
                            </div>
                            <div class="search-set">
                                <div class="search-input">
                                    <span class="btn-searchset"><i class="ti ti-search fs-14 feather-search"></i></span>
                                <div class="dataTables_filter">
                                    <label> <input type="search" class="form-control form-control-sm" placeholder="Search"></label>
                                </div></div>
                            </div>
                        </div>
                    </div>
                    <div class="tab-content" id="v-pills-tabContent2">
                        <div class="tab-pane fade active show" id="v-pills-profile" role="tabpanel"
                            aria-labelledby="v-pills-profile-tab">
                            <div class="border-bottom mb-4 pb-4">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div
                                            class="d-flex align-items-center justify-content-between flex-wrap mb-2">
                                            <div class="d-flex align-items-center mb-3">
                                                <h4>Important </h4>
                                                <div class="owl-nav slide-nav5 text-end nav-control ms-3"></div>
                                            </div>
                                            <div class="notes-close mb-3">
                                                <a href="javascript:void(0);" class="text-danger fs-15"><i
                                                        class="fas fa-times me-1"></i> Close </a>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="notes-slider owl-carousel">
                                            <div class="card rounded-3 mb-0">
                                                <div class="card-body p-4">
                                                    <div class="d-flex align-items-center justify-content-between">
                                                        <span
                                                            class="badge bg-outline-warning d-inline-flex align-items-center"><i
                                                                class="fas fa-circle fs-6 me-1"></i>Medium</span>
                                                        <div>
                                                            <a href="javascript:void(0);" data-bs-toggle="dropdown"
                                                                aria-expanded="false">
                                                                <i class="fas fa-ellipsis-v"></i>
                                                            </a>
                                                            <div class="dropdown-menu notes-menu dropdown-menu-end">
                                                                <a href="#" class="dropdown-item"
                                                                    data-bs-toggle="modal"
                                                                    data-bs-target="#edit-note-units"><span><i
                                                                            data-feather="edit"></i></span>Edit</a>
                                                                <a href="#" class="dropdown-item"
                                                                    data-bs-toggle="modal"
                                                                    data-bs-target="#delete_modal"><span><i
                                                                            data-feather="trash-2"></i></span>Delete</a>
                                                                <a href="javascript:void(0);"
                                                                    class="dropdown-item"><span><i
                                                                            data-feather="star"></i></span>Not
                                                                    Important</a>
                                                                <a href="#" class="dropdown-item"
                                                                    data-bs-toggle="modal"
                                                                    data-bs-target="#view-note-units"><span><i
                                                                            data-feather="eye"></i></span>View</a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="my-3">
                                                        <h5 class="text-truncate mb-1"><a
                                                                href="javascript:void(0);">Plan a trip to another
                                                                country</a></h5>
                                                        <p class="mb-3 d-flex align-items-center text-dark"><i
                                                                class="ti ti-calendar me-1"></i>20 Jan 2024</p>
                                                        <p class="text-truncate line-clamb-2 text-wrap">Space, the
                                                            final frontier. These are the voyages of the Starship
                                                            Enterprise.</p>
                                                    </div>
                                                    <div
                                                        class="d-flex align-items-center justify-content-between border-top pt-3">
                                                        <div class="d-flex align-items-center">
                                                            <a href="javascript:void(0);"
                                                                class="avatar avatar-md me-2">
                                                                <img src="{{URL::asset('build/img/profiles/avatar-01.jpg')}}"
                                                                    alt="Profile" class="img-fluid rounded-circle">
                                                            </a>
                                                            <span class="text-info d-flex align-items-center"><i
                                                                    class="fas fa-square square-rotate fs-10 me-1"></i>Personal</span>
                                                        </div>
                                                        <div class="d-flex align-items-center">
                                                            <a href="javascript:void(0);" class="me-2">
                                                                <span><i
                                                                        class="fas fa-star text-warning"></i></span>
                                                            </a>
                                                            <a href="javascript:void(0);">
                                                                <span><i class="ti ti-trash text-danger"></i></span>
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="card rounded-3 mb-0">
                                                <div class="card-body p-4">
                                                    <div class="d-flex align-items-center justify-content-between">
                                                        <span
                                                            class="badge bg-outline-danger d-inline-flex align-items-center"><i
                                                                class="fas fa-circle fs-6 me-1"></i>Low</span>
                                                        <div>
                                                            <a href="javascript:void(0);" data-bs-toggle="dropdown"
                                                                aria-expanded="false">
                                                                <i class="fas fa-ellipsis-v"></i>
                                                            </a>
                                                            <div class="dropdown-menu notes-menu dropdown-menu-end">
                                                                <a href="#" class="dropdown-item"
                                                                    data-bs-toggle="modal"
                                                                    data-bs-target="#edit-note-units"><span><i
                                                                            data-feather="edit"></i></span>Edit</a>
                                                                <a href="#" class="dropdown-item"
                                                                    data-bs-toggle="modal"
                                                                    data-bs-target="#delete_modal"><span><i
                                                                            data-feather="trash-2"></i></span>Delete</a>
                                                                <a href="javascript:void(0);"
                                                                    class="dropdown-item"><span><i
                                                                            data-feather="star"></i></span>Not
                                                                    Important</a>
                                                                <a href="#" class="dropdown-item"
                                                                    data-bs-toggle="modal"
                                                                    data-bs-target="#view-note-units"><span><i
                                                                            data-feather="eye"></i></span>View</a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="my-3">
                                                        <h5 class="text-truncate mb-1"><a
                                                                href="javascript:void(0);">Improve touch typing</a>
                                                        </h5>
                                                        <p class="mb-3 d-flex align-items-center text-dark"><i
                                                                class="ti ti-calendar me-1"></i>22 Jan 2024</p>
                                                        <p class="text-truncate line-clamb-2 text-wrap">Well, the
                                                            way they make shows is, they make one show.</p>
                                                    </div>
                                                    <div
                                                        class="d-flex align-items-center justify-content-between border-top pt-3">
                                                        <div class="d-flex align-items-center">
                                                            <a href="javascript:void(0);"
                                                                class="avatar avatar-md me-2">
                                                                <img src="{{URL::asset('build/img/profiles/avatar-02.jpg')}}"
                                                                    alt="Profile" class="img-fluid rounded-circle">
                                                            </a>
                                                            <span class="text-success d-flex align-items-center"><i
                                                                    class="fas fa-square square-rotate fs-10 me-1"></i>Work</span>
                                                        </div>
                                                        <div class="d-flex align-items-center">
                                                            <a href="javascript:void(0);" class="me-2">
                                                                <span><i
                                                                        class="fas fa-star text-warning"></i></span>
                                                            </a>
                                                            <a href="javascript:void(0);">
                                                                <span><i class="ti ti-trash text-danger"></i></span>
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="card rounded-3 mb-0">
                                                <div class="card-body p-4">
                                                    <div class="d-flex align-items-center justify-content-between">
                                                        <span
                                                            class="badge bg-outline-danger d-inline-flex align-items-center"><i
                                                                class="fas fa-circle fs-6 me-1"></i>Low</span>
                                                        <div>
                                                            <a href="javascript:void(0);" data-bs-toggle="dropdown"
                                                                aria-expanded="false">
                                                                <i class="fas fa-ellipsis-v"></i>
                                                            </a>
                                                            <div class="dropdown-menu notes-menu dropdown-menu-end">
                                                                <a href="#" class="dropdown-item"
                                                                    data-bs-toggle="modal"
                                                                    data-bs-target="#edit-note-units"><span><i
                                                                            data-feather="edit"></i></span>Edit</a>
                                                                <a href="#" class="dropdown-item"
                                                                    data-bs-toggle="modal"
                                                                    data-bs-target="#delete_modal"><span><i
                                                                            data-feather="trash-2"></i></span>Delete</a>
                                                                <a href="javascript:void(0);"
                                                                    class="dropdown-item"><span><i
                                                                            data-feather="star"></i></span>Not
                                                                    Important</a>
                                                                <a href="#" class="dropdown-item"
                                                                    data-bs-toggle="modal"
                                                                    data-bs-target="#view-note-units"><span><i
                                                                            data-feather="eye"></i></span>View</a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="my-3">
                                                        <h5 class="text-truncate mb-1"><a
                                                                href="javascript:void(0);">Learn calligraphy</a>
                                                        </h5>
                                                        <p class="mb-3 d-flex align-items-center text-dark"><i
                                                                class="ti ti-calendar me-1"></i>24 Jan 2024</p>
                                                        <p class="text-truncate line-clamb-2 text-wrap">Calligraphy,
                                                            the art of beautiful handwriting. The term may derive
                                                            from the Greek words. </p>
                                                    </div>
                                                    <div
                                                        class="d-flex align-items-center justify-content-between border-top pt-3">
                                                        <div class="d-flex align-items-center">
                                                            <a href="javascript:void(0);"
                                                                class="avatar avatar-md me-2">
                                                                <img src="{{URL::asset('build/img/profiles/avatar-03.jpg')}}"
                                                                    alt="Profile" class="img-fluid rounded-circle">
                                                            </a>
                                                            <span class="text-info d-flex align-items-center"><i
                                                                    class="fas fa-square square-rotate fs-10 me-1"></i>Social</span>
                                                        </div>
                                                        <div class="d-flex align-items-center">
                                                            <a href="javascript:void(0);" class="me-2">
                                                                <span><i
                                                                        class="fas fa-star text-warning"></i></span>
                                                            </a>
                                                            <a href="javascript:void(0);">
                                                                <span><i class="ti ti-trash text-danger"></i></span>
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="card rounded-3 mb-0">
                                                <div class="card-body p-4">
                                                    <div class="d-flex align-items-center justify-content-between">
                                                        <span
                                                            class="badge bg-outline-warning d-inline-flex align-items-center"><i
                                                                class="fas fa-circle fs-6 me-1"></i>Medium</span>
                                                        <div>
                                                            <a href="javascript:void(0);" data-bs-toggle="dropdown"
                                                                aria-expanded="false">
                                                                <i class="fas fa-ellipsis-v"></i>
                                                            </a>
                                                            <div class="dropdown-menu notes-menu dropdown-menu-end">
                                                                <a href="#" class="dropdown-item"
                                                                    data-bs-toggle="modal"
                                                                    data-bs-target="#edit-note-units"><span><i
                                                                            data-feather="edit"></i></span>Edit</a>
                                                                <a href="#" class="dropdown-item"
                                                                    data-bs-toggle="modal"
                                                                    data-bs-target="#delete_modal"><span><i
                                                                            data-feather="trash-2"></i></span>Delete</a>
                                                                <a href="javascript:void(0);"
                                                                    class="dropdown-item"><span><i
                                                                            data-feather="star"></i></span>Not
                                                                    Important</a>
                                                                <a href="#" class="dropdown-item"
                                                                    data-bs-toggle="modal"
                                                                    data-bs-target="#view-note-units"><span><i
                                                                            data-feather="eye"></i></span>View</a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="my-3">
                                                        <h5 class="text-truncate mb-1"><a
                                                                href="javascript:void(0);">Plan a trip to another
                                                                country</a></h5>
                                                        <p class="mb-3 d-flex align-items-center text-dark"><i
                                                                class="ti ti-calendar me-1"></i>25 Jan 2024</p>
                                                        <p class="text-truncate line-clamb-2 text-wrap">Space, the
                                                            final frontier. These are the voyages of the Starship
                                                            Enterprise.</p>
                                                    </div>
                                                    <div
                                                        class="d-flex align-items-center justify-content-between border-top pt-3">
                                                        <div class="d-flex align-items-center">
                                                            <a href="javascript:void(0);"
                                                                class="avatar avatar-md me-2">
                                                                <img src="{{URL::asset('build/img/profiles/avatar-01.jpg')}}"
                                                                    alt="Profile" class="img-fluid rounded-circle">
                                                            </a>
                                                            <span class="text-info d-flex align-items-center"><i
                                                                    class="fas fa-square square-rotate fs-10 me-1"></i>Personal</span>
                                                        </div>
                                                        <div class="d-flex align-items-center">
                                                            <a href="javascript:void(0);" class="me-2">
                                                                <span><i
                                                                        class="fas fa-star text-warning"></i></span>
                                                            </a>
                                                            <a href="javascript:void(0);">
                                                                <span><i class="ti ti-trash text-danger"></i></span>
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="card rounded-3 mb-0">
                                                <div class="card-body p-4">
                                                    <div class="d-flex align-items-center justify-content-between">
                                                        <span
                                                            class="badge bg-outline-danger d-inline-flex align-items-center"><i
                                                                class="fas fa-circle fs-6 me-1"></i>Low</span>
                                                        <div>
                                                            <a href="javascript:void(0);" data-bs-toggle="dropdown"
                                                                aria-expanded="false">
                                                                <i class="fas fa-ellipsis-v"></i>
                                                            </a>
                                                            <div class="dropdown-menu notes-menu dropdown-menu-end">
                                                                <a href="#" class="dropdown-item"
                                                                    data-bs-toggle="modal"
                                                                    data-bs-target="#edit-note-units"><span><i
                                                                            data-feather="edit"></i></span>Edit</a>
                                                                <a href="#" class="dropdown-item"
                                                                    data-bs-toggle="modal"
                                                                    data-bs-target="#delete_modal"><span><i
                                                                            data-feather="trash-2"></i></span>Delete</a>
                                                                <a href="javascript:void(0);"
                                                                    class="dropdown-item"><span><i
                                                                            data-feather="star"></i></span>Not
                                                                    Important</a>
                                                                <a href="#" class="dropdown-item"
                                                                    data-bs-toggle="modal"
                                                                    data-bs-target="#view-note-units"><span><i
                                                                            data-feather="eye"></i></span>View</a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="my-3">
                                                        <h5 class="text-truncate mb-1"><a
                                                                href="javascript:void(0);">Improve touch typing</a>
                                                        </h5>
                                                        <p class="mb-3 d-flex align-items-center text-dark"><i
                                                                class="ti ti-calendar me-1"></i>26 Jan 2024</p>
                                                        <p class="text-truncate line-clamb-2 text-wrap">Well, the
                                                            way they make shows is, they make one show.</p>
                                                    </div>
                                                    <div
                                                        class="d-flex align-items-center justify-content-between border-top pt-3">
                                                        <div class="d-flex align-items-center">
                                                            <a href="javascript:void(0);"
                                                                class="avatar avatar-md me-2">
                                                                <img src="{{URL::asset('build/img/profiles/avatar-02.jpg')}}"
                                                                    alt="Profile" class="img-fluid rounded-circle">
                                                            </a>
                                                            <span class="text-success d-flex align-items-center"><i
                                                                    class="fas fa-square square-rotate fs-10 me-1"></i>Work</span>
                                                        </div>
                                                        <div class="d-flex align-items-center">
                                                            <a href="javascript:void(0);" class="me-2">
                                                                <span><i
                                                                        class="fas fa-star text-warning"></i></span>
                                                            </a>
                                                            <a href="javascript:void(0);">
                                                                <span><i class="ti ti-trash text-danger"></i></span>
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4 d-flex">
                                    <div class="card rounded-3 mb-4 flex-fill">
                                        <div class="card-body p-4">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <span
                                                    class="badge bg-outline-success d-inline-flex align-items-center"><i
                                                        class="fas fa-circle fs-6 me-1"></i>High</span>
                                                <div>
                                                    <a href="javascript:void(0);" data-bs-toggle="dropdown"
                                                        aria-expanded="false">
                                                        <i class="fas fa-ellipsis-v"></i>
                                                    </a>
                                                    <div class="dropdown-menu notes-menu dropdown-menu-end">
                                                        <a href="#" class="dropdown-item" data-bs-toggle="modal"
                                                            data-bs-target="#edit-note-units"><span><i
                                                                    data-feather="edit"></i></span>Edit</a>
                                                        <a href="#" class="dropdown-item" data-bs-toggle="modal"
                                                            data-bs-target="#delete_modal"><span><i
                                                                    data-feather="trash-2"></i></span>Delete</a>
                                                        <a href="javascript:void(0);" class="dropdown-item"><span><i
                                                                    data-feather="star"></i></span>Not Important</a>
                                                        <a href="#" class="dropdown-item" data-bs-toggle="modal"
                                                            data-bs-target="#view-note-units"><span><i
                                                                    data-feather="eye"></i></span>View</a>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="my-3">
                                                <h5 class="text-truncate mb-1"><a href="javascript:void(0);">Backup
                                                        Files EOD</a></h5>
                                                <p class="mb-3 d-flex align-items-center text-dark"><i
                                                        class="ti ti-calendar me-1"></i>20 Jan 2024</p>
                                                <p class="text-truncate line-clamb-2 text-wrap">Project files should
                                                    be took backup before end of the day.</p>
                                            </div>
                                            <div
                                                class="d-flex align-items-center justify-content-between border-top pt-3">
                                                <div class="d-flex align-items-center">
                                                    <a href="javascript:void(0);" class="avatar avatar-md me-2">
                                                        <img src="{{URL::asset('build/img/profiles/avatar-05.jpg')}}" alt="Profile"
                                                            class="img-fluid rounded-circle">
                                                    </a>
                                                    <span class="text-info d-flex align-items-center"><i
                                                            class="fas fa-square square-rotate fs-10 me-1"></i>Personal</span>
                                                </div>
                                                <div class="d-flex align-items-center">
                                                    <a href="javascript:void(0);" class="me-2">
                                                        <span><i class="fas fa-star text-warning"></i></span>
                                                    </a>
                                                    <a href="javascript:void(0);">
                                                        <span><i class="ti ti-trash text-danger"></i></span>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 d-flex">
                                    <div class="card rounded-3 mb-4 flex-fill">
                                        <div class="card-body p-4">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <span
                                                    class="badge bg-outline-danger d-inline-flex align-items-center"><i
                                                        class="fas fa-circle fs-6 me-1"></i>Low</span>
                                                <div>
                                                    <a href="javascript:void(0);" data-bs-toggle="dropdown"
                                                        aria-expanded="false">
                                                        <i class="fas fa-ellipsis-v"></i>
                                                    </a>
                                                    <div class="dropdown-menu notes-menu dropdown-menu-end">
                                                        <a href="#" class="dropdown-item" data-bs-toggle="modal"
                                                            data-bs-target="#edit-note-units"><span><i
                                                                    data-feather="edit"></i></span>Edit</a>
                                                        <a href="#" class="dropdown-item" data-bs-toggle="modal"
                                                            data-bs-target="#delete_modal"><span><i
                                                                    data-feather="trash-2"></i></span>Delete</a>
                                                        <a href="javascript:void(0);" class="dropdown-item"><span><i
                                                                    data-feather="star"></i></span>Not Important</a>
                                                        <a href="#" class="dropdown-item" data-bs-toggle="modal"
                                                            data-bs-target="#view-note-units"><span><i
                                                                    data-feather="eye"></i></span>View</a>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="my-3">
                                                <h5 class="text-truncate mb-1"><a
                                                        href="javascript:void(0);">Download Server Logs</a></h5>
                                                <p class="mb-3 d-flex align-items-center text-dark"><i
                                                        class="ti ti-calendar me-1"></i>25 Jan 2024</p>
                                                <p class="text-truncate line-clamb-2 text-wrap">Server log is a text
                                                    document that contains a record of all activity.</p>
                                            </div>
                                            <div
                                                class="d-flex align-items-center justify-content-between border-top pt-3">
                                                <div class="d-flex align-items-center">
                                                    <a href="javascript:void(0);" class="avatar avatar-md me-2">
                                                        <img src="{{URL::asset('build/img/profiles/avatar-06.jpg')}}" alt="Profile"
                                                            class="img-fluid rounded-circle">
                                                    </a>
                                                    <span class="text-success d-flex align-items-center"><i
                                                            class="fas fa-square square-rotate fs-10 me-1"></i>Work</span>
                                                </div>
                                                <div class="d-flex align-items-center">
                                                    <a href="javascript:void(0);" class="me-2">
                                                        <span><i class="fas fa-star text-warning"></i></span>
                                                    </a>
                                                    <a href="javascript:void(0);">
                                                        <span><i class="ti ti-trash text-danger"></i></span>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 d-flex">
                                    <div class="card rounded-3 mb-4 flex-fill">
                                        <div class="card-body p-4">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <span
                                                    class="badge bg-outline-warning d-inline-flex align-items-center"><i
                                                        class="fas fa-circle fs-6 me-1"></i>Medium</span>
                                                <div>
                                                    <a href="javascript:void(0);" data-bs-toggle="dropdown"
                                                        aria-expanded="false">
                                                        <i class="fas fa-ellipsis-v"></i>
                                                    </a>
                                                    <div class="dropdown-menu notes-menu dropdown-menu-end">
                                                        <a href="#" class="dropdown-item" data-bs-toggle="modal"
                                                            data-bs-target="#edit-note-units"><span><i
                                                                    data-feather="edit"></i></span>Edit</a>
                                                        <a href="#" class="dropdown-item" data-bs-toggle="modal"
                                                            data-bs-target="#delete_modal"><span><i
                                                                    data-feather="trash-2"></i></span>Delete</a>
                                                        <a href="javascript:void(0);" class="dropdown-item"><span><i
                                                                    data-feather="star"></i></span>Not Important</a>
                                                        <a href="#" class="dropdown-item" data-bs-toggle="modal"
                                                            data-bs-target="#view-note-units"><span><i
                                                                    data-feather="eye"></i></span>View</a>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="my-3">
                                                <h5 class="text-truncate mb-1"><a href="javascript:void(0);">Team
                                                        meet at Starbucks</a></h5>
                                                <p class="mb-3 d-flex align-items-center text-dark"><i
                                                        class="ti ti-calendar me-1"></i>26 Jan 2024</p>
                                                <p class="text-truncate line-clamb-2 text-wrap">Meeting all teamets
                                                    at Starbucks for identifying them all.</p>
                                            </div>
                                            <div
                                                class="d-flex align-items-center justify-content-between border-top pt-3">
                                                <div class="d-flex align-items-center">
                                                    <a href="javascript:void(0);" class="avatar avatar-md me-2">
                                                        <img src="{{URL::asset('build/img/profiles/avatar-07.jpg')}}" alt="Profile"
                                                            class="img-fluid rounded-circle">
                                                    </a>
                                                    <span class="text-warning d-flex align-items-center"><i
                                                            class="fas fa-square square-rotate fs-10 me-1"></i>Social</span>
                                                </div>
                                                <div class="d-flex align-items-center">
                                                    <a href="javascript:void(0);" class="me-2">
                                                        <span><i class="fas fa-star text-warning"></i></span>
                                                    </a>
                                                    <a href="javascript:void(0);">
                                                        <span><i class="ti ti-trash text-danger"></i></span>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 d-flex">
                                    <div class="card rounded-3 mb-4 flex-fill">
                                        <div class="card-body p-4">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <span
                                                    class="badge bg-outline-success d-inline-flex align-items-center"><i
                                                        class="fas fa-circle fs-6 me-1"></i>High</span>
                                                <div>
                                                    <a href="javascript:void(0);" data-bs-toggle="dropdown"
                                                        aria-expanded="false">
                                                        <i class="fas fa-ellipsis-v"></i>
                                                    </a>
                                                    <div class="dropdown-menu notes-menu dropdown-menu-end">
                                                        <a href="#" class="dropdown-item" data-bs-toggle="modal"
                                                            data-bs-target="#edit-note-units"><span><i
                                                                    data-feather="edit"></i></span>Edit</a>
                                                        <a href="#" class="dropdown-item" data-bs-toggle="modal"
                                                            data-bs-target="#delete_modal"><span><i
                                                                    data-feather="trash-2"></i></span>Delete</a>
                                                        <a href="javascript:void(0);" class="dropdown-item"><span><i
                                                                    data-feather="star"></i></span>Not Important</a>
                                                        <a href="#" class="dropdown-item" data-bs-toggle="modal"
                                                            data-bs-target="#view-note-units"><span><i
                                                                    data-feather="eye"></i></span>View</a>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="my-3">
                                                <h5 class="text-truncate mb-1"><a href="javascript:void(0);">Create
                                                        a compost pile</a></h5>
                                                <p class="mb-3 d-flex align-items-center text-dark"><i
                                                        class="ti ti-calendar me-1"></i>27 Jan 2024</p>
                                                <p class="text-truncate line-clamb-2 text-wrap">Compost pile refers
                                                    to fruit and vegetable scraps, used tea, coffee grounds etc..
                                                </p>
                                            </div>
                                            <div
                                                class="d-flex align-items-center justify-content-between border-top pt-3">
                                                <div class="d-flex align-items-center">
                                                    <a href="javascript:void(0);" class="avatar avatar-md me-2">
                                                        <img src="{{URL::asset('build/img/profiles/avatar-08.jpg')}}" alt="Profile"
                                                            class="img-fluid rounded-circle">
                                                    </a>
                                                    <span class="text-warning d-flex align-items-center"><i
                                                            class="fas fa-square square-rotate fs-10 me-1"></i>Social</span>
                                                </div>
                                                <div class="d-flex align-items-center">
                                                    <a href="javascript:void(0);" class="me-2">
                                                        <span><i class="fas fa-star text-warning"></i></span>
                                                    </a>
                                                    <a href="javascript:void(0);">
                                                        <span><i class="ti ti-trash text-danger"></i></span>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 d-flex">
                                    <div class="card rounded-3 mb-4 flex-fill">
                                        <div class="card-body p-4">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <span
                                                    class="badge bg-outline-danger d-inline-flex align-items-center"><i
                                                        class="fas fa-circle fs-6 me-1"></i>Low</span>
                                                <div>
                                                    <a href="javascript:void(0);" data-bs-toggle="dropdown"
                                                        aria-expanded="false">
                                                        <i class="fas fa-ellipsis-v"></i>
                                                    </a>
                                                    <div class="dropdown-menu notes-menu dropdown-menu-end">
                                                        <a href="#" class="dropdown-item" data-bs-toggle="modal"
                                                            data-bs-target="#edit-note-units"><span><i
                                                                    data-feather="edit"></i></span>Edit</a>
                                                        <a href="#" class="dropdown-item" data-bs-toggle="modal"
                                                            data-bs-target="#delete_modal"><span><i
                                                                    data-feather="trash-2"></i></span>Delete</a>
                                                        <a href="javascript:void(0);" class="dropdown-item"><span><i
                                                                    data-feather="star"></i></span>Not Important</a>
                                                        <a href="#" class="dropdown-item" data-bs-toggle="modal"
                                                            data-bs-target="#view-note-units"><span><i
                                                                    data-feather="eye"></i></span>View</a>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="my-3">
                                                <h5 class="text-truncate mb-1"><a href="javascript:void(0);">Take a
                                                        hike at a local park</a></h5>
                                                <p class="mb-3 d-flex align-items-center text-dark"><i
                                                        class="ti ti-calendar me-1"></i>28 Jan 2024</p>
                                                <p class="text-truncate line-clamb-2 text-wrap">Hiking involves a
                                                    long energetic walk in a natural environment.</p>
                                            </div>
                                            <div
                                                class="d-flex align-items-center justify-content-between border-top pt-3">
                                                <div class="d-flex align-items-center">
                                                    <a href="javascript:void(0);" class="avatar avatar-md me-2">
                                                        <img src="{{URL::asset('build/img/profiles/avatar-09.jpg')}}" alt="Profile"
                                                            class="img-fluid rounded-circle">
                                                    </a>
                                                    <span class="text-info d-flex align-items-center"><i
                                                            class="fas fa-square square-rotate fs-10 me-1"></i>Personal</span>
                                                </div>
                                                <div class="d-flex align-items-center">
                                                    <a href="javascript:void(0);" class="me-2">
                                                        <span><i class="fas fa-star text-warning"></i></span>
                                                    </a>
                                                    <a href="javascript:void(0);">
                                                        <span><i class="ti ti-trash text-danger"></i></span>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 d-flex">
                                    <div class="card rounded-3 mb-4 flex-fill">
                                        <div class="card-body p-4">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <span
                                                    class="badge bg-outline-info d-inline-flex align-items-center"><i
                                                        class="fas fa-circle fs-6 me-1"></i>medium</span>
                                                <div>
                                                    <a href="javascript:void(0);" data-bs-toggle="dropdown"
                                                        aria-expanded="false">
                                                        <i class="fas fa-ellipsis-v"></i>
                                                    </a>
                                                    <div class="dropdown-menu notes-menu dropdown-menu-end">
                                                        <a href="#" class="dropdown-item" data-bs-toggle="modal"
                                                            data-bs-target="#edit-note-units"><span><i
                                                                    data-feather="edit"></i></span>Edit</a>
                                                        <a href="#" class="dropdown-item" data-bs-toggle="modal"
                                                            data-bs-target="#delete_modal"><span><i
                                                                    data-feather="trash-2"></i></span>Delete</a>
                                                        <a href="javascript:void(0);" class="dropdown-item"><span><i
                                                                    data-feather="star"></i></span>Not Important</a>
                                                        <a href="#" class="dropdown-item" data-bs-toggle="modal"
                                                            data-bs-target="#view-note-units"><span><i
                                                                    data-feather="eye"></i></span>View</a>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="my-3">
                                                <h5 class="text-truncate mb-1"><a
                                                        href="javascript:void(0);">Research a topic interested</a>
                                                </h5>
                                                <p class="mb-3 d-flex align-items-center text-dark"><i
                                                        class="ti ti-calendar me-1"></i>28 Jan 2024</p>
                                                <p class="text-truncate line-clamb-2 text-wrap">Research a topic
                                                    interested by listen actively and attentively.</p>
                                            </div>
                                            <div
                                                class="d-flex align-items-center justify-content-between border-top pt-3">
                                                <div class="d-flex align-items-center">
                                                    <a href="javascript:void(0);" class="avatar avatar-md me-2">
                                                        <img src="{{URL::asset('build/img/profiles/avatar-10.jpg')}}" alt="Profile"
                                                            class="img-fluid rounded-circle">
                                                    </a>
                                                    <span class="text-success d-flex align-items-center"><i
                                                            class="fas fa-square square-rotate fs-10 me-1"></i>Work</span>
                                                </div>
                                                <div class="d-flex align-items-center">
                                                    <a href="javascript:void(0);" class="me-2">
                                                        <span><i class="fas fa-star text-warning"></i></span>
                                                    </a>
                                                    <a href="javascript:void(0);">
                                                        <span><i class="ti ti-trash text-danger"></i></span>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="v-pills-messages" role="tabpanel"
                            aria-labelledby="v-pills-messages-tab">
                            <div class="row">
                                <div class="col-md-4 d-flex">
                                    <div class="card rounded-3 mb-4 flex-fill">
                                        <div class="card-body p-4">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <span
                                                    class="badge bg-outline-success d-inline-flex align-items-center"><i
                                                        class="fas fa-circle fs-6 me-1"></i>High</span>
                                                <div>
                                                    <a href="javascript:void(0);" data-bs-toggle="dropdown"
                                                        aria-expanded="false">
                                                        <i class="fas fa-ellipsis-v"></i>
                                                    </a>
                                                    <div class="dropdown-menu notes-menu dropdown-menu-end">
                                                        <a href="#" class="dropdown-item" data-bs-toggle="modal"
                                                            data-bs-target="#edit-note-units"><span><i
                                                                    data-feather="edit"></i></span>Edit</a>
                                                        <a href="#" class="dropdown-item" data-bs-toggle="modal"
                                                            data-bs-target="#delete_modal"><span><i
                                                                    data-feather="trash-2"></i></span>Delete</a>
                                                        <a href="javascript:void(0);" class="dropdown-item"><span><i
                                                                    data-feather="star"></i></span>Not Important</a>
                                                        <a href="#" class="dropdown-item" data-bs-toggle="modal"
                                                            data-bs-target="#view-note-units"><span><i
                                                                    data-feather="eye"></i></span>View</a>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="my-3">
                                                <h5 class="text-truncate mb-1"><a href="javascript:void(0);">Backup
                                                        Files EOD</a></h5>
                                                <p class="mb-3 d-flex align-items-center text-dark"><i
                                                        class="ti ti-calendar me-1"></i>20 Jan 2024</p>
                                                <p class="text-truncate line-clamb-2 text-wrap">Project files should
                                                    be took backup before end of the day.</p>
                                            </div>
                                            <div
                                                class="d-flex align-items-center justify-content-between border-top pt-3">
                                                <div class="d-flex align-items-center">
                                                    <a href="javascript:void(0);" class="avatar avatar-md me-2">
                                                        <img src="{{URL::asset('build/img/profiles/avatar-05.jpg')}}" alt="Profile"
                                                            class="img-fluid rounded-circle">
                                                    </a>
                                                    <span class="text-info d-flex align-items-center"><i
                                                            class="fas fa-square square-rotate fs-10 me-1"></i>Personal</span>
                                                </div>
                                                <div class="d-flex align-items-center">
                                                    <a href="javascript:void(0);" class="me-2">
                                                        <span><i class="fas fa-star text-warning"></i></span>
                                                    </a>
                                                    <a href="javascript:void(0);">
                                                        <span><i class="ti ti-trash text-danger"></i></span>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 d-flex">
                                    <div class="card rounded-3 mb-4 flex-fill">
                                        <div class="card-body p-4">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <span
                                                    class="badge bg-outline-danger d-inline-flex align-items-center"><i
                                                        class="fas fa-circle fs-6 me-1"></i>Low</span>
                                                <div>
                                                    <a href="javascript:void(0);" data-bs-toggle="dropdown"
                                                        aria-expanded="false">
                                                        <i class="fas fa-ellipsis-v"></i>
                                                    </a>
                                                    <div class="dropdown-menu notes-menu dropdown-menu-end">
                                                        <a href="#" class="dropdown-item" data-bs-toggle="modal"
                                                            data-bs-target="#edit-note-units"><span><i
                                                                    data-feather="edit"></i></span>Edit</a>
                                                        <a href="#" class="dropdown-item" data-bs-toggle="modal"
                                                            data-bs-target="#delete_modal"><span><i
                                                                    data-feather="trash-2"></i></span>Delete</a>
                                                        <a href="javascript:void(0);" class="dropdown-item"><span><i
                                                                    data-feather="star"></i></span>Not Important</a>
                                                        <a href="#" class="dropdown-item" data-bs-toggle="modal"
                                                            data-bs-target="#view-note-units"><span><i
                                                                    data-feather="eye"></i></span>View</a>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="my-3">
                                                <h5 class="text-truncate mb-1"><a
                                                        href="javascript:void(0);">Download Server Logs</a></h5>
                                                <p class="mb-3 d-flex align-items-center text-dark"><i
                                                        class="ti ti-calendar me-1"></i>25 Jan 2024</p>
                                                <p class="text-truncate line-clamb-2 text-wrap">Server log is a text
                                                    document that contains a record of all activity.</p>
                                            </div>
                                            <div
                                                class="d-flex align-items-center justify-content-between border-top pt-3">
                                                <div class="d-flex align-items-center">
                                                    <a href="javascript:void(0);" class="avatar avatar-md me-2">
                                                        <img src="{{URL::asset('build/img/profiles/avatar-06.jpg')}}" alt="Profile"
                                                            class="img-fluid rounded-circle">
                                                    </a>
                                                    <span class="text-success d-flex align-items-center"><i
                                                            class="fas fa-square square-rotate fs-10 me-1"></i>Work</span>
                                                </div>
                                                <div class="d-flex align-items-center">
                                                    <a href="javascript:void(0);" class="me-2">
                                                        <span><i class="fas fa-star text-warning"></i></span>
                                                    </a>
                                                    <a href="javascript:void(0);">
                                                        <span><i class="ti ti-trash text-danger"></i></span>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 d-flex">
                                    <div class="card rounded-3 mb-4 flex-fill">
                                        <div class="card-body p-4">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <span
                                                    class="badge bg-outline-warning d-inline-flex align-items-center"><i
                                                        class="fas fa-circle fs-6 me-1"></i>Medium</span>
                                                <div>
                                                    <a href="javascript:void(0);" data-bs-toggle="dropdown"
                                                        aria-expanded="false">
                                                        <i class="fas fa-ellipsis-v"></i>
                                                    </a>
                                                    <div class="dropdown-menu notes-menu dropdown-menu-end">
                                                        <a href="#" class="dropdown-item" data-bs-toggle="modal"
                                                            data-bs-target="#edit-note-units"><span><i
                                                                    data-feather="edit"></i></span>Edit</a>
                                                        <a href="#" class="dropdown-item" data-bs-toggle="modal"
                                                            data-bs-target="#delete_modal"><span><i
                                                                    data-feather="trash-2"></i></span>Delete</a>
                                                        <a href="javascript:void(0);" class="dropdown-item"><span><i
                                                                    data-feather="star"></i></span>Not Important</a>
                                                        <a href="#" class="dropdown-item" data-bs-toggle="modal"
                                                            data-bs-target="#view-note-units"><span><i
                                                                    data-feather="eye"></i></span>View</a>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="my-3">
                                                <h5 class="text-truncate mb-1"><a href="javascript:void(0);">Team
                                                        meet at Starbucks</a></h5>
                                                <p class="mb-3 d-flex align-items-center text-dark"><i
                                                        class="ti ti-calendar me-1"></i>26 Jan 2024</p>
                                                <p class="text-truncate line-clamb-2 text-wrap">Meeting all teamets
                                                    at Starbucks for identifying them all.</p>
                                            </div>
                                            <div
                                                class="d-flex align-items-center justify-content-between border-top pt-3">
                                                <div class="d-flex align-items-center">
                                                    <a href="javascript:void(0);" class="avatar avatar-md me-2">
                                                        <img src="{{URL::asset('build/img/profiles/avatar-07.jpg')}}" alt="Profile"
                                                            class="img-fluid rounded-circle">
                                                    </a>
                                                    <span class="text-warning d-flex align-items-center"><i
                                                            class="fas fa-square square-rotate fs-10 me-1"></i>Social</span>
                                                </div>
                                                <div class="d-flex align-items-center">
                                                    <a href="javascript:void(0);" class="me-2">
                                                        <span><i class="fas fa-star text-warning"></i></span>
                                                    </a>
                                                    <a href="javascript:void(0);">
                                                        <span><i class="ti ti-trash text-danger"></i></span>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 d-flex">
                                    <div class="card rounded-3 mb-4 flex-fill">
                                        <div class="card-body p-4">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <span
                                                    class="badge bg-outline-success d-inline-flex align-items-center"><i
                                                        class="fas fa-circle fs-6 me-1"></i>High</span>
                                                <div>
                                                    <a href="javascript:void(0);" data-bs-toggle="dropdown"
                                                        aria-expanded="false">
                                                        <i class="fas fa-ellipsis-v"></i>
                                                    </a>
                                                    <div class="dropdown-menu notes-menu dropdown-menu-end">
                                                        <a href="#" class="dropdown-item" data-bs-toggle="modal"
                                                            data-bs-target="#edit-note-units"><span><i
                                                                    data-feather="edit"></i></span>Edit</a>
                                                        <a href="#" class="dropdown-item" data-bs-toggle="modal"
                                                            data-bs-target="#delete_modal"><span><i
                                                                    data-feather="trash-2"></i></span>Delete</a>
                                                        <a href="javascript:void(0);" class="dropdown-item"><span><i
                                                                    data-feather="star"></i></span>Not Important</a>
                                                        <a href="#" class="dropdown-item" data-bs-toggle="modal"
                                                            data-bs-target="#view-note-units"><span><i
                                                                    data-feather="eye"></i></span>View</a>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="my-3">
                                                <h5 class="text-truncate mb-1"><a href="javascript:void(0);">Create
                                                        a compost pile</a></h5>
                                                <p class="mb-3 d-flex align-items-center text-dark"><i
                                                        class="ti ti-calendar me-1"></i>27 Jan 2024</p>
                                                <p class="text-truncate line-clamb-2 text-wrap">Compost pile refers
                                                    to fruit and vegetable scraps, used tea, coffee grounds etc..
                                                </p>
                                            </div>
                                            <div
                                                class="d-flex align-items-center justify-content-between border-top pt-3">
                                                <div class="d-flex align-items-center">
                                                    <a href="javascript:void(0);" class="avatar avatar-md me-2">
                                                        <img src="{{URL::asset('build/img/profiles/avatar-08.jpg')}}" alt="Profile"
                                                            class="img-fluid rounded-circle">
                                                    </a>
                                                    <span class="text-warning d-flex align-items-center"><i
                                                            class="fas fa-square square-rotate fs-10 me-1"></i>Social</span>
                                                </div>
                                                <div class="d-flex align-items-center">
                                                    <a href="javascript:void(0);" class="me-2">
                                                        <span><i class="fas fa-star text-warning"></i></span>
                                                    </a>
                                                    <a href="javascript:void(0);">
                                                        <span><i class="ti ti-trash text-danger"></i></span>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 d-flex">
                                    <div class="card rounded-3 mb-4 flex-fill">
                                        <div class="card-body p-4">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <span
                                                    class="badge bg-outline-danger d-inline-flex align-items-center"><i
                                                        class="fas fa-circle fs-6 me-1"></i>Low</span>
                                                <div>
                                                    <a href="javascript:void(0);" data-bs-toggle="dropdown"
                                                        aria-expanded="false">
                                                        <i class="fas fa-ellipsis-v"></i>
                                                    </a>
                                                    <div class="dropdown-menu notes-menu dropdown-menu-end">
                                                        <a href="#" class="dropdown-item" data-bs-toggle="modal"
                                                            data-bs-target="#edit-note-units"><span><i
                                                                    data-feather="edit"></i></span>Edit</a>
                                                        <a href="#" class="dropdown-item" data-bs-toggle="modal"
                                                            data-bs-target="#delete_modal"><span><i
                                                                    data-feather="trash-2"></i></span>Delete</a>
                                                        <a href="javascript:void(0);" class="dropdown-item"><span><i
                                                                    data-feather="star"></i></span>Not Important</a>
                                                        <a href="#" class="dropdown-item" data-bs-toggle="modal"
                                                            data-bs-target="#view-note-units"><span><i
                                                                    data-feather="eye"></i></span>View</a>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="my-3">
                                                <h5 class="text-truncate mb-1"><a href="javascript:void(0);">Take a
                                                        hike at a local park</a></h5>
                                                <p class="mb-3 d-flex align-items-center text-dark"><i
                                                        class="ti ti-calendar me-1"></i>28 Jan 2024</p>
                                                <p class="text-truncate line-clamb-2 text-wrap">Hiking involves a
                                                    long energetic walk in a natural environment.</p>
                                            </div>
                                            <div
                                                class="d-flex align-items-center justify-content-between border-top pt-3">
                                                <div class="d-flex align-items-center">
                                                    <a href="javascript:void(0);" class="avatar avatar-md me-2">
                                                        <img src="{{URL::asset('build/img/profiles/avatar-09.jpg')}}" alt="Profile"
                                                            class="img-fluid rounded-circle">
                                                    </a>
                                                    <span class="text-info d-flex align-items-center"><i
                                                            class="fas fa-square square-rotate fs-10 me-1"></i>Personal</span>
                                                </div>
                                                <div class="d-flex align-items-center">
                                                    <a href="javascript:void(0);" class="me-2">
                                                        <span><i class="fas fa-star text-warning"></i></span>
                                                    </a>
                                                    <a href="javascript:void(0);">
                                                        <span><i class="ti ti-trash text-danger"></i></span>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 d-flex">
                                    <div class="card rounded-3 mb-4 flex-fill">
                                        <div class="card-body p-4">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <span
                                                    class="badge bg-outline-info d-inline-flex align-items-center"><i
                                                        class="fas fa-circle fs-6 me-1"></i>medium</span>
                                                <div>
                                                    <a href="javascript:void(0);" data-bs-toggle="dropdown"
                                                        aria-expanded="false">
                                                        <i class="fas fa-ellipsis-v"></i>
                                                    </a>
                                                    <div class="dropdown-menu notes-menu dropdown-menu-end">
                                                        <a href="#" class="dropdown-item" data-bs-toggle="modal"
                                                            data-bs-target="#edit-note-units"><span><i
                                                                    data-feather="edit"></i></span>Edit</a>
                                                        <a href="#" class="dropdown-item" data-bs-toggle="modal"
                                                            data-bs-target="#delete_modal"><span><i
                                                                    data-feather="trash-2"></i></span>Delete</a>
                                                        <a href="javascript:void(0);" class="dropdown-item"><span><i
                                                                    data-feather="star"></i></span>Not Important</a>
                                                        <a href="#" class="dropdown-item" data-bs-toggle="modal"
                                                            data-bs-target="#view-note-units"><span><i
                                                                    data-feather="eye"></i></span>View</a>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="my-3">
                                                <h5 class="text-truncate mb-1"><a
                                                        href="javascript:void(0);">Research a topic interested</a>
                                                </h5>
                                                <p class="mb-3 d-flex align-items-center text-dark"><i
                                                        class="ti ti-calendar me-1"></i>28 Jan 2024</p>
                                                <p class="text-truncate line-clamb-2 text-wrap">Research a topic
                                                    interested by listen actively and attentively.</p>
                                            </div>
                                            <div
                                                class="d-flex align-items-center justify-content-between border-top pt-3">
                                                <div class="d-flex align-items-center">
                                                    <a href="javascript:void(0);" class="avatar avatar-md me-2">
                                                        <img src="{{URL::asset('build/img/profiles/avatar-10.jpg')}}" alt="Profile"
                                                            class="img-fluid rounded-circle">
                                                    </a>
                                                    <span class="text-success d-flex align-items-center"><i
                                                            class="fas fa-square square-rotate fs-10 me-1"></i>Work</span>
                                                </div>
                                                <div class="d-flex align-items-center">
                                                    <a href="javascript:void(0);" class="me-2">
                                                        <span><i class="fas fa-star text-warning"></i></span>
                                                    </a>
                                                    <a href="javascript:void(0);">
                                                        <span><i class="ti ti-trash text-danger"></i></span>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="v-pills-settings" role="tabpanel"
                            aria-labelledby="v-pills-settings-tab">
                            <div class="row">
                                <div class="col-12 d-flex align-items-center justify-content-end">
                                    <a href="#" class="btn btn-danger mb-4">
                                        <span> <i class="ti ti-trash f-20 me-2"></i> </span>
                                        Restore all
                                    </a>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4 d-flex">
                                    <div class="card rounded-3 mb-4 flex-fill">
                                        <div class="card-body p-4">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <span
                                                    class="badge bg-outline-success d-inline-flex align-items-center"><i
                                                        class="fas fa-circle fs-6 me-1"></i>High</span>
                                                <div>
                                                    <a href="javascript:void(0);" data-bs-toggle="dropdown"
                                                        aria-expanded="false">
                                                        <i class="fas fa-ellipsis-v"></i>
                                                    </a>
                                                    <div class="dropdown-menu notes-menu dropdown-menu-end">
                                                        <a href="#" class="dropdown-item" data-bs-toggle="modal"
                                                            data-bs-target="#edit-note-units"><span><i
                                                                    data-feather="edit"></i></span>Edit</a>
                                                        <a href="#" class="dropdown-item" data-bs-toggle="modal"
                                                            data-bs-target="#delete_modal"><span><i
                                                                    data-feather="trash-2"></i></span>Delete</a>
                                                        <a href="javascript:void(0);" class="dropdown-item"><span><i
                                                                    data-feather="star"></i></span>Not Important</a>
                                                        <a href="#" class="dropdown-item" data-bs-toggle="modal"
                                                            data-bs-target="#view-note-units"><span><i
                                                                    data-feather="eye"></i></span>View</a>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="my-3">
                                                <h5 class="text-truncate mb-1"><a href="javascript:void(0);">Create
                                                        a compost pile</a></h5>
                                                <p class="mb-3 d-flex align-items-center text-dark"><i
                                                        class="ti ti-calendar me-1"></i>27 Jan 2024</p>
                                                <p class="text-truncate line-clamb-2 text-wrap">Compost pile refers
                                                    to fruit and vegetable scraps, used tea, coffee grounds etc..
                                                </p>
                                            </div>
                                            <div
                                                class="d-flex align-items-center justify-content-between border-top pt-3">
                                                <div class="d-flex align-items-center">
                                                    <a href="javascript:void(0);" class="avatar avatar-md me-2">
                                                        <img src="{{URL::asset('build/img/profiles/avatar-08.jpg')}}" alt="Profile"
                                                            class="img-fluid rounded-circle">
                                                    </a>
                                                    <span class="text-warning d-flex align-items-center"><i
                                                            class="fas fa-square square-rotate fs-10 me-1"></i>Social</span>
                                                </div>
                                                <div class="d-flex align-items-center">
                                                    <a href="javascript:void(0);" class="me-2">
                                                        <span><i class="fas fa-star text-warning"></i></span>
                                                    </a>
                                                    <a href="javascript:void(0);">
                                                        <span><i class="ti ti-trash text-danger"></i></span>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 d-flex">
                                    <div class="card rounded-3 mb-4 flex-fill">
                                        <div class="card-body p-4">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <span
                                                    class="badge bg-outline-danger d-inline-flex align-items-center"><i
                                                        class="fas fa-circle fs-6 me-1"></i>Low</span>
                                                <div>
                                                    <a href="javascript:void(0);" data-bs-toggle="dropdown"
                                                        aria-expanded="false">
                                                        <i class="fas fa-ellipsis-v"></i>
                                                    </a>
                                                    <div class="dropdown-menu notes-menu dropdown-menu-end">
                                                        <a href="#" class="dropdown-item" data-bs-toggle="modal"
                                                            data-bs-target="#edit-note-units"><span><i
                                                                    data-feather="edit"></i></span>Edit</a>
                                                        <a href="#" class="dropdown-item" data-bs-toggle="modal"
                                                            data-bs-target="#delete_modal"><span><i
                                                                    data-feather="trash-2"></i></span>Delete</a>
                                                        <a href="javascript:void(0);" class="dropdown-item"><span><i
                                                                    data-feather="star"></i></span>Not Important</a>
                                                        <a href="#" class="dropdown-item" data-bs-toggle="modal"
                                                            data-bs-target="#view-note-units"><span><i
                                                                    data-feather="eye"></i></span>View</a>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="my-3">
                                                <h5 class="text-truncate mb-1"><a href="javascript:void(0);">Take a
                                                        hike at a local park</a></h5>
                                                <p class="mb-3 d-flex align-items-center text-dark"><i
                                                        class="ti ti-calendar me-1"></i>28 Jan 2024</p>
                                                <p class="text-truncate line-clamb-2 text-wrap">Hiking involves a
                                                    long energetic walk in a natural environment.</p>
                                            </div>
                                            <div
                                                class="d-flex align-items-center justify-content-between border-top pt-3">
                                                <div class="d-flex align-items-center">
                                                    <a href="javascript:void(0);" class="avatar avatar-md me-2">
                                                        <img src="{{URL::asset('build/img/profiles/avatar-09.jpg')}}" alt="Profile"
                                                            class="img-fluid rounded-circle">
                                                    </a>
                                                    <span class="text-info d-flex align-items-center"><i
                                                            class="fas fa-square square-rotate fs-10 me-1"></i>Personal</span>
                                                </div>
                                                <div class="d-flex align-items-center">
                                                    <a href="javascript:void(0);" class="me-2">
                                                        <span><i class="fas fa-star text-warning"></i></span>
                                                    </a>
                                                    <a href="javascript:void(0);">
                                                        <span><i class="ti ti-trash text-danger"></i></span>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 d-flex">
                                    <div class="card rounded-3 mb-4 flex-fill">
                                        <div class="card-body p-4">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <span
                                                    class="badge bg-outline-info d-inline-flex align-items-center"><i
                                                        class="fas fa-circle fs-6 me-1"></i>medium</span>
                                                <div>
                                                    <a href="javascript:void(0);" data-bs-toggle="dropdown"
                                                        aria-expanded="false">
                                                        <i class="fas fa-ellipsis-v"></i>
                                                    </a>
                                                    <div class="dropdown-menu notes-menu dropdown-menu-end">
                                                        <a href="#" class="dropdown-item" data-bs-toggle="modal"
                                                            data-bs-target="#edit-note-units"><span><i
                                                                    data-feather="edit"></i></span>Edit</a>
                                                        <a href="#" class="dropdown-item" data-bs-toggle="modal"
                                                            data-bs-target="#delete_modal"><span><i
                                                                    data-feather="trash-2"></i></span>Delete</a>
                                                        <a href="javascript:void(0);" class="dropdown-item"><span><i
                                                                    data-feather="star"></i></span>Not Important</a>
                                                        <a href="#" class="dropdown-item" data-bs-toggle="modal"
                                                            data-bs-target="#view-note-units"><span><i
                                                                    data-feather="eye"></i></span>View</a>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="my-3">
                                                <h5 class="text-truncate mb-1"><a
                                                        href="javascript:void(0);">Research a topic interested</a>
                                                </h5>
                                                <p class="mb-3 d-flex align-items-center text-dark"><i
                                                        class="ti ti-calendar me-1"></i>28 Jan 2024</p>
                                                <p class="text-truncate line-clamb-2 text-wrap">Research a topic
                                                    interested by listen actively and attentively.</p>
                                            </div>
                                            <div
                                                class="d-flex align-items-center justify-content-between border-top pt-3">
                                                <div class="d-flex align-items-center">
                                                    <a href="javascript:void(0);" class="avatar avatar-md me-2">
                                                        <img src="{{URL::asset('build/img/profiles/avatar-10.jpg')}}" alt="Profile"
                                                            class="img-fluid rounded-circle">
                                                    </a>
                                                    <span class="text-success d-flex align-items-center"><i
                                                            class="fas fa-square square-rotate fs-10 me-1"></i>Work</span>
                                                </div>
                                                <div class="d-flex align-items-center">
                                                    <a href="javascript:void(0);" class="me-2">
                                                        <span><i class="fas fa-star text-warning"></i></span>
                                                    </a>
                                                    <a href="javascript:void(0);">
                                                        <span><i class="ti ti-trash text-danger"></i></span>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row custom-pagination">
                        <div class="col-md-12">
                            <div class="paginations d-flex justify-content-end">
                                <span><i class="fas fa-chevron-left"></i></span>
                                <ul class="d-flex align-items-center page-wrap">
                                    <li>
                                        <a href="javascript:void(0);" class="active">
                                            1
                                        </a>
                                    </li>
                                    <li>
                                        <a href="javascript:void(0);">
                                            2
                                        </a>
                                    </li>
                                    <li>
                                        <a href="javascript:void(0);">
                                            3
                                        </a>
                                    </li>
                                    <li>
                                        <a href="javascript:void(0);">
                                            4
                                        </a>
                                    </li>
                                </ul>
                                <span><i class="fas fa-chevron-right"></i></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>				
        </div>
        <div class="footer d-sm-flex align-items-center justify-content-between border-top bg-white p-3">
            <p class="mb-0">2014 - 2025 &copy; DreamsPOS. All Right Reserved</p>
            <p>Designed &amp; Developed by <a href="javascript:void(0);" class="text-primary">Dreams</a></p>
        </div>
    </div>
@endsection
