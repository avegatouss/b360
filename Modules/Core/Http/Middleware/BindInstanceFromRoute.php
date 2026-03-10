<?php

namespace Modules\Core\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Instances\Instance;
use Modules\Core\Support\CurrentInstance;

/**
 * Résout l'instance depuis le paramètre {slug} de la route
 * et la lie dans le container via CurrentInstance::set().
 *
 * Doit être exécuté AVANT core.instance.resolved,
 * core.spatie.team et core.instance.member.
 */
final class BindInstanceFromRoute
{
    public function handle(Request $request, Closure $next)
    {
        $slug = $request->route('slug');

        if (!$slug) {
            Log::warning('BindInstanceFromRoute: paramètre slug manquant', [
                'url' => $request->fullUrl(),
            ]);
            abort(404, 'Paramètre slug manquant dans la route.');
        }

        $instance = Instance::query()
            ->where('slug', $slug)
            ->where('is_active', true)
            ->first();

        if (!$instance) {
            Log::warning('BindInstanceFromRoute: instance introuvable ou inactive', [
                'slug' => $slug,
                'url'  => $request->fullUrl(),
            ]);
            abort(404, "Instance « {$slug} » introuvable ou inactive.");
        }

        CurrentInstance::set($instance);

        return $next($request);
    }
}
