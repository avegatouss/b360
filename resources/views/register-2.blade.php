<?php $page = 'register-2'; ?>
@extends('layout.mainlayout')
@section('content')
    <div class="account-content">
        <div class="row login-wrapper m-0">
            <div class="col-lg-6 p-0">
                <div class="login-content">
                    <form action="{{url('signin')}}">
                        <div class="login-userset">
                            <div class="login-logo logo-normal">
                            <img src="{{URL::asset('build/img/logo.svg')}}" alt="img">
                        </div>
                        <a href="{{url('index')}}" class="login-logo logo-white">
                            <img src="{{URL::asset('build/img/logo-white.svg')}}"  alt="Img">
                        </a>
                        <div class="login-userheading">
                            <h3>Register</h3>
                            <h4>Create New Dreamspos Account</h4>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Name <span class="text-danger"> *</span></label>
                            <div class="input-group">
                                <input type="text" value="" class="form-control border-end-0">
                                <span class="input-group-text border-start-0">
                                    <i class="ti ti-user"></i>
                                </span>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email <span class="text-danger"> *</span></label>
                            <div class="input-group">
                                <input type="text" value="" class="form-control border-end-0">
                                <span class="input-group-text border-start-0">
                                    <i class="ti ti-mail"></i>
                                </span>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password <span class="text-danger"> *</span></label>
                            <div class="pass-group">
                                <input type="password" class="pass-input form-control">
                                <span class="ti toggle-password ti-eye-off text-gray-9"></span>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Confirm Password <span class="text-danger"> *</span></label>
                            <div class="pass-group">
                                <input type="password" class="pass-inputs form-control">
                                <span class="ti toggle-passwords ti-eye-off text-gray-9"></span>
                            </div>
                        </div>
                        <div class="form-login authentication-check">
                            <div class="row">
                                <div class="col-sm-8">
                                    <div class="custom-control custom-checkbox justify-content-start">
                                        <div class="custom-control custom-checkbox">
                                            <label class="checkboxs ps-4 mb-0 pb-0 line-height-1">
                                                <input type="checkbox">
                                                <span class="checkmarks"></span>I agree to the <a href="#" class="text-primary">Terms & Privacy</a>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-login">
                            <button type="submit" class="btn btn-login">Sign Up</button>
                        </div>
                        <div class="signinform">
                            <h4>Already have an account ? <a href="{{url('signin')}}" class="hover-a">Sign In Instead</a></h4>
                        </div>
                        <div class="form-setlogin or-text">
                            <h4>OR</h4>
                        </div>
                        <div class="mt-2">
                            <div class="d-flex align-items-center justify-content-center flex-wrap">
                                <div class="text-center me-2 flex-fill">
                                    <a href="javascript:void(0);"
                                        class="br-10 p-2 btn btn-info d-flex align-items-center justify-content-center">
                                        <img class="img-fluid m-1" src="{{URL::asset('build/img/icons/facebook-logo.svg')}}" alt="Facebook">
                                    </a>
                                </div>
                                <div class="text-center me-2 flex-fill">
                                    <a href="javascript:void(0);"
                                        class="btn btn-white br-10 p-2  border d-flex align-items-center justify-content-center">
                                        <img class="img-fluid m-1" src="{{URL::asset('build/img/icons/google-logo.svg')}}" alt="Facebook">
                                    </a>
                                </div>
                                <div class="text-center flex-fill">
                                    <a href="javascript:void(0);"
                                        class="bg-dark br-10 p-2 btn btn-dark d-flex align-items-center justify-content-center">
                                        <img class="img-fluid m-1" src="{{URL::asset('build/img/icons/apple-logo.svg')}}" alt="Apple">
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="my-4 d-flex justify-content-center align-items-center copyright-text">
                            <p>Copyright &copy; 2025 DreamsPOS</p>
                        </div>
                    </div>
                    </form>
                </div>
            </div>
            <div class="col-lg-6 p-0">
                <div class="login-img">
                    <img src="{{URL::asset('build/img/authentication/authentication-02.svg')}}" alt="img">
                </div>
            </div>
        </div>
    </div>
@endsection
