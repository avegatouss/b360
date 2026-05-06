# DB_INDEX — B360

> Auto-généré par `make audit-db`. Mise à jour : **2026-05-06 14:00:03    **

---

## Tables détectées (à partir des migrations)

### Migrations racine (`database/migrations/`)

- `cache_locks`
- `cache`
- `failed_jobs`
- `instances`
- `job_batches`
- `jobs`
- `notifications`
- `password_reset_tokens`
- `personne_morales`
- `personne_physiques`
- `personne_roles`
- `personnes`
- `representants`
- `sessions`
- `users`

### Auth

- `ip_rules` (1 migration(s))
- `login_logs` (1 migration(s))
- `login_logs_new` (1 migration(s))
- `login_logs_old` (1 migration(s))

### Core

- `audit_logs` (1 migration(s))
- `backup_logs` (1 migration(s))
- `cron_logs` (1 migration(s))
- `documentation_pages` (1 migration(s))
- `tour_completions` (1 migration(s))

### Currency

- `currencies` (2 migration(s))
- `exchange_rate_history` (1 migration(s))
- `order_currency_snapshots` (1 migration(s))
- `tenant_currency_settings` (1 migration(s))
- `user_currency_preferences` (1 migration(s))

### Eshop360

- `eshop_account_transactions` (1 migration(s))
- `eshop_account_transfers` (1 migration(s))
- `eshop_accounts` (1 migration(s))
- `eshop_api_logs` (1 migration(s))
- `eshop_attendance` (1 migration(s))
- `eshop_audit_logs` (1 migration(s))
- `eshop_brands` (1 migration(s))
- `eshop_bulk_message_logs` (1 migration(s))
- `eshop_carts` (3 migration(s))
- `eshop_cash_registers` (2 migration(s))
- `eshop_categories` (1 migration(s))
- `eshop_channel_credit_usages` (1 migration(s))
- `eshop_channel_credits` (1 migration(s))
- `eshop_channel_margin_logs` (1 migration(s))
- `eshop_channel_product_prices` (3 migration(s))
- `eshop_channel_users` (1 migration(s))
- `eshop_charge_categories` (1 migration(s))
- `eshop_charge_logs` (1 migration(s))
- `eshop_codifarm_margin_config` (2 migration(s))
- `eshop_codifarm_margin_logs` (2 migration(s))
- `eshop_company_charges` (1 migration(s))
- `eshop_coupons` (2 migration(s))
- `eshop_customer_dues` (2 migration(s))
- `eshop_customer_groups` (1 migration(s))
- `eshop_customer_transactions` (1 migration(s))
- `eshop_customers` (4 migration(s))
- `eshop_discount_plans` (1 migration(s))
- `eshop_discounts` (1 migration(s))
- `eshop_distribution_channels` (3 migration(s))
- `eshop_email_templates` (2 migration(s))
- `eshop_employee_commissions` (2 migration(s))
- `eshop_employee_salaries` (1 migration(s))
- `eshop_employees` (1 migration(s))
- `eshop_events` (1 migration(s))
- `eshop_expense_categories` (1 migration(s))
- `eshop_expenses` (1 migration(s))
- `eshop_fne_invoices` (1 migration(s))
- `eshop_gift_card_topups` (1 migration(s))
- `eshop_gift_cards` (2 migration(s))
- `eshop_holdings` (2 migration(s))
- `eshop_import_cost_types` (1 migration(s))
- `eshop_import_costs` (1 migration(s))
- `eshop_import_order_items` (1 migration(s))
- `eshop_import_orders` (1 migration(s))
- `eshop_income_sources` (1 migration(s))
- `eshop_incomes` (1 migration(s))
- `eshop_installment_payments` (1 migration(s))
- `eshop_installment_plans` (1 migration(s))
- `eshop_invoice_items` (2 migration(s))
- `eshop_invoices` (6 migration(s))
- `eshop_loan_payments` (1 migration(s))
- `eshop_loan_schedules` (1 migration(s))
- `eshop_loans` (2 migration(s))
- `eshop_messages` (2 migration(s))
- `eshop_module_settings` (2 migration(s))
- `eshop_online_order_items` (1 migration(s))
- `eshop_online_orders` (2 migration(s))
- `eshop_order_items` (3 migration(s))
- `eshop_orders` (9 migration(s))
- `eshop_payment_gateways` (1 migration(s))
- `eshop_payment_methods` (1 migration(s))
- `eshop_payments` (3 migration(s))
- `eshop_pricing_rule_configs` (1 migration(s))
- `eshop_pricing_rule_versions` (1 migration(s))
- `eshop_pricing_rules` (1 migration(s))
- `eshop_product_group_items` (1 migration(s))
- `eshop_product_groups` (1 migration(s))
- `eshop_product_taxes` (1 migration(s))
- `eshop_product_variations` (2 migration(s))
- `eshop_products` (8 migration(s))
- `eshop_projects` (1 migration(s))
- `eshop_purchase_items` (2 migration(s))
- `eshop_purchase_orders` (3 migration(s))
- `eshop_purchase_return_items` (1 migration(s))
- `eshop_purchase_returns` (2 migration(s))
- `eshop_quotation_items` (1 migration(s))
- `eshop_quotations` (1 migration(s))
- `eshop_receipt_templates` (1 migration(s))
- `eshop_recurring_invoices` (1 migration(s))
- `eshop_sale_returns` (1 migration(s))
- `eshop_sms_gateways` (1 migration(s))
- `eshop_sms_logs` (1 migration(s))
- `eshop_stock_movements` (1 migration(s))
- `eshop_stock_transfer_items` (1 migration(s))
- `eshop_stock_transfers` (1 migration(s))
- `eshop_stocks` (2 migration(s))
- `eshop_stores` (1 migration(s))
- `eshop_supplier_store` (1 migration(s))
- `eshop_suppliers` (1 migration(s))
- `eshop_support_team_members` (1 migration(s))
- `eshop_support_teams` (1 migration(s))
- `eshop_support_tickets` (1 migration(s))
- `eshop_task_comments` (1 migration(s))
- `eshop_tasks` (1 migration(s))
- `eshop_taxes` (1 migration(s))
- `eshop_ticket_messages` (1 migration(s))
- `eshop_user_assignments` (1 migration(s))
- `eshop_warehouses` (2 migration(s))
- `eshop_webhook_logs` (3 migration(s))
- `eshop_webhooks` (1 migration(s))
- `tenant_feature_overrides` (1 migration(s))

### Lang

- `translations` (1 migration(s))

### Users

- `user_preferences` (1 migration(s))


## Statut migrations (snapshot)

```

  [90mMigration name[39m [90m......................................................................................[39m [90mBatch / Status[39m  
  0001_01_01_000000_create_users_table [90m.......................................................................[39m [1m[1][22m [32;1mRan[39;22m  
  0001_01_01_000001_create_cache_table [90m.......................................................................[39m [1m[1][22m [32;1mRan[39;22m  
  0001_01_01_000002_create_jobs_table [90m........................................................................[39m [1m[1][22m [32;1mRan[39;22m  
  2025_09_29_155749_create_personnes_table [90m...................................................................[39m [1m[1][22m [32;1mRan[39;22m  
  2025_09_29_155910_create_personne_roles_table [90m..............................................................[39m [1m[1][22m [32;1mRan[39;22m  
  2025_09_29_155958_create_personne_physiques_table [90m..........................................................[39m [1m[1][22m [32;1mRan[39;22m  
  2025_09_29_160321_create_personne_morales_table [90m............................................................[39m [1m[1][22m [32;1mRan[39;22m  
  2025_09_29_160530_create_representants_table [90m...............................................................[39m [1m[1][22m [32;1mRan[39;22m  
  2025_12_13_121716_create_instances_table [90m...................................................................[39m [1m[1][22m [32;1mRan[39;22m  
  2026_01_07_000001_create_modules_table [90m.....................................................................[39m [1m[1][22m [32;1mRan[39;22m  
  2026_01_07_000002_create_instance_user_table [90m...............................................................[39m [1m[1][22m [32;1mRan[39;22m  
  2026_01_07_170946_create_permission_tables [90m.................................................................[39m [1m[1][22m [32;1mRan[39;22m  
  2026_02_26_000001_add_personne_fks_to_users_table [90m..........................................................[39m [1m[1][22m [32;1mRan[39;22m  
  2026_02_26_100000_add_metadata_to_personne_roles_table [90m.....................................................[39m [1m[1][22m [32;1mRan[39;22m  
  2026_02_26_100001_drop_user_id_from_personnes_table [90m........................................................[39m [1m[1][22m [32;1mRan[39;22m  
  2026_02_27_000001_create_settings_table [90m....................................................................[39m [1m[1][22m [32;1mRan[39;22m  
  2026_03_05_175228_add_uuid_to_core_tables [90m..................................................................[39m [1m[1][22m [32;1mRan[39;22m  
  2026_03_07_000001_create_plans_table [90m.......................................................................[39m [1m[1][22m [32;1mRan[39;22m  
  2026_03_07_000002_create_subscriptions_table [90m...............................................................[39m [1m[1][22m [32;1mRan[39;22m  
  2026_03_07_000003_create_invoices_table [90m....................................................................[39m [1m[1][22m [32;1mRan[39;22m  
  2026_03_07_000004_create_payments_table [90m....................................................................[39m [1m[1][22m [32;1mRan[39;22m  
  2026_03_07_000005_create_licenses_table [90m....................................................................[39m [1m[1][22m [32;1mRan[39;22m  
  2026_03_08_000001_add_visibility_to_plans_and_create_plan_instance_table [90m...................................[39m [1m[1][22m [32;1mRan[39;22m  
  2026_03_12_000001_create_eshop_brands_table [90m................................................................[39m [1m[1][22m [32;1mRan[39;22m  
  2026_03_12_000001_create_translations_table [90m................................................................[39m [1m[1][22m [32;1mRan[39;22m  
  2026_03_12_000002_create_eshop_categories_table [90m............................................................[39m [1m[1][22m [32;1mRan[39;22m  
  2026_03_12_000003_create_eshop_warehouses_table [90m............................................................[39m [1m[1][22m [32;1mRan[39;22m  
  2026_03_12_000004_create_eshop_stores_table [90m................................................................[39m [1m[1][22m [32;1mRan[39;22m  
  2026_03_12_000005_create_eshop_products_table [90m..............................................................[39m [1m[1][22m [32;1mRan[39;22m  
  2026_03_12_000006_create_eshop_stocks_table [90m................................................................[39m [1m[1][22m [32;1mRan[39;22m  
  2026_03_12_000007_create_eshop_stock_movements_table [90m.......................................................[39m [1m[1][22m [32;1mRan[39;22m  
  2026_03_12_000008_create_eshop_stock_transfers_table [90m.......................................................[39m [1m[1][22m [32;1mRan[39;22m  
  2026_03_12_000009_create_eshop_stock_transfer_items_table [90m..................................................[39m [1m[1][22m [32;1mRan[39;22m  
  2026_03_12_000010_create_eshop_customers_table [90m.............................................................[39m [1m[1][22m [32;1mRan[39;22m  
  2026_03_12_000011_create_eshop_orders_table [90m................................................................[39m [1m[1][22m [32;1mRan[39;22m  
  2026_03_12_000012_create_eshop_order_items_table [90m...........................................................[39m [1m[1][22m [32;1mRan[39;22m  
  2026_03_12_000013_create_eshop_invoices_table [90m..............................................................[39m [1m[1][22m [32;1mRan[39;22m  
  2026_03_12_000014_create_eshop_invoice_items_table [90m.........................................................[39m [1m[1][22m [32;1mRan[39;22m  
  2026_03_12_000015_create_eshop_payments_table [90m..............................................................[39m [1m[1][22m [32;1mRan[39;22m  
  2026_03_12_000016_create_eshop_purchase_orders_table [90m.......................................................[39m [1m[1][22m [32;1mRan[39;22m  
  2026_03_12_000017_create_eshop_purchase_items_table [90m........................................................[39m [1m[1][22m [32;1mRan[39;22m  
  2026_03_12_000018_create_eshop_purchase_returns_table [90m......................................................[39m [1m[1][22m [32;1mRan[39;22m  
  2026_03_12_000019_create_eshop_coupons_table [90m...............................................................[39m [1m[1][22m [32;1mRan[39;22m  
  2026_03_12_000020_create_eshop_discounts_table [90m.............................................................[39m [1m[1][22m [32;1mRan[39;22m  
  2026_03_12_000021_create_eshop_discount_plans_table [90m........................................................[39m [1m[1][22m [32;1mRan[39;22m  
  2026_03_12_000022_create_eshop_quotations_table [90m............................................................[39m [1m[1][22m [32;1mRan[39;22m  
  2026_03_12_000023_create_eshop_quotation_items_table [90m.......................................................[39m [1m[1][22m [32;1mRan[39;22m  
  2026_03_12_000024_create_eshop_sale_returns_table [90m..........................................................[39m [1m[1][22m [32;1mRan[39;22m  
```

---

## Convention

- Préfixe par module (`eshop_`, `billing_`, `core_`, `auth_`)
- Toute table métier a une colonne `instance_id` (foreign key vers `instances`)
- Index obligatoires : `instance_id`, colonnes de filtre fréquentes
- Soft delete uniquement si métier (`deleted_at`)
- Pas de `dropColumn` ni `renameColumn` sans plan documenté dans `docs/runbooks/`
- Migrations additives uniquement, `up()` et `down()` réversibles
