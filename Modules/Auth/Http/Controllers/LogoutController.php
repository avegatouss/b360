<?php

namespace Modules\Auth\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\Core\Support\CurrentInstance;

final class LogoutController extends Controller
{
    public function __invoke(Request $request, string $slug)
    {
        $instance = CurrentInstance::get();
        if (!$instance || $instance->slug !== $slug) {
            abort(404);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->to('/i/' . $instance->slug . '/login');
    }
}
