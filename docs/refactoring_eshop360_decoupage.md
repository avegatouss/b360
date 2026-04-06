# Plan de decoupage Eshop360 — Monolithe → Bounded Contexts

> Date : 2026-04-04
> Strategie : "Stabiliser puis decouper" (option A validee dans `04_resume_strategique.md`)
> Etat actuel : 90 modeles, 79 controleurs, 53 services, 140 migrations

---

## 1. Bounded Contexts identifies

| # | Sous-domaine | Modeles | Tables principales | Priorite |
|---|-------------|---------|-------------------|----------|
| 1 | **Catalog** | Product, Category, Brand, ProductVariation, ProductGroup, ProductTax, Tax, Barcode | eshop_products, eshop_categories, eshop_brands | Phase 2A |
| 2 | **Inventory** | Stock, StockMovement, StockTransfer, StockTransferItem, Warehouse, Store, CashRegister | eshop_stocks, eshop_warehouses, eshop_stores | Phase 2B |
| 3 | **Sales** | Order, OrderItem, Cart, PersistentCart, Holding, SaleReturn, OnlineOrder, OnlineOrderItem, Quotation, QuotationItem | eshop_orders, eshop_order_items | Phase 2C |
| 4 | **Invoicing** | Invoice, InvoiceItem, RecurringInvoice, FneInvoice | eshop_invoices, eshop_invoice_items | Phase 3A |
| 5 | **CRM** | Customer, CustomerGroup, CustomerTransaction, CustomerDue | eshop_customers | Phase 3B |
| 6 | **Purchasing** | Supplier, PurchaseOrder, PurchaseItem, PurchaseReturn, PurchaseReturnItem, ImportOrder, ImportOrderItem, ImportCost | eshop_purchase_orders | Phase 3C |
| 7 | **Finance** | Account, AccountTransaction, AccountTransfer, Expense, ExpenseCategory, Income, IncomeSource, Loan, LoanPayment, GiftCard, GiftCardTopup, InstallmentPlan, InstallmentPayment, CompanyCharge, ChargeLog | eshop_accounts | Phase 4A |
| 8 | **Channel** | DistributionChannel, ChannelProductPrice, ChannelMarginLog, ChannelUser | eshop_distribution_channels | Phase 4B |
| 9 | **HR** | Employee, EmployeeSalary, EmployeeCommission, Attendance | eshop_employees | Phase 5A |
| 10 | **ProjectMgmt** | Project, Task, TaskComment, Event | eshop_projects | Phase 5B |
| 11 | **Communication** | Message, BulkMessageLog, EmailTemplate, SmsGateway, SmsLog, SupportTicket, TicketMessage, SupportTeam | eshop_messages | Phase 5C |

## 2. Regle de decoupage

Organisation physique dans `Modules/Eshop360/` (dossiers par domaine, PAS de module nwidart separe pour l'instant) :

```
Modules/Eshop360/
  Domain/
    Catalog/
      Models/          Product.php, Category.php, Brand.php, ...
      Services/        ProductPricingService.php, CostCalculatorService.php
      Controllers/     ProductController.php, CategoryController.php, ...
    Inventory/
      Models/          Stock.php, StockMovement.php, Warehouse.php, ...
      Services/        StockService.php
      Controllers/     StockController.php, StockTransferController.php, ...
    Sales/
      Models/          Order.php, OrderItem.php, Cart.php, ...
      Services/        OrderService.php, CartService.php, OnlineOrderService.php
      Controllers/     OrderController.php, CartController.php, ...
    Invoicing/
      Models/          Invoice.php, InvoiceItem.php, ...
      Services/        InvoiceService.php
      Controllers/     InvoiceController.php, ...
    CRM/
      Models/          Customer.php, CustomerGroup.php, ...
      Services/        (split from FinanceService wallet logic)
      Controllers/     CustomerController.php, ...
    Purchasing/
      Models/          Supplier.php, PurchaseOrder.php, ...
      Services/        ImportService.php, SupplierService.php
      Controllers/     PurchaseController.php, ...
    Finance/
      Models/          Account.php, Expense.php, Income.php, ...
      Services/        FinanceService.php, ChargesService.php
      Controllers/     AccountController.php, ExpenseController.php, ...
    Channel/
      Models/          DistributionChannel.php, ChannelProductPrice.php, ...
      Services/        ChannelAccessService.php, ChannelB2BService.php, MarginService.php
      Controllers/     ChannelPortal/*.php
    HR/
      Models/          Employee.php, EmployeeSalary.php, ...
      Services/        HRService.php
    ProjectMgmt/
      Models/          Project.php, Task.php, Event.php
    Communication/
      Models/          Message.php, SupportTicket.php, ...
      Services/        EmailService.php, SmsService.php

  Pricing/               (nouveau — Prompt #3)
    Contracts/           PricingRuleInterface.php
    DTOs/                PricingContext.php, LineItemPrice.php
    Engines/             PricingEngine.php
    Rules/               BasePriceRule.php, TaxRule.php, ...

  Providers/             (inchange — orchestration)
  Routes/                (inchange — reorganisation progressive)
  Database/Migrations/   (inchange — migrations existantes)
  Tests/                 (reorganisation par domaine en Phase 3+)
```

## 3. Regles inter-domaines

| Source | Destination | Type de communication |
|--------|------------|----------------------|
| Sales | Inventory | `StockService::adjustStock()` (appel direct — meme module) |
| Sales | Invoicing | `InvoiceService::createFromOrder()` |
| Sales | Finance | `FinanceService::depositForSale()` |
| Sales | HR | `HRService::calculateCommissionForSale()` |
| Sales | Channel | `MarginService::syncOrderMargins()` |
| Purchasing | Catalog | `CostCalculatorService::updateProductPricing()` |
| Purchasing | Inventory | `StockService::adjustStock()` |
| Channel | Catalog | `DistributionChannel::products()` (via pivot) |
| CRM | Finance | `FinanceService::creditWallet()` / `debitWallet()` |

**Regle** : tant que tous les domaines restent dans le meme module Eshop360, les appels directs entre services sont acceptes. Quand un domaine sera extrait en module nwidart separe, les appels seront remplaces par des interfaces Core + Events.

## 4. Pilote : extraction Inventory (Phase 2B)

### Fichiers a deplacer

| Depuis | Vers |
|--------|------|
| `Models/Stock.php` | `Domain/Inventory/Models/Stock.php` |
| `Models/StockMovement.php` | `Domain/Inventory/Models/StockMovement.php` |
| `Models/StockTransfer.php` | `Domain/Inventory/Models/StockTransfer.php` |
| `Models/StockTransferItem.php` | `Domain/Inventory/Models/StockTransferItem.php` |
| `Models/Warehouse.php` | `Domain/Inventory/Models/Warehouse.php` |
| `Models/Store.php` | `Domain/Inventory/Models/Store.php` |
| `Services/StockService.php` | `Domain/Inventory/Services/StockService.php` |
| `Controllers/Inventory/StockController.php` | `Domain/Inventory/Controllers/StockController.php` |
| `Controllers/Inventory/StockTransferController.php` | `Domain/Inventory/Controllers/StockTransferController.php` |
| `Controllers/Inventory/StockAdjustmentController.php` | `Domain/Inventory/Controllers/StockAdjustmentController.php` |
| `Controllers/Inventory/WarehouseController.php` | `Domain/Inventory/Controllers/WarehouseController.php` |

### Namespaces

```php
// Avant
namespace Modules\Eshop360\Models;
class Stock extends Model { ... }

// Apres
namespace Modules\Eshop360\Domain\Inventory\Models;
class Stock extends Model { ... }

// Alias de compatibilite (temporaire)
// Modules/Eshop360/Models/Stock.php
class_alias(\Modules\Eshop360\Domain\Inventory\Models\Stock::class, \Modules\Eshop360\Models\Stock::class);
```

### Imports a mettre a jour

| Fichier consommateur | Import actuel | Nouvel import |
|---------------------|--------------|---------------|
| `OrderService.php` | `use Models\Stock` | `use Domain\Inventory\Models\Stock` |
| `ImportService.php` | `use Models\Stock` | `use Domain\Inventory\Models\Stock` |
| `PosController.php` | `use Models\Warehouse` | `use Domain\Inventory\Models\Warehouse` |
| `CheckoutController.php` | `use Models\Stock` | `use Domain\Inventory\Models\Stock` |

## 5. Metriques cibles

| Phase | Taille max domaine (modeles) | Couplage | Tests |
|-------|------------------------------|----------|-------|
| Actuel | 90 (monolithe) | Tous imports directs | 266 passed |
| Phase 2 | 25 (Catalog+Inventory extraits) | Appels directs OK | 270+ |
| Phase 3 | 15 (Sales+Invoicing+CRM extraits) | Interfaces pour cross-domain | 300+ |
| Phase 4+ | < 10 par domaine | Events pour async | 350+ |

## 6. Critere go/no-go

Avant de deplacer un fichier :
1. `php artisan test` = 0 failed
2. `php artisan route:list | grep {domain}` = toutes les routes intactes
3. Aucun `class_not_found` dans les logs Laravel
4. Le seeder demo fonctionne toujours
