<?php

namespace Modules\Eshop360\Tests\Feature;

use Illuminate\Support\Facades\Schema;
use Modules\Eshop360\Tests\TestCase;

/**
 * R-103 — Verrouille la consolidation Codifarm → DistributionChannel.
 *
 * Contexte : le système historique `eshop_codifarm_margin_config` +
 * `eshop_codifarm_margin_logs` + colonnes `orders.is_codifarm` /
 * `products.sale_price_codifarm` a été migré puis supprimé par la
 * paire de migrations :
 *
 *   - 2026_03_16_100002_migrate_codifarm_to_channels (migre les data)
 *   - 2026_03_16_100003_drop_codifarm_tables_and_columns (drop)
 *
 * Le canon est désormais :
 *   - `eshop_distribution_channels` (config canal + margin_rate +
 *     debt_share + channel_share + owner_share)
 *   - `eshop_channel_margin_logs` (audit margin par ordre, colonnes
 *     total_margin + debt_part + channel_part + owner_part)
 *   - `eshop_channel_product_prices` (pricing per-canal)
 *
 * Ces tests verrouillent la décision :
 *   1. Les tables legacy n'existent plus dans le schéma.
 *   2. Aucun code applicatif actif (Services/Controllers/Models) ne
 *      référence la structure legacy (`codifarm_margin`, `is_codifarm`,
 *      `sale_price_codifarm`).
 *   3. Les seuls résidus « codifarm » dans le code sont des noms
 *      métier (demo channel CODIFARM Sarl = nom de grossiste pharma)
 *      et sont acceptables.
 *
 * Toute PR qui recréerait les tables, ou qui ajouterait une référence
 * à la structure legacy dans un service/controller/model casserait
 * ces tests → R-103 ne peut pas rouvrir par accident.
 *
 * Voir ADR-007-codifarm-channel-consolidation.
 */
final class CodifarmLegacyRemovalTest extends TestCase
{
    /**
     * Test 1 — SCHÉMA : les tables legacy n'existent plus.
     *
     * Garantit que la migration 2026_03_16_100003_drop_codifarm_*
     * a bien été exécutée et ne peut pas être rollback sans casser
     * ce test.
     */
    public function test_legacy_codifarm_tables_do_not_exist(): void
    {
        $this->assertFalse(
            Schema::hasTable('eshop_codifarm_margin_config'),
            'eshop_codifarm_margin_config doit être supprimée (migration 2026_03_16_100003).'
        );

        $this->assertFalse(
            Schema::hasTable('eshop_codifarm_margin_logs'),
            'eshop_codifarm_margin_logs doit être supprimée (migration 2026_03_16_100003).'
        );
    }

    /**
     * Test 2 — SCHÉMA : les colonnes legacy n'existent plus sur
     * `eshop_orders` et `eshop_products`.
     */
    public function test_legacy_codifarm_columns_do_not_exist(): void
    {
        $this->assertTrue(
            Schema::hasTable('eshop_orders'),
            'Table eshop_orders doit exister.'
        );
        $this->assertFalse(
            Schema::hasColumn('eshop_orders', 'is_codifarm'),
            'Colonne eshop_orders.is_codifarm doit être supprimée (remplacée par channel_id).'
        );

        $this->assertTrue(
            Schema::hasTable('eshop_products'),
            'Table eshop_products doit exister.'
        );
        $this->assertFalse(
            Schema::hasColumn('eshop_products', 'sale_price_codifarm'),
            'Colonne eshop_products.sale_price_codifarm doit être supprimée (remplacée par eshop_channel_product_prices).'
        );
    }

    /**
     * Test 3 — CODE : aucune référence active à la structure legacy
     * dans les Services, Controllers, Models d'Eshop360.
     *
     * Les références résiduelles autorisées (noms métier « CODIFARM »
     * pour le demo channel) sont dans Seeders/ et Tests/ seulement.
     * Une PR qui réintroduirait `codifarm_margin_*` ou
     * `is_codifarm` / `sale_price_codifarm` dans le code applicatif
     * casserait ce test.
     */
    public function test_no_active_codifarm_references_in_application_code(): void
    {
        $eshopPath = base_path('Modules/Eshop360');
        $directoriesToCheck = [
            $eshopPath.'/Services',
            $eshopPath.'/Http/Controllers',
            $eshopPath.'/Models',
            $eshopPath.'/Domain',
        ];

        $forbiddenTokens = [
            'codifarm_margin_config',
            'codifarm_margin_log',
            'is_codifarm',
            'sale_price_codifarm',
            'CodifarmMarginConfig',
            'CodifarmMarginLog',
        ];

        $violations = [];

        foreach ($directoriesToCheck as $dir) {
            if (! is_dir($dir)) {
                continue;
            }
            $violations = array_merge(
                $violations,
                $this->findForbiddenTokens($dir, $forbiddenTokens)
            );
        }

        $this->assertEmpty(
            $violations,
            "Références à la structure legacy Codifarm détectées dans le code applicatif (R-103 FERMÉ interdit ces références). Violations :\n - ".implode("\n - ", $violations)
        );
    }

    /**
     * @param  array<int, string>  $tokens
     * @return array<int, string>
     */
    private function findForbiddenTokens(string $directory, array $tokens): array
    {
        $violations = [];
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory, \FilesystemIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if (! $file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }

            $content = (string) file_get_contents($file->getPathname());

            foreach ($tokens as $token) {
                if (str_contains($content, $token)) {
                    $violations[] = $file->getPathname().' → '.$token;
                }
            }
        }

        return $violations;
    }
}
