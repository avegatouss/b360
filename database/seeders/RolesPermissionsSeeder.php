<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Seeder des rôles et permissions de base de B360.
 *
 * Stratégie team_id pour Spatie Permission :
 * ─────────────────────────────────────────────
 * Spatie stocke les rôles utilisateurs dans model_has_roles avec :
 *   instance_id (team_foreign_key) NOT NULL (partie de la PRIMARY KEY MySQL)
 *
 * On ne peut donc pas utiliser NULL pour les rôles "globaux".
 * Convention B360 :
 *   instance_id = 0  → rôle global / cross-instance  (ex: super-admin)
 *   instance_id = N  → rôle scoped à l'instance N
 *
 * Gate::before vérifiera toujours avec team_id=0 pour super-admin.
 *
 * Ce seeder est IDEMPOTENT : il utilise firstOrCreate pour les permissions
 * et givePermissionTo (additive) pour les rôles, sauf instance-admin qui
 * reçoit syncPermissions car il doit toujours avoir TOUTES les permissions.
 */
class RolesPermissionsSeeder extends Seeder
{
    // Convention : 0 = contexte global (pas d'instance)
    public const GLOBAL_TEAM_ID = 0;

    public function run(): void
    {
        $registrar = app(PermissionRegistrar::class);

        // Réinitialiser le cache
        $registrar->forgetCachedPermissions();

        // Contexte global pour tous les rôles templates
        $registrar->setPermissionsTeamId(self::GLOBAL_TEAM_ID);

        /*
        |----------------------------------------------------------------------
        | Permissions — 87+ granulaires, organisées par module
        |----------------------------------------------------------------------
        */
        $permissions = [
            // ── Dashboard ──
            'dashboard.view',

            // ── Products ──
            'products.view',
            'products.create',
            'products.edit',
            'products.delete',
            'products.import',
            'products.export',
            'products.factory_price',   // DG only — voir prix usine
            'products.cost_real',       // DG only — voir coût réel
            'products.pght',            // manager+DG — voir PGHT

            // ── Stock ──
            'stock.view',
            'stock.adjust',
            'stock.transfer',
            'stock.receive',            // réception marchandises
            'stock.alerts',             // alertes de stock

            // ── Sales ──
            'sales.view',
            'sales.create',
            'sales.edit',
            'sales.delete',
            'sales.returns',            // traiter les retours
            'sales.real_margin',        // DG only — marge réelle
            'sales.provisional_margin', // manager — marge provisionnelle

            // ── POS ──
            'pos.access',
            'pos.open_register',
            'pos.close_register',
            'pos.hold_transactions',
            'pos.discount',

            // ── Purchases ──
            'purchases.view',
            'purchases.create',
            'purchases.edit',
            'purchases.delete',
            'purchases.returns',
            'purchases.receive',

            // ── Imports ──
            'imports.view',
            'imports.create',
            'imports.edit',
            'imports.costs',            // DG only — voir coûts import
            'imports.receive',

            // ── Clients ──
            'clients.view',
            'clients.create',
            'clients.edit',
            'clients.delete',
            'clients.wallet',           // gérer le portefeuille
            'clients.dues',             // voir les créances

            // ── Suppliers ──
            'suppliers.view',
            'suppliers.create',
            'suppliers.edit',
            'suppliers.delete',
            'suppliers.statement',      // relevé fournisseur

            // ── Invoices ──
            'invoices.view',
            'invoices.create',
            'invoices.edit',
            'invoices.delete',
            'invoices.send',            // envoyer par email
            'invoices.pdf',             // générer PDF

            // ── Quotations ──
            'quotations.view',
            'quotations.create',
            'quotations.edit',
            'quotations.delete',
            'quotations.convert',       // convertir en facture

            // ── Finance ──
            'finance.view',
            'finance.accounts',
            'finance.expenses',
            'finance.incomes',
            'finance.loans',
            'finance.gift_cards',
            'finance.installments',
            'finance.transfers',

            // ── Reports ──
            'reports.overview',
            'reports.cashbook',
            'reports.profit_loss',
            'reports.profit_loss_real',         // DG only
            'reports.profit_loss_provisional',  // manager
            'reports.stock',
            'reports.sales',
            'reports.purchases',
            'reports.taxes',
            'reports.customers',
            'reports.suppliers',
            'reports.commissions',
            'reports.pos_overview',
            'reports.installments',
            'reports.export',

            // ── Channels ──
            'channels.view',
            'channels.manage',
            'channels.margins',

            // ── HR ──
            'hr.employees',
            'hr.salaries',
            'hr.attendance',
            'hr.commissions',

            // ── Projects ──
            'projects.view',
            'projects.create',
            'projects.edit',
            'projects.delete',
            'projects.tasks',

            // ── Communication ──
            'communication.email',
            'communication.sms',
            'communication.bulk',
            'communication.templates',

            // ── Admin ──
            'admin.settings',
            'admin.users',
            'admin.roles',
            'admin.backup',
            'admin.file_manager',
            'admin.audit_logs',
            'admin.ip_rules',
            'admin.maintenance',
            'admin.modules',
            'admin.payment_gateways',
            'admin.sms_gateways',

            // ── Online Orders ──
            'online_orders.view',
            'online_orders.manage',
            'online_orders.status_update',

            // ── Charges ──
            'charges.view',
            'charges.manage',

            // ── Printing ──
            'printing.receipts',
            'printing.barcodes',
            'printing.templates',

            // ── Legacy / Core (kept for backward compatibility) ──
            'users.view',
            'users.manage',
            'instances.view',
            'instances.manage',
            'modules.view',
            'modules.manage',
            'settings.view',
            'settings.manage',
            'billing.view',
            'billing.manage',
        ];

        foreach ($permissions as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        /*
        |----------------------------------------------------------------------
        | Rôle global : super-admin (instance_id = 0)
        | Gate::before bypass → pas besoin de permissions explicites
        |----------------------------------------------------------------------
        */
        Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'web']);

        /*
        |----------------------------------------------------------------------
        | instance-admin (DG / Propriétaire) — TOUTES les permissions
        |----------------------------------------------------------------------
        */
        $instanceAdmin = Role::firstOrCreate(['name' => 'instance-admin', 'guard_name' => 'web']);
        $instanceAdmin->syncPermissions($permissions);

        /*
        |----------------------------------------------------------------------
        | manager (Gérant) — tout SAUF données confidentielles DG et admin
        |----------------------------------------------------------------------
        */
        $managerExcluded = [
            'products.factory_price',
            'products.cost_real',
            'sales.real_margin',
            'imports.costs',
            'reports.profit_loss_real',
            'admin.settings',
            'admin.users',
            'admin.roles',
            'admin.backup',
            'admin.file_manager',
            'admin.audit_logs',
            'admin.ip_rules',
            'admin.maintenance',
            'admin.modules',
            'admin.payment_gateways',
            'admin.sms_gateways',
            'instances.manage',
            'modules.manage',
        ];
        $managerPermissions = array_values(array_diff($permissions, $managerExcluded));

        $manager = Role::firstOrCreate(['name' => 'manager', 'guard_name' => 'web']);
        $manager->syncPermissions($managerPermissions);

        /*
        |----------------------------------------------------------------------
        | agent (Opérateur / Comptable) — opérations quotidiennes
        |----------------------------------------------------------------------
        */
        $agentPermissions = [
            'dashboard.view',

            // Products — lecture seule + PGHT
            'products.view',
            'products.pght',

            // Stock — complet
            'stock.view',
            'stock.adjust',
            'stock.transfer',
            'stock.receive',
            'stock.alerts',

            // Sales — créer et voir (pas supprimer, pas marges)
            'sales.view',
            'sales.create',
            'sales.edit',
            'sales.returns',

            // POS — opérations caisse
            'pos.access',
            'pos.open_register',
            'pos.close_register',
            'pos.hold_transactions',

            // Purchases — voir et réceptionner
            'purchases.view',
            'purchases.receive',

            // Imports — voir et réceptionner
            'imports.view',
            'imports.receive',

            // Clients / Fournisseurs — lecture
            'clients.view',
            'clients.create',
            'clients.edit',
            'clients.dues',
            'suppliers.view',

            // Invoices — voir et créer
            'invoices.view',
            'invoices.create',
            'invoices.pdf',

            // Online Orders — vue et mise à jour statut
            'online_orders.view',
            'online_orders.status_update',

            // Printing — toutes
            'printing.receipts',
            'printing.barcodes',
            'printing.templates',

            // Reports basiques
            'reports.overview',
            'reports.cashbook',
            'reports.stock',
            'reports.pos_overview',
        ];

        $agent = Role::firstOrCreate(['name' => 'agent', 'guard_name' => 'web']);
        $agent->syncPermissions($agentPermissions);

        /*
        |----------------------------------------------------------------------
        | user (Client portail) — accès minimal
        |----------------------------------------------------------------------
        */
        $userPermissions = [
            'dashboard.view',
            'online_orders.view',
        ];

        $user = Role::firstOrCreate(['name' => 'user', 'guard_name' => 'web']);
        $user->syncPermissions($userPermissions);

        /*
        |----------------------------------------------------------------------
        | Roles metier — specifiques a des postes
        |----------------------------------------------------------------------
        */

        // Caissier(e) — acces POS, ventes, clients, facturation basique
        $cashier = Role::firstOrCreate(['name' => 'caissiere', 'guard_name' => 'web']);
        $cashier->syncPermissions([
            'dashboard.view',
            'products.view',
            'stock.view',
            'sales.view', 'sales.create',
            'pos.access', 'pos.manage',
            'customers.view', 'customers.create',
            'invoices.view',
            'online_orders.view',
            'printing.manage',
        ]);

        // Responsable entrepot — inventaire, stock, receptions, transferts
        $warehouseManager = Role::firstOrCreate(['name' => 'responsable-entrepot', 'guard_name' => 'web']);
        $warehouseManager->syncPermissions([
            'dashboard.view',
            'products.view', 'products.edit',
            'stock.view', 'stock.manage', 'stock.adjust', 'stock.transfer',
            'purchases.view', 'purchases.create', 'purchases.edit',
            'imports.view', 'imports.create', 'imports.edit',
            'suppliers.view',
            'reports.view',
        ]);

        // Restaurer au contexte global
        $registrar->setPermissionsTeamId(self::GLOBAL_TEAM_ID);
        $registrar->forgetCachedPermissions();
    }
}
