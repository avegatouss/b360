<?php

declare(strict_types=1);

namespace Modules\Core\Tests\Feature;

use Modules\Core\Hooks\DTO\PostEnableRedirect;
use Modules\Core\Hooks\Registry\HookRegistry;
use PHPUnit\Framework\TestCase;

/**
 * R-401-FIX S1 / ADR-022 — Verrouille l'API post_enable_redirects.
 *
 * ModuleController::toggle utilisera ces hooks pour décider où rediriger
 * après l'activation d'un module (wizard de setup le cas échéant), à la
 * place de la condition hardcodée `if ($name === 'Eshop360')`.
 */
final class HookRegistryPostEnableRedirectsTest extends TestCase
{
    public function test_add_and_retrieve_by_module_name(): void
    {
        $registry = new HookRegistry;
        $registry->addPostEnableRedirect(new PostEnableRedirect(
            moduleName: 'Eshop360',
            route: 'eshop360.setup.hub',
        ));

        $hit = $registry->postEnableRedirect('Eshop360');

        $this->assertNotNull($hit);
        $this->assertSame('Eshop360', $hit->moduleName);
        $this->assertSame('eshop360.setup.hub', $hit->route);
    }

    public function test_retrieve_unknown_module_returns_null(): void
    {
        $registry = new HookRegistry;
        $this->assertNull($registry->postEnableRedirect('Inexistant'));
    }

    public function test_post_enable_redirects_collection_returns_all(): void
    {
        $registry = new HookRegistry;
        $registry->addPostEnableRedirect(new PostEnableRedirect(
            moduleName: 'Eshop360',
            route: 'eshop360.setup.hub',
        ));
        $registry->addPostEnableRedirect(new PostEnableRedirect(
            moduleName: 'Menuiserie360',
            route: 'menuiserie.setup.wizard',
        ));

        $all = $registry->postEnableRedirects();
        $this->assertSame(2, $all->count());

        $names = $all->pluck('moduleName')->all();
        $this->assertContains('Eshop360', $names);
        $this->assertContains('Menuiserie360', $names);
    }
}
