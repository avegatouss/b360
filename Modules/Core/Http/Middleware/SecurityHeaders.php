<?php

namespace Modules\Core\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Apply configured security headers
        foreach (config('security.headers', []) as $header => $value) {
            $response->headers->set($header, $value);
        }

        // HSTS — only if enabled (should only be used with HTTPS)
        if (config('security.hsts.enabled', false)) {
            $maxAge = config('security.hsts.max_age', 31536000);
            $value = "max-age={$maxAge}";

            if (config('security.hsts.include_subdomains', true)) {
                $value .= '; includeSubDomains';
            }

            $response->headers->set('Strict-Transport-Security', $value);
        }

        // Content-Security-Policy — only if enabled
        if (config('security.csp.enabled', false)) {
            $policy = config('security.csp.policy', '');

            if ($policy) {
                $response->headers->set('Content-Security-Policy', $policy);
            }
        }

        return $response;
    }
}
