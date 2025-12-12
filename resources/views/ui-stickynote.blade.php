<?php $page = 'ui-stickynote'; ?>
@extends('layout.mainlayout')
@section('content')
    <div class="page-wrapper cardhead">
        <div class="content ">

            @component('components.breadcrumb')
                @slot('title')
                    Sticky Note
                @endslot
                @slot('li_1')
                    Dashboard
                @endslot
                @slot('li_2')
                    Sticky Note
                @endslot
            @endcomponent

            <div class="row">

                <!-- Sticky -->
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">Sticky Note <a class="btn btn-primary float-sm-end m-l-10" id="add_new"
                                    href="javascript:void(0);">Add New Note</a></h5>
                        </div>
                        <div class="card-body">
                            <div class="sticky-note" id="board"></div>
                        </div>
                    </div>
                </div>
                <!-- /Sticky -->

            </div>

        </div>
        <div class="footer d-sm-flex align-items-center justify-content-between border-top bg-white p-3">
            <p class="mb-0">2014 - 2025 &copy; DreamsPOS. All Right Reserved</p>
            <p>Designed &amp; Developed by <a href="javascript:void(0);" class="text-primary">Dreams</a></p>
        </div>
    </div>
@endsection
