<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Ajout de channel_id sur TOUTES les tables Eshop360 pour isolation complète par channel.
 *
 * - channel_id nullable : les enregistrements existants restent NULL (hub-level)
 * - FK vers eshop_distribution_channels avec nullOnDelete
 * - Index pour performance des requêtes filtrées
 */
return new class extends Migration
{
    /**
     * Tables qui ont BESOIN de channel_id.
     * (Celles qui l'ont déjà sont exclues : orders, customers, cash_registers,
     *  holdings, coupons, carts, module_settings, online_orders)
     */
    private array $tables = [
        // Catalogue
        'eshop_brands',
        'eshop_categories',
        'eshop_products',
        'eshop_product_variations',
        'eshop_product_taxes',
        'eshop_product_groups',
        'eshop_taxes',

        // Inventaire
        'eshop_warehouses',
        'eshop_stores',
        'eshop_stocks',
        'eshop_stock_movements',
        'eshop_stock_transfers',
        'eshop_stock_transfer_items',

        // Achats / Import
        'eshop_suppliers',
        'eshop_purchase_orders',
        'eshop_purchase_items',
        'eshop_purchase_returns',
        'eshop_purchase_return_items',
        'eshop_import_orders',
        'eshop_import_order_items',
        'eshop_import_costs',
        'eshop_import_cost_types',

        // Finance
        'eshop_accounts',
        'eshop_account_transactions',
        'eshop_account_transfers',
        'eshop_expenses',
        'eshop_expense_categories',
        'eshop_incomes',
        'eshop_income_sources',
        'eshop_loans',
        'eshop_loan_payments',
        'eshop_loan_schedules',
        'eshop_gift_cards',
        'eshop_gift_card_topups',
        'eshop_installment_plans',
        'eshop_installment_payments',
        'eshop_company_charges',
        'eshop_charge_logs',
        'eshop_charge_categories',

        // RH
        'eshop_employees',
        'eshop_employee_salaries',
        'eshop_employee_commissions',
        'eshop_attendance',

        // Ventes / Facturation
        'eshop_invoices',
        'eshop_invoice_items',
        'eshop_order_items',
        'eshop_online_order_items',
        'eshop_payments',
        'eshop_payment_methods',
        'eshop_discounts',
        'eshop_discount_plans',
        'eshop_quotations',
        'eshop_quotation_items',
        'eshop_sale_returns',
        'eshop_recurring_invoices',
        'eshop_fne_invoices',

        // Clients
        'eshop_customer_groups',
        'eshop_customer_transactions',
        'eshop_customer_dues',

        // Projets
        'eshop_projects',
        'eshop_tasks',
        'eshop_task_comments',
        'eshop_events',

        // Communication
        'eshop_email_templates',
        'eshop_sms_gateways',
        'eshop_sms_logs',
        'eshop_bulk_message_logs',
        'eshop_support_tickets',
        'eshop_ticket_messages',
        'eshop_messages',
        'eshop_support_teams',

        // Système / Config
        'eshop_receipt_templates',
        'eshop_payment_gateways',
        'eshop_audit_logs',
        'eshop_api_logs',
        'eshop_webhooks',
        'eshop_webhook_logs',
        'eshop_user_assignments',
    ];

    public function up(): void
    {
        foreach ($this->tables as $table) {
            if (Schema::hasTable($table) && ! Schema::hasColumn($table, 'channel_id')) {
                Schema::table($table, function (Blueprint $blueprint) {
                    $blueprint->foreignId('channel_id')
                        ->nullable()
                        ->after('instance_id')
                        ->constrained('eshop_distribution_channels')
                        ->nullOnDelete();

                    $blueprint->index('channel_id');
                });
            }
        }
    }

    public function down(): void
    {
        foreach (array_reverse($this->tables) as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'channel_id')) {
                Schema::table($table, function (Blueprint $blueprint) {
                    $blueprint->dropConstrainedForeignId('channel_id');
                });
            }
        }
    }
};
