<?php $page = 'social-authentication'; ?>
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
                                                    <a href="javascript:void(0);" class="active subdrop">
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
                                                        <li><a href="{{url('social-authentication')}}" class="active">Social Authentication</a></li>
                                                        <li><a href="{{url('language-settings')}}">Language</a></li>
                                                    </ul>
                                                </li>
                                                <li class="submenu">
                                                    <a href="javascript:void(0);">
                                                        <i class="ti ti-device-mobile fs-18"></i>
                                                        <span class="fs-14 fw-medium ms-2">App Settings</span>
                                                        <span class="menu-arrow"></span>
                                                    </a>
                                                    <ul>
                                                        <li><a href="{{url('invoice-settings')}}">Invoice Settings</a></li>
                                                        <li><a href="{{url('invoice-templates')}}">Invoice Templates</a></li>
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
                            <div class="card-header">
                                <h4 class="fs-18 fw-bold">System Settings</h4>
                            </div>
                            <div class="card-body pb-0">
                                <div class="row">
                                    <div class="col-xl-6 col-lg-12 col-md-6 d-flex">
                                        <div class="card">
                                            <div class="card-body">
                                                <div class="d-flex align-items-center justify-content-between mb-2">
                                                    <div class="d-flex align-items-center">
                                                        <span class="avatar avatar-lg bg-light border me-2">
                                                            <img src="{{URL::asset('build/img/icons/fb-icon.svg')}}" class="w-auto h-auto" alt="Img">
                                                        </span>
                                                        <div>
                                                            <h5>Facebook</h5>
                                                        </div>
                                                    </div>
                                                    <span class="badge bg-outline-success">Connected</span>
                                                </div>
                                                <p class="border-bottom pb-3 mb-3">Connect with friends, family and share updates, photos, moments with people you know.</p>
                                                <div class="d-flex align-items-center justify-content-between">
                                                    <div>
                                                        <a href="javascript:void(0);" class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#fb-connect"><i class="ti ti-tool me-1"></i>View Integration</a>
                                                    </div>
                                                    <div class="status-toggle modal-status d-flex justify-content-between align-items-center ms-2">
                                                        <input type="checkbox" id="user1" class="check" checked>
                                                        <label for="user1" class="checktoggle">	</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xl-6 col-lg-12 col-md-6 d-flex">
                                        <div class="card">
                                            <div class="card-body">
                                                <div class="d-flex align-items-center justify-content-between mb-2">
                                                    <div class="d-flex align-items-center">
                                                        <span class="avatar avatar-lg bg-light border me-2">
                                                            <img src="{{URL::asset('build/img/icons/twitter-icon.svg')}}" class="w-auto h-auto" alt="Img">
                                                        </span>
                                                        <div>
                                                            <h5>Twitter</h5>
                                                        </div>
                                                    </div>
                                                    <span class="badge bg-outline-light">Not connected</span>
                                                </div>
                                                <p class="border-bottom pb-3 mb-3">Communicate and stay connected through the exchange of quick, frequent messages</p>
                                                <div class="d-flex align-items-center justify-content-between">
                                                    <div>
                                                        <a href="javascript:void(0);" class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#twitter-connect"><i class="ti ti-tool me-1"></i>View Integration</a>
                                                    </div>
                                                    <div class="status-toggle modal-status d-flex justify-content-between align-items-center ms-2">
                                                        <input type="checkbox" id="user2" class="check" checked>
                                                        <label for="user2" class="checktoggle">	</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xl-6 col-lg-12 col-md-6 d-flex">
                                        <div class="card">
                                            <div class="card-body">
                                                <div class="d-flex align-items-center justify-content-between mb-2">
                                                    <div class="d-flex align-items-center">
                                                        <span class="avatar avatar-lg bg-light border me-2">
                                                            <img src="{{URL::asset('build/img/icons/linkedin-icon.svg')}}" class="w-auto h-auto" alt="Img">
                                                        </span>
                                                        <div>
                                                            <h5>Linkedin</h5>
                                                        </div>
                                                    </div>
                                                    <span class="badge bg-outline-success">Connected</span>
                                                </div>
                                                <p class="border-bottom pb-3 mb-3">Network with people and professional organizations in your industry.</p>
                                                <div class="d-flex align-items-center justify-content-between">
                                                    <div>
                                                        <a href="javascript:void(0);" class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#linkedin-connect"><i class="ti ti-tool me-1"></i>Connect Now</a>
                                                    </div>
                                                    <div class="status-toggle modal-status d-flex justify-content-between align-items-center ms-2">
                                                        <input type="checkbox" id="user3" class="check" checked>
                                                        <label for="user3" class="checktoggle">	</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xl-6 col-lg-12 col-md-6 d-flex">
                                        <div class="card">
                                            <div class="card-body">
                                                <div class="d-flex align-items-center justify-content-between mb-2">
                                                    <div class="d-flex align-items-center">
                                                        <span class="avatar avatar-lg bg-light border me-2">
                                                            <img src="{{URL::asset('build/img/icons/google-icon.svg')}}" class="w-auto h-auto" alt="Img">
                                                        </span>
                                                        <div>
                                                            <h5>Google</h5>
                                                        </div>
                                                    </div>
                                                    <span class="badge bg-outline-success">Connected</span>
                                                </div>
                                                <p class="border-bottom pb-3 mb-3">Google has many special features to help you find exactly what you're looking for.</p>
                                                <div class="d-flex align-items-center justify-content-between">
                                                    <div>
                                                        <a href="javascript:void(0);" class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#google-connect"><i class="ti ti-tool me-1"></i>View Integration</a>
                                                    </div>
                                                    <div class="status-toggle modal-status d-flex justify-content-between align-items-center ms-2">
                                                        <input type="checkbox" id="user4" class="check" checked>
                                                        <label for="user4" class="checktoggle">	</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
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
