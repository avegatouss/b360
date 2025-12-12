<?php $page = 'localization-settings'; ?>
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
                                                        <li><a href="{{url('localization-settings')}}" class="active">Localization</a></li>
                                                        <li><a href="{{url('prefixes')}}">Prefixes</a></li>
                                                        <li><a href="{{url('preference')}}">Preference</a></li>
                                                        <li><a href="{{url('appearance')}}">Appearance</a></li>
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
                                <h4 class="fs-18 fw-bold">Localization</h4>
                            </div>
                            <div class="card-body">
                                <form action="{{url('localization-settings')}}">
                                    <div class="border-bottom mb-3">
                                        <div class="card-title-head">
                                            <h6 class="fs-16 fw-bold mb-3">
                                                <span class="fs-18 me-2"><i class="ti ti-list"></i></span> 
                                                Basic Information
                                            </h6>
                                        </div>
                                        <div>
                                            <div class="row align-items-center">
                                                <div class="col-sm-6">
                                                    <div class="mb-3">
                                                        <h6 class="mb-1 fw-medium">Language</h6>
                                                        <p>Select Language of the Website</p>
                                                    </div>
                                                </div>
                                                <div class="col-sm-6">
                                                    <div class="localization-select ms-sm-auto mb-3">
                                                        <select class="select">
                                                            <option>English</option>
                                                            <option>Spanish</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row align-items-center">
                                                <div class="col-sm-6">
                                                    <div class="mb-3">
                                                        <h6 class="mb-1 fw-medium">Language Switcher</h6>
                                                        <p>To display in all the pages</p>
                                                    </div>
                                                </div>
                                                <div class="col-sm-6">
                                                    <div class="localization-select ms-sm-auto mb-3">
                                                        <div class="status-toggle modal-status d-flex justify-content-between align-items-center">
                                                            <input type="checkbox" id="user3" class="check" checked>
                                                            <label for="user3" class="checktoggle"></label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row align-items-center">
                                                <div class="col-sm-6">
                                                    <div class="mb-3">
                                                        <h6 class="mb-1 fw-medium">Timezone</h6>
                                                        <p>Select Time zone in website</p>
                                                    </div>
                                                </div>
                                                <div class="col-sm-6">
                                                    <div class="localization-select ms-sm-auto mb-3">
                                                        <select class="select">
                                                            <option>UTC 5:30</option>
                                                            <option>(UTC+11:00) INR</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row align-items-center">
                                                <div class="col-sm-6">
                                                    <div class="mb-3">
                                                        <h6 class="mb-1 fw-medium">Date format</h6>
                                                        <p>Select date format to display in website</p>
                                                    </div>
                                                </div>
                                                <div class="col-sm-6">
                                                    <div class="localization-select ms-sm-auto mb-3">
                                                        <select class="select">
                                                            <option>01 Jan 2025</option>
                                                            <option>Jul 22 2025</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row align-items-center">
                                                <div class="col-sm-6">
                                                    <div class="mb-3">
                                                        <h6 class="mb-1 fw-medium">Time Format</h6>
                                                        <p>Select time format to display in website</p>
                                                    </div>
                                                </div>
                                                <div class="col-sm-6">
                                                    <div class="localization-select ms-sm-auto mb-3">
                                                        <select class="select">
                                                            <option>12 Hours</option>
                                                            <option>24 Hours</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row align-items-center">
                                                <div class="col-sm-6">
                                                    <div class="mb-3">
                                                        <h6 class="mb-1 fw-medium">Financial Year</h6>
                                                        <p>Select year for finance </p>
                                                    </div>
                                                </div>
                                                <div class="col-sm-6">
                                                    <div class="localization-select ms-sm-auto mb-3">
                                                        <select class="select">
                                                            <option>2025</option>
                                                            <option>2026</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row align-items-center">
                                                <div class="col-sm-6">
                                                    <div class="mb-3">
                                                        <h6 class="mb-1 fw-medium">Starting Month</h6>
                                                        <p>Select starting month to display  </p>
                                                    </div>
                                                </div>
                                                <div class="col-sm-6">
                                                    <div class="localization-select ms-sm-auto mb-3">
                                                        <select class="select">
                                                            <option>January</option>
                                                            <option>February</option>
                                                            <option>March</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="border-bottom mb-3">
                                        <div class="card-title-head">
                                            <h6 class="fs-16 fw-bold mb-3">
                                                <span class="fs-18 me-2"><i class="ti ti-credit-card"></i></span> 
                                                Currency Settings
                                            </h6>
                                        </div>
                                        <div class="localization-info">
                                            <div class="row align-items-center">
                                                <div class="col-sm-6">
                                                    <div class="mb-3">
                                                        <h6 class="mb-1 fw-medium">Currency</h6>
                                                        <p>Select Time zone in website</p>
                                                    </div>
                                                </div>
                                                <div class="col-sm-6">
                                                    <div class="localization-select ms-sm-auto mb-3">
                                                        <select class="select">
                                                            <option>USA</option>
                                                            <option>India</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row align-items-center">
                                                <div class="col-sm-6">
                                                    <div class="mb-3">
                                                        <h6 class="mb-1 fw-medium">Currency Symbol</h6>
                                                        <p>Select date format to display in website</p>
                                                    </div>
                                                </div>
                                                <div class="col-sm-6">
                                                    <div class="localization-select ms-sm-auto mb-3">
                                                        <select class="select">
                                                            <option>$</option>
                                                            <option>€</option>
                                                            <option>¥</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row align-items-center">
                                                <div class="col-sm-6">
                                                    <div class="mb-3">
                                                        <h6 class="mb-1 fw-medium">Currency Position</h6>
                                                        <p>Select time format to display in website</p>
                                                    </div>
                                                </div>
                                                <div class="col-sm-6">
                                                    <div class="localization-select ms-sm-auto mb-3">
                                                        <select class="select">
                                                            <option>$100</option>
                                                            <option>$400</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row align-items-center">
                                                <div class="col-sm-6">
                                                    <div class="mb-3">
                                                        <h6 class="mb-1 fw-medium">Decimal Separator</h6>
                                                        <p>Select year for finance</p>
                                                    </div>
                                                </div>
                                                <div class="col-sm-6">
                                                    <div class="localization-select ms-sm-auto mb-3">
                                                        <select class="select">
                                                            <option>.</option>
                                                            <option>.</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row align-items-center">
                                                <div class="col-sm-6">
                                                    <div class="mb-3">
                                                        <h6 class="mb-1 fw-medium">Thousand Separator</h6>
                                                        <p>Select starting month to display</p>
                                                    </div>
                                                </div>
                                                <div class="col-sm-6">
                                                    <div class="localization-select ms-sm-auto mb-3">
                                                        <select class="select">
                                                            <option>,</option>
                                                            <option>,</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="border-bottom mb-3">
                                        <div class="card-title-head">
                                            <h6 class="fs-16 fw-bold mb-3">
                                                <span class="fs-18 me-2"><i class="ti ti-map"></i></span> 
                                                Country Settings
                                            </h6>
                                        </div>
                                        <div class="localization-info">
                                            <div class="row align-items-center">
                                                <div class="col-sm-6">
                                                    <div class="mb-3">
                                                        <h6 class="mb-1 fw-medium">Countries Restriction</h6>
                                                        <p>Select countries restriction</p>
                                                    </div>
                                                </div>
                                                <div class="col-sm-6">
                                                    <div class="localization-select ms-sm-auto mb-3">
                                                        <select class="select">
                                                            <option>Allow All Countries</option>
                                                            <option>Deny All Countries</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="card-title-head">
                                            <h6 class="fs-16 fw-bold mb-3">
                                                <span class="fs-18 me-2"><i class="ti ti-map"></i></span> 
                                                File Settings
                                            </h6>
                                        </div>
                                        <div class="localization-info">
                                            <div class="row align-items-center">
                                                <div class="col-sm-6">
                                                    <div class="mb-3">
                                                        <h6 class="fw-medium mb-1">Allowed Files</h6>
                                                        <p>Select files</p>
                                                    </div>
                                                </div>
                                                <div class="col-sm-6">
                                                    <div class="localization-select ms-sm-auto mb-3 w-100">
                                                        <div class="mb-3">
                                                            <input class="input-tags form-control" type="text" data-role="tagsinput"  name="specialist" value="JPG,GIF,PNG">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row align-items-center">
                                                <div class="col-sm-6">
                                                    <div class="mb-3 mb-sm-0">
                                                        <h6 class="fw-medium mb-1">Max File Size</h6>
                                                        <p>File size</p>
                                                    </div>
                                                </div>
                                                <div class="col-sm-6">
                                                    <div class="localization-select ms-sm-auto mb-3 d-flex align-items-center mb-sm-0">
                                                        <input type="text" class="form-control" value="5000">
                                                        <span class="ms-2 text-gray-9">MB</span>
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
        </div>
        <div class="footer d-sm-flex align-items-center justify-content-between border-top bg-white p-3">
            <p class="mb-0">2014 - 2025 &copy; DreamsPOS. All Right Reserved</p>
            <p>Designed &amp; Developed by <a href="javascript:void(0);" class="text-primary">Dreams</a></p>
        </div>
    </div>
@endsection
