<?php $page = 'activities'; ?>
@extends('layout.mainlayout')
@section('content')
    <div class="page-wrapper">
        <div class="content mb-4">
            <div class="page-header">
                <div class="page-title">
                    <h4>All Notifications</h4>
                    <h6>View your all activities</h6>
                </div>
            </div>
            
            <div class="card mb-3 border shadow-none">
                <div class="px-3 py-3">
                    <div class="d-flex align-items-center" data-toggle="tooltip" data-placement="right" data-title="2 hrs ago" data-original-title="" title="">
                        <div class="d-flex me-2">
                            <a href="{{url('profile')}}" class="avatar avatar-lg avatar-rounded">
                                <img src="{{URL::asset('build/img/customer/profile3.jpg')}}" alt="Elwis Mathew">
                            </a>
                        </div>
                        <div class="flex-fill ml-3">
                            <p class="text-sm lh-140 mb-0"><a href="{{url('profile')}}" class="h6">Elwis Mathew</a> <span>added a new product</span> <a href="javascript:void(0);" class="h6">Redmi Pro 7 Mobile</a></p>
                            <small><i class="far fa-clock me-1"></i>Just Now</small>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card mb-3 border shadow-none">
                <div class="px-3 py-3">
                    <div class="d-flex align-items-center" data-toggle="tooltip" data-placement="right" data-title="2 hrs ago" data-original-title="" title="">
                        <div class="d-flex me-2">
                            <a href="{{url('profile')}}" class="avatar avatar-lg avatar-rounded">
                                <img src="{{URL::asset('build/img/customer/profile4.jpg')}}" alt="Elizabeth Olsen">
                            </a>
                        </div>
                        <div class="flex-fill ml-3">
                            <p class="text-sm lh-140 mb-0"><a href="{{url('profile')}}" class="h6">Elizabeth Olsen</a> <span>added a new product category</span> <a href="javascript:void(0);" class="h6">Desktop Computers</a></p>
                            <small><i class="far fa-clock me-1"></i>4 min ago</small>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card mb-3 border shadow-none">
                <div class="px-3 py-3">
                    <div class="d-flex align-items-center" data-toggle="tooltip" data-placement="right" data-title="2 hrs ago" data-original-title="" title="">
                        <div class="d-flex me-2">
                            <a href="{{url('profile')}}" class="avatar avatar-lg avatar-rounded">
                                <img src="{{URL::asset('build/img/customer/profile5.jpg')}}" alt="William Smith">
                            </a>
                        </div>
                        <div class="flex-fill ml-3">
                            <p class="text-sm lh-140 mb-0"><a href="{{url('profile')}}" class="h6">William Smith</a> <span>added a new sales list for</span> <a href="javascript:void(0);" class="h6">January Month</a></p>
                            <small><i class="far fa-clock me-1"></i>6 min ago</small>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card mb-3 border shadow-none">
                <div class="px-3 py-3">
                    <div class="d-flex align-items-center" data-toggle="tooltip" data-placement="right" data-title="2 hrs ago" data-original-title="" title="">
                        <div class="d-flex me-2">
                            <a href="{{url('profile')}}" class="avatar avatar-lg avatar-rounded">
                                <img src="{{URL::asset('build/img/customer/customer4.jpg')}}" alt="Lesley Grauer">
                            </a>
                        </div>
                        <div class="flex-fill ml-3">
                            <p class="text-sm lh-140 mb-0"><a href="{{url('profile')}}" class="h6">Lesley Grauer</a> <span>has updated invoice</span> <a href="javascript:void(0);" class="h6">#987654</a></p>
                            <small><i class="far fa-clock me-1"></i>12 min ago</small>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card mb-3 border shadow-none">
                <div class="px-3 py-3">
                    <div class="d-flex align-items-center" data-toggle="tooltip" data-placement="right" data-title="2 hrs ago" data-original-title="" title="">
                        <div class="d-flex me-2">
                            <span class="avatar avatar-lg bg-success avatar-rounded">
                                <span class="avatar-title">CE</span>
                            </span>
                        </div>
                        <div class="flex-fill ml-3">
                            <p class="text-sm lh-140 mb-0"><a href="{{url('profile')}}" class="h6">Carl Evans</a> <span>adjust the stock</span> <a href="javascript:void(0);" class="h6">Apple Series 5 Watch</a></p>
                            <small><i class="far fa-clock me-1"></i>2 days ago</small>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card mb-3 border shadow-none">
                <div class="px-3 py-3">
                    <div class="d-flex align-items-center" data-toggle="tooltip" data-placement="right" data-title="2 hrs ago" data-original-title="" title="">
                        <div class="d-flex me-2">
                            <a href="{{url('profile')}}" class="avatar avatar-lg bg-primary avatar-rounded">
                                <span class="avatar-title">MR</span>
                            </a>
                        </div>
                        <div class="flex-fill ml-3">
                            <p class="text-sm lh-140 mb-0"><a href="{{url('profile')}}" class="h6">Minerva Rameriz</a> <span>accepted Quotation</span> <a href="javascript:void(0);" class="h6">#QUO0001</a></p>
                            <small><i class="far fa-clock me-1"></i>1 month ago</small>
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
