<?php

namespace Modules\Auth\Http\Controllers;

use App\Instances\Instance;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

final class LogoutController extends Controller
{
    public function __invoke(Request $request, string $slug)
    {
        $instance = Instance::where('slug', $slug)->first();
        if (!$instance) {
            abort(404);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->to('/i/' . $slug . '/login');
    }
}
