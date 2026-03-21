<?php

namespace Modules\Eshop360\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Core\Support\TeamContext;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Seeder des permissions Eshop360.
 *
 * Ce seeder crée les permissions eshop-specifiques (préfixées eshop.*)
 * qui complètent les permissions granulaires du RolesPermissionsSeeder principal.
 *
 * Les permissions eshop.* sont des alias/compléments pour le module Eshop360
 * et sont mappées aux mêmes rôles que les permissions principales.
 *
 * IDEMPOTENT : utilise firstOrCreate pour les permissions.
 */
class Eshop360PermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $registrar = app(PermissionRegistrar::class);
        $registrar->forgetCachedPermissions();

        // Set team context to global (0 = cross-instance)
        $registrar->setPermissionsTeamId(TeamContext::GLOBAL_TEAM_ID);

        try {
            $permissions = [
                // POS
                'eshop.pos.access',
                // Catalogue
                'eshop.products.view',
                'eshop.products.manage',
                'eshop.products.factory_price',
                'eshop.products.cost_real',
                // Inventory
                'eshop.inventory.view',
                'eshop.inventory.manage',
                // Sales
                'eshop.sales.view',
                'eshop.sales.manage',
                'eshop.sales.delete',
                'eshop.sales.real_margin',
                // Online Orders — fast actions
                'eshop.online-orders.fast-deliver',
                'eshop.online-orders.fast-complete',
                // FNE
                'eshop.fne.manage',
                // Customers
                'eshop.customers.view',
                'eshop.customers.manage',
                // Suppliers
                'eshop.suppliers.view',
                'eshop.suppliers.manage',
                // Imports
                'eshop.imports.view',
                'eshop.imports.manage',
                'eshop.imports.costs',
                // Purchases
                'eshop.purchases.view',
                'eshop.purchases.manage',
                // Invoices
                'eshop.invoices.view',
                'eshop.invoices.manage',
                // Finance
                'eshop.finance.view',
                'eshop.finance.manage',
                // Promotions
                'eshop.promotions.manage',
                // HR
                'eshop.hr.view',
                'eshop.hr.manage',
                // Charges
                'eshop.charges.view',
                'eshop.charges.manage',
                // Distribution Channels
                'eshop.channels.view',
                'eshop.channels.manage',
                // Reports
                'eshop.reports.view',
                'eshop.reports.profit_loss_real',
                'eshop.reports.profit_loss_provisional',
                // Settings
                'eshop.settings.manage',
            ];

            // Create all permissions (guard: web) — idempotent
            foreach ($permissions as $permission) {
                Permission::firstOrCreate([
                    'name' => $permission,
                    'guard_name' => 'web',
                ]);
            }

            // super-admin gets everything via Gate::before, no explicit assignment needed

            // instance-admin: all eshop permissions
            $instanceAdmin = Role::where('name', 'instance-admin')->first();
            if ($instanceAdmin) {
                $instanceAdmin->givePermissionTo($permissions);
            }

            // manager: all except sensitive DG data, settings, channels manage
            $manager = Role::where('name', 'manager')->first();
            if ($manager) {
                $excluded = [
                    'eshop.settings.manage',
                    'eshop.products.factory_price',
                    'eshop.products.cost_real',
                    'eshop.sales.real_margin',
                    'eshop.reports.profit_loss_real',
                    'eshop.channels.manage',
                    'eshop.imports.costs',
                ];
                $managerPerms = array_values(array_filter(
                    $permissions,
                    fn ($p) => !in_array($p, $excluded)
                ));
                $manager->givePermissionTo($managerPerms);
            }

            // agent: POS + sales + customers + inventory view + invoices view + purchases view
            $agent = Role::where('name', 'agent')->first();
            if ($agent) {
                $agent->givePermissionTo([
                    'eshop.pos.access',
                    'eshop.products.view',
                    'eshop.inventory.view',
                    'eshop.sales.view',
                    'eshop.sales.manage',
                    'eshop.customers.view',
                    'eshop.customers.manage',
                    'eshop.invoices.view',
                    'eshop.suppliers.view',
                    'eshop.purchases.view',
                ]);
            }

            // user: minimal view access
            $user = Role::where('name', 'user')->first();
            if ($user) {
                $user->givePermissionTo([
                    'eshop.products.view',
                    'eshop.sales.view',
                ]);
            }
        } finally {
            // Restore team context to global (never null — PK constraint)
            $registrar->setPermissionsTeamId(TeamContext::GLOBAL_TEAM_ID);
        }
    }
}
