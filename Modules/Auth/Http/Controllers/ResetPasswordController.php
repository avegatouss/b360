<?php

namespace Modules\Auth\Http\Controllers;

use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

final class ResetPasswordController extends Controller
{
    public function show(Request $request, string $token)
    {
        return view('authmod::passwords.reset', [
            'token' => $token,
            'email' => $request->email,
        ]);
    }

    public function reset(Request $request)
    {
        $passwordRules = ['required', 'string', 'min:' . (int) setting('security.password_min_length', 8), 'confirmed', 'max:255'];
        if (setting('security.password_require_uppercase')) {
            $passwordRules[] = 'regex:/[A-Z]/';
        }
        if (setting('security.password_require_number')) {
            $passwordRules[] = 'regex:/[0-9]/';
        }

        $request->validate([
            'token'                 => ['required'],
            'email'                 => ['required', 'email'],
            'password'              => $passwordRules,
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                ])->setRememberToken(Str::random(60));

                $user->save();

                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('login')
                ->with('status', __($status));
        }

        return back()
            ->withErrors(['email' => __($status)])
            ->withInput($request->only('email'));
    }
}
