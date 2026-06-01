<?php

namespace Modules\Eshop360\Tests\Unit;

use Illuminate\Database\Eloquent\Relations\Relation;
use Modules\Eshop360\Tests\TestCase;

/**
 * R-101 S12 closure guard.
 *
 * Structural assertions that lock in the final architecture once the 88 alias
 * stubs are deleted. If any of these fails, the closure invariants have been
 * broken (someone re-introduced a stub, removed an entry from the morph map,
 * or moved a model out of its canonical Domain folder).
 */
final class R101ClosureStructuralTest extends TestCase
{
    public function test_modules_eshop360_models_directory_only_holds_retained_non_stubs(): void
    {
        $modelsDir = base_path('Modules/Eshop360/Models');
        $this->assertDirectoryExists($modelsDir);

        $files = collect(scandir($modelsDir))
            ->filter(fn ($f) => str_ends_with($f, '.php'))
            ->values()
            ->all();

        sort($files);

        $this->assertSame(
            ['EshopModuleSetting.php', 'UserAssignment.php'],
            $files,
            'Modules/Eshop360/Models/ must hold only the 2 retained non-stubs after R-101 S12. '
            .'Anything else means a stub was re-introduced.'
        );
    }

    public function test_morph_map_contains_exactly_88_legacy_fqn_entries(): void
    {
        $map = Relation::morphMap();

        $eshopEntries = array_filter(
            $map,
            fn ($key) => str_starts_with($key, 'Modules\Eshop360\Models\\'),
            ARRAY_FILTER_USE_KEY
        );

        $this->assertCount(
            88,
            $eshopEntries,
            'Eshop360ServiceProvider must register exactly 88 legacy FQN -> canonical class entries.'
        );

        foreach ($eshopEntries as $key => $class) {
            $this->assertStringStartsWith(
                'Modules\Eshop360\Domain\\',
                $class,
                "Morph map value for {$key} must point to a canonical Domain class, got {$class}"
            );
            $this->assertTrue(
                class_exists($class),
                "Canonical class {$class} (mapped from {$key}) must exist"
            );
        }
    }

    public function test_no_canonical_domain_model_pins_morph_class(): void
    {
        $domainDir = base_path('Modules/Eshop360/Domain');
        $this->assertDirectoryExists($domainDir);

        $offenders = [];
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($domainDir, \RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if (! $file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }
            if (! str_contains($file->getPathname(), DIRECTORY_SEPARATOR.'Models'.DIRECTORY_SEPARATOR)) {
                continue;
            }

            $contents = file_get_contents($file->getPathname());
            if (preg_match('/protected\s+\$morphClass\b/', $contents) === 1) {
                $offenders[] = $file->getPathname();
            }
        }

        $this->assertSame(
            [],
            $offenders,
            'Canonical Domain models must NOT define `protected $morphClass`. '
            .'The morph map in Eshop360ServiceProvider is the single source of truth. '
            .'Offenders: '.implode(', ', $offenders)
        );
    }
}
