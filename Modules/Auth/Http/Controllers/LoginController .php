<?php

namespace Modules\Auth\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\Core\Support\CurrentInstance;

final class LoginController extends Controller
{
    public function show()
    {
        return view('authmod::login');
    }

    public function login(Request $request)
    {
        $instance = CurrentInstance::get();
        if (!$instance) abort(503, 'Instance context not resolved.');

        $credentials = $request->validate([
            'email' => ['required','email'],
            'password' => ['required','string'],
        ]);

        if (!Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors(['email' => 'Invalid credentials'])->onlyInput('email');
        }

        $request->session()->regenerate();

        $user = $request->user();

        // If super-admin => allowed (Gate::before will apply; membership middleware also bypasses)
        if (method_exists($user, 'hasRole') && $user->hasRole('super-admin')) {
            return redirect()->to('/');
        }

        // Fail-closed: require ACTIVE membership for current instance
        $status = DB::connection('system')
            ->table('instance_user')
            ->where('instance_id', $instance->id)
            ->where('user_id', $user->id)
            ->value('status');

        if ($status !== 'active') {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            abort(403, 'Membership not active for this instance.');
        }

        return redirect()->to('/');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->to('/login');
    }
}
