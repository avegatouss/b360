<?php $page = 'balance-sheet'; ?>
@extends('layout.mainlayout')
@section('content')
   
<div class="page-wrapper">
    <div class="content">
        <div class="page-header">
            <div class="add-item d-flex">
                <div class="page-title">
                    <h4 class="fw-bold">Balance Sheet</h4>
                    <h6>View Your Balance Sheet </h6>
                </div>
            </div>
            <ul class="table-top-head">
                <li>
                    <a data-bs-toggle="tooltip" data-bs-placement="top" title="Pdf"><img src="{{URL::asset('build/img/icons/pdf.svg')}}" alt="img"></a>
                </li>
                <li>
                    <a data-bs-toggle="tooltip" data-bs-placement="top" title="Excel"><img src="{{URL::asset('build/img/icons/excel.svg')}}" alt="img"></a>
                </li>
                <li>
                    <a data-bs-toggle="tooltip" data-bs-placement="top" title="Refresh"><i class="ti ti-refresh"></i></a>
                </li>
                <li>
                    <a data-bs-toggle="tooltip" data-bs-placement="top" title="Collapse" id="collapse-header"><i class="ti ti-chevron-up"></i></a>
                </li>
            </ul>
        </div>
        
        <!-- /product list -->
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
                <div class="search-set">
                    <div class="search-input">
                        <span class="btn-searchset"><i class="ti ti-search fs-14 feather-search"></i></span>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table datatable">
                        <thead class="thead-light">
                            <tr>						
                                <th>Name</th>
                                <th>Bank & Account Number</th>
                                <th>Credit</th>
                                <th>Debit</th>
                                <th>Balance</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                              <td class="p-3">Zephyr Indira</td>
                              <td class="p-3">HBSC - 3298784309485</td>
                              <td class="p-3">$4565</td>
                              <td class="p-3">-$200</td>
                              <td class="p-3">$4365</td>
                            </tr>   
                            <tr>
                                <td class="p-3">Quillon Elysia</td>
                                <td class="p-3">SWIZ - 5475878970090</td>
                                <td class="p-3">$4494</td>
                                <td class="p-3">-$50</td>
                                <td class="p-3">$4444</td>
                              </tr>  
                              <tr>
                                <td class="p-3">Thaddeus Juniper</td>
                                <td class="p-3">SWIZ - 3255465758698</td>
                                <td class="p-3">$65945</td>
                                <td class="p-3">-$800</td>
                                <td class="p-3">$65145</td>
                              </tr>  
                              <tr>
                                <td class="p-3">Orion Astrid</td>
                                <td class="p-3">IBO - 4353689870544</td>
                                <td class="p-3">$1948</td>
                                <td class="p-3">-$100</td>
                                <td class="p-3">$1848</td>
                              </tr>
                              <tr>
                                <td class="p-3">Caspian Marigold</td>
                                <td class="p-3">NBC - 4324356677889</td>
                                <td class="p-3">$1686</td>
                                <td class="p-3">-$700</td>
                                <td class="p-3">$986</td>
                              </tr>
                              <tr>
                                <td class="p-3">Emma James</td>
                                <td class="p-3">NBC - 2343547586900</td>
                                <td class="p-3">$16547</td>
                                <td class="p-3">-$1000</td>
                                <td class="p-3">$15547</td>
                              </tr>
                              <tr>
                                <td class="p-3">Olivia Ethan</td>
                                <td class="p-3">IBO - 3453647664889</td>
                                <td class="p-3">$141845</td>
                                <td class="p-3">-$1200</td>
                                <td class="p-3">$141645</td>
                              </tr>
                              <tr>
                                <td class="p-3">Sophia Liam</td>
                                <td class="p-3">SWIZ - 3354456565687</td>
                                <td class="p-3">$44188</td>
                                <td class="p-3">-$750</td>
                                <td class="p-3">$4356</td>
                              </tr>
                              <tr>
                                <td class="p-3">Ava Mason</td>
                                <td class="p-3">SWIZ - 3456565767787</td>
                                <td class="p-3">$614848</td>
                                <td class="p-3">-$450</td>
                                <td class="p-3">$614389</td>
                              </tr>
                              <tr>
                                <td class="p-3">Isabella Jackson</td>
                                <td class="p-3">IBO - 3434565776768</td>
                                <td class="p-3">$77818</td>
                                <td class="p-3">-$300</td>
                                <td class="p-3">$77518</td>
                              </tr>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td class="bg-light fw-bold p-3 fs-16" colspan="2">Total</td>
                                <td class="bg-light fw-bold p-3 fs-16">$332642.53</td>
                                <td class="bg-light fw-bold p-3 fs-16">- $16590.96</td>
                                <td class="bg-light fw-bold p-3 fs-16">$332687442.53</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
        <!-- /product list -->
    </div>
    <div class="footer d-sm-flex align-items-center justify-content-between border-top bg-white p-3">
        <p class="mb-0 text-gray-9">2014 - 2025 &copy; DreamsPOS. All Right Reserved</p>
        <p>Designed &amp; Developed by <a href="javascript:void(0);" class="text-primary">Dreams</a></p>
    </div>
</div>

@endsection
