<?php

declare(strict_types=1);

namespace Modules\Referentiel360\Tests\Unit\Architecture;

use PHPUnit\Framework\TestCase;

/**
 * ADR-030 contrainte #1 — Referentiel360 (L2) ne référence AUCUN module L3.
 *
 * Scanne tout `Modules/Referentiel360` (hors Tests/Support) pour :
 *   - `use Modules\Eshop360\*` / `use Modules\Menuiserie360\*`
 *   - `DB::table('eshop_*'|'mnu_*')`
 * → 0 occurrence attendue.
 */
final class NoBusinessModuleImportTest extends TestCase
{
    public function test_no_l3_module_imports_or_raw_business_tables(): void
    {
        $root = dirname(__DIR__, 3); // Modules/Referentiel360
        $offenders = [];

        $forbiddenPatterns = [
            'use Modules\\Eshop360\\',
            'use Modules\\Menuiserie360\\',
        ];
        $rawTableRegex = "/DB::table\\(\\s*['\"](eshop_|mnu_)/i";

        /** @var \SplFileInfo $file */
        foreach ($this->phpFiles($root) as $file) {
            $path = $file->getPathname();

            // On exclut le présent test et le dossier Tests (assertions, fixtures).
            if (str_contains($path, DIRECTORY_SEPARATOR.'Tests'.DIRECTORY_SEPARATOR)) {
                continue;
            }

            $contents = (string) file_get_contents($path);

            foreach ($forbiddenPatterns as $needle) {
                if (str_contains($contents, $needle)) {
                    $offenders[] = "{$path} :: {$needle}";
                }
            }

            if (preg_match($rawTableRegex, $contents) === 1) {
                $offenders[] = "{$path} :: DB::table('eshop_*'|'mnu_*')";
            }
        }

        $this->assertSame(
            [],
            $offenders,
            "Violation de couche L2→L3 détectée :\n".implode("\n", $offenders)
        );
    }

    /**
     * @return iterable<int, \SplFileInfo>
     */
    private function phpFiles(string $root): iterable
    {
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($root, \FilesystemIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file instanceof \SplFileInfo && $file->isFile() && $file->getExtension() === 'php') {
                yield $file;
            }
        }
    }
}
