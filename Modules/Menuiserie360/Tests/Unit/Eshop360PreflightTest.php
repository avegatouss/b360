<?php

declare(strict_types=1);

namespace Modules\Menuiserie360\Tests\Unit;

use Illuminate\Container\Container;
use Modules\Eshop360\Contracts\Catalog\CatalogReader;
use Modules\Eshop360\Contracts\Customer\CustomerReader;
use Modules\Eshop360\Contracts\Pricing\PricingResolver;
use Modules\Menuiserie360\Providers\Menuiserie360ServiceProvider;
use PHPUnit\Framework\TestCase;

/**
 * R-403 — Verrouille le contrat de preflight check Eshop360 dans
 * Menuiserie360ServiceProvider. Le ServiceProvider n'est pas instancié ici
 * (mergeConfigFrom requiert une Application Laravel) — on teste uniquement
 * la logique pure exposée via les helpers statiques.
 *
 * Régression cible : si Eshop360 est désactivé alors que Menuiserie360 est
 * actif, l'exécution doit échouer rapidement et explicitement plutôt que
 * lever un BindingResolutionException opaque au moment où l'utilisateur
 * ouvre un écran client.
 */
final class Eshop360PreflightTest extends TestCase
{
    public function test_missing_contracts_listed_when_container_empty(): void
    {
        $container = new Container;

        $missing = Menuiserie360ServiceProvider::missingEshop360Contracts($container);

        $this->assertSame(
            [
                CustomerReader::class,
                CatalogReader::class,
                PricingResolver::class,
            ],
            $missing,
            'Tous les contracts Eshop360 attendus doivent être listés comme manquants quand le container est vide.'
        );
    }

    public function test_no_missing_when_all_contracts_bound(): void
    {
        $container = new Container;
        foreach (Menuiserie360ServiceProvider::eshop360RequiredContracts() as $contract) {
            $container->bind($contract, fn () => new \stdClass);
        }

        $this->assertSame(
            [],
            Menuiserie360ServiceProvider::missingEshop360Contracts($container),
            'Aucun contract ne doit être marqué manquant si tous sont bindés.'
        );
    }

    public function test_partial_binding_detected(): void
    {
        $container = new Container;
        $container->bind(CustomerReader::class, fn () => new \stdClass);

        $missing = Menuiserie360ServiceProvider::missingEshop360Contracts($container);

        $this->assertContains(CatalogReader::class, $missing);
        $this->assertContains(PricingResolver::class, $missing);
        $this->assertNotContains(CustomerReader::class, $missing);
    }

    public function test_warning_message_contains_r403_reference_and_contract_names(): void
    {
        // Anti-régression : si l'opérateur supprime le mot-clé R-403 ou la
        // liste des contrats, le diagnostic en prod devient illisible.
        $message = Menuiserie360ServiceProvider::buildPreflightWarningMessage([
            CustomerReader::class,
        ]);

        $this->assertStringContainsString('R-403', $message);
        $this->assertStringContainsString('Eshop360', $message);
        $this->assertStringContainsString(CustomerReader::class, $message);
    }
}
