<?php

namespace Modules\Auth\Http\Controllers;

use App\Instances\Instance;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\Auth\Services\LoginRedirector;
use Modules\Core\Support\CurrentInstance;

final class LoginController extends Controller
{
    public function __construct(private readonly LoginRedirector $redirector) {}

    /*
    |--------------------------------------------------------------------------
    | Global login (entrypoint sans instance résolue)
    |--------------------------------------------------------------------------
    */

    public function showGlobal()
    {
        if (Auth::check()) {
            return $this->redirector->redirectAfterGlobalLogin(Auth::user());
        }

        return view('authmod::login', [
            'mode'     => 'global',
            'instance' => null,
        ]);
    }

    public function loginGlobal(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (!Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()
                ->withErrors(['email' => 'Identifiants invalides.'])
                ->onlyInput('email');
        }

        $request->session()->regenerate();

        return $this->redirector->redirectAfterGlobalLogin($request->user());
    }

    /*
    |--------------------------------------------------------------------------
    | Login scopé à une instance (canonique)
    |--------------------------------------------------------------------------
    */

    public function showInstance(string $slug)
    {
        $instance = $this->resolveActiveInstance($slug);

        if (!$instance) {
            abort(404, 'Instance introuvable ou inactive.');
        }

        if (Auth::check()) {
            return $this->redirector->redirectAfterInstanceLogin(Auth::user(), $instance);
        }

        return view('authmod::login', [
            'mode'     => 'instance',
            'instance' => $instance,
        ]);
    }

    public function loginInstance(Request $request, string $slug)
    {
        $instance = $this->resolveActiveInstance($slug);

        if (!$instance) {
            abort(404, 'Instance introuvable ou inactive.');
        }

        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (!Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()
                ->withErrors(['email' => 'Identifiants invalides.'])
                ->onlyInput('email');
        }

        $request->session()->regenerate();

        $user = $request->user();

        // Super-admin → bypass membership check (Gate::before s'appliquera sur les ressources)
        if (method_exists($user, 'hasRole') && $user->hasRole('super-admin')) {
            return $this->redirector->redirectAfterInstanceLogin($user, $instance);
        }

        // Fail-closed : vérifier membership actif pour cette instance
        $status = DB::connection('system')
            ->table('instance_user')
            ->where('instance_id', $instance->id)
            ->where('user_id', $user->id)
            ->value('status');

        if ($status !== 'active') {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()->withErrors([
                'email' => 'Votre compte n\'est pas un membre actif de cette instance.',
            ]);
        }

        return $this->redirector->redirectAfterInstanceLogin($user, $instance);
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    private function resolveActiveInstance(string $slug): ?Instance
    {
        return Instance::query()
            ->where('slug', $slug)
            ->where('is_active', true)
            ->first();
    }
}
