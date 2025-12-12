<?php $page = 'chart-c3'; ?>
@extends('layout.mainlayout')
@section('content')
    <div class="page-wrapper cardhead">
        <div class="content">
        
            <!-- Page Header -->
            <div class="page-header">
                <div class="row">
                    <div class="col-sm-12">
                        <h3 class="page-title">C3 Chart</h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{url('index')}}">Dashboard</a></li>
                            <li class="breadcrumb-item active">C3 Charts</li>
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
                            <div class="card-title">Bar Chart</div>
                        </div>
                        <div class="card-body">
                            <div id="chart-bar-stacked"></div>
                        </div>
                    </div>
                </div>
                <!-- /Chart -->
                
                <!-- Chart -->
                <div class="col-md-6">	
                    <div class="card">
                        <div class="card-header">
                            <div class="card-title">Multiple Bar Chart</div>
                        </div>
                        <div class="card-body">
                            <div  id="chart-bar"></div>
                        </div>
                    </div>
                </div>
                <!-- /Chart -->
                
                <!-- Chart -->
                <div class="col-md-6">	
                    <div class="card">
                        <div class="card-header">
                            <div class="card-title">Horizontal Bar Chart</div>
                        </div>
                        <div class="card-body">
                            <div  id="chart-bar-rotated" ></div>
                        </div>
                    </div>
                </div>
                <!-- /Chart -->
                
                <!-- Chart -->
                <div class="col-md-6">	
                    <div class="card">
                        <div class="card-header">
                            <div class="card-title">Line Chart</div>
                        </div>
                        <div class="card-body">
                            <div id="chart-sracked"></div>
                        </div>
                    </div>
                </div>
                <!-- /Chart -->
                
                <!-- Chart -->
                <div class="col-md-6">	
                    <div class="card">
                        <div class="card-header">
                            <div class="card-title">Line Chart</div>
                        </div>
                        <div class="card-body">
                            <div id="chart-spline-rotated"></div>
                        </div>
                    </div>
                </div>
                <!-- /Chart -->
                
                <!-- Chart -->
                <div class="col-md-6">	
                    <div class="card">
                        <div class="card-header">
                            <div class="card-title">Line Chart</div>
                        </div>
                        <div class="card-body">
                            <div id="chart-area-spline-sracked"></div>
                        </div>
                    </div>
                </div>
                <!-- /Chart -->
                
                <!-- Chart -->
                <div class="col-md-6">	
                    <div class="card">
                        <div class="card-header">
                            <div class="card-title">Pie Chart</div>
                        </div>
                        <div class="card-body">
                            <div id="chart-pie"></div>
                        </div>
                    </div>
                </div>
                <!-- /Chart -->
                
                <!-- Chart -->
                <div class="col-md-6">	
                    <div class="card">
                        <div class="card-header">
                            <div class="card-title">Donut Chart</div>
                        </div>
                        <div class="card-body">
                            <div id="chart-donut"></div>
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
