<?php $page = 'appearance'; ?>
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
                                                        <li><a href="{{url('appearance')}}" class="active">Appearance</a></li>
                                                        <li><a href="{{url('social-authentication')}}">Social Authentication</a></li>
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
                                <h4 class="fs-18 fw-bold">Appearance</h4>
                            </div>
                            <div class="card-body">
                                <form action="{{url('appearance')}}">
                                    <div class="appearance-settings">
                                        <div class="row">
                                            <div class="col-xl-4 col-lg-12 col-md-4">
                                                <div class="setting-info mb-4">
                                                    <h6 class="mb-1">Select Theme</h6>
                                                    <p>Choose accent colour of website</p>
                                                </div>
                                            </div>
                                            <div class="col-xl-8 col-lg-12 col-md-8">
                                                <div class="theme-type-images d-flex align-items-center mb-4">
                                                    <div class="theme-image border">
                                                        <div class="theme-image-set">
                                                            <img src="{{URL::asset('build/img/theme/theme-img-08.jpg')}}" alt="Img">
                                                        </div>
                                                        <h6>Light</h6>
                                                    </div>
                                                    <div class="theme-image border">
                                                        <div class="theme-image-set">
                                                            <img src="{{URL::asset('build/img/theme/theme-img-09.jpg')}}" alt="Img">
                                                        </div>
                                                        <h6>Dark</h6>
                                                    </div>
                                                    <div class="theme-image border">
                                                        <div class="theme-image-set">
                                                            <img src="{{URL::asset('build/img/theme/theme-img-10.jpg')}}" alt="Img">
                                                        </div>
                                                        <h6>Automatic</h6>
                                                    </div>
                                                </div>
                                                
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-xl-4 col-lg-12 col-md-4">
                                                <div class="setting-info mb-4">
                                                    <h6 class="mb-1">Accent Color</h6>
                                                    <p>Choose accent colour of website</p>
                                                </div>
                                            </div>
                                            <div class="col-xl-4 col-lg-12 col-md-4">
                                                <div class="theme-colors mb-4">
                                                    <ul>
                                                        <li>
                                                        <span class="themecolorset defaultcolor active"></span>
                                                        </li>
                                                        <li>
                                                        <span class="themecolorset theme-violet"></span>
                                                        </li>
                                                        <li>
                                                        <span class="themecolorset theme-blue"></span>
                                                        </li>
                                                        <li>
                                                        <span class="themecolorset theme-brown"></span>
                                                        </li>
                                                        </ul>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-xl-4 col-lg-12 col-md-4">
                                                <div class="setting-info mb-4">
                                                    <h6 class="mb-1">Expand Sidebar</h6>
                                                    <p>Choose accent colour of website</p>
                                                </div>
                                            </div>
                                            <div class="col-xl-4 col-lg-12 col-md-4">
                                                <div class="status-toggle modal-status d-flex justify-content-between align-items-center ms-2">
                                                    <input type="checkbox" id="user1" class="check" checked>
                                                    <label for="user1" class="checktoggle">	</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-xl-4 col-lg-12 col-md-4">
                                                <div class="setting-info mb-4">
                                                    <h6 class="mb-1">Sidebar Size</h6>
                                                    <p>Select size of the sidebar to display</p>
                                                </div>
                                            </div>
                                            <div class="col-xl-4 col-lg-12 col-md-4">
                                                <div class="localization-select">
                                                    <select class="select">
                                                        <option>Small - 85px</option>
                                                        <option>Large - 250px</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-xl-4 col-lg-12 col-md-4">
                                                <div class="setting-info mb-4">
                                                    <h6 class="mb-1">Font Family</h6>
                                                    <p>Select font family of website</p>
                                                </div>
                                            </div>
                                            <div class="col-xl-4 col-lg-12 col-md-4">
                                                <div class="localization-select">
                                                    <select class="select">
                                                        <option>Nunito</option>
                                                        <option>Poppins</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="text-end settings-bottom-btn mt-0">
                                        <button type="button" class="btn btn-secondary me-2">Cancel</button>
                                        <button type="submit" class="btn btn-primary">Save Changes</button>
                                    </div>
                                </form>
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
