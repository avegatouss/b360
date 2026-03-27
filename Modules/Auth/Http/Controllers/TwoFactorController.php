<?php

namespace Modules\Auth\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use PragmaRX\Google2FA\Google2FA;

/**
 * Two-Factor Authentication (TOTP) Controller.
 *
 * Dependencies:
 *   - pragmarx/google2fa (composer require pragmarx/google2fa)
 *   - bacon/bacon-qr-code (included with pragmarx/google2fa)
 */
final class TwoFactorController extends Controller
{
    private Google2FA $google2fa;

    public function __construct()
    {
        $this->google2fa = new Google2FA();
    }

    /*
    |--------------------------------------------------------------------------
    | 2FA Setup (enable / confirm / disable)
    |--------------------------------------------------------------------------
    */

    /**
     * GET /two-factor/enable — Show the 2FA setup page.
     */
    public function showEnable(Request $request)
    {
        $user = $request->user();

        return view('authmod::two-factor.enable', [
            'enabled'       => $user->hasTwoFactorEnabled(),
            'qrCodeUri'     => session('two_factor_qr_uri'),
            'secret'        => session('two_factor_secret'),
            'recoveryCodes' => $user->hasTwoFactorEnabled()
                ? $user->two_factor_recovery_codes
                : null,
        ]);
    }

    /**
     * POST /two-factor/enable — Generate secret and show QR code.
     */
    public function enable(Request $request)
    {
        $user = $request->user();

        if ($user->hasTwoFactorEnabled()) {
            return redirect()->route('two-factor.enable')
                ->with('status', 'L\'authentification 2FA est deja activee.');
        }

        $secret = $this->google2fa->generateSecretKey();

        $qrCodeUri = $this->google2fa->getQRCodeUrl(
            config('app.name', 'B360'),
            $user->email,
            $secret,
        );

        // Store secret in session until user confirms with a valid TOTP code
        session([
            'two_factor_secret' => $secret,
            'two_factor_qr_uri' => $qrCodeUri,
        ]);

        return redirect()->route('two-factor.enable');
    }

    /**
     * POST /two-factor/confirm — Verify TOTP code to finalize 2FA activation.
     */
    public function confirm(Request $request)
    {
        $request->validate([
            'code' => ['required', 'string', 'size:6'],
        ]);

        $secret = session('two_factor_secret');

        if (!$secret) {
            return redirect()->route('two-factor.enable')
                ->withErrors(['code' => 'Aucun secret 2FA en attente. Veuillez recommencer.']);
        }

        $valid = (bool) $this->google2fa->verifyKey($secret, $request->input('code'));

        if (!$valid) {
            return back()->withErrors(['code' => 'Le code de verification est invalide.']);
        }

        $user = $request->user();
        $recoveryCodes = $user->generateTwoFactorRecoveryCodes();

        $user->forceFill([
            'two_factor_secret'         => $secret,
            'two_factor_recovery_codes' => $recoveryCodes,
            'two_factor_confirmed_at'   => now(),
        ])->save();

        // Clean up session
        session()->forget(['two_factor_secret', 'two_factor_qr_uri']);

        // Mark session as 2FA-verified (the user just confirmed)
        session(['two_factor_verified' => true]);

        return redirect()->route('two-factor.enable')
            ->with('status', 'Authentification a deux facteurs activee avec succes.')
            ->with('recovery_codes', $recoveryCodes);
    }

    /**
     * POST /two-factor/disable — Disable 2FA (requires password confirmation).
     */
    public function disable(Request $request)
    {
        $request->validate([
            'password' => ['required', 'string'],
        ]);

        $user = $request->user();

        if (!Hash::check($request->input('password'), $user->getAuthPassword())) {
            return back()->withErrors(['password' => 'Le mot de passe est incorrect.']);
        }

        $user->forceFill([
            'two_factor_secret'         => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at'   => null,
        ])->save();

        session()->forget('two_factor_verified');

        return redirect()->route('two-factor.enable')
            ->with('status', 'Authentification a deux facteurs desactivee.');
    }

    /*
    |--------------------------------------------------------------------------
    | 2FA Challenge (after login)
    |--------------------------------------------------------------------------
    */

    /**
     * GET /two-factor/challenge — Show the TOTP challenge page.
     */
    public function showChallenge(Request $request)
    {
        if (!$request->user() || !$request->user()->hasTwoFactorEnabled()) {
            return redirect('/');
        }

        if (session('two_factor_verified')) {
            return redirect()->intended('/');
        }

        return view('authmod::two-factor.challenge');
    }

    /**
     * POST /two-factor/challenge — Verify TOTP code during login.
     */
    public function verifyChallenge(Request $request)
    {
        $request->validate([
            'code' => ['required', 'string', 'size:6'],
        ]);

        $user = $request->user();

        if (!$user || !$user->validTwoFactorCode($request->input('code'))) {
            return back()->withErrors(['code' => 'Le code de verification est invalide.']);
        }

        session(['two_factor_verified' => true]);

        return redirect()->intended('/');
    }

    /**
     * GET /two-factor/recovery — Show recovery code input page.
     */
    public function showRecovery(Request $request)
    {
        if (!$request->user() || !$request->user()->hasTwoFactorEnabled()) {
            return redirect('/');
        }

        if (session('two_factor_verified')) {
            return redirect()->intended('/');
        }

        return view('authmod::two-factor.recovery');
    }

    /**
     * POST /two-factor/recovery — Verify a recovery code.
     */
    public function verifyRecovery(Request $request)
    {
        $request->validate([
            'recovery_code' => ['required', 'string'],
        ]);

        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        $recoveryCodes = $user->two_factor_recovery_codes ?? [];
        $inputCode     = trim($request->input('recovery_code'));

        if (!in_array($inputCode, $recoveryCodes, true)) {
            return back()->withErrors(['recovery_code' => 'Le code de recuperation est invalide.']);
        }

        // Remove the used recovery code
        $remaining = array_values(array_filter($recoveryCodes, fn ($c) => $c !== $inputCode));

        $user->forceFill([
            'two_factor_recovery_codes' => $remaining,
        ])->save();

        session(['two_factor_verified' => true]);

        return redirect()->intended('/');
    }
}
