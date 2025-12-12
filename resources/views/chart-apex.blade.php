<?php $page = 'chart-apex'; ?>
@extends('layout.mainlayout')
@section('content')
    <div class="page-wrapper cardhead">
        <div class="content ">
        
            <!-- Page Header -->
            <div class="page-header">
                <div class="row">
                    <div class="col-sm-12">
                        <h3 class="page-title">Charts</h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{url('index')}}">Dashboard</a></li>
                            <li class="breadcrumb-item active">Charts</li>
                        </ul>
                    </div>
                </div>
            </div>
            <!-- /Page Header -->
            
            <div class="row">
            
                <!-- Chart -->
                <div class="col-md-6">	
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">Apex Simple</h5>
                        </div>
                        <div class="card-body">
                            <div id="s-line" class="chart-set"></div>
                        </div>
                    </div>
                </div>
                <!-- /Chart -->
                    
                <!-- Chart -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">Area Chart</h5>
                        </div>
                        <div class="card-body">
                            <div id="s-line-area" class="chart-set"></div>
                        </div>
                    </div>
                </div>
                <!-- /Chart -->
                    
                <!-- Chart -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">Column Chart</h5>
                        </div>
                        <div class="card-body">
                            <div id="s-col" class="chart-set"></div>
                        </div>
                    </div>
                </div>
                <!-- /Chart -->
                
                <!-- Chart -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">Column Stacked Chart</h5>
                        </div>
                        <div class="card-body">
                            <div id="s-col-stacked" class="chart-set"></div>
                        </div>
                    </div>
                </div>
                <!-- /Chart -->
                
                
                <!-- Chart -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">Bar Chart</h5>
                        </div>
                        <div class="card-body">
                            <div id="s-bar" class="chart-set"></div>
                        </div>
                    </div>
                </div>
                <!-- /Chart -->
                
                <!-- Chart -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">Mixed Chart</h5>
                        </div>
                        <div class="card-body">
                            <div id="mixed-chart" class="chart-set"></div>
                        </div>
                    </div>
                </div>
                <!-- /Chart -->
                
                <!-- Chart -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">Donut Chart</h5>
                        </div>
                        <div class="card-body">
                            <div id="donut-chart" class="chart-set"></div>
                        </div>
                    </div>
                </div>
                <!-- /Chart -->
                
                <!-- Chart -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">Radial Chart</h5>
                        </div>
                        <div class="card-body">
                            <div id="radial-chart" class="chart-set"></div>
                        </div>
                    </div>
                </div>
                <!-- /Chart -->
                
            </div>
        </div>	
        <div class="footer d-sm-flex align-items-center justify-content-between border-top bg-white p-3">
            <p class="mb-0 text-gray-9">2014 - 2025 &copy; DreamsPOS. All Right Reserved</p>
            <p>Designed &amp; Developed by <a href="javascript:void(0);" class="text-primary">Dreams</a></p>
        </div>
    </div>
@endsection
