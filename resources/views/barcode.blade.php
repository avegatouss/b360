<?php $page = 'barcode'; ?>
@extends('layout.mainlayout')
@section('content')
    <div class="page-wrapper notes-page-wrapper">
        <div class="content">
            <div class="page-header">
                <div class="add-item d-flex">
                    <div class="page-title">
                        <h4 class="fw-bold">Print Barcode</h4>
                        <h6>Manage your barcodes</h6>
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
                            <div class="row seacrh-barcode-item mb-1">
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
                            <div class="mb-3 search-form seacrh-barcode-item">
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
                    <div class="p-3 bg-light rounded border mb-3">
                        <div class="table-responsive rounded border">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th>SKU</th>
                                        <th>Code</th>
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
                                        <td>
                                            <div class="product-quantity border-secondary-transparent">
                                                <span class="quantity-btn"><i data-feather="minus-circle" class="feather-search"></i></span>                                                        
                                                <input type="text" class="quntity-input" value="4">
                                                <span class="quantity-btn">+<i data-feather="plus-circle" class="plus-circle"></i></span>
                                                
                                            </div>
                                        </td>
                                        <td class="action-table-data">
                                            <div class="edit-delete-action">
                                                <a data-bs-toggle="modal" data-bs-target="#delete-modal" class="barcode-delete-icon" href="javascript:void(0);">
                                                    <i data-feather="trash-2" class="feather-trash-2"></i>
                                                </a>
                                            </div>
                                            
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <a href="javascript:void(0);" class="avatar avatar-md me-2">
                                                    <img src="{{URL::asset('build/img/products/stock-img-03.png')}}" alt="product">
                                                </a>
                                                <a href="javascript:void(0);">Apple Series 5 Watch</a>
                                            </div>												
                                        </td>
                                        <td>PT003</td>
                                        <td>TEUIU7</td>
                                        <td>
                                            <div class="product-quantity border-secondary-transparent">
                                                <span class="quantity-btn"><i data-feather="minus-circle" class="feather-search"></i></span>                                                        
                                                <input type="text" class="quntity-input" value="4">
                                                <span class="quantity-btn">+<i data-feather="plus-circle" class="plus-circle"></i></span>                                                       
                                            </div>
                                        </td>
                                        <td class="action-table-data">
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
                            <form class="mb-0">
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
                                        <p>Show Store Name</p>
                                        <div class="m-0">
                                            <div class="status-toggle modal-status d-flex justify-content-between align-items-center">
                                                <input type="checkbox" id="user7" class="check" checked>
                                                <label for="user7" class="checktoggle mb-0"></label>
                                            </div>
                                        </div>
                                    </div> 
                                </div>    
                                    
                                <div class="col-sm-4">
                                    <div class="search-toggle-list">
                                        <p>Show Product Name</p>
                                        <div class="m-0">
                                            <div class="status-toggle modal-status d-flex justify-content-between align-items-center">
                                                <input type="checkbox" id="user8" class="check" checked>
                                                <label for="user8" class="checktoggle mb-0"></label>
                                            </div>
                                        </div>
                                    </div> 
                                </div>


                                <div class="col-sm-4">
                                    <div class="search-toggle-list">
                                        <p>Show Price</p>
                                        <div class="m-0">
                                            <div class="status-toggle modal-status d-flex justify-content-between align-items-center">
                                                <input type="checkbox" id="user9" class="check" checked>
                                                <label for="user9" class="checktoggle mb-0">	</label>
                                            </div>
                                        </div>
                                    </div> 
                                </div> 
                            </div>                                                               
                        </div>
                    </div>
                </div> 

                <div class="search-barcode-button">                            
                    <a href="javascript:void(0);" class="btn btn-submit btn-primary me-2 mt-0" data-bs-toggle="modal" data-bs-target="#prints-barcode">
                        <span><i class="fas fa-eye me-1"></i></span>Generate Barcode
                    </a>
                    <a href="javascript:void(0);" class="btn btn-cancel btn-secondary fs-13 me-2">
                        <span><i class="fas fa-power-off me-1"></i></span>Reset Barcode
                    </a>
                    <a href="javascript:void(0);" class="btn btn-cancel btn-danger close-btn">
                        <span><i class="fas fa-print me-1"></i></span>Print Barcode
                    </a>
                </div>
            </div>                   								
        </div>
        <div class="footer d-sm-flex align-items-center justify-content-between border-top bg-white p-3">
            <p class="mb-0 text-gray-9">2014 - 2025 &copy; DreamsPOS. All Right Reserved</p>
            <p>Designed &amp; Developed by <a href="javascript:void(0);" class="text-primary">Dreams</a></p>
        </div>
    </div>
@endsection
