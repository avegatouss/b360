<?php $page = 'blank-page'; ?>
@extends('layout.mainlayout')
@section('content')
    <div class="page-wrapper pagehead d-flex flex-column justify-content-between">
        <div class="content flex-grow-1">
            <div class="page-header">
                <div class="page-title">
                    <h4>Blank Page</h4>
                    <h6>Sub Title</h6>
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
        </div>
        <div class="footer d-sm-flex align-items-center justify-content-between border-top bg-white p-3 align-content-end">
            <p class="mb-0">2014 - 2025 &copy; DreamsPOS. All Right Reserved</p>
            <p>Designed &amp; Developed by <a href="javascript:void(0);" class="text-primary">Dreams</a></p>
        </div>
    </div>
@endsection