<?php $page = 'forgot-password-2'; ?>
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
                            <h3>Forgot password?</h3>
                            <h4>If you forgot your password, well, then we’ll email you instructions to reset your password.</h4>
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
                        <div class="form-login">
                                <button type="submit" class="btn btn-login">Sign Up</button>
                        </div>
                        <div class="signinform text-center">
                            <h4>Return to<a href="{{url('signin-2')}}" class="hover-a"> login </a></h4>
                        </div>
                        <div class="form-setlogin or-text">
                            <h4>OR</h4>
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
                    <img src="{{URL::asset('build/img/authentication/authentication-03.svg')}}" alt="img">
                </div>
            </div>
        </div>
    </div>
@endsection
