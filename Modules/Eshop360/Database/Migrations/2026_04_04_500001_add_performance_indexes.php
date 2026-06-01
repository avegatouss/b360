<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Add performance indexes to critical Eshop360 tables.
 * These indexes optimize the most common query patterns:
 * - Stock lookups by product+warehouse
 * - Order listings by instance+status
 * - Channel product price lookups
 * - Customer order history
 */
return new class extends Migration
{
    public function up(): void
    {
        // Stock: lookup by product+warehouse (StockService::adjustStock)
        if (Schema::hasTable('eshop_stocks')) {
            Schema::table('eshop_stocks', function (Blueprint $table) {
                if (!$this->hasIndex('eshop_stocks', 'idx_stocks_product_warehouse')) {
                    $table->index(['product_id', 'warehouse_id'], 'idx_stocks_product_warehouse');
                }
            });
        }

        // Orders: listing by instance+status (ReportService, OrderController)
        if (Schema::hasTable('eshop_orders')) {
            Schema::table('eshop_orders', function (Blueprint $table) {
                if (!$this->hasIndex('eshop_orders', 'idx_orders_instance_status')) {
                    $table->index(['instance_id', 'status'], 'idx_orders_instance_status');
                }
                if (!$this->hasIndex('eshop_orders', 'idx_orders_customer')) {
                    $table->index(['customer_id', 'created_at'], 'idx_orders_customer');
                }
            });
        }

        // Channel product prices: lookup by channel+product (ProductPricingService)
        if (Schema::hasTable('eshop_channel_product_prices')) {
            Schema::table('eshop_channel_product_prices', function (Blueprint $table) {
                if (!$this->hasIndex('eshop_channel_product_prices', 'idx_channel_prices_lookup')) {
                    $table->index(['channel_id', 'product_id'], 'idx_channel_prices_lookup');
                }
            });
        }

        // Products: active products by instance (catalog listing)
        if (Schema::hasTable('eshop_products')) {
            Schema::table('eshop_products', function (Blueprint $table) {
                if (!$this->hasIndex('eshop_products', 'idx_products_instance_active')) {
                    $table->index(['instance_id', 'is_active', 'deleted_at'], 'idx_products_instance_active');
                }
            });
        }
    }

    public function down(): void
    {
        $indexes = [
            'eshop_stocks' => 'idx_stocks_product_warehouse',
            'eshop_orders' => ['idx_orders_instance_status', 'idx_orders_customer'],
            'eshop_channel_product_prices' => 'idx_channel_prices_lookup',
            'eshop_products' => 'idx_products_instance_active',
        ];

        foreach ($indexes as $table => $names) {
            if (Schema::hasTable($table)) {
                Schema::table($table, function (Blueprint $t) use ($names) {
                    foreach ((array) $names as $name) {
                        try { $t->dropIndex($name); } catch (\Throwable) {}
                    }
                });
            }
        }
    }

    private function hasIndex(string $table, string $indexName): bool
    {
        try {
            $indexes = Schema::getIndexes($table);
            foreach ($indexes as $index) {
                if ($index['name'] === $indexName) {
                    return true;
                }
            }
        } catch (\Throwable) {}
        return false;
    }
};
