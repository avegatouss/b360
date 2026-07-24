<?php

declare(strict_types=1);

namespace Modules\Couture360\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class CoutureApiAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        return $next($request); // Replaced with real logic in Task 3.
    }
}
