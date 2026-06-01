<?php

namespace Modules\Auth\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Hash;

final class LockscreenController extends Controller
{
    /**
     * POST /lockscreen/lock — lock the screen.
     */
    public function lock(Request $request)
    {
        $request->session()->put('screen_locked', true);
        $request->session()->put('locked_at', now());

        return redirect()->route('lockscreen');
    }

    /**
     * GET /lockscreen — show lockscreen.
     */
    public function show(Request $request)
    {
        if (! $request->session()->get('screen_locked')) {
            return redirect('/');
        }

        return view('authmod::lockscreen.show', [
            'user' => $request->user(),
        ]);
    }

    /**
     * POST /lockscreen/unlock — verify password and unlock.
     */
    public function unlock(Request $request)
    {
        $request->validate(['password' => 'required|string']);

        if (! Hash::check($request->password, $request->user()->password)) {
            return back()->withErrors(['password' => 'Mot de passe incorrect.']);
        }

        $request->session()->forget('screen_locked');
        $request->session()->forget('locked_at');

        return redirect()->intended('/');
    }
}
