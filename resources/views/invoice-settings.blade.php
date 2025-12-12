<?php $page = 'invoice-settings'; ?>
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
                        <div class="sidebars settings-sidebar" id="sidebar2">
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
                                                        <li><a href="{{url('general-settings')}}" >Profile</a></li>
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
                                                    <a href="javascript:void(0);"  class="active subdrop">
                                                        <i class="ti ti-device-mobile fs-18"></i>
                                                        <span class="fs-14 fw-medium ms-2">App Settings</span>
                                                        <span class="menu-arrow"></span>
                                                    </a>
                                                    <ul>
                                                        <li><a href="{{url('invoice-settings')}}" class="active">Invoice Settings</a></li>
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
                            <form action="{{url('invoice-settings')}}">
                                <div class="card-header">
                                    <h4>Invoice Settings</h4>
                                </div>
                                <div class="card-body ">
                                    <ul class="logo-company">
                                        <li>
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <div class="logo-info me-0 mb-3 mb-md-0">
                                                        <h6>Invoice Logo</h6>
                                                        <p>Upload Logo of your Company to display in Invoice</p>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="profile-pic-upload mb-0 me-0">
                                                        <div class="new-employee-field">
                                                            <div class="mb-3 mb-md-0">
                                                                <div class="image-upload mb-0">
                                                                    <input type="file">
                                                                    <div class="image-uploads">
                                                                        <h4><i data-feather="upload"></i>Upload Photo</h4>
                                                                    </div>
                                                                </div>
                                                                <span>For better preview recommended size is 450px x 450px. Max size 5mb.</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-2">
                                                    <div class="new-logo ms-auto">
                                                        <a href="#"><img src="{{URL::asset('build/img/logo-small.png')}}" alt="Logo"></a>
                                                    </div>
                                                </div>
                                            </div>																								
                                        </li>
                                    </ul>
                                    <div class="localization-info">
                                        <div class="row align-items-center">
                                            <div class="col-sm-4">
                                                <div class="setting-info">
                                                    <h6>Invoice Prefix</h6>
                                                    <p>Add prefix to your invoice</p>
                                                </div>
                                            </div>
                                            <div class="col-sm-4">
                                                <div class="localization-select">
                                                    <input type="text" class="form-control" value="INV -">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row align-items-center">
                                            <div class="col-sm-4">
                                                <div class="setting-info">
                                                    <h6>Invoice Due</h6>
                                                    <p>Select due date  to display in Invoice</p>
                                                </div>
                                            </div>
                                            <div class="col-sm-4">
                                                <div class="localization-select d-flex align-items-center fixed-width">
                                                    <select class="select">
                                                        <option>5</option>
                                                        <option>6</option>
                                                        <option>7</option>
                                                    </select>
                                                    <span class="ms-2">Days</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row align-items-center">
                                            <div class="col-sm-4">
                                                <div class="setting-info">
                                                    <h6>Invoice Round Off</h6>
                                                    <p>Value Roundoff in Invoice</p>
                                                </div>
                                            </div>
                                            <div class="col-sm-4">
                                                <div class="localization-select d-flex align-items-center width-custom">
                                                    <div class="status-toggle modal-status d-flex justify-content-between align-items-center me-3">
                                                        <input type="checkbox" id="user3" class="check" checked>
                                                        <label for="user3" class="checktoggle"></label>
                                                    </div>
                                                    <select class="select">
                                                        <option>Round Off Up</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row align-items-center">
                                            <div class="col-sm-4">
                                                <div class="setting-info">
                                                    <h6>Show Company Details</h6>
                                                    <p>Show / Hide Company Details in Invoice</p>
                                                </div>
                                            </div>
                                            <div class="col-sm-4">
                                                <div class="localization-select d-flex align-items-center">
                                                    <div class="status-toggle modal-status d-flex justify-content-between align-items-center me-3">
                                                        <input type="checkbox" id="user4" class="check" checked>
                                                        <label for="user4" class="checktoggle"></label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-sm-4">
                                                <div class="setting-info">
                                                    <h6>Invoice Header Terms</h6>
                                                </div>
                                            </div>
                                            <div class="col-sm-8">
                                                <div class="mb-3">
                                                    <textarea  rows="4" class="form-control" placeholder="Type your message"></textarea>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-sm-4">
                                                <div class="setting-info">
                                                    <h6>Invoice Footer Terms</h6>
                                                </div>
                                            </div>
                                            <div class="col-sm-8">
                                                <div class="mb-3">
                                                    <textarea  rows="4" class="form-control" placeholder="Type your message"></textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center justify-content-end">
                                        <button type="button" class="btn btn-secondary me-2" >Cancel</button>
                                        <button type="submit" class="btn btn-primary">Save Changes</button>
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
