<?php

namespace Modules\Eshop360\Providers;

use Modules\Core\Hooks\Contracts\RegistersHooks;
use Modules\Core\Hooks\DTO\BillableFeature;
use Modules\Core\Hooks\DTO\DemoDataProvider;
use Modules\Core\Hooks\DTO\MenuItem;
use Modules\Core\Hooks\DTO\PermissionGroup;
use Modules\Core\Hooks\Registry\HookRegistry;
use Modules\Eshop360\Database\Seeders\DemoCatalogPharmaSeeder;
use Modules\Eshop360\Database\Seeders\DemoCustomersSeeder;
use Modules\Eshop360\Database\Seeders\DemoFinanceSeeder;
use Modules\Eshop360\Database\Seeders\DemoHRSeeder;
use Modules\Eshop360\Database\Seeders\DemoInventorySeeder;
use Modules\Eshop360\Database\Seeders\DemoPromotionsSeeder;
use Modules\Eshop360\Database\Seeders\DemoSuppliersSeeder;

final class Eshop360HooksProvider implements RegistersHooks
{
    public function moduleName(): string
    {
        return 'Eshop360';
    }

    public function registerHooks(HookRegistry $registry): void
    {
        $this->registerMenuItems($registry);
        $this->registerPermissionGroups($registry);
        $this->registerBillableFeatures($registry);
        $this->registerDemoProviders($registry);
    }

    private function registerMenuItems(HookRegistry $registry): void
    {
        // =====================================================================
        // E-Shop (parent)
        // =====================================================================
        $registry->addMenu(new MenuItem(
            id: 'eshop360.eshop',
            label: 'E-Shop',
            icon: 'ti ti-shopping-cart',
            priority: 800,
            requiredModule: 'Eshop360',
            group: 'main',
        ));

        $registry->addMenu(new MenuItem(
            id: 'eshop360.eshop.pos',
            label: 'Terminal POS',
            route: 'eshop360.pos.index',
            priority: 800,
            requiredPermission: 'eshop.pos.access',
            requiredModule: 'Eshop360',
            group: 'main',
            activePattern: 'eshop360.pos.*',
            parentId: 'eshop360.eshop',
        ));

        $registry->addMenu(new MenuItem(
            id: 'eshop360.eshop.dashboard',
            label: 'Tableau de bord',
            route: 'eshop360.sales.dashboard',
            priority: 790,
            requiredPermission: 'eshop.sales.view',
            requiredModule: 'Eshop360',
            group: 'main',
            activePattern: 'eshop360.sales.dashboard',
            parentId: 'eshop360.eshop',
        ));

        // =====================================================================
        // Catalogue (parent)
        // =====================================================================
        $registry->addMenu(new MenuItem(
            id: 'eshop360.catalogue',
            label: 'Catalogue',
            icon: 'ti ti-package',
            priority: 790,
            requiredModule: 'Eshop360',
            group: 'main',
        ));

        $registry->addMenu(new MenuItem(
            id: 'eshop360.catalogue.products',
            label: 'Produits',
            route: 'eshop360.products.index',
            priority: 800,
            requiredPermission: 'eshop.products.view',
            requiredModule: 'Eshop360',
            group: 'main',
            activePattern: 'eshop360.products.*',
            parentId: 'eshop360.catalogue',
        ));

        $registry->addMenu(new MenuItem(
            id: 'eshop360.catalogue.categories',
            label: 'Catégories',
            route: 'eshop360.categories.index',
            priority: 790,
            requiredPermission: 'eshop.products.view',
            requiredModule: 'Eshop360',
            group: 'main',
            activePattern: 'eshop360.categories.*',
            parentId: 'eshop360.catalogue',
        ));

        $registry->addMenu(new MenuItem(
            id: 'eshop360.catalogue.brands',
            label: 'Marques',
            route: 'eshop360.brands.index',
            priority: 780,
            requiredPermission: 'eshop.products.view',
            requiredModule: 'Eshop360',
            group: 'main',
            activePattern: 'eshop360.brands.*',
            parentId: 'eshop360.catalogue',
        ));

        $registry->addMenu(new MenuItem(
            id: 'eshop360.catalogue.barcodes',
            label: 'Codes-barres',
            route: 'eshop360.barcodes.index',
            priority: 770,
            requiredPermission: 'eshop.products.view',
            requiredModule: 'Eshop360',
            group: 'main',
            activePattern: 'eshop360.barcodes.*',
            parentId: 'eshop360.catalogue',
        ));

        // =====================================================================
        // Stocks (parent)
        // =====================================================================
        $registry->addMenu(new MenuItem(
            id: 'eshop360.stocks',
            label: 'Stocks',
            icon: 'ti ti-building-warehouse',
            priority: 780,
            requiredModule: 'Eshop360',
            group: 'main',
        ));

        $registry->addMenu(new MenuItem(
            id: 'eshop360.stocks.index',
            label: 'Gestion des stocks',
            route: 'eshop360.stocks.index',
            priority: 800,
            requiredPermission: 'eshop.inventory.view',
            requiredModule: 'Eshop360',
            group: 'main',
            activePattern: 'eshop360.stocks.index',
            parentId: 'eshop360.stocks',
        ));

        $registry->addMenu(new MenuItem(
            id: 'eshop360.stocks.low',
            label: 'Stocks faibles',
            route: 'eshop360.stocks.low',
            priority: 790,
            requiredPermission: 'eshop.inventory.view',
            requiredModule: 'Eshop360',
            group: 'main',
            activePattern: 'eshop360.stocks.low',
            parentId: 'eshop360.stocks',
        ));

        $registry->addMenu(new MenuItem(
            id: 'eshop360.stocks.adjustments',
            label: 'Ajustements',
            route: 'eshop360.stock-adjustments.index',
            priority: 780,
            requiredPermission: 'eshop.inventory.manage',
            requiredModule: 'Eshop360',
            group: 'main',
            activePattern: 'eshop360.stock-adjustments.*',
            parentId: 'eshop360.stocks',
        ));

        $registry->addMenu(new MenuItem(
            id: 'eshop360.stocks.transfers',
            label: 'Transferts',
            route: 'eshop360.stock-transfers.index',
            priority: 770,
            requiredPermission: 'eshop.inventory.manage',
            requiredModule: 'Eshop360',
            group: 'main',
            activePattern: 'eshop360.stock-transfers.*',
            parentId: 'eshop360.stocks',
        ));

        $registry->addMenu(new MenuItem(
            id: 'eshop360.stocks.warehouses',
            label: 'Entrepôts',
            route: 'eshop360.warehouses.index',
            priority: 760,
            requiredPermission: 'eshop.inventory.manage',
            requiredModule: 'Eshop360',
            group: 'main',
            activePattern: 'eshop360.warehouses.*',
            parentId: 'eshop360.stocks',
        ));

        // =====================================================================
        // Ventes (parent)
        // =====================================================================
        $registry->addMenu(new MenuItem(
            id: 'eshop360.ventes',
            label: 'Ventes',
            icon: 'ti ti-receipt',
            priority: 770,
            requiredModule: 'Eshop360',
            group: 'main',
        ));

        $registry->addMenu(new MenuItem(
            id: 'eshop360.ventes.sales',
            label: 'Liste des ventes',
            route: 'eshop360.sales.index',
            priority: 800,
            requiredPermission: 'eshop.sales.view',
            requiredModule: 'Eshop360',
            group: 'main',
            activePattern: 'eshop360.sales.index',
            parentId: 'eshop360.ventes',
        ));

        $registry->addMenu(new MenuItem(
            id: 'eshop360.ventes.orders',
            label: 'Commandes',
            route: 'eshop360.orders.index',
            priority: 790,
            requiredPermission: 'eshop.sales.view',
            requiredModule: 'Eshop360',
            group: 'main',
            activePattern: 'eshop360.orders.*',
            parentId: 'eshop360.ventes',
        ));

        $registry->addMenu(new MenuItem(
            id: 'eshop360.ventes.online-orders',
            label: 'Commandes en ligne',
            route: 'eshop360.online-orders.index',
            priority: 780,
            requiredPermission: 'eshop.sales.view',
            requiredModule: 'Eshop360',
            group: 'main',
            activePattern: 'eshop360.online-orders.*',
            parentId: 'eshop360.ventes',
        ));

        $registry->addMenu(new MenuItem(
            id: 'eshop360.ventes.returns',
            label: 'Retours',
            route: 'eshop360.sales.returns',
            priority: 770,
            requiredPermission: 'eshop.sales.view',
            requiredModule: 'Eshop360',
            group: 'main',
            activePattern: 'eshop360.sales.returns',
            parentId: 'eshop360.ventes',
        ));

        // =====================================================================
        // Clients (parent)
        // =====================================================================
        $registry->addMenu(new MenuItem(
            id: 'eshop360.clients',
            label: 'Clients',
            icon: 'ti ti-users-group',
            priority: 760,
            requiredModule: 'Eshop360',
            group: 'main',
        ));

        $registry->addMenu(new MenuItem(
            id: 'eshop360.clients.list',
            label: 'Liste des clients',
            route: 'eshop360.customers.index',
            priority: 800,
            requiredPermission: 'eshop.customers.view',
            requiredModule: 'Eshop360',
            group: 'main',
            activePattern: 'eshop360.customers.*',
            parentId: 'eshop360.clients',
        ));

        $registry->addMenu(new MenuItem(
            id: 'eshop360.clients.report',
            label: 'Rapport clients',
            route: 'eshop360.customers.report',
            priority: 790,
            requiredPermission: 'eshop.customers.view',
            requiredModule: 'Eshop360',
            group: 'main',
            activePattern: 'eshop360.customers.report',
            parentId: 'eshop360.clients',
        ));

        $registry->addMenu(new MenuItem(
            id: 'eshop360.clients.tickets',
            label: 'Tickets support',
            route: 'eshop360.tickets.index',
            priority: 780,
            requiredPermission: 'eshop.customers.view',
            requiredModule: 'Eshop360',
            group: 'main',
            activePattern: 'eshop360.tickets.*',
            parentId: 'eshop360.clients',
        ));

        // =====================================================================
        // Fournisseurs (parent)
        // =====================================================================
        $registry->addMenu(new MenuItem(
            id: 'eshop360.fournisseurs',
            label: 'Fournisseurs',
            icon: 'ti ti-truck',
            priority: 755,
            requiredModule: 'Eshop360',
            group: 'main',
        ));

        $registry->addMenu(new MenuItem(
            id: 'eshop360.fournisseurs.list',
            label: 'Liste fournisseurs',
            route: 'eshop360.suppliers.index',
            priority: 800,
            requiredPermission: 'eshop.suppliers.view',
            requiredModule: 'Eshop360',
            group: 'main',
            activePattern: 'eshop360.suppliers.*',
            parentId: 'eshop360.fournisseurs',
        ));

        $registry->addMenu(new MenuItem(
            id: 'eshop360.fournisseurs.imports',
            label: 'Importations',
            route: 'eshop360.imports.index',
            priority: 790,
            requiredPermission: 'eshop.imports.view',
            requiredModule: 'Eshop360',
            group: 'main',
            activePattern: 'eshop360.imports.*',
            parentId: 'eshop360.fournisseurs',
        ));

        // =====================================================================
        // Achats (parent)
        // =====================================================================
        $registry->addMenu(new MenuItem(
            id: 'eshop360.achats',
            label: 'Achats',
            icon: 'ti ti-truck-delivery',
            priority: 750,
            requiredModule: 'Eshop360',
            group: 'main',
        ));

        $registry->addMenu(new MenuItem(
            id: 'eshop360.achats.list',
            label: 'Liste des achats',
            route: 'eshop360.purchases.index',
            priority: 800,
            requiredPermission: 'eshop.purchases.view',
            requiredModule: 'Eshop360',
            group: 'main',
            activePattern: 'eshop360.purchases.*',
            parentId: 'eshop360.achats',
        ));

        $registry->addMenu(new MenuItem(
            id: 'eshop360.achats.returns',
            label: 'Retours fournisseurs',
            route: 'eshop360.purchase-returns.index',
            priority: 790,
            requiredPermission: 'eshop.purchases.manage',
            requiredModule: 'Eshop360',
            group: 'main',
            activePattern: 'eshop360.purchase-returns.*',
            parentId: 'eshop360.achats',
        ));

        // =====================================================================
        // Factures (parent)
        // =====================================================================
        $registry->addMenu(new MenuItem(
            id: 'eshop360.factures',
            label: 'Factures',
            icon: 'ti ti-file-invoice',
            priority: 740,
            requiredModule: 'Eshop360',
            group: 'main',
        ));

        $registry->addMenu(new MenuItem(
            id: 'eshop360.factures.list',
            label: 'Liste des factures',
            route: 'eshop360.invoices.index',
            priority: 800,
            requiredPermission: 'eshop.invoices.view',
            requiredModule: 'Eshop360',
            group: 'main',
            activePattern: 'eshop360.invoices.*',
            parentId: 'eshop360.factures',
        ));

        $registry->addMenu(new MenuItem(
            id: 'eshop360.factures.templates',
            label: 'Modèles',
            route: 'eshop360.invoices.templates',
            priority: 790,
            requiredPermission: 'eshop.settings.manage',
            requiredModule: 'Eshop360',
            group: 'main',
            activePattern: 'eshop360.invoices.templates',
            parentId: 'eshop360.factures',
        ));

        // =====================================================================
        // Finances (parent)
        // =====================================================================
        $registry->addMenu(new MenuItem(
            id: 'eshop360.finances',
            label: 'Finances',
            icon: 'ti ti-wallet',
            priority: 720,
            requiredModule: 'Eshop360',
            group: 'main',
        ));

        $registry->addMenu(new MenuItem(
            id: 'eshop360.finances.accounts',
            label: 'Comptes',
            route: 'eshop360.finance.accounts.index',
            priority: 800,
            requiredPermission: 'eshop.finance.view',
            requiredModule: 'Eshop360',
            group: 'main',
            activePattern: 'eshop360.finance.accounts.*',
            parentId: 'eshop360.finances',
        ));

        $registry->addMenu(new MenuItem(
            id: 'eshop360.finances.expenses',
            label: 'Dépenses',
            route: 'eshop360.finance.expenses.index',
            priority: 790,
            requiredPermission: 'eshop.finance.view',
            requiredModule: 'Eshop360',
            group: 'main',
            activePattern: 'eshop360.finance.expenses.*',
            parentId: 'eshop360.finances',
        ));

        $registry->addMenu(new MenuItem(
            id: 'eshop360.finances.incomes',
            label: 'Revenus',
            route: 'eshop360.finance.incomes.index',
            priority: 780,
            requiredPermission: 'eshop.finance.view',
            requiredModule: 'Eshop360',
            group: 'main',
            activePattern: 'eshop360.finance.incomes.*',
            parentId: 'eshop360.finances',
        ));

        $registry->addMenu(new MenuItem(
            id: 'eshop360.finances.loans',
            label: 'Prêts',
            route: 'eshop360.finance.loans.index',
            priority: 770,
            requiredPermission: 'eshop.finance.view',
            requiredModule: 'Eshop360',
            group: 'main',
            activePattern: 'eshop360.finance.loans.*',
            parentId: 'eshop360.finances',
        ));

        $registry->addMenu(new MenuItem(
            id: 'eshop360.finances.gift-cards',
            label: 'Cartes cadeaux',
            route: 'eshop360.finance.gift-cards.index',
            priority: 760,
            requiredPermission: 'eshop.finance.view',
            requiredModule: 'Eshop360',
            group: 'main',
            activePattern: 'eshop360.finance.gift-cards.*',
            parentId: 'eshop360.finances',
        ));

        $registry->addMenu(new MenuItem(
            id: 'eshop360.finances.installments',
            label: 'Échéanciers',
            route: 'eshop360.finance.installments.index',
            priority: 750,
            requiredPermission: 'eshop.finance.view',
            requiredModule: 'Eshop360',
            group: 'main',
            activePattern: 'eshop360.finance.installments.*',
            parentId: 'eshop360.finances',
        ));

        // =====================================================================
        // Promotions (parent)
        // =====================================================================
        $registry->addMenu(new MenuItem(
            id: 'eshop360.promotions',
            label: 'Promotions',
            icon: 'ti ti-discount',
            priority: 735,
            requiredModule: 'Eshop360',
            group: 'main',
        ));

        $registry->addMenu(new MenuItem(
            id: 'eshop360.promotions.coupons',
            label: 'Coupons',
            route: 'eshop360.coupons.index',
            priority: 800,
            requiredPermission: 'eshop.promotions.manage',
            requiredModule: 'Eshop360',
            group: 'main',
            activePattern: 'eshop360.coupons.*',
            parentId: 'eshop360.promotions',
        ));

        $registry->addMenu(new MenuItem(
            id: 'eshop360.promotions.discounts',
            label: 'Remises',
            route: 'eshop360.discounts.index',
            priority: 790,
            requiredPermission: 'eshop.promotions.manage',
            requiredModule: 'Eshop360',
            group: 'main',
            activePattern: 'eshop360.discounts.*',
            parentId: 'eshop360.promotions',
        ));

        $registry->addMenu(new MenuItem(
            id: 'eshop360.promotions.quotations',
            label: 'Devis',
            route: 'eshop360.quotations.index',
            priority: 780,
            requiredPermission: 'eshop.sales.view',
            requiredModule: 'Eshop360',
            group: 'main',
            activePattern: 'eshop360.quotations.*',
            parentId: 'eshop360.promotions',
        ));

        // =====================================================================
        // RH (parent)
        // =====================================================================
        $registry->addMenu(new MenuItem(
            id: 'eshop360.rh',
            label: 'Ressources Humaines',
            icon: 'ti ti-user-check',
            priority: 710,
            requiredModule: 'Eshop360',
            group: 'main',
        ));

        $registry->addMenu(new MenuItem(
            id: 'eshop360.rh.employees',
            label: 'Employés',
            route: 'eshop360.hr.employees.index',
            priority: 800,
            requiredPermission: 'eshop.hr.view',
            requiredModule: 'Eshop360',
            group: 'main',
            activePattern: 'eshop360.hr.employees.*',
            parentId: 'eshop360.rh',
        ));

        $registry->addMenu(new MenuItem(
            id: 'eshop360.rh.salaries',
            label: 'Salaires',
            route: 'eshop360.hr.salaries.index',
            priority: 790,
            requiredPermission: 'eshop.hr.view',
            requiredModule: 'Eshop360',
            group: 'main',
            activePattern: 'eshop360.hr.salaries.*',
            parentId: 'eshop360.rh',
        ));

        $registry->addMenu(new MenuItem(
            id: 'eshop360.rh.attendance',
            label: 'Pointage',
            route: 'eshop360.hr.attendance.index',
            priority: 780,
            requiredPermission: 'eshop.hr.view',
            requiredModule: 'Eshop360',
            group: 'main',
            activePattern: 'eshop360.hr.attendance.*',
            parentId: 'eshop360.rh',
        ));

        // =====================================================================
        // Charges temps réel
        // =====================================================================
        $registry->addMenu(new MenuItem(
            id: 'eshop360.charges',
            label: 'Charges',
            icon: 'ti ti-clock-dollar',
            priority: 705,
            route: 'eshop360.charges.index',
            requiredPermission: 'eshop.charges.view',
            requiredModule: 'Eshop360',
            group: 'main',
            activePattern: 'eshop360.charges.*',
        ));

        // =====================================================================
        // Projets & Tâches
        // =====================================================================
        $registry->addMenu(new MenuItem(
            id: 'eshop360.projects',
            label: 'Projets',
            icon: 'ti ti-checklist',
            priority: 703,
            route: 'eshop360.projects.index',
            requiredPermission: 'eshop.sales.view',
            requiredModule: 'Eshop360',
            group: 'main',
            activePattern: 'eshop360.projects.*',
        ));

        // =====================================================================
        // Distribution Channels
        // =====================================================================
        $registry->addMenu(new MenuItem(
            id: 'eshop360.channels',
            label: 'Canaux de distribution',
            icon: 'ti ti-building-store',
            priority: 700,
            requiredPermission: 'eshop.channels.view',
            requiredModule: 'Eshop360',
            group: 'main',
            route: 'eshop360.channels.index',
            activePattern: 'eshop360.channels.*',
        ));

        // =====================================================================
        // Communication
        // =====================================================================
        $registry->addMenu(new MenuItem(
            id: 'eshop360.communication',
            label: 'Communication',
            icon: 'ti ti-message-circle',
            priority: 695,
            requiredModule: 'Eshop360',
            group: 'main',
        ));

        $registry->addMenu(new MenuItem(
            id: 'eshop360.communication.inbox',
            label: 'Messagerie',
            route: 'eshop360.messages.inbox',
            priority: 800,
            requiredModule: 'Eshop360',
            group: 'main',
            activePattern: 'eshop360.messages.*',
            parentId: 'eshop360.communication',
        ));

        $registry->addMenu(new MenuItem(
            id: 'eshop360.communication.tickets',
            label: 'Tickets support',
            route: 'eshop360.tickets.index',
            priority: 790,
            requiredPermission: 'eshop.customers.view',
            requiredModule: 'Eshop360',
            group: 'main',
            activePattern: 'eshop360.tickets.*',
            parentId: 'eshop360.communication',
        ));

        // =====================================================================
        // Rapports (parent)
        // =====================================================================
        $registry->addMenu(new MenuItem(
            id: 'eshop360.rapports',
            label: 'Rapports',
            icon: 'ti ti-chart-bar',
            priority: 690,
            requiredModule: 'Eshop360',
            group: 'main',
        ));

        $registry->addMenu(new MenuItem(
            id: 'eshop360.rapports.overview',
            label: 'Vue d\'ensemble',
            route: 'eshop360.reports.overview',
            priority: 850,
            requiredPermission: 'eshop.reports.view',
            requiredModule: 'Eshop360',
            group: 'main',
            activePattern: 'eshop360.reports.overview',
            parentId: 'eshop360.rapports',
        ));

        $registry->addMenu(new MenuItem(
            id: 'eshop360.rapports.sales',
            label: 'Rapport ventes',
            route: 'eshop360.reports.sales',
            priority: 800,
            requiredPermission: 'eshop.reports.view',
            requiredModule: 'Eshop360',
            group: 'main',
            activePattern: 'eshop360.reports.sales',
            parentId: 'eshop360.rapports',
        ));

        $registry->addMenu(new MenuItem(
            id: 'eshop360.rapports.profit-loss',
            label: 'Profit & Pertes',
            route: 'eshop360.reports.profit-loss',
            priority: 795,
            requiredPermission: 'eshop.reports.view',
            requiredModule: 'Eshop360',
            group: 'main',
            activePattern: 'eshop360.reports.profit-loss',
            parentId: 'eshop360.rapports',
        ));

        $registry->addMenu(new MenuItem(
            id: 'eshop360.rapports.cashbook',
            label: 'Cashbook',
            route: 'eshop360.reports.cashbook',
            priority: 793,
            requiredPermission: 'eshop.reports.view',
            requiredModule: 'Eshop360',
            group: 'main',
            activePattern: 'eshop360.reports.cashbook',
            parentId: 'eshop360.rapports',
        ));

        $registry->addMenu(new MenuItem(
            id: 'eshop360.rapports.inventory',
            label: 'Rapport stocks',
            route: 'eshop360.reports.inventory',
            priority: 790,
            requiredPermission: 'eshop.reports.view',
            requiredModule: 'Eshop360',
            group: 'main',
            activePattern: 'eshop360.reports.inventory',
            parentId: 'eshop360.rapports',
        ));

        $registry->addMenu(new MenuItem(
            id: 'eshop360.rapports.products',
            label: 'Rapport produits',
            route: 'eshop360.reports.products',
            priority: 780,
            requiredPermission: 'eshop.reports.view',
            requiredModule: 'Eshop360',
            group: 'main',
            activePattern: 'eshop360.reports.products',
            parentId: 'eshop360.rapports',
        ));

        $registry->addMenu(new MenuItem(
            id: 'eshop360.rapports.best-sellers',
            label: 'Meilleures ventes',
            route: 'eshop360.reports.best-sellers',
            priority: 770,
            requiredPermission: 'eshop.reports.view',
            requiredModule: 'Eshop360',
            group: 'main',
            activePattern: 'eshop360.reports.best-sellers',
            parentId: 'eshop360.rapports',
        ));

        $registry->addMenu(new MenuItem(
            id: 'eshop360.rapports.channels',
            label: 'Rapport Canaux',
            route: 'eshop360.reports.channels',
            priority: 760,
            requiredPermission: 'eshop.channels.view',
            requiredModule: 'Eshop360',
            group: 'main',
            activePattern: 'eshop360.reports.channels',
            parentId: 'eshop360.rapports',
        ));

        $registry->addMenu(new MenuItem(
            id: 'eshop360.rapports.invoices',
            label: 'Rapport factures',
            route: 'eshop360.reports.invoices',
            priority: 750,
            requiredPermission: 'eshop.reports.view',
            requiredModule: 'Eshop360',
            group: 'main',
            activePattern: 'eshop360.reports.invoices',
            parentId: 'eshop360.rapports',
        ));
    }

    private function registerPermissionGroups(HookRegistry $registry): void
    {
        $registry->addPermissionGroup(new PermissionGroup(
            id: 'eshop.pos',
            label: 'Terminal POS',
            permissions: [
                'eshop.pos.access' => 'Accéder au terminal POS',
            ],
            priority: 800,
            module: 'Eshop360',
        ));

        $registry->addPermissionGroup(new PermissionGroup(
            id: 'eshop.catalog',
            label: 'Catalogue Produits',
            permissions: [
                'eshop.products.view' => 'Voir les produits',
                'eshop.products.manage' => 'Gérer les produits, catégories et marques',
                'eshop.products.factory_price' => 'Voir les prix usine',
                'eshop.products.cost_real' => 'Voir les coûts de revient réels',
            ],
            priority: 790,
            module: 'Eshop360',
        ));

        $registry->addPermissionGroup(new PermissionGroup(
            id: 'eshop.inventory',
            label: 'Gestion des Stocks',
            permissions: [
                'eshop.inventory.view' => 'Voir les stocks',
                'eshop.inventory.manage' => 'Gérer les stocks, ajustements et transferts',
            ],
            priority: 780,
            module: 'Eshop360',
        ));

        $registry->addPermissionGroup(new PermissionGroup(
            id: 'eshop.sales',
            label: 'Ventes et Commandes',
            permissions: [
                'eshop.sales.view' => 'Voir les ventes et commandes',
                'eshop.sales.manage' => 'Gérer les ventes, commandes et retours',
                'eshop.sales.delete' => 'Supprimer des ventes',
                'eshop.sales.real_margin' => 'Voir la marge réelle (niveau 2)',
            ],
            priority: 770,
            module: 'Eshop360',
        ));

        $registry->addPermissionGroup(new PermissionGroup(
            id: 'eshop.customers',
            label: 'Clients',
            permissions: [
                'eshop.customers.view' => 'Voir les clients',
                'eshop.customers.manage' => 'Gérer les clients',
            ],
            priority: 760,
            module: 'Eshop360',
        ));

        $registry->addPermissionGroup(new PermissionGroup(
            id: 'eshop.suppliers',
            label: 'Fournisseurs',
            permissions: [
                'eshop.suppliers.view' => 'Voir les fournisseurs',
                'eshop.suppliers.manage' => 'Gérer les fournisseurs',
            ],
            priority: 755,
            module: 'Eshop360',
        ));

        $registry->addPermissionGroup(new PermissionGroup(
            id: 'eshop.imports',
            label: 'Importations',
            permissions: [
                'eshop.imports.view' => 'Voir les importations',
                'eshop.imports.manage' => 'Gérer les importations et frais',
                'eshop.imports.costs' => 'Voir les coûts d\'importation détaillés',
            ],
            priority: 752,
            module: 'Eshop360',
        ));

        $registry->addPermissionGroup(new PermissionGroup(
            id: 'eshop.purchases',
            label: 'Achats Fournisseurs',
            permissions: [
                'eshop.purchases.view' => 'Voir les achats',
                'eshop.purchases.manage' => 'Gérer les achats et retours fournisseurs',
            ],
            priority: 750,
            module: 'Eshop360',
        ));

        $registry->addPermissionGroup(new PermissionGroup(
            id: 'eshop.invoices',
            label: 'Factures',
            permissions: [
                'eshop.invoices.view' => 'Voir les factures',
                'eshop.invoices.manage' => 'Gérer les factures et templates',
            ],
            priority: 740,
            module: 'Eshop360',
        ));

        $registry->addPermissionGroup(new PermissionGroup(
            id: 'eshop.finance',
            label: 'Finances & Comptabilité',
            permissions: [
                'eshop.finance.view' => 'Voir les comptes et transactions',
                'eshop.finance.manage' => 'Gérer les comptes, dépenses, revenus et prêts',
            ],
            priority: 720,
            module: 'Eshop360',
        ));

        $registry->addPermissionGroup(new PermissionGroup(
            id: 'eshop.promotions',
            label: 'Promotions',
            permissions: [
                'eshop.promotions.manage' => 'Gérer les coupons et plans de remise',
            ],
            priority: 735,
            module: 'Eshop360',
        ));

        $registry->addPermissionGroup(new PermissionGroup(
            id: 'eshop.hr',
            label: 'Ressources Humaines',
            permissions: [
                'eshop.hr.view' => 'Voir les employés et pointage',
                'eshop.hr.manage' => 'Gérer les employés, salaires et commissions',
            ],
            priority: 710,
            module: 'Eshop360',
        ));

        $registry->addPermissionGroup(new PermissionGroup(
            id: 'eshop.charges',
            label: 'Charges Entreprise',
            permissions: [
                'eshop.charges.view' => 'Voir les charges en temps réel',
                'eshop.charges.manage' => 'Gérer les charges mensuelles',
            ],
            priority: 705,
            module: 'Eshop360',
        ));

        $registry->addPermissionGroup(new PermissionGroup(
            id: 'eshop.channels',
            label: 'Canaux de distribution',
            permissions: [
                'eshop.channels.view' => 'Voir les canaux de distribution et marges',
                'eshop.channels.manage' => 'Créer/modifier les canaux et répartitions',
            ],
            priority: 700,
            module: 'Eshop360',
        ));

        $registry->addPermissionGroup(new PermissionGroup(
            id: 'eshop.reports',
            label: 'Rapports E-Shop',
            permissions: [
                'eshop.reports.view' => 'Voir les rapports de vente, stock et clients',
                'eshop.reports.profit_loss_real' => 'Voir le rapport profit/perte réel (DG)',
                'eshop.reports.profit_loss_provisional' => 'Voir le rapport profit/perte provisoire',
            ],
            priority: 690,
            module: 'Eshop360',
        ));

        $registry->addPermissionGroup(new PermissionGroup(
            id: 'eshop.settings',
            label: 'Paramètres E-Shop',
            permissions: [
                'eshop.settings.manage' => 'Gérer les paramètres POS, factures et impressions',
            ],
            priority: 680,
            module: 'Eshop360',
        ));
    }

    /**
     * Register billable features — replaces hardcoded FeatureGate constants.
     * Each feature is either 'free' (always available) or 'paid' (requires plan).
     */
    private function registerBillableFeatures(HookRegistry $registry): void
    {
        // FREE features — always available
        $freeFeatures = [
            'eshop360.pos.basic' => ['POS de base', 'pos'],
            'eshop360.products.crud' => ['Gestion produits', 'catalog'],
            'eshop360.categories.crud' => ['Gestion categories', 'catalog'],
            'eshop360.brands.crud' => ['Gestion marques', 'catalog'],
            'eshop360.inventory.basic' => ['Gestion stocks de base', 'inventory'],
            'eshop360.sales.basic' => ['Ventes de base', 'sales'],
            'eshop360.invoices.basic' => ['Factures de base', 'invoicing'],
            'eshop360.customers.crud' => ['Gestion clients', 'crm'],
            'eshop360.suppliers.crud' => ['Gestion fournisseurs', 'purchasing'],
            'eshop360.purchases.basic' => ['Achats de base', 'purchasing'],
            'eshop360.reports.basic' => ['Rapports de base', 'reporting'],
            'eshop360.expenses.basic' => ['Depenses de base', 'finance'],
            'eshop360.incomes.basic' => ['Revenus de base', 'finance'],
            'eshop360.barcodes' => ['Codes-barres', 'catalog'],
            'eshop360.messages' => ['Messagerie interne', 'communication'],
            'eshop360.settings.basic' => ['Parametres de base', 'admin'],
        ];

        foreach ($freeFeatures as $id => [$label, $category]) {
            $registry->addFeature(new BillableFeature(
                id: $id,
                label: $label,
                module: 'Eshop360',
                tier: 'free',
                category: $category,
            ));
        }

        // PAID features — require a subscription plan
        $paidFeatures = [
            'eshop360.channels' => ['Canaux de distribution', 'sales', 'Gerez vos canaux de distribution et marges tripartites.'],
            'eshop360.reports.advanced' => ['Rapports avances', 'reporting', 'P&L, taxes, commissions, rapports mensuels et canaux.'],
            'eshop360.reports.export' => ['Export CSV/Excel', 'reporting', 'Exportez tous vos rapports en CSV ou Excel.'],
            'eshop360.pdf.invoices' => ['PDF Factures', 'invoicing', 'Generez des factures PDF professionnelles.'],
            'eshop360.pdf.reports' => ['PDF Rapports', 'reporting', 'Exportez vos rapports en PDF.'],
            'eshop360.payment.cinetpay' => ['Paiement CinetPay', 'payments', 'Acceptez les paiements via CinetPay.'],
            'eshop360.online_orders' => ['Commandes en ligne', 'sales', 'Gerez les commandes clients en ligne.'],
            'eshop360.installments' => ['Paiements echelonnes', 'finance', 'Proposez des plans de paiement echelonnes.'],
            'eshop360.gift_cards' => ['Cartes cadeaux', 'finance', 'Gerez les cartes cadeaux et rechargements.'],
            'eshop360.loans' => ['Gestion prets', 'finance', 'Enregistrez et suivez les prets.'],
            'eshop360.hr' => ['Ressources humaines', 'hr', 'Employes, salaires, pointage et commissions.'],
            'eshop360.charges' => ['Charges temps reel', 'finance', 'Suivi des charges en temps reel.'],
            'eshop360.holdings' => ['Mises en attente', 'pos', 'Mettez des commandes en attente au POS.'],
            'eshop360.cash_registers' => ['Caisses', 'pos', 'Ouverture et fermeture de caisse.'],
            'eshop360.email_templates' => ['Templates email', 'communication', 'Personnalisez vos templates email.'],
            'eshop360.sms' => ['Integration SMS', 'communication', 'Envoi de SMS groupes et automatiques.'],
            'eshop360.support_tickets' => ['Tickets support', 'crm', 'Systeme de tickets de support client.'],
            'eshop360.promotions.advanced' => ['Promotions avancees', 'sales', 'Coupons et plans de remise.'],
            'eshop360.quotations' => ['Devis', 'invoicing', 'Creez et convertissez des devis en factures.'],
            'eshop360.purchase_returns' => ['Retours achats', 'purchasing', 'Gerez les retours fournisseurs.'],
            'eshop360.stock_transfers' => ['Transferts stock', 'inventory', 'Transferts inter-entrepots.'],
            'eshop360.multi_warehouse' => ['Multi-entrepots', 'inventory', 'Gerez plusieurs entrepots.'],
            'eshop360.customer_groups' => ['Groupes clients', 'crm', 'Groupes avec remises et CRM.'],
            'eshop360.imports' => ['Importations', 'purchasing', 'Commandes usine avec repartition des frais.'],
            'eshop360.audit_logs' => ['Journaux audit', 'admin', 'Tracabilite de toutes les actions.'],
            'eshop360.scheduled_alerts' => ['Alertes planifiees', 'admin', 'Alertes stock, expiration, anniversaires.'],
            'eshop360.projects' => ['Projets et taches', 'projects', 'Gestion de projets et taches.'],
        ];

        $priority = 100;
        foreach ($paidFeatures as $id => [$label, $category, $description]) {
            $registry->addFeature(new BillableFeature(
                id: $id,
                label: $label,
                module: 'Eshop360',
                tier: 'paid',
                priority: $priority--,
                description: $description,
                category: $category,
            ));
        }
    }

    private function registerDemoProviders(HookRegistry $registry): void
    {
        $registry->addDemoProvider(new DemoDataProvider(
            id: 'eshop360.catalog.pharma',
            label: 'Catalogue pharmaceutique',
            module: 'Eshop360',
            seederClass: DemoCatalogPharmaSeeder::class,
            priority: 100,
            description: '10 categories, 20 marques et 100 produits pharmaceutiques avec pricing SAPHIR/CODIFARM.',
            category: 'catalog',
        ));

        $registry->addDemoProvider(new DemoDataProvider(
            id: 'eshop360.inventory',
            label: 'Entrepots et stock initial',
            module: 'Eshop360',
            seederClass: DemoInventorySeeder::class,
            priority: 95,
            description: '3 entrepots, 3 comptoirs, stock initial sur 100 produits pharma.',
            category: 'inventory',
        ));

        $registry->addDemoProvider(new DemoDataProvider(
            id: 'eshop360.customers',
            label: 'Clients et groupes',
            module: 'Eshop360',
            seederClass: DemoCustomersSeeder::class,
            priority: 90,
            description: '5 groupes clients, 15 clients (grossistes, pharmacies, ONG, CODIFARM).',
            category: 'crm',
        ));

        $registry->addDemoProvider(new DemoDataProvider(
            id: 'eshop360.suppliers',
            label: 'Fournisseurs',
            module: 'Eshop360',
            seederClass: DemoSuppliersSeeder::class,
            priority: 85,
            description: '8 fournisseurs pharmaceutiques (France, Inde, Allemagne, Cote d\'Ivoire, Maroc).',
            category: 'purchasing',
        ));

        $registry->addDemoProvider(new DemoDataProvider(
            id: 'eshop360.finance',
            label: 'Finance et comptabilite',
            module: 'Eshop360',
            seederClass: DemoFinanceSeeder::class,
            priority: 80,
            description: '4 comptes, 14 categories depenses, 8 sources revenus, 7 methodes paiement, 6 charges, exemples depenses/revenus.',
            category: 'finance',
        ));

        $registry->addDemoProvider(new DemoDataProvider(
            id: 'eshop360.hr',
            label: 'Ressources humaines',
            module: 'Eshop360',
            seederClass: DemoHRSeeder::class,
            priority: 75,
            description: '10 employes avec postes, departements, salaires et taux de commission.',
            category: 'hr',
        ));

        $registry->addDemoProvider(new DemoDataProvider(
            id: 'eshop360.promotions',
            label: 'Promotions et coupons',
            module: 'Eshop360',
            seederClass: DemoPromotionsSeeder::class,
            priority: 70,
            description: '5 coupons de reduction et 3 remises automatiques.',
            category: 'promotions',
        ));
    }
}
