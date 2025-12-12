<?php $page = 'two-step-verification'; ?>
@extends('layout.mainlayout')
@section('content')
    <div class="account-content">
        <div class="login-wrapper bg-img">
            <div class="login-content authent-content">
                <form action="{{url('reset-password')}}" class="digit-group">
                    <div class="login-userset">
                        <div class="login-logo logo-normal">
                            <img src="{{URL::asset('build/img/logo.svg')}}" alt="img">
                        </div>
                        <a href="{{url('index')}}" class="login-logo logo-white">
                            <img src="{{URL::asset('build/img/logo-white.svg')}}"  alt="Img">
                        </a>
                        <div>
                            <div class="login-userheading">
                                <h3>2 Step Verification</h3>
                                <h4>Please enter the OTP received to confirm your account ownership. A code has been send to ******doe@example.com</h4>
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
                            <div class="mb-3">
                                <button type="submit" class="btn btn-primary w-100">Submit</button>
                            </div>
                        </div>
                        <div class="my-4 d-flex justify-content-center align-items-center copyright-text">
                            <p>Copyright &copy; 2025 DreamsPOS</p>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
