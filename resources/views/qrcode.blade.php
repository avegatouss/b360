<?php $page = 'qrcode'; ?>
@extends('layout.mainlayout')
@section('content')

<div class="page-wrapper notes-page-wrapper">
    <div class="content">


        <div class="page-header">
            <div class="add-item d-flex">
                <div class="page-title">
                    <h4 class="fw-bold">Print QR Code</h4>
                    <h6>Manage your QR code</h6>
                </div>
            </div>
            <div class="d-flex align-items-center">
                <ul class="table-top-head">
                    <li>
                        <a data-bs-toggle="tooltip" data-bs-placement="top" title="Refresh"><i class="ti ti-refresh"></i></a>
                    </li>
                    <li>
                        <a data-bs-toggle="tooltip" data-bs-placement="top" title="Collapse" id="collapse-header"><i class="ti ti-chevron-up"></i></a>
                    </li>
                </ul>
            </div>
        </div>
        <div class="barcode-content-list">
            <form>
                <div class="row">
                    <div class="col-lg-6 col-12">
                        <div class="row seacrh-barcode-item">
                            <div class="col-sm-6 mb-3 seacrh-barcode-item-one">
                                <label class="form-label">Warehouse<span class="text-danger ms-1">*</span></label>
                                <select class="select">
                                    <option>Select</option>
                                    <option>Lavish Warehouse</option>
                                    <option>Quaint Warehouse</option>
                                    <option>Traditional Warehouse</option>
                                    <option>Cool Warehouse</option>
                                    <option>Overflow Warehouse</option>
                                    <option>Nova Storage Hub</option>
                                    <option>Retail Supply Hub</option>
                                    <option>EdgeWare Solutions</option>
                                </select>
                            </div>
                            <div class="col-sm-6 mb-3 seacrh-barcode-item-one">
                                <label class="form-label">Store<span class="text-danger ms-1">*</span></label>
                                <select class="select">
                                    <option>Select</option>
                                    <option>Electro Mart</option>
                                    <option>Quantum Gadgets</option>
                                    <option>Prime Bazaar</option>
                                    <option>Gadget World</option>
                                    <option>Volt Vault</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-6">
                        
                        <div class="search-form  seacrh-barcode-item">
                            <div class="search-form">
                                <label class="form-label">Product<span class="text-danger ms-1">*</span></label>
                                <div class="position-relative">
                                    <input type="text" class="form-control" placeholder="Search Product by Code">
                                    <i data-feather="search" class="feather-search"></i>
                                </div>
                            
                              <div class="dropdown-menu search-dropdown w-100 h-auto rounded-1 mt-2" aria-labelledby="dropdownsearchClickable">
                                <ul>
                                    <li class="fs-14 text-gray-9 mb-2">Amazon Echo Dot</li>
                                    <li class="fs-14 text-gray-9 mb-2">Armani Belt</li>
                                    <li class="fs-14 text-gray-9 mb-2">Apple  Watch</li>
                                    <li class="fs-14 text-gray-9">Apple Iphone 14 Pro</li>
                                </ul>
                              </div>
                            </div>
                        </div>                                                             
                                                     
                    </div>
                </div>
            </form>  

            <div class="col-lg-12">
                <div class="modal-body-table search-modal-header bg-light p-2 p-sm-4">
                    <div class="table-responsive rounded-1 qrcode-table">
                        <table class="table  datatable">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>SKU</th>
                                    <th>Code</th>
                                    <th>Reference Number</th>
                                    <th>Qty</th>
                                    <th class="text-center no-sort bg-secondary-transparent"></th>
                                </tr>
                            </thead>
                            <tbody>
                                
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <a href="javascript:void(0);" class="avatar avatar-md me-2">
                                                <img src="{{URL::asset('build/img/products/stock-img-02.png')}}" alt="product">
                                            </a>
                                            <a href="javascript:void(0);">Nike Jordan</a>
                                        </div>												
                                    </td>
                                    <td>PT002</td>
                                    <td>HG3FK</td>
                                    <td>32RRR554</td>
                                    <td>
                                        <div class="product-quantity">
                                            <span class="quantity-btn"><i data-feather="minus-circle" class="feather-search"></i></span>
                                            <input type="text" class="quntity-input" value="4">                                                        
                                            <span class="quantity-btn">+<i data-feather="plus-circle" class="plus-circle"></i></span>
                                        </div>
                                    </td>
                                    <td class="action-table-data justify-content-center">
                                        <div class="edit-delete-action">                                                       
                                            <a data-bs-toggle="modal" data-bs-target="#delete-modal" class="barcode-delete-icon" href="javascript:void(0);">
                                                <i data-feather="trash-2" class="feather-trash-2"></i>
                                            </a>
                                        </div>
                                        
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                
            </div>

            <div class="paper-search-size">
                <div class="row align-items-center">
                    <div class="col-lg-6">
                        <form>
                            <label class="form-label">Paper Size<span class="text-danger ms-1">*</span></label>
                            <select class="select">
                                <option>Select</option>
                                <option>A3</option>
                                <option>A4</option>
                                <option>A5</option>
                                <option>A6</option>
                            </select>
                        </form>
                    </div>
                    <div class="col-lg-6 pt-3">
                        <div class="row">
                            <div class="col-sm-4">
                                <div class="search-toggle-list">
                                    <p>Reference Number</p>
                                    <div class="m-0">
                                        <div class="status-toggle modal-status d-flex justify-content-between align-items-center">
                                            <input type="checkbox" id="user7" class="check" checked>
                                            <label for="user7" class="checktoggle mb-0">	</label>
                                        </div>
                                    </div>
                                </div> 
                            </div>    
                        </div>                                                               
                    </div>
                </div>
            </div> 

            <div class="search-barcode-button">                            
                <a href="javascript:void(0);" class="btn btn-submit me-2 mt-0 fs-13 btn-primary shadow-none" data-bs-toggle="modal" data-bs-target="#prints-barcode">
                    <span><i class="fas fa-eye me-2"></i></span>                                
                    Generate QR Code</a>
                <a href="javascript:void(0);" class="btn btn-cancel me-2 fs-13 btn-secondary shadow-none">
                    <span><i class="fas fa-power-off me-2"></i></span>                                  
                    Reset</a>
                <a href="javascript:void(0);" class="btn btn-cancel close-btn fs-13 btn-danger shadow-none">
                    <span><i class="fas fa-print me-2"></i></span>                                  
                    Print QRcode</a>
            </div>
        </div>                								
    </div>
    <div class="footer d-sm-flex align-items-center justify-content-between border-top bg-white p-3">
        <p class="mb-0 text-gray-9">2014 - 2025 &copy; DreamsPOS. All Right Reserved</p>
        <p>Designed &amp; Developed by <a href="javascript:void(0);" class="text-primary">Dreams</a></p>
    </div>
</div>
@endsection
