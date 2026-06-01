<?php

namespace Modules\Eshop360\Tests\Feature;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\Eshop360\Tests\TestCase;

/**
 * R-001 — Garantie SGBD de non-négativité du stock.
 *
 * La migration 2026_04_04_100001_add_stock_quantity_check_constraint ajoute
 * une contrainte CHECK (quantity >= 0) sur `eshop_stocks` en MySQL / PostgreSQL.
 *
 * SQLite ne supporte pas ALTER TABLE ADD CONSTRAINT : la migration est
 * volontairement no-op sur ce driver. En prod (MySQL), cette contrainte
 * forme la dernière ligne de défense si le lock applicatif échoue.
 */
final class StockCheckConstraintTest extends TestCase
{
    /**
     * Test "documentaire" : lisible sur tout driver, exécuté sur MySQL.
     * Sur SQLite, on valide que la migration existante contient bien la
     * branche no-op et le CHECK SQL en littéral (verrouillage architectural).
     */
    public function test_check_constraint_migration_exists_with_correct_sql(): void
    {
        $migration = base_path(
            'Modules/Eshop360/Database/Migrations/2026_04_04_100001_add_stock_quantity_check_constraint.php'
        );

        $this->assertFileExists($migration, 'Migration CHECK constraint présente.');

        $source = (string) file_get_contents($migration);

        $this->assertStringContainsString(
            'CHECK (quantity >= 0)',
            $source,
            'La migration doit poser le CHECK quantity >= 0.'
        );
        $this->assertStringContainsString(
            'chk_quantity_non_negative',
            $source,
            'Le nom de la contrainte doit être stable pour pouvoir la retrouver.'
        );
        $this->assertStringContainsString(
            "'sqlite'",
            $source,
            'La migration doit détecter le driver SQLite pour rester no-op en tests.'
        );
    }

    /**
     * Test actif uniquement sur MySQL : vérifie que la contrainte CHECK
     * est effectivement présente dans information_schema. Skippé en SQLite.
     */
    public function test_database_enforces_non_negative_stock_on_mysql(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver !== 'mysql') {
            $this->markTestSkipped(
                "Contrainte CHECK SQL non vérifiable sur driver [{$driver}] — "
                .'voir migration 2026_04_04_100001 et ADR-002 pour le design.'
            );
        }

        $databaseName = DB::connection()->getDatabaseName();

        $constraint = DB::selectOne(
            'SELECT CONSTRAINT_NAME, CHECK_CLAUSE
             FROM information_schema.CHECK_CONSTRAINTS
             WHERE CONSTRAINT_SCHEMA = ?
               AND CONSTRAINT_NAME = ?',
            [$databaseName, 'chk_quantity_non_negative']
        );

        $this->assertNotNull(
            $constraint,
            'La contrainte chk_quantity_non_negative doit exister en MySQL.'
        );

        $this->assertMatchesRegularExpression(
            '/quantity\s*>=\s*0/',
            (string) ($constraint->CHECK_CLAUSE ?? ''),
            'La contrainte doit imposer quantity >= 0.'
        );
    }
}
