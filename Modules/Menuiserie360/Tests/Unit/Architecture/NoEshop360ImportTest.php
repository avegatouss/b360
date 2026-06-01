<?php

declare(strict_types=1);

namespace Modules\Menuiserie360\Tests\Unit\Architecture;

use Modules\Menuiserie360\Tests\TestCase;

final class NoEshop360ImportTest extends TestCase
{
    public function test_menuiserie360_has_no_eshop360_imports(): void
    {
        $root = realpath(__DIR__.'/../../../');
        $this->assertNotFalse($root);

        $matches = [];
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator((string) $root, \RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if (! $file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }

            $path = (string) $file->getRealPath();
            if ($path === __FILE__) {
                continue;
            }

            $content = (string) file_get_contents($path);
            if (preg_match('/^use Modules\\\\Eshop360\\\\/m', $content) === 1) {
                $matches[] = str_replace((string) $root.DIRECTORY_SEPARATOR, '', $path);
            }
        }

        sort($matches);

        $this->assertSame([], $matches, 'Menuiserie360 doit etre autonome : aucun import Modules\\Eshop360\\*.');
    }
}
