<?php $page = 'all-blog'; ?>
@extends('layout.mainlayout')
@section('content')
    <div class="page-wrapper">
        <div class="content">
            <div class="page-header">
                <div class="add-item d-flex">
                    <div class="page-title">
                        <h4>Blogs</h4>
                        <h6>Manage your blogs</h6>
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
                    <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#add_blog"><i class="ti ti-circle-plus me-1"></i>Add Blog</a>
                </div>
            </div>

            <!-- /product list -->
            <div class="card">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between flex-wrap row-gap-3">
                        <div class="position-relative input-icon me-3">
                            <span class="input-icon-addon">
                                <i class="ti ti-search"></i>
                            </span>
                            <input type="text" class="form-control" placeholder="Search">
                        </div>
                        <div class="d-flex my-xl-auto right-content align-items-center flex-wrap row-gap-3">
                            
                            <div class="dropdown me-3">
                                <a href="javascript:void(0);" class="dropdown-toggle btn btn-white btn-md d-inline-flex align-items-center" data-bs-toggle="dropdown">
                                    Select Status
                                </a>
                                <ul class="dropdown-menu  dropdown-menu-end p-3">
                                    <li>
                                        <a href="javascript:void(0);" class="dropdown-item rounded-1">Active</a>
                                    </li>
                                    <li>
                                        <a href="javascript:void(0);" class="dropdown-item rounded-1">Inactive</a>
                                    </li>
                                </ul>
                            </div>
                            <div class="dropdown">
                                <a href="javascript:void(0);" class="dropdown-toggle btn btn-white d-inline-flex align-items-center" data-bs-toggle="dropdown">
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
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row justify-content-center">
                <div class="col-xxl-4 col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <div class="img-sec w-100 position-relative mb-3">
                                <a href="{{url('blog-details')}}"><img src="{{URL::asset('build/img/blogs/blog-01.jpg')}}" class="img-fluid rounded w-100" alt="img"></a>
                                <div>
                                    <a href="{{url('blog-details')}}" class="trend-tag badge bg-soft-info shadow-none fs-10 fw-medium">Features</a>
                                    <span class="badge badge-success dot-icon"><i class="ti ti-point-filled"></i> Active</span>
                                </div>
                            </div>   
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <div class="d-flex align-items-center">
                                    <span class="me-2 d-flex align-items-center"><i class="ti ti-calendar me-1"></i> 24 Dec 2024</span>
                                    <a href="javascript:void(0);" class="border-start link-default fs-14 fw-normal ps-2 me-2 text-truncate d-inline-flex align-items-center d-inline-flex align-items-center"><img src="{{URL::asset('build/img/users/user-02.jpg')}}" class="avatar avatar-xs rounded-circle me-2 flex-shrink-0" alt="Img">Gertrude Bowie</a>
                                </div>
                                <div class="d-flex align-items-center">
                                    <a href="#" class="p-1 d-flex align-items-center me-1" data-bs-toggle="modal" data-bs-target="#edit_blog"><i class="ti ti-edit"></i></a>
                                    <a href="#" class="link-default p-1 d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#delete_modal"><i class="ti ti-trash"></i></a>
                                </div>
                            </div>
                            <h5>
                                <a href="{{url('blog-details')}}" class="fs-16 text-truncate">What is a POS System? A Beginner’s Guide</a>
                            </h5>
                        </div>
                    </div>
                </div>
                <div class="col-xxl-4 col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <div class="img-sec w-100 position-relative mb-3">
                                <a href="{{url('blog-details')}}"><img src="{{URL::asset('build/img/blogs/blog-02.jpg')}}" class="img-fluid rounded w-100" alt="img"></a>
                                <div>
                                    <a href="{{url('blog-details')}}" class="trend-tag badge bg-soft-info shadow-none">Business</a>
                                    <span class="badge badge-success dot-icon"><i class="ti ti-point-filled"></i> Active</span>
                                </div>
                            </div>   
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <div class="d-flex align-items-center">
                                    <span class="me-2 d-flex align-items-center"><i class="ti ti-calendar me-1"></i> 20 Dec 2024</span>
                                    <a href="javascript:void(0);" class="border-start link-default fs-14 fw-normal ps-2 me-2 text-truncate d-inline-flex align-items-center"><img src="{{URL::asset('build/img/users/user-03.jpg')}}" class="avatar avatar-xs rounded-circle me-2 flex-shrink-0" alt="Img">Edward Marcus</a>
                                </div>
                                <div class="d-flex align-items-center">
                                    <a href="#" class="p-1 d-flex align-items-center me-1" data-bs-toggle="modal" data-bs-target="#edit_blog"><i class="ti ti-edit"></i></a>
                                        <a href="#" class="p-1 d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#delete_modal"><i class="ti ti-trash"></i></a>
                                </div>
                            </div>
                            <h5>
                                <a href="{{url('blog-details')}}" class="fs-16 text-truncate">Best POS Systems for Retail Businesses</a>
                            </h5>
                        </div>
                    </div>
                </div>
                <div class="col-xxl-4 col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <div class="img-sec w-100 position-relative mb-3">
                                <a href="{{url('blog-details')}}"><img src="{{URL::asset('build/img/blogs/blog-03.jpg')}}" class="img-fluid rounded w-100" alt="img"></a>
                                <div>
                                    <a href="{{url('blog-details')}}" class="trend-tag badge bg-soft-info shadow-none">Features</a>
                                    <span class="badge badge-success dot-icon"><i class="ti ti-point-filled"></i> Active</span>
                                </div>
                            </div>   
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <div class="d-flex align-items-center">
                                    <span class="me-2 d-flex align-items-center"><i class="ti ti-calendar me-1"></i> 11 Dec 2024</span>
                                    <a href="javascript:void(0);" class="border-start link-default fs-14 fw-normal ps-2 me-2 text-truncate d-inline-flex align-items-center"><img src="{{URL::asset('build/img/users/user-05.jpg')}}" class="avatar avatar-xs rounded-circle me-2 flex-shrink-0" alt="Img">Mark Phillips</a>
                                </div>
                                <div class="d-flex align-items-center">
                                    <a href="#" class="p-1 d-flex align-items-center me-1" data-bs-toggle="modal" data-bs-target="#edit_blog"><i class="ti ti-edit"></i></a>
                                        <a href="#" class="p-1 d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#delete_modal"><i class="ti ti-trash"></i></a>
                                </div>
                            </div>
                            <h5>
                                <a href="{{url('blog-details')}}" class="fs-16 text-truncate">Key Features of a Modern POS</a>
                            </h5>                        
                        </div>
                    </div>
                </div>
                <div class="col-xxl-4 col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <div class="img-sec w-100 position-relative mb-3">
                                <a href="{{url('blog-details')}}"><img src="{{URL::asset('build/img/blogs/blog-04.jpg')}}" class="img-fluid rounded w-100" alt="img"></a>
                                <div>
                                    <a href="{{url('blog-details')}}" class="trend-tag badge bg-soft-info shadow-none">Business</a>
                                    <span class="badge badge-success dot-icon"><i class="ti ti-point-filled"></i> Active</span>
                                </div>
                            </div>   
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <div class="d-flex align-items-center">
                                    <span class="me-2 d-flex align-items-center"><i class="ti ti-calendar me-1"></i> 25 Nov 2024</span>
                                    <a href="javascript:void(0);" class="border-start link-default fs-14 fw-normal ps-2 me-2 text-truncate d-inline-flex align-items-center"><img src="{{URL::asset('build/img/users/user-04.jpg')}}" class="avatar avatar-xs rounded-circle me-2 flex-shrink-0" alt="Img">Nidia Hale</a>
                                </div>
                                <div class="d-flex align-items-center">
                                    <a href="#" class="p-1 d-flex align-items-center me-1" data-bs-toggle="modal" data-bs-target="#edit_blog"><i class="ti ti-edit"></i></a>
                                        <a href="#" class="p-1 d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#delete_modal"><i class="ti ti-trash"></i></a>
                                </div>
                            </div>
                            <h5>
                                <a href="{{url('blog-details')}}" class="fs-16 text-truncate">Integrating POS with E-Commerce</a>
                            </h5>                    
                        </div>
                    </div>
                </div>
                <div class="col-xxl-4 col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <div class="img-sec w-100 position-relative mb-3">
                                <a href="{{url('blog-details')}}"><img src="{{URL::asset('build/img/blogs/blog-05.jpg')}}" class="img-fluid rounded w-100" alt="img"></a>
                                <div>
                                    <a href="{{url('blog-details')}}" class="trend-tag badge bg-soft-info shadow-none">Features</a>
                                    <span class="badge badge-success dot-icon"><i class="ti ti-point-filled"></i> Active</span>
                                </div>
                            </div>   
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <div class="d-flex align-items-center">
                                    <span class="me-2 d-flex align-items-center"><i class="ti ti-calendar me-1"></i> 16 Nov 2024</span>
                                    <a href="javascript:void(0);" class="border-start link-default fs-14 fw-normal ps-2 me-2 text-truncate d-inline-flex align-items-center"><img src="{{URL::asset('build/img/users/user-06.jpg')}}" class="avatar avatar-xs rounded-circle me-2 flex-shrink-0" alt="Img">Rebecca Dale</a>
                                </div>
                                <div class="d-flex align-items-center">
                                    <a href="#" class="p-1 d-flex align-items-center me-1" data-bs-toggle="modal" data-bs-target="#edit_blog"><i class="ti ti-edit"></i></a>
                                        <a href="#" class="p-1 d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#delete_modal"><i class="ti ti-trash"></i></a>
                                </div>
                            </div>
                            <h5>
                                <a href="{{url('blog-details')}}" class="fs-16 text-truncate">AI & the Future of POS Systems</a>
                            </h5>                        
                        </div>
                    </div>
                </div>
                <div class="col-xxl-4 col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <div class="img-sec w-100 position-relative mb-3">
                                <a href="{{url('blog-details')}}"><img src="{{URL::asset('build/img/blogs/blog-06.jpg')}}" class="img-fluid rounded w-100" alt="img"></a>
                                <div>
                                    <a href="{{url('blog-details')}}" class="trend-tag badge bg-soft-info shadow-none">Technology</a>
                                    <span class="badge badge-success dot-icon"><i class="ti ti-point-filled"></i> Active</span>
                                </div>
                            </div>   
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <div class="d-flex align-items-center">
                                    <span class="me-2 d-flex align-items-center"><i class="ti ti-calendar me-1"></i> 10 Nov 2024</span>
                                    <a href="javascript:void(0);" class="border-start link-default fs-14 fw-normal ps-2 me-2 text-truncate d-inline-flex align-items-center"><img src="{{URL::asset('build/img/users/user-08.jpg')}}" class="avatar avatar-xs rounded-circle me-2 flex-shrink-0" alt="Img">Jimmy Johnson</a>
                                </div>
                                <div class="d-flex align-items-center">
                                    <a href="#" class="p-1 d-flex align-items-center me-1" data-bs-toggle="modal" data-bs-target="#edit_blog"><i class="ti ti-edit"></i></a>
                                        <a href="#" class="p-1 d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#delete_modal"><i class="ti ti-trash"></i></a>
                                </div>
                            </div>
                            <h5>
                                <a href="{{url('blog-details')}}" class="fs-16 text-truncate">Retail vs Restaurant POS: Key Differences</a>
                            </h5>                       
                        </div>
                    </div>
                </div>
                <div class="col-xxl-4 col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <div class="img-sec w-100 position-relative mb-3">
                                <a href="{{url('blog-details')}}"><img src="{{URL::asset('build/img/blogs/blog-07.jpg')}}" class="img-fluid rounded w-100" alt="img"></a>
                                <div>
                                    <a href="{{url('blog-details')}}" class="trend-tag badge bg-soft-info shadow-none">Features</a>
                                    <span class="badge badge-success dot-icon"><i class="ti ti-point-filled"></i> Active</span>
                                </div>
                            </div>   
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <div class="d-flex align-items-center">
                                    <span class="me-2 d-flex align-items-center"><i class="ti ti-calendar me-1"></i> 23 Oct 2024</span>
                                    <a href="javascript:void(0);" class="border-start link-default fs-14 fw-normal ps-2 me-2 text-truncate d-inline-flex align-items-center"><img src="{{URL::asset('build/img/users/user-07.jpg')}}" class="avatar avatar-xs rounded-circle me-2 flex-shrink-0" alt="Img">Stanley Pierre</a>
                                </div>
                                <div class="d-flex align-items-center">
                                    <a href="#" class="p-1 d-flex align-items-center me-1" data-bs-toggle="modal" data-bs-target="#edit_blog"><i class="ti ti-edit"></i></a>
                                        <a href="#" class="p-1 d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#delete_modal"><i class="ti ti-trash"></i></a>
                                </div>
                            </div>
                            <h5>
                                <a href="{{url('blog-details')}}" class="fs-16 text-truncate">Understanding PCI Compliance for POS </a>
                            </h5>                        
                        </div>
                    </div>
                </div>
                <div class="col-xxl-4 col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <div class="img-sec w-100 position-relative mb-3">
                                <a href="{{url('blog-details')}}"><img src="{{URL::asset('build/img/blogs/blog-08.jpg')}}" class="img-fluid rounded w-100" alt="img"></a>
                                <div>
                                    <a href="{{url('blog-details')}}" class="trend-tag badge bg-soft-info shadow-none">Technology</a>
                                    <span class="badge badge-success dot-icon"><i class="ti ti-point-filled"></i> Active</span>
                                </div>
                            </div>   
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <div class="d-flex align-items-center">
                                    <span class="me-2 d-flex align-items-center"><i class="ti ti-calendar me-1"></i> 14 Oct 2024</span>
                                    <a href="javascript:void(0);" class="border-start link-default fs-14 fw-normal ps-2 me-2 text-truncate d-inline-flex align-items-center"><img src="{{URL::asset('build/img/users/user-10.jpg')}}" class="avatar avatar-xs rounded-circle me-2 flex-shrink-0" alt="Img">Alice Garcia</a>
                                </div>
                                <div class="d-flex align-items-center">
                                    <a href="#" class="p-1 d-flex align-items-center me-1" data-bs-toggle="modal" data-bs-target="#edit_blog"><i class="ti ti-edit"></i></a>
                                        <a href="#" class="p-1 d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#delete_modal"><i class="ti ti-trash"></i></a>
                                </div>
                            </div>
                            <h5>
                                <a href="{{url('blog-details')}}" class="fs-16 text-truncate">Cloud vs On-Premise POS: Which is Better?</a>
                            </h5>                      
                        </div>
                    </div>
                </div>
                <div class="col-xxl-4 col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <div class="img-sec w-100 position-relative mb-3">
                                <a href="{{url('blog-details')}}"><img src="{{URL::asset('build/img/blogs/blog-09.jpg')}}" class="img-fluid rounded w-100" alt="img"></a>
                                <div>
                                    <a href="{{url('blog-details')}}" class="trend-tag badge bg-soft-info shadow-none">Features</a>
                                    <span class="badge badge-success dot-icon"><i class="ti ti-point-filled"></i> Active</span>
                                </div>
                            </div>   
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <div class="d-flex align-items-center">
                                    <span class="me-2 d-flex align-items-center"><i class="ti ti-calendar me-1"></i> 08 Oct 2024</span>
                                    <a href="javascript:void(0);" class="border-start link-default fs-14 fw-normal ps-2 me-2 text-truncate d-inline-flex align-items-center"><img src="{{URL::asset('build/img/users/user-09.jpg')}}" class="avatar avatar-xs rounded-circle me-2 flex-shrink-0" alt="Img">James Currier</a>
                                </div>
                                <div class="d-flex align-items-center">
                                    <a href="#" class="p-1 d-flex align-items-center me-1" data-bs-toggle="modal" data-bs-target="#edit_blog"><i class="ti ti-edit"></i></a>
                                        <a href="#" class="p-1 d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#delete_modal"><i class="ti ti-trash"></i></a>
                                </div>
                            </div>
                            <h5>
                                <a href="{{url('blog-details')}}" class="fs-16 text-truncate">The Role of POS in Inventory Management</a>
                            </h5>
                        </div>
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