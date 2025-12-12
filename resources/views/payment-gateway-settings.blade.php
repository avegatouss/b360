<?php $page = 'payment-gateway-settings'; ?>
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
                                                    <a href="javascript:void(0);" class="active subdrop">
                                                        <i class="ti ti-settings-dollar fs-18"></i>
                                                        <span class="fs-14 fw-medium ms-2">Financial Settings</span>
                                                        <span class="menu-arrow"></span>
                                                    </a>
                                                    <ul>
                                                        <li><a href="{{url('payment-gateway-settings')}}" class="active">Payment Gateway</a></li>
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
                                <h4>Payment Gateway</h4>
                            </div>
                            <div class="card-body pb-0">
                                <div class="row">
                                    <div class="col-xxl-4 col-xl-6 col-lg-12 col-md-6 d-flex">
                                        <div class="card flex-fill">
                                            <div class="w-100 card-body">
                                                <div class="d-flex flex-column align-items-start">
                                                    <div class="d-flex align-items-center justify-content-between w-100 mb-2">
                                                        <div class="d-flex align-items-center">
                                                            <span>
                                                                <img src="{{URL::asset('build/img/icons/payment-icon-01.svg')}}" alt="Payment">
                                                            </span>
                                                        </div>
                                                        <span class="badge bg-outline-success">Connected</span>
                                                    </div>
                                                    <p class="mb-3">PayPal is the faster, safer way to send and receive money </p>
                                                </div>
                                                <div class="d-flex align-items-center justify-content-between border-top pt-3">
                                                    <a href="javascript:void(0);" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#payment-connect">
                                                        <i class="ti ti-tool me-2"></i>View Integration
                                                    </a>
                                                    <div class="status-toggle modal-status d-flex justify-content-between align-items-center ms-2">
                                                        <input type="checkbox" id="user1" class="check" checked>
                                                        <label for="user1" class="checktoggle">	</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xxl-4 col-xl-6 col-lg-12 col-md-6 d-flex">
                                        <div class="card flex-fill">
                                            <div class="w-100 card-body">
                                                <div class="d-flex flex-column align-items-start">
                                                    <div class="d-flex align-items-center justify-content-between w-100 mb-2">
                                                        <div class="d-flex align-items-center">
                                                            <span>
                                                                <img src="{{URL::asset('build/img/icons/payment-icon-02.svg')}}" alt="Payment">
                                                            </span>
                                                        </div>
                                                        <span class="badge border text-dark">Not connected</span>
                                                    </div>
                                                    <p class="mb-3">APIs to accept cards, manage subscriptions, send money.</p>
                                                </div>
                                                <div class="d-flex align-items-center justify-content-between border-top pt-3">
                                                    <a href="javascript:void(0);" class="btn btn-sm btn-outline-secondary">
                                                        <i class="ti ti-tool me-2"></i>Connect
                                                    </a>
                                                    <div class="status-toggle modal-status d-flex justify-content-between align-items-center ms-2">
                                                        <input type="checkbox" id="user2" class="check">
                                                        <label for="user2" class="checktoggle">	</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xxl-4 col-xl-6 col-lg-12 col-md-6 d-flex">
                                        <div class="card flex-fill">
                                            <div class="w-100 card-body">
                                                <div class="d-flex flex-column align-items-start">
                                                    <div class="d-flex align-items-center justify-content-between w-100 mb-2">
                                                        <div class="d-flex align-items-center">
                                                            <span>
                                                                <img src="{{URL::asset('build/img/icons/payment-icon-03.svg')}}" alt="Payment">
                                                            </span>
                                                        </div>
                                                        <span class="badge border text-dark">Not connected</span>
                                                    </div>
                                                    <p class="mb-3">Braintree offers more fraud protection and security features.</p>
                                                </div>
                                                <div class="d-flex align-items-center justify-content-between border-top pt-3">
                                                    <a href="javascript:void(0);" class="btn btn-sm btn-outline-secondary">
                                                        <i class="ti ti-tool me-2"></i>Connect
                                                    </a>
                                                    <div class="status-toggle modal-status d-flex justify-content-between align-items-center ms-2">
                                                        <input type="checkbox" id="user3" class="check">
                                                        <label for="user3" class="checktoggle">	</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xxl-4 col-xl-6 col-lg-12 col-md-6 d-flex">
                                        <div class="card flex-fill">
                                            <div class="w-100 card-body">
                                                <div class="d-flex flex-column align-items-start">
                                                    <div class="d-flex align-items-center justify-content-between w-100 mb-2">
                                                        <div class="d-flex align-items-center">
                                                            <span>
                                                                <img src="{{URL::asset('build/img/icons/payment-icon-04.svg')}}" alt="Payment">
                                                            </span>
                                                        </div>
                                                        <span class="badge border text-dark">Not connected</span>
                                                    </div>
                                                    <p class="mb-3">Razorpay is an India's all in one payment solution.</p>
                                                </div>
                                                <div class="d-flex align-items-center justify-content-between border-top pt-3">
                                                    <a href="javascript:void(0);" class="btn btn-sm btn-outline-secondary">
                                                        <i class="ti ti-tool me-2"></i>Connect
                                                    </a>
                                                    <div class="status-toggle modal-status d-flex justify-content-between align-items-center ms-2">
                                                        <input type="checkbox" id="user4" class="check">
                                                        <label for="user4" class="checktoggle">	</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xxl-4 col-xl-6 col-lg-12 col-md-6 d-flex">
                                        <div class="card flex-fill">
                                            <div class="w-100 card-body">
                                                <div class="d-flex flex-column align-items-start">
                                                    <div class="d-flex align-items-center justify-content-between w-100 mb-2">
                                                        <div class="d-flex align-items-center">
                                                            <span>
                                                                <img src="{{URL::asset('build/img/icons/payment-icon-05.svg')}}" alt="Payment">
                                                            </span>
                                                        </div>
                                                        <span class="badge bg-outline-success">Connected</span>
                                                    </div>
                                                    <p class="mb-3">Works stably and reliably and features are valuable </p>
                                                </div>
                                                <div class="d-flex align-items-center justify-content-between border-top pt-3">
                                                    <a href="javascript:void(0);" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#payment-connect">
                                                        <i class="ti ti-tool me-2"></i>View Integration
                                                    </a>
                                                    <div class="status-toggle modal-status d-flex justify-content-between align-items-center ms-2">
                                                        <input type="checkbox" id="user5" class="check" checked>
                                                        <label for="user5" class="checktoggle">	</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xxl-4 col-xl-6 col-lg-12 col-md-6 d-flex">
                                        <div class="card flex-fill">
                                            <div class="w-100 card-body">
                                                <div class="d-flex flex-column align-items-start">
                                                    <div class="d-flex align-items-center justify-content-between w-100 mb-2">
                                                        <div class="d-flex align-items-center">
                                                            <span>
                                                                <img src="{{URL::asset('build/img/icons/payment-icon-06.svg')}}" alt="Payment">
                                                            </span>
                                                        </div>
                                                        <span class="badge border text-dark">Not connected</span>
                                                    </div>
                                                    <p class="mb-3">Allows  send international money transfers and payments quickly </p>
                                                </div>
                                                <div class="d-flex align-items-center justify-content-between border-top pt-3">
                                                    <a href="javascript:void(0);" class="btn btn-sm btn-outline-secondary">
                                                        <i class="ti ti-tool me-2"></i>Connect
                                                    </a>
                                                    <div class="status-toggle modal-status d-flex justify-content-between align-items-center ms-2">
                                                        <input type="checkbox" id="user6" class="check">
                                                        <label for="user6" class="checktoggle">	</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xxl-4 col-xl-6 col-lg-12 col-md-6 d-flex">
                                        <div class="card flex-fill">
                                            <div class="w-100 card-body">
                                                <div class="d-flex flex-column align-items-start">
                                                    <div class="d-flex align-items-center justify-content-between w-100 mb-2">
                                                        <div class="d-flex align-items-center">
                                                            <span>
                                                                <img src="{{URL::asset('build/img/icons/payment-icon-07.svg')}}" alt="Payment">
                                                            </span>
                                                        </div>
                                                        <span class="badge bg-outline-success">Connected</span>
                                                    </div>
                                                    <p class="mb-3">Provide payment solution to individuals to make payments</p>
                                                </div>
                                                <div class="d-flex align-items-center justify-content-between border-top pt-3">
                                                    <a href="javascript:void(0);" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#payment-connect">
                                                        <i class="ti ti-tool me-2"></i>View Integration
                                                    </a>
                                                    <div class="status-toggle modal-status d-flex justify-content-between align-items-center ms-2">
                                                        <input type="checkbox" id="user7" class="check" checked>
                                                        <label for="user7" class="checktoggle">	</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xxl-4 col-xl-6 col-lg-12 col-md-6 d-flex">
                                        <div class="card flex-fill">
                                            <div class="w-100 card-body">
                                                <div class="d-flex flex-column align-items-start">
                                                    <div class="d-flex align-items-center justify-content-between w-100 mb-2">
                                                        <div class="d-flex align-items-center">
                                                            <span>
                                                                <img src="{{URL::asset('build/img/icons/payment-icon-08.svg')}}" alt="Payment">
                                                            </span>
                                                        </div>
                                                        <span class="badge border text-dark">Not connected</span>
                                                    </div>
                                                    <p class="mb-3">Replaces your physical cards and cash with private and secure </p>
                                                </div>
                                                <div class="d-flex align-items-center justify-content-between border-top pt-3">
                                                    <a href="javascript:void(0);" class="btn btn-sm btn-outline-secondary">
                                                        <i class="ti ti-tool me-2"></i>Connect
                                                    </a>
                                                    <div class="status-toggle modal-status d-flex justify-content-between align-items-center ms-2">
                                                        <input type="checkbox" id="user8" class="check">
                                                        <label for="user8" class="checktoggle">	</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xxl-4 col-xl-6 col-lg-12 col-md-6 d-flex">
                                        <div class="card flex-fill">
                                            <div class="w-100 card-body">
                                                <div class="d-flex flex-column align-items-start">
                                                    <div class="d-flex align-items-center justify-content-between w-100 mb-2">
                                                        <div class="d-flex align-items-center">
                                                            <span>
                                                                <img src="{{URL::asset('build/img/icons/payment-icon-09.svg')}}" alt="Payment">
                                                            </span>
                                                        </div>
                                                        <span class="badge border text-dark">Not connected</span>
                                                    </div>
                                                    <p class="mb-3">Fast, Low-Cost Solution for your International Business. </p>
                                                </div>
                                                <div class="d-flex align-items-center justify-content-between border-top pt-3">
                                                    <a href="javascript:void(0);" class="btn btn-sm btn-outline-secondary">
                                                        <i class="ti ti-tool me-2"></i>Connect
                                                    </a>
                                                    <div class="status-toggle modal-status d-flex justify-content-between align-items-center ms-2">
                                                        <input type="checkbox" id="user9" class="check">
                                                        <label for="user9" class="checktoggle">	</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xxl-4 col-xl-6 col-lg-12 col-md-6 d-flex">
                                        <div class="card flex-fill">
                                            <div class="w-100 card-body">
                                                <div class="d-flex flex-column align-items-start">
                                                    <div class="d-flex align-items-center justify-content-between w-100 mb-2">
                                                        <div class="d-flex align-items-center">
                                                            <span>
                                                                <img src="{{URL::asset('build/img/icons/payment-icon-10.svg')}}" alt="Payment">
                                                            </span>
                                                        </div>
                                                        <span class="badge border text-dark">Not connected</span>
                                                    </div>
                                                    <p class="mb-3">Online payment platform that enables to send & receive money </p>
                                                </div>
                                                <div class="d-flex align-items-center justify-content-between border-top pt-3">
                                                    <a href="javascript:void(0);" class="btn btn-sm btn-outline-secondary">
                                                        <i class="ti ti-tool me-2"></i>Connect
                                                    </a>
                                                    <div class="status-toggle modal-status d-flex justify-content-between align-items-center ms-2">
                                                        <input type="checkbox" id="user10" class="check">
                                                        <label for="user10" class="checktoggle">	</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xxl-4 col-xl-6 col-lg-12 col-md-6 d-flex">
                                        <div class="card flex-fill">
                                            <div class="w-100 card-body">
                                                <div class="d-flex flex-column align-items-start">
                                                    <div class="d-flex align-items-center justify-content-between w-100 mb-2">
                                                        <div class="d-flex align-items-center">
                                                            <span>
                                                                <img src="{{URL::asset('build/img/icons/payment-icon-11.svg')}}" alt="Payment">
                                                            </span>
                                                        </div>
                                                        <span class="badge bg-outline-success">Connected</span>
                                                    </div>
                                                    <p class="mb-3">Paytm stands for Pay through mobile </p>
                                                </div>
                                                <div class="d-flex align-items-center justify-content-between border-top pt-3">
                                                    <a href="javascript:void(0);" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#payment-connect">
                                                        <i class="ti ti-tool me-2"></i>View Integration
                                                    </a>
                                                    <div class="status-toggle modal-status d-flex justify-content-between align-items-center ms-2">
                                                        <input type="checkbox" id="user11" class="check" checked>
                                                        <label for="user11" class="checktoggle">	</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xxl-4 col-xl-6 col-lg-12 col-md-6 d-flex">
                                        <div class="card flex-fill">
                                            <div class="w-100 card-body">
                                                <div class="d-flex flex-column align-items-start">
                                                    <div class="d-flex align-items-center justify-content-between w-100 mb-2">
                                                        <div class="d-flex align-items-center">
                                                            <span>
                                                                <img src="{{URL::asset('build/img/icons/payment-icon-12.svg')}}" alt="Payment">
                                                            </span>
                                                        </div>
                                                        <span class="badge border text-dark">Not connected</span>
                                                    </div>
                                                    <p class="mb-3">Midtrans provides the maximum number of payment methods</p>
                                                </div>
                                                <div class="d-flex align-items-center justify-content-between border-top pt-3">
                                                    <a href="javascript:void(0);" class="btn btn-sm btn-outline-secondary">
                                                        <i class="ti ti-tool me-2"></i>Connect
                                                    </a>
                                                    <div class="status-toggle modal-status d-flex justify-content-between align-items-center ms-2">
                                                        <input type="checkbox" id="user12" class="check">
                                                        <label for="user12" class="checktoggle">	</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xxl-4 col-xl-6 col-lg-12 col-md-6 d-flex">
                                        <div class="card flex-fill">
                                            <div class="w-100 card-body">
                                                <div class="d-flex flex-column align-items-start">
                                                    <div class="d-flex align-items-center justify-content-between w-100 mb-2">
                                                        <div class="d-flex align-items-center">
                                                            <span>
                                                                <img src="{{URL::asset('build/img/icons/payment-icon-13.svg')}}" alt="Payment">
                                                            </span>
                                                        </div>
                                                        <span class="badge border text-dark">Not connected</span>
                                                    </div>
                                                    <p class="mb-3">PyTorch, a network through which your customers transfer funds</p>
                                                </div>
                                                <div class="d-flex align-items-center justify-content-between border-top pt-3">
                                                    <a href="javascript:void(0);" class="btn btn-sm btn-outline-secondary">
                                                        <i class="ti ti-tool me-2"></i>Connect
                                                    </a>
                                                    <div class="status-toggle modal-status d-flex justify-content-between align-items-center ms-2">
                                                        <input type="checkbox" id="user13" class="check">
                                                        <label for="user13" class="checktoggle">	</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xxl-4 col-xl-6 col-lg-12 col-md-6 d-flex">
                                        <div class="card flex-fill">
                                            <div class="w-100 card-body">
                                                <div class="d-flex flex-column align-items-start">
                                                    <div class="d-flex align-items-center justify-content-between w-100 mb-2">
                                                        <div class="d-flex align-items-center">
                                                            <span>
                                                                <img src="{{URL::asset('build/img/icons/payment-icon-14.svg')}}" alt="Payment">
                                                            </span>
                                                        </div>
                                                        <span class="badge bg-outline-success">Connected</span>
                                                    </div>
                                                    <p class="mb-3">Direct transfer of funds from one bank account into another.</p>
                                                </div>
                                                <div class="d-flex align-items-center justify-content-between border-top pt-3">
                                                    <a href="javascript:void(0);" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#payment-connect">
                                                        <i class="ti ti-tool me-2"></i>View Integration
                                                    </a>
                                                    <div class="status-toggle modal-status d-flex justify-content-between align-items-center ms-2">
                                                        <input type="checkbox" id="user14" class="check" checked>
                                                        <label for="user14" class="checktoggle">	</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xxl-4 col-xl-6 col-lg-12 col-md-6 d-flex">
                                        <div class="card flex-fill">
                                            <div class="w-100 card-body">
                                                <div class="d-flex flex-column align-items-start">
                                                    <div class="d-flex align-items-center justify-content-between w-100 mb-2">
                                                        <div class="d-flex align-items-center">
                                                            <span>
                                                                <img src="{{URL::asset('build/img/icons/payment-icon-15.svg')}}" alt="Payment">
                                                            </span>
                                                        </div>
                                                        <span class="badge border text-dark">Not connected</span>
                                                    </div>
                                                    <p class="mb-3">Indicating that goods must be paid for at the time of delivery. </p>
                                                </div>
                                                <div class="d-flex align-items-center justify-content-between border-top pt-3">
                                                    <a href="javascript:void(0);" class="btn btn-sm btn-outline-secondary">
                                                        <i class="ti ti-tool me-2"></i>Connect
                                                    </a>
                                                    <div class="status-toggle modal-status d-flex justify-content-between align-items-center ms-2">
                                                        <input type="checkbox" id="user15" class="check">
                                                        <label for="user15" class="checktoggle">	</label>
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
