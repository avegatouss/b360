# Guide de contribution B360

## Prerequis

- PHP 8.2+, MySQL 8.0, Redis 7.x
- Composer 2.x, Node.js 20+, npm

```bash
composer install
npm ci && npm run build
cp .env.example .env && php artisan key:generate
php artisan migrate
php artisan test   # 0 failed attendu
```

## Standards de code

- **Pint** : `vendor/bin/pint --preset=laravel` avant chaque commit
- **DTOs** : toujours `readonly class` (PHP 8.2)
- **Controllers** : max 15 lignes par methode, pas de logique metier — deleguer aux Services
- **Services** : singleton enregistre dans le ServiceProvider du module
- **Transactions** : toute operation multi-table dans `DB::transaction()`
- **Locking** : `lockForUpdate()` pour les operations de stock, wallet, credits

## Tests obligatoires avant PR

- Ajouter un test unitaire pour chaque nouveau Service
- Ajouter un test Feature pour chaque nouveau Controller
- 0 regression : `php artisan test` doit passer avec le meme nombre de tests ou plus
- Tests de race condition pour les operations concurrentes (stock, paiement)

## Conventions de nommage

### Tables

Chaque module prefixe ses tables :
- `eshop_` pour Eshop360
- `mnu_` pour Menuiserie360
- `billing_` pour Billing (sauf `plans`, `subscriptions` legacy)

### Migrations

Format : `YYYY_MM_DD_HHMMSS_{module}_{action}_{table}.php`

Exemple : `2026_04_04_100001_eshop_add_pricing_modes_to_products.php`

### Permissions

Format : `{module}.{entity}.{action}`

Exemples : `eshop.sales.manage`, `menuiserie.devis.create`, `billing.manage`

### Events

Nom actif au passe : `OrderCompleted`, `StockAdjusted`, `DevisAccepte`

### Routes

- Toutes les routes instance-scoped : `/i/{slug}/...`
- Route names : `{module}.{domain}.{action}` — ex: `eshop360.orders.show`

## Utilisation des hooks Core

Le `HookRegistry` permet d'enregistrer des extensions sans toucher au Core :

| Hook type | DTO | Exemple |
|-----------|-----|---------|
| Menu | `MenuItem` | Ajouter une entree dans la sidebar |
| Widget | `DashboardWidget` | Ajouter un widget au dashboard |
| Permissions | `PermissionGroup` | Declarer des permissions groupees |
| Features | `BillableFeature` | Declarer une feature payante/gratuite |
| Settings | `SettingsGroup` | Ajouter un groupe de parametres |
| Payment | `PaymentGatewayDefinition` | Enregistrer une gateway de paiement |
| Demo | `DemoDataProvider` | Fournir des donnees de demo |

Enregistrement dans le `HooksProvider` du module :
```php
$registry->addMenuItem(new MenuItem(
    id: 'monmodule.menu',
    label: 'Mon Module',
    icon: 'ti ti-box',
    route: 'monmodule.index',
    module: 'MonModule',
));
```

## Ajouter un nouveau module

```bash
php artisan module:make NomModule
```

1. Configurer `module.json` (name, alias, providers)
2. Creer le `HooksProvider` : menu + permissions + features
3. Prefixer toutes les tables avec `{alias}_`
4. Ajouter les tests dans `Modules/NomModule/Tests/`
5. Ne JAMAIS importer directement un modele d'un autre module — utiliser les interfaces Core

## Multi-tenancy

- Chaque modele instance-scoped DOIT avoir le trait `BelongsToInstance`
- Chaque modele channel-scoped DOIT avoir le trait `BelongsToChannel`
- Le `ChannelScope` filtre automatiquement — utiliser `withoutGlobalScopes()` quand on a deja la PK
- Les queries de resolution (middleware, lookup par ID) DOIVENT bypasser les scopes

## Architecture des domaines Eshop360

Eshop360 est en cours de decomposition en bounded contexts :

```
Modules/Eshop360/Domain/{Catalog,Inventory,Sales,Invoicing,CRM,...}/
  Models/
  Services/
  Controllers/
```

Voir `docs/refactoring_eshop360_decoupage.md` pour le plan complet.
