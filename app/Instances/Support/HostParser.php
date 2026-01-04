<?php

namespace App\Instances\Support;

/**
 * HostParser v1
 *
 * Objectif: extraire un subdomain de manière sûre:
 * - base domain = host de APP_URL (ex: b360.test)
 * - host courant = acme.b360.test => subdomain = acme
 *
 * Si host ne match pas base domain => subdomain null.
 * (Permet ensuite fallback/404)
 */
final class HostParser
{
    public static function baseHost(): ?string
    {
        $host = parse_url(config('app.url'), PHP_URL_HOST);
        return $host ? strtolower($host) : null;
    }

    public static function host(): string
    {
        // On ne dépend pas de Request ici; le middleware passe déjà le host
        return '';
    }

    public static function extractSubdomain(string $currentHost, ?string $baseHost): ?string
    {
        $currentHost = strtolower($currentHost);

        if (!$baseHost) {
            return null;
        }

        $baseHost = strtolower($baseHost);

        // Ex: acme.b360.test doit finir par ".b360.test"
        if ($currentHost === $baseHost) {
            return null; // root
        }

        $suffix = '.' . $baseHost;
        if (!str_ends_with($currentHost, $suffix)) {
            return null;
        }

        $sub = substr($currentHost, 0, -strlen($suffix));
        $sub = trim($sub, '.');

        // v1 : on accepte un seul niveau (acme), pas a.b.c
        // Si tu veux multi-niveaux plus tard, on adaptera.
        if ($sub === '' || str_contains($sub, '.')) {
            return null;
        }

        return $sub;
    }
}
