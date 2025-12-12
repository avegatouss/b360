<?php $page = 'audio-call'; ?>
@extends('layout.mainlayout')
@section('content')
    <div class="page-wrapper">
        <div class="content mb-3">
            <div class="page-header">
                <div class="add-item d-flex">
                    <div class="page-title">
                        <h4>Calls</h4>
                        <h6>Manage your calls</h6>
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
                <div class="page-btn">
                    <a href="#" class="btn btn-primary"><i class="ti ti-circle-plus me-1"></i>Add People</a>
                </div>
            </div>
            
            <div class="row">

                <!-- Call -->
                <div class="col-xxl-12">
                    <div class="card incoming-call mb-0">
                        <div class="card-header">
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center">
                                    <span class="avatar avatar-lg avatar-rounded me-2">
                                        <img src="{{URL::asset('build/img/users/user-27.jpg')}}" class="img-fluid rounded-circle" alt="img">
                                    </span>
                                    <div>
                                        <h5 class="mb-1"><a href="#">Anthony Lewis</a></h5>
                                        <span class="d-block">Online</span>
                                    </div>
                                </div>
                                <a href="#" class="avatar avatar-md rounded-circle bg-secondary-transparent text-dark">
                                    <i class="ti ti-user-plus fs-20"></i>
                                </a>
                            </div>
                        </div>
                        <div class="card-body position-relative text-center d-flex flex-column justify-content-center">
                            <div class="voice-call-img mb-3">
                                <img src="{{URL::asset('build/img/users/user-27.jpg')}}" class="img-fluid rounded-circle" alt="img">
                            </div>
                            <h4>Anthony Lewis</h4>
                            <p>00:24</p>
                            <a href="#" class="avatar avatar-xl position-absolute end-0 bottom-0 m-3"><img src="{{URL::asset('build/img/users/user-05.jpg')}}" alt="Img"></a>
                        </div>
                        <div class="card-footer">
                            <div class="d-flex align-items-center justify-content-center">
                                <a href="#" class="btn btn-light call-item py-1 px-2 d-flex align-items-center justify-content-center rounded-circle me-3"><i class="ti ti-video fs-20"></i></a>
                                <a href="#" class="btn btn-danger call-item py-1 px-2 d-flex align-items-center justify-content-center rounded-circle me-3"><i class="ti ti-phone fs-20"></i></a>
                                <a href="#" class="btn btn-light call-item py-1 px-2 d-flex align-items-center justify-content-center rounded-circle"><i class="ti ti-microphone fs-20"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /Call -->

            </div>

        </div>
        <div class="footer d-sm-flex align-items-center justify-content-between border-top bg-white p-3">
            <p class="mb-0">2014 - 2025 &copy; DreamsPOS. All Right Reserved</p>
            <p>Designed &amp; Developed by <a href="javascript:void(0);" class="text-primary">Dreams</a></p>
        </div>
    </div> 

@endsection
