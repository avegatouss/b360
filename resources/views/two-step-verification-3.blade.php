<?php $page = 'two-step-verification-3'; ?>
@extends('layout.mainlayout')
@section('content')
    <div class="account-content">
        <div class="login-wrapper login-new">
            <div class="row w-100">
                <div class="col-lg-5 mx-auto">
                    <div class="login-content user-login">
                        <div class="login-logo">
                            <img src="{{URL::asset('build/img/logo.svg')}}" alt="img">
                            <a href="{{url('index')}}" class="login-logo logo-white">
                                <img src="{{URL::asset('build/img/logo-white.svg')}}"  alt="Img">
                            </a>
                        </div>
                        <form action="{{url('reset-password')}}" class="digit-group">
                            <div class="card">
                                <div class="card-body p-5">
                                    <div class="login-userheading">
                                        <h3>2 Step Verification</h3>
                                        <h4>Enter the OTP sent to ******doe@example.com to confirm your account.</h4>
                                    </div>
                                    <div class="text-center otp-input">
                                        <div class="d-flex align-items-center mb-3">
                                            <input type="text" class=" rounded w-100 py-sm-3 py-2 text-center fs-26 fw-bold me-3" id="digit-1" name="digit-1" data-next="digit-2" maxlength="1">
                                            <input type="text" class=" rounded w-100 py-sm-3 py-2 text-center fs-26 fw-bold me-3" id="digit-2" name="digit-2" data-next="digit-3" data-previous="digit-1" maxlength="1">
                                            <input type="text" class=" rounded w-100 py-sm-3 py-2 text-center fs-26 fw-bold me-3" id="digit-3" name="digit-3" data-next="digit-4" data-previous="digit-2" maxlength="1">
                                            <input type="text" class=" rounded w-100 py-sm-3 py-2 text-center fs-26 fw-bold" id="digit-4" name="digit-4" data-next="digit-5" data-previous="digit-3" maxlength="1">
                                        </div>
                                        <div>
                                            <div class="badge bg-danger-transparent mb-3">
                                                <p class="d-flex align-items-center "><i class="ti ti-clock me-1"></i>09:59</p>
                                            </div>
                                            <div class="mb-3 d-flex justify-content-center">
                                                <p class="text-gray-9">Didn't get the OTP? <a href="javascript:void(0);" class="text-primary">Resend OTP</a></p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mb-0">
                                        <button type="submit" class="btn btn-primary w-100">Verify & Proceed</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="my-4 d-flex justify-content-center align-items-center copyright-text">
                <p>Copyright &copy; 2025 DreamsPOS</p>
            </div>
        </div>
    </div>
@endsection
