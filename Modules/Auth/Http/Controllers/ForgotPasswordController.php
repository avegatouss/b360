<?php

namespace Modules\Auth\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Password;
use Modules\Auth\Rules\RecaptchaV3;

final class ForgotPasswordController extends Controller
{
    public function show()
    {
        return view('authmod::passwords.forgot');
    }

    public function send(Request $request)
    {
        $rules = [
            'email' => ['required', 'email'],
        ];

        if (config('recaptcha.enabled')) {
            $rules['recaptcha_token'] = ['required', 'string', new RecaptchaV3()];
        }

        $request->validate($rules);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status === Password::RESET_LINK_SENT) {
            return back()->with('status', __($status));
        }

        return back()->withErrors(['email' => __($status)]);
    }
}
