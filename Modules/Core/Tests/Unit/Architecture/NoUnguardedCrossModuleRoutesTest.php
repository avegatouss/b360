<?php

declare(strict_types=1);

namespace Modules\Core\Tests\Unit\Architecture;

use FilesystemIterator;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

/**
 * Structural guard: platform modules (L0/L1/L2 in MODULE_DEPENDENCY_MAP)
 * MUST NOT reference business-module routes (eshop360.*, menuiserie360.*, …)
 * without a `Route::has(...)` guard somewhere in the same source file.
 *
 * Why: business modules can be disabled at runtime via modules_statuses.json.
 * When a platform module references a business route unconditionally, every
 * page rendered by that platform module crashes with RouteNotFoundException
 * as soon as the business module is off. This happened in 2026-05 when
 * Eshop360 was disabled on the Menuiserie360 dev branch and the Dashboard
 * layout exploded.
 *
 * How to apply: wrap the call with
 *   - PHP:   `if (Route::has('<prefix>.<name>')) { … route('<prefix>.<name>') … }`
 *   - Blade: `@if(Route::has('<prefix>.<name>')) … {{ route(...) }} … @endif`
 *
 * If you need a finer-grained signal (e.g. show a UI section only when a
 * full module is active), pair with `\Nwidart\Modules\Facades\Module::find()`.
 */
final class NoUnguardedCrossModuleRoutesTest extends TestCase
{
    /**
     * Modules considered platform / non-business. They must not depend on
     * any business module without guarding the reference.
     *
     * Demo is included on purpose: it is allowed to KNOW about business
     * modules (it seeds their demo data) but must not CRASH when they are
     * disabled at runtime — same guard rule applies.
     */
    private const PLATFORM_MODULES = [
        'Core',
        'Auth',
        'Users',
        'Settings',
        'Billing',
        'Lang',
        'Currency',
        'Instances',
        'ModuleManager',
        'Installer',
        'Dashboard',
        'Demo',
    ];

    /**
     * Business module route prefixes. Add new business verticals here.
     */
    private const BUSINESS_PREFIXES = [
        'eshop360',
        'menuiserie360',
        'ccc360',
        'treso360',
    ];

    public function test_no_unguarded_cross_module_route_in_platform_modules(): void
    {
        $repoRoot = $this->repoRoot();
        $offenders = [];

        foreach (self::PLATFORM_MODULES as $module) {
            $path = $repoRoot.DIRECTORY_SEPARATOR.'Modules'.DIRECTORY_SEPARATOR.$module;
            if (! is_dir($path)) {
                continue;
            }

            foreach ($this->iterateSourceFiles($path) as $file) {
                foreach ($this->findUnguardedRouteCalls($file) as $hit) {
                    $offenders[] = $hit;
                }
            }
        }

        $this->assertSame(
            [],
            $offenders,
            $this->buildFailureMessage($offenders)
        );
    }

    /**
     * @return iterable<string>
     */
    private function iterateSourceFiles(string $dir): iterable
    {
        $rii = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS)
        );

        /** @var SplFileInfo $info */
        foreach ($rii as $info) {
            if (! $info->isFile()) {
                continue;
            }
            $name = $info->getFilename();
            if (! (str_ends_with($name, '.blade.php') || str_ends_with($name, '.php'))) {
                continue;
            }
            // Skip test files themselves (would self-detect via their own assertions).
            if (str_contains($info->getPathname(), DIRECTORY_SEPARATOR.'Tests'.DIRECTORY_SEPARATOR)) {
                continue;
            }
            yield $info->getPathname();
        }
    }

    /**
     * @return list<array{file: string, line: int, prefix: string, snippet: string}>
     */
    private function findUnguardedRouteCalls(string $file): array
    {
        $content = @file_get_contents($file);
        if ($content === false || $content === '') {
            return [];
        }

        $prefixAlt = implode('|', array_map(
            static fn (string $p): string => preg_quote($p, '/'),
            self::BUSINESS_PREFIXES
        ));
        // Matches: route('eshop360.<anything>') or route("eshop360.<anything>")
        $callPattern = '/route\(\s*[\'"]('.$prefixAlt.')\.[a-zA-Z0-9_.\-]+[\'"]/';

        $lines = preg_split('/\r\n|\r|\n/', $content);
        $offenders = [];

        foreach ($lines as $idx => $line) {
            if (! preg_match_all($callPattern, $line, $matches, PREG_SET_ORDER)) {
                continue;
            }

            foreach ($matches as $m) {
                $prefix = $m[1];
                if ($this->hasGuardFor($content, $prefix)) {
                    continue;
                }
                $offenders[] = [
                    'file' => $this->relativize($file),
                    'line' => $idx + 1,
                    'prefix' => $prefix,
                    'snippet' => trim($line),
                ];
            }
        }

        return $offenders;
    }

    private function hasGuardFor(string $content, string $prefix): bool
    {
        $guard = '/Route::has\(\s*[\'"]'.preg_quote($prefix, '/').'\./';

        return (bool) preg_match($guard, $content);
    }

    /**
     * @param  list<array{file: string, line: int, prefix: string, snippet: string}>  $offenders
     */
    private function buildFailureMessage(array $offenders): string
    {
        if ($offenders === []) {
            return '';
        }
        $lines = [
            'Platform modules must not call route(\'<business>.<name>\') without a Route::has() guard.',
            'Wrap the call with `Route::has(...)` (PHP) or `@if(Route::has(...))` (Blade).',
            'Offenders:',
        ];
        foreach ($offenders as $o) {
            $lines[] = sprintf('  - %s:%d  ->  %s', $o['file'], $o['line'], $o['snippet']);
        }

        return implode("\n", $lines);
    }

    private function relativize(string $absolute): string
    {
        $root = $this->repoRoot();
        if (str_starts_with($absolute, $root.DIRECTORY_SEPARATOR)) {
            return substr($absolute, strlen($root) + 1);
        }

        return $absolute;
    }

    private function repoRoot(): string
    {
        // __DIR__ = Modules/Core/Tests/Unit/Architecture
        return dirname(__DIR__, 5);
    }
}
