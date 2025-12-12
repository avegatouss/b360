<?php $page = 'faq'; ?>
@extends('layout.mainlayout')
@section('content')
    <div class="page-wrapper">
        <div class="content settings-content">
            <div class="page-header settings-pg-header">
                <div class="add-item d-flex">
                    <div class="page-title">
                        <h4>Settings</h4>
                        <h6>Manage your settings on portal</h6>
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
            <div class="row">
                <div class="col-xl-12">
                        <div class="settings-wrapper d-flex">
                        <div class="settings-sidebar" id="sidebar2">
                            <div class="sidebar-inner slimscroll">
                                <div id="sidebar-menu5" class="sidebar-menu">
                                    <h4 class="fw-bold fs-18 mb-2 pb-2">Settings</h4>
                                    <ul>
                                        <li class="submenu-open">
                                            <ul>
                                                <li class="submenu">
                                                    <a href="javascript:void(0);">
                                                        <i class="ti ti-settings fs-18"></i>
                                                        <span class="fs-14 fw-medium ms-2">General Settings</span>
                                                        <span class="menu-arrow"></span>
                                                    </a>
                                                    <ul>
                                                        <li><a href="{{url('general-settings')}}">Profile</a></li>
                                                        <li><a href="{{url('security-settings')}}">Security</a></li>
                                                        <li><a href="{{url('notification')}}">Notifications</a></li>
                                                        <li><a href="{{url('connected-apps')}}">Connected Apps</a></li>
                                                    </ul>
                                                </li>
                                                <li class="submenu">
                                                    <a href="javascript:void(0);">
                                                        <i class="ti ti-world fs-18"></i>
                                                        <span class="fs-14 fw-medium ms-2">Website Settings</span>
                                                        <span class="menu-arrow"></span>
                                                    </a>
                                                    <ul>
                                                        <li><a href="{{url('system-settings')}}">System Settings</a></li>
                                                        <li><a href="{{url('company-settings')}}">Company Settings </a></li>
                                                        <li><a href="{{url('localization-settings')}}">Localization</a></li>
                                                        <li><a href="{{url('prefixes')}}">Prefixes</a></li>
                                                        <li><a href="{{url('preference')}}">Preference</a></li>
                                                        <li><a href="{{url('appearance')}}">Appearance</a></li>
                                                        <li><a href="{{url('social-authentication')}}">Social Authentication</a></li>
                                                        <li><a href="{{url('language-settings')}}">Language</a></li>
                                                    </ul>
                                                </li>
                                                <li class="submenu">
                                                    <a href="javascript:void(0);" class="active subdrop">
                                                        <i class="ti ti-device-mobile fs-18"></i>
                                                        <span class="fs-14 fw-medium ms-2">App Settings</span>
                                                        <span class="menu-arrow"></span>
                                                    </a>
                                                    <ul>
                                                        <li><a href="{{url('invoice-settings')}}">Invoice Settings</a></li>
                                                        <li><a href="{{url('invoice-templates')}}" class="active">Invoice Templates</a></li>
                                                        <li><a href="{{url('printer-settings')}}">Printer </a></li>
                                                        <li><a href="{{url('pos-settings')}}">POS</a></li>
                                                        <li><a href="{{url('signatures')}}">Signatures</a></li>
                                                        <li><a href="{{url('custom-fields')}}">Custom Fields</a></li>
                                                    </ul>
                                                </li>
                                                <li class="submenu">
                                                    <a href="javascript:void(0);">
                                                        <i class="ti ti-device-desktop fs-18"></i>
                                                        <span class="fs-14 fw-medium ms-2">System Settings</span>
                                                        <span class="menu-arrow"></span>
                                                    </a>
                                                    <ul>
                                                        <li class="submenu submenu-two"><a href="javascript:void(0);">Email<span class="menu-arrow inside-submenu"></span></a>
                                                            <ul>
                                                                <li><a href="{{url('email-settings')}}">Email Settings</a></li>
                                                                <li><a href="{{url('email-templates')}}">Email Templates</a></li>
                                                            </ul>
                                                        </li>
                                                        <li class="submenu submenu-two"><a href="javascript:void(0);">SMS<span class="menu-arrow inside-submenu"></span></a>
                                                            <ul>
                                                                <li><a href="{{url('sms-settings')}}">SMS Settings</a></li>
                                                                <li><a href="{{url('sms-templates')}}">SMS Templates</a></li>
                                                            </ul>
                                                        </li>
                                                        
                                                        <li><a href="{{url('otp-settings')}}">OTP</a></li>
                                                        <li><a href="{{url('gdpr-settings')}}">GDPR Cookies</a></li>
                                                    </ul>
                                                </li>
                                                <li class="submenu">
                                                    <a href="javascript:void(0);">
                                                        <i class="ti ti-settings-dollar fs-18"></i>
                                                        <span class="fs-14 fw-medium ms-2">Financial Settings</span>
                                                        <span class="menu-arrow"></span>
                                                    </a>
                                                    <ul>
                                                        <li><a href="{{url('payment-gateway-settings')}}">Payment Gateway</a></li>
                                                        <li><a href="{{url('bank-settings-grid')}}">Bank Accounts </a></li>
                                                        <li><a href="{{url('tax-rates')}}">Tax Rates</a></li>
                                                        <li><a href="{{url('currency-settings')}}">Currencies</a></li>
                                                    </ul>
                                                </li>
                                                <li class="submenu">
                                                    <a href="javascript:void(0);">
                                                        <i class="ti ti-settings-2 fs-18"></i>
                                                        <span class="fs-14 fw-medium ms-2">Other Settings</span>
                                                        <span class="menu-arrow"></span>
                                                    </a>
                                                    <ul>
                                                        <li><a href="{{url('storage-settings')}}">Storage</a></li>
                                                        <li><a href="{{url('ban-ip-address')}}">Ban IP Address </a></li>
                                                    </ul>
                                                </li>
                                            </ul>								
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="card flex-fill mb-0">
                            <form action="{{url('invoice-settings')}}">
                                <div class="card-header">
                                    <h4>Invoice Templates</h4>
                                </div>
                                <div class="card-body pb-0">
                                    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
                                        <ul class="nav nav-pills low-stock-tab d-flex me-2 mb-0" id="pills-tab" role="tablist">
                                            <li class="nav-item" role="presentation">
                                                <button class="nav-link active" id="pills-home-tab" data-bs-toggle="pill" data-bs-target="#pills-home" type="button" role="tab" aria-controls="pills-home" aria-selected="true">Invoices</button>
                                            </li>
                                            <li class="nav-item" role="presentation">
                                                <button class="nav-link" id="pills-profile-tab" data-bs-toggle="pill" data-bs-target="#pills-profile" type="button" role="tab" aria-controls="pills-profile" aria-selected="false">Purchases</button>
                                            </li>							
                                            <li class="nav-item" role="presentation">
                                                <button class="nav-link" id="pills-profile-tab2" data-bs-toggle="pill" data-bs-target="#pills-profile2" type="button" role="tab" aria-controls="pills-profile2" aria-selected="false">Receipts</button>
                                            </li>							
                                        </ul>	
                                    </div>
                                    <div class="tab-content" id="pills-tabContent">
                                        <div class="tab-pane fade show active" id="pills-home" role="tabpanel" aria-labelledby="pills-home-tab">
                                            <div class="row gx-3">
                                                <div class="col-xl-3 col-lg-4 col-mg-6">
                                                    <div class="card bg-light invoice-card">
                                                        <div class="card-body p-2">
                                                            <span class="d-block mb-2"><img src="{{URL::asset('build/img/invoice/invoice-01.svg')}}" class="w-100" alt="Img"></span>
                                                            <div class="d-flex align-items-center justify-content-between">
                                                                <p class="mb-0">General Invoice 1</p>
                                                                <a href="#" class="avatar avatar-sm rounded-circle bg-secondary-transparent"><i class="ti ti-star"></i></a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xl-3 col-lg-4 col-mg-6">
                                                    <div class="card bg-light invoice-card">
                                                        <div class="card-body p-2">
                                                            <span class="d-block mb-2"><img src="{{URL::asset('build/img/invoice/invoice-01.svg')}}" class="w-100" alt="Img"></span>
                                                            <div class="d-flex align-items-center justify-content-between">
                                                                <p class="mb-0">General Invoice 2</p>
                                                                <a href="#" class="avatar avatar-sm rounded-circle bg-secondary-transparent"><i class="ti ti-star"></i></a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xl-3 col-lg-4 col-mg-6">
                                                    <div class="card bg-light invoice-card">
                                                        <div class="card-body p-2">
                                                            <span class="d-block mb-2"><img src="{{URL::asset('build/img/invoice/invoice-01.svg')}}" class="w-100" alt="Img"></span>
                                                            <div class="d-flex align-items-center justify-content-between">
                                                                <p class="mb-0">General Invoice 3</p>
                                                                <a href="#" class="avatar avatar-sm rounded-circle bg-secondary-transparent"><i class="ti ti-star"></i></a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xl-3 col-lg-4 col-mg-6">
                                                    <div class="card bg-light invoice-card">
                                                        <div class="card-body p-2">
                                                            <span class="d-block mb-2"><img src="{{URL::asset('build/img/invoice/invoice-01.svg')}}" class="w-100" alt="Img"></span>
                                                            <div class="d-flex align-items-center justify-content-between">
                                                                <p class="mb-0">General Invoice 4</p>
                                                                <a href="#" class="avatar avatar-sm rounded-circle bg-secondary-transparent"><i class="ti ti-star"></i></a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xl-3 col-lg-4 col-mg-6">
                                                    <div class="card bg-light invoice-card">
                                                        <div class="card-body p-2">
                                                            <span class="d-block mb-2"><img src="{{URL::asset('build/img/invoice/invoice-01.svg')}}" class="w-100" alt="Img"></span>
                                                            <div class="d-flex align-items-center justify-content-between">
                                                                <p class="mb-0">General Invoice 5</p>
                                                                <a href="#" class="avatar avatar-sm rounded-circle bg-secondary-transparent"><i class="ti ti-star"></i></a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="tab-pane fade" id="pills-profile" role="tabpanel" aria-labelledby="pills-profile-tab">
                                            <div class="row gx-3">
                                                <div class="col-xl-3 col-lg-4 col-mg-6">
                                                    <div class="card bg-light invoice-card">
                                                        <div class="card-body p-2">
                                                            <span class="d-block mb-2"><img src="{{URL::asset('build/img/invoice/invoice-01.svg')}}" class="w-100" alt="Img"></span>
                                                            <div class="d-flex align-items-center justify-content-between">
                                                                <p class="mb-0">General Invoice 1</p>
                                                                <a href="#" class="avatar avatar-sm rounded-circle bg-secondary-transparent"><i class="ti ti-star"></i></a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xl-3 col-lg-4 col-mg-6">
                                                    <div class="card bg-light invoice-card">
                                                        <div class="card-body p-2">
                                                            <span class="d-block mb-2"><img src="{{URL::asset('build/img/invoice/invoice-01.svg')}}" class="w-100" alt="Img"></span>
                                                            <div class="d-flex align-items-center justify-content-between">
                                                                <p class="mb-0">General Invoice 2</p>
                                                                <a href="#" class="avatar avatar-sm rounded-circle bg-secondary-transparent"><i class="ti ti-star"></i></a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xl-3 col-lg-4 col-mg-6">
                                                    <div class="card bg-light invoice-card">
                                                        <div class="card-body p-2">
                                                            <span class="d-block mb-2"><img src="{{URL::asset('build/img/invoice/invoice-01.svg')}}" class="w-100" alt="Img"></span>
                                                            <div class="d-flex align-items-center justify-content-between">
                                                                <p class="mb-0">General Invoice 3</p>
                                                                <a href="#" class="avatar avatar-sm rounded-circle bg-secondary-transparent"><i class="ti ti-star"></i></a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xl-3 col-lg-4 col-mg-6">
                                                    <div class="card bg-light invoice-card">
                                                        <div class="card-body p-2">
                                                            <span class="d-block mb-2"><img src="{{URL::asset('build/img/invoice/invoice-01.svg')}}" class="w-100" alt="Img"></span>
                                                            <div class="d-flex align-items-center justify-content-between">
                                                                <p class="mb-0">General Invoice 4</p>
                                                                <a href="#" class="avatar avatar-sm rounded-circle bg-secondary-transparent"><i class="ti ti-star"></i></a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xl-3 col-lg-4 col-mg-6">
                                                    <div class="card bg-light invoice-card">
                                                        <div class="card-body p-2">
                                                            <span class="d-block mb-2"><img src="{{URL::asset('build/img/invoice/invoice-01.svg')}}" class="w-100" alt="Img"></span>
                                                            <div class="d-flex align-items-center justify-content-between">
                                                                <p class="mb-0">General Invoice 5</p>
                                                                <a href="#" class="avatar avatar-sm rounded-circle bg-secondary-transparent"><i class="ti ti-star"></i></a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="tab-pane fade" id="pills-profile2" role="tabpanel" aria-labelledby="pills-profile-tab2">
                                            <div class="row gx-3">
                                                <div class="col-xl-3 col-lg-4 col-mg-6">
                                                    <div class="card bg-light invoice-card">
                                                        <div class="card-body p-2">
                                                            <span class="d-block mb-2"><img src="{{URL::asset('build/img/invoice/invoice-02.svg')}}" class="w-100" alt="Img"></span>
                                                            <div class="d-flex align-items-center justify-content-between">
                                                                <p class="mb-0">Receipt Invoice 1</p>
                                                                <a href="#" class="avatar avatar-sm rounded-circle bg-secondary-transparent"><i class="ti ti-star"></i></a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xl-3 col-lg-4 col-mg-6">
                                                    <div class="card bg-light invoice-card">
                                                        <div class="card-body p-2">
                                                            <span class="d-block mb-2"><img src="{{URL::asset('build/img/invoice/invoice-02.svg')}}" class="w-100" alt="Img"></span>
                                                            <div class="d-flex align-items-center justify-content-between">
                                                                <p class="mb-0">Receipt Invoice 2</p>
                                                                <a href="#" class="avatar avatar-sm rounded-circle bg-secondary-transparent"><i class="ti ti-star"></i></a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xl-3 col-lg-4 col-mg-6">
                                                    <div class="card bg-light invoice-card">
                                                        <div class="card-body p-2">
                                                            <span class="d-block mb-2"><img src="{{URL::asset('build/img/invoice/invoice-02.svg')}}" class="w-100" alt="Img"></span>
                                                            <div class="d-flex align-items-center justify-content-between">
                                                                <p class="mb-0">Receipt Invoice 3</p>
                                                                <a href="#" class="avatar avatar-sm rounded-circle bg-secondary-transparent"><i class="ti ti-star"></i></a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xl-3 col-lg-4 col-mg-6">
                                                    <div class="card bg-light invoice-card">
                                                        <div class="card-body p-2">
                                                            <span class="d-block mb-2"><img src="{{URL::asset('build/img/invoice/invoice-02.svg')}}" class="w-100" alt="Img"></span>
                                                            <div class="d-flex align-items-center justify-content-between">
                                                                <p class="mb-0">Receipt Invoice 4</p>
                                                                <a href="#" class="avatar avatar-sm rounded-circle bg-secondary-transparent"><i class="ti ti-star"></i></a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
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