<?php

declare(strict_types=1);

namespace Modules\Menuiserie360\Tests\Unit;

use InvalidArgumentException;
use Modules\Menuiserie360\Domain\Commercial\DTOs\DevisLineInputDto;
use Modules\Menuiserie360\Domain\Commercial\Exceptions\MargeInsuffisanteException;
use Modules\Menuiserie360\Domain\Commercial\Services\DevisCalculatorService;
use PHPUnit\Framework\TestCase;

/**
 * P1-8 — Tests unitaires DevisCalculatorService.
 *
 * Service PUR (sans I/O) → tests purs PHPUnit, pas besoin d'extension
 * Laravel TestCase. 24 cas couvrant :
 *   - Calcul ligne (qty, prix, remise, plancher 0)
 *   - Calcul global (HT, TVA 18% CI, TTC, remise globale plafonnée)
 *   - Marge brute + fraction
 *   - Enforcement marge minimum (succès / lève exception)
 *   - Helpers dimensionnels (surfaceM2, perimetreLineaire)
 *   - Helper suggestPriceFromMatiere
 *   - Guards (quantité, prix, coût, remise, taux TVA, marge)
 *   - Précision arrondie 2 décimales
 */
final class DevisCalculatorServiceTest extends TestCase
{
    private DevisCalculatorService $calculator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->calculator = new DevisCalculatorService;
    }

    private function line(
        float $prix,
        int $qty = 1,
        float $cout = 0.0,
        float $remise = 0.0,
        ?int $largeur = null,
        ?int $hauteur = null,
    ): DevisLineInputDto {
        return new DevisLineInputDto(
            designation: 'Test',
            quantite: $qty,
            prixUnitaireHt: $prix,
            coutRevientUnitaire: $cout,
            remiseLigne: $remise,
            largeurMm: $largeur,
            hauteurMm: $hauteur,
        );
    }

    // ─── Calcul ligne ───────────────────────────────────────────

    public function test_calculate_simple_one_line_no_remise(): void
    {
        $result = $this->calculator->calculate([$this->line(prix: 100, qty: 2)]);

        $this->assertSame(200.0, $result->montantHt);
        $this->assertSame(36.0, $result->montantTva);     // 18% de 200
        $this->assertSame(236.0, $result->montantTtc);
    }

    public function test_calculate_applies_line_discount(): void
    {
        // 100 x 5 = 500, remise 50 = 450
        $result = $this->calculator->calculate([$this->line(prix: 100, qty: 5, remise: 50)]);

        $this->assertSame(450.0, $result->montantHt);
        $this->assertSame(81.0, $result->montantTva);
        $this->assertSame(531.0, $result->montantTtc);
    }

    public function test_calculate_floors_line_amount_at_zero_when_discount_exceeds(): void
    {
        // 100 x 1 = 100, remise 200 → ligne = 0 (pas négatif)
        $result = $this->calculator->calculate([$this->line(prix: 100, qty: 1, remise: 200)]);

        $this->assertSame(0.0, $result->montantHt);
        $this->assertSame(0.0, $result->montantTva);
        $this->assertSame(0.0, $result->montantTtc);
    }

    public function test_calculate_aggregates_multiple_lines(): void
    {
        $result = $this->calculator->calculate([
            $this->line(prix: 100, qty: 2),  // 200
            $this->line(prix: 50, qty: 3),   // 150
            $this->line(prix: 25, qty: 4),   // 100
        ]);

        $this->assertSame(450.0, $result->montantHt);
        $this->assertSame(81.0, $result->montantTva);
        $this->assertSame(531.0, $result->montantTtc);
    }

    public function test_calculate_returns_per_line_snapshots(): void
    {
        $result = $this->calculator->calculate([
            $this->line(prix: 100, qty: 2),
            $this->line(prix: 50, qty: 3, remise: 10),
        ]);

        $this->assertCount(2, $result->lines);
        $this->assertSame(200.0, $result->lines[0]->montantHt);
        $this->assertSame(140.0, $result->lines[1]->montantHt);  // 50*3 - 10
    }

    // ─── Remise globale ─────────────────────────────────────────

    public function test_calculate_applies_global_discount(): void
    {
        $result = $this->calculator->calculate(
            [$this->line(prix: 100, qty: 10)],   // 1000 HT brut
            remiseGlobale: 100,
        );

        $this->assertSame(900.0, $result->montantHt);
        $this->assertSame(162.0, $result->montantTva);   // 18% de 900
        $this->assertSame(1062.0, $result->montantTtc);
        $this->assertSame(100.0, $result->remiseGlobale);
    }

    public function test_calculate_caps_global_discount_at_brut_total(): void
    {
        // HT brut 100, remise demandée 200 → effective = 100, HT net = 0
        $result = $this->calculator->calculate(
            [$this->line(prix: 100, qty: 1)],
            remiseGlobale: 200,
        );

        $this->assertSame(0.0, $result->montantHt);
        $this->assertSame(100.0, $result->remiseGlobale);  // cappée
    }

    // ─── Taux TVA ───────────────────────────────────────────────

    public function test_calculate_uses_default_tva_18_pct_ci(): void
    {
        $result = $this->calculator->calculate([$this->line(prix: 1000, qty: 1)]);

        $this->assertSame(0.18, $result->tauxTva);
        $this->assertSame(180.0, $result->montantTva);
        $this->assertSame(1180.0, $result->montantTtc);
    }

    public function test_calculate_accepts_custom_tva_rate(): void
    {
        $result = $this->calculator->calculate(
            [$this->line(prix: 1000, qty: 1)],
            tauxTva: 0.20,
        );

        $this->assertSame(0.20, $result->tauxTva);
        $this->assertSame(200.0, $result->montantTva);
        $this->assertSame(1200.0, $result->montantTtc);
    }

    public function test_calculate_zero_tva_for_export_or_exoneration(): void
    {
        $result = $this->calculator->calculate(
            [$this->line(prix: 500, qty: 2)],
            tauxTva: 0.0,
        );

        $this->assertSame(1000.0, $result->montantHt);
        $this->assertSame(0.0, $result->montantTva);
        $this->assertSame(1000.0, $result->montantTtc);
    }

    // ─── Marge brute + fraction ────────────────────────────────

    public function test_calculate_computes_marge_brute_and_fraction(): void
    {
        // 100 x 1 = 100 HT, coût 60 → marge brute 40, fraction 0.40
        $result = $this->calculator->calculate([$this->line(prix: 100, qty: 1, cout: 60)]);

        $this->assertSame(40.0, $result->margeBrute);
        $this->assertSame(0.40, $result->margeFraction);
    }

    public function test_calculate_marge_zero_when_ht_zero(): void
    {
        $result = $this->calculator->calculate([$this->line(prix: 0, qty: 1)]);

        $this->assertSame(0.0, $result->margeBrute);
        $this->assertSame(0.0, $result->margeFraction);
    }

    public function test_calculate_marge_can_be_negative(): void
    {
        // Vente à perte : 50 x 1 = 50 HT, coût 80 → marge -30
        $result = $this->calculator->calculate([$this->line(prix: 50, qty: 1, cout: 80)]);

        $this->assertSame(-30.0, $result->margeBrute);
        $this->assertSame(-0.6, $result->margeFraction);
    }

    // ─── Enforcement marge minimum ─────────────────────────────

    public function test_calculate_passes_when_marge_at_or_above_minimum(): void
    {
        // Marge 40% (PV 100, coût 60) — seuil 15% → OK
        $result = $this->calculator->calculate(
            [$this->line(prix: 100, qty: 1, cout: 60)],
            margeMinimum: 0.15,
            enforceMarge: true,
        );

        $this->assertSame(0.40, $result->margeFraction);
    }

    public function test_calculate_throws_when_marge_below_minimum_and_enforced(): void
    {
        // Marge 10% (PV 100, coût 90) — seuil 15% → erreur
        $this->expectException(MargeInsuffisanteException::class);
        $this->calculator->calculate(
            [$this->line(prix: 100, qty: 1, cout: 90)],
            margeMinimum: 0.15,
            enforceMarge: true,
        );
    }

    public function test_calculate_does_not_enforce_marge_by_default(): void
    {
        // Marge 5% (PV 100, coût 95), seuil ignoré sans enforce → no throw
        $result = $this->calculator->calculate(
            [$this->line(prix: 100, qty: 1, cout: 95)],
            margeMinimum: 0.20,
        );

        $this->assertSame(0.05, $result->margeFraction);
    }

    // ─── Helpers dimensionnels ─────────────────────────────────

    public function test_surface_m2_from_dimensions_mm(): void
    {
        // 1500 x 1200 mm = 1.5 x 1.2 = 1.8 m²
        $this->assertSame(1.8, $this->calculator->surfaceM2(1500, 1200));
    }

    public function test_surface_m2_returns_null_when_dimension_missing(): void
    {
        $this->assertNull($this->calculator->surfaceM2(null, 1200));
        $this->assertNull($this->calculator->surfaceM2(1500, null));
        $this->assertNull($this->calculator->surfaceM2(null, null));
    }

    public function test_perimetre_lineaire_from_dimensions_mm(): void
    {
        // 1500 x 1200 mm = 2 * (1.5 + 1.2) = 5.4 m
        $this->assertSame(5.4, $this->calculator->perimetreLineaire(1500, 1200));
    }

    // ─── suggestPriceFromMatiere ──────────────────────────────

    public function test_suggest_price_applies_marge_minimum(): void
    {
        // Coût 850, marge 15% → PV = 850 / 0.85 = 1000
        $this->assertSame(1000.0, $this->calculator->suggestPriceFromMatiere(850, 0.15));
    }

    public function test_suggest_price_throws_when_marge_is_100_pct_or_more(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->calculator->suggestPriceFromMatiere(100, 1.0);
    }

    // ─── Guards ────────────────────────────────────────────────

    public function test_calculate_throws_on_zero_quantity(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->calculator->calculate([$this->line(prix: 100, qty: 0)]);
    }

    public function test_calculate_throws_on_negative_price(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->calculator->calculate([$this->line(prix: -10)]);
    }

    public function test_calculate_throws_on_negative_cost(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->calculator->calculate([$this->line(prix: 100, cout: -5)]);
    }

    public function test_calculate_throws_on_negative_global_discount(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->calculator->calculate([$this->line(prix: 100)], remiseGlobale: -10);
    }

    public function test_calculate_throws_on_invalid_tva_rate(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->calculator->calculate([$this->line(prix: 100)], tauxTva: 1.5);
    }

    // ─── Précision arrondie ───────────────────────────────────

    public function test_calculate_rounds_to_2_decimals(): void
    {
        // 100.123 x 3 = 300.369 → arrondi 300.37
        $result = $this->calculator->calculate([$this->line(prix: 100.123, qty: 3)]);

        $this->assertSame(300.37, $result->montantHt);
    }
}
