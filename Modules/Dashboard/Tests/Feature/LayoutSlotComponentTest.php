<?php

declare(strict_types=1);

namespace Modules\Dashboard\Tests\Feature;

use Illuminate\Support\Facades\Blade;
use Modules\Core\Hooks\DTO\LayoutSlotContribution;
use Modules\Core\Hooks\Registry\HookRegistry;
use Modules\Dashboard\Tests\TestCase;

/**
 * R-401-FIX S2 / ADR-022 — Verrouille le rendu du composant
 * <x-dashboard::layout-slot>.
 *
 * Le master layout master.blade.php utilisera ce composant (S4) à la
 * place des `route('eshop360.*')` en dur — il doit donc :
 *  - rendre 0 HTML si aucune contribution n'est active (cas Eshop360 OFF),
 *  - rendre toutes les contributions du slot demandé,
 *  - filtrer par requiredModule via HookFilter (déjà testé par MenuVisibility
 *    R-402 mais re-vérifié ici dans le contexte slot).
 */
final class LayoutSlotComponentTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Vue test minimaliste pour les contributions (évite de dépendre
        // de vues Eshop360 ou autres modules dans ce test unitaire).
        Blade::component('dashboard::layout-slot', \Modules\Dashboard\View\Components\LayoutSlot::class);
    }

    public function test_renders_nothing_when_no_contribution_registered(): void
    {
        // Slot inexistant → fragment vide.
        $html = Blade::render('<x-dashboard::layout-slot name="ghost.slot" :instance="null" />');

        $this->assertSame('', trim($html));
    }

    public function test_renders_contributions_registered_for_the_slot(): void
    {
        // Crée une vue inline temporaire référencée par la contribution.
        $tmpView = storage_path('framework/views/_layout_slot_test_'.uniqid().'.blade.php');
        file_put_contents($tmpView, '<span class="test-slot-output">{{ $custom ?? "default" }}</span>');

        /** @var HookRegistry $registry */
        $registry = app(HookRegistry::class);

        try {
            $registry->addLayoutSlot(new LayoutSlotContribution(
                id: 'test.slot-contribution',
                slot: 'test.slot',
                view: $this->registerInlineView($tmpView, 'test.layout-slot.inline'),
                params: ['custom' => 'hello-world'],
            ));

            $html = Blade::render('<x-dashboard::layout-slot name="test.slot" :instance="null" />');

            $this->assertStringContainsString('class="test-slot-output"', $html);
            $this->assertStringContainsString('hello-world', $html);
        } finally {
            @unlink($tmpView);
            $registry->remove('layout_slots', 'test.slot-contribution');
        }
    }

    /**
     * Enregistre la vue inline dans le namespace `_test`. Renvoie le nom
     * complet à utiliser dans la contribution.
     */
    private function registerInlineView(string $absolutePath, string $viewName): string
    {
        // Le plus simple : créer dans le dir Laravel views temporary path
        // et déclarer un namespace `_test` pointant vers ce dir.
        $dir = dirname($absolutePath);
        $filename = basename($absolutePath, '.blade.php');

        view()->addNamespace('_test', $dir);

        return "_test::{$filename}";
    }
}
