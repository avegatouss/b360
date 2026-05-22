<?php
namespace Modules\Auth\Http\Controllers;

use App\Instances\Instance;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\Core\Support\TeamContext;

final class LogoutController extends Controller
{
    public function __invoke(Request $request, string $slug)
    {
        $instance = Instance::where('slug', $slug)->firstOrFail();

        // ← AVANT le logout, sinon $user = null
        $user         = Auth::user();
        $isSuperAdmin = $user && TeamContext::isSuperAdmin($user);

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($isSuperAdmin || $instance->isRoot()) {
            return redirect()->route('login');
        }

        return redirect()->to('/i/' . $slug . '/login');
    }
}
