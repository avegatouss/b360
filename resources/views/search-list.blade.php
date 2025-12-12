<?php $page = 'search-list'; ?>
@extends('layout.mainlayout')
@section('content')

    <div class="page-wrapper">
        <div class="content">

            <div class="page-header">
                <div class="add-item d-flex">
                    <div class="page-title">
                        <h4>Search List</h4>
                        <h6>Manage your search</h6>
                    </div>
                </div>
                <div class="d-flex flex-sm-row flex-column align-items-sm-center align-items-start">
                    <ul class="table-top-head">
                        <li>
                            <a data-bs-toggle="tooltip" data-bs-placement="top" title="Refresh"><i class="ti ti-refresh"></i></a>
                        </li>
                        <li>
                            <a  data-bs-toggle="tooltip" data-bs-placement="top" title="Collapse" id="collapse-header"><i class="ti ti-chevron-up"></i></a>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <form action="{{url('search-list')}}">
                        <div class="d-flex align-items-center">
                            <input type="text" class="form-control flex-fill me-3" value="DreamsPOS">
                            <button type="submit" class="btn btn-primary">Search</button>
                        </div>
                    </form>
                </div>
            </div>
            <div class="card">
                <div class="card-body">
                    <h5 class="mb-3">Search result for "DreamsPOS"</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card shadow-none">
                                <div class="card-body">
                                    <a href="#" class="text-info text-truncate mb-2">https://themeforest.net/search/dreamspos</a>
                                    <p class="text-truncate line-clamb-2 mb-2">DreamsPOS - Html, Vue 3, Angular 17+, React & Node HR Project Management & CRM Admin Dashboard Template</p>
                                    <div class="d-flex align-items-center flex-wrap row-gap-2">
                                        <span class="text-gray-9 me-3 pe-3 border-end">1.7K Sales</span>
                                        <div class="text-gray-9 d-flex align-items-center me-3 pe-3 border-end">
                                            <i class="ti ti-star-filled text-warning me-1"></i>
                                            <i class="ti ti-star-filled text-warning me-1"></i>
                                            <i class="ti ti-star-filled text-warning me-1"></i>
                                            <i class="ti ti-star-filled text-warning me-1"></i>
                                            <i class="ti ti-star-filled text-gray-2 me-1"></i>
                                            (45)
                                        </div>
                                        <span class="text-gray-9">$35</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <h5 class="mb-3">Images</h5>
                    <div class="row g-3">
                        <div class="col-xl-2 col-md-4 col-6">
                            <a href="{{URL::asset('build/img/media/media-15.jpg')}}" data-fancybox="gallery" class="gallery-item">
                                <img src="{{URL::asset('build/img/media/media-15.jpg')}}" class="rounded" alt="img">
                            </a>
                        </div>
                        <div class="col-xl-2 col-md-4 col-6">
                            <a href="{{URL::asset('build/img/media/media-16.jpg')}}" data-fancybox="gallery" class="gallery-item">
                                <img src="{{URL::asset('build/img/media/media-16.jpg')}}" class="rounded" alt="img">
                            </a>
                        </div>
                        <div class="col-xl-2 col-md-4 col-6">
                            <a href="{{URL::asset('build/img/media/media-17.jpg')}}" data-fancybox="gallery" class="gallery-item">
                                <img src="{{URL::asset('build/img/media/media-17.jpg')}}" class="rounded" alt="img">
                            </a>
                        </div>
                        <div class="col-xl-2 col-md-4 col-6">
                            <a href="{{URL::asset('build/img/media/media-18.jpg')}}" data-fancybox="gallery" class="gallery-item">
                                <img src="{{URL::asset('build/img/media/media-18.jpg')}}" class="rounded" alt="img">
                            </a>
                        </div>
                        <div class="col-xl-2 col-md-4 col-6">
                            <a href="{{URL::asset('build/img/media/media-19.jpg')}}" data-fancybox="gallery" class="gallery-item">
                                <img src="{{URL::asset('build/img/media/media-19.jpg')}}" class="rounded" alt="img">
                            </a>
                        </div>
                        <div class="col-xl-2 col-md-4 col-6">
                            <a href="{{URL::asset('build/img/media/media-20.jpg')}}" data-fancybox="gallery" class="gallery-item">
                                <img src="{{URL::asset('build/img/media/media-20.jpg')}}" class="rounded" alt="img">
                            </a>
                        </div>
                        <div class="col-xl-2 col-md-4 col-6">
                            <a href="{{URL::asset('build/img/media/media-21.jpg')}}" data-fancybox="gallery" class="gallery-item">
                                <img src="{{URL::asset('build/img/media/media-21.jpg')}}" class="rounded" alt="img">
                            </a>
                        </div>
                        <div class="col-xl-2 col-md-4 col-6">
                            <a href="{{URL::asset('build/img/media/media-22.jpg')}}" data-fancybox="gallery" class="gallery-item">
                                <img src="{{URL::asset('build/img/media/media-22.jpg')}}" class="rounded" alt="img">
                            </a>
                        </div>
                        <div class="col-xl-2 col-md-4 col-6">
                            <a href="{{URL::asset('build/img/media/media-23.jpg')}}" data-fancybox="gallery" class="gallery-item">
                                <img src="{{URL::asset('build/img/media/media-23.jpg')}}" class="rounded" alt="img">
                            </a>
                        </div>
                        <div class="col-xl-2 col-md-4 col-6">
                            <a href="{{URL::asset('build/img/media/media-24.jpg')}}" data-fancybox="gallery" class="gallery-item">
                                <img src="{{URL::asset('build/img/media/media-24.jpg')}}" class="rounded" alt="img">
                            </a>
                        </div>
                        <div class="col-xl-2 col-md-4 col-6">
                            <a href="{{URL::asset('build/img/media/media-25.jpg')}}" data-fancybox="gallery" class="gallery-item">
                                <img src="{{URL::asset('build/img/media/media-25.jpg')}}" class="rounded" alt="img">
                            </a>
                        </div>
                        <div class="col-xl-2 col-md-4 col-6">
                            <a href="{{URL::asset('build/img/media/media-26.jpg')}}" data-fancybox="gallery" class="gallery-item">
                                <img src="{{URL::asset('build/img/media/media-26.jpg')}}" class="rounded" alt="img">
                            </a>
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