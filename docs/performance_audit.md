# Audit de Performance — B360 Eshop360

> Date : 2026-04-04
> Prompt #7 — Performance 3→5

---

## 1. Corrections appliquees

### 1.1 reserved_quantity fonctionnelle

| Composant | Avant | Apres |
|-----------|-------|-------|
| `CartService::addItem()` | Pas de reservation | `Stock::increment('reserved_quantity', qty)` avec `lockForUpdate()` |
| `CartService::updateItem()` | Pas de delta | Delta +/- reserve en fonction du changement de quantite |
| `CartService::clear()` | Pas de liberation | `releaseStock()` pour chaque item avant suppression |
| `CartService::removeItem()` | Pas de liberation | `releaseStock()` sur l'item retire |
| `OrderService` | `quantity -= qty` seulement | `quantity -= qty` ET `reserved_quantity -= qty` (deja fait dans StockService) |
| Paniers expires | Pas de nettoyage | Commande `eshop:release-expired-carts` toutes les 15 minutes |

### 1.2 Cache rapports

| Avant | Apres |
|-------|-------|
| Manifest-based (fragile, source de 3 echecs de tests) | Tag-based si Redis disponible, manifest en fallback |
| `Cache::put($manifestKey, $keys)` | `Cache::tags(["reports.{$instanceId}"])->remember(...)` |
| Invalidation manuelle par prefix | `Cache::tags([...])->flush()` pour Redis |

### 1.3 Index DB critiques

| Table | Index | Pattern optimise |
|-------|-------|-----------------|
| `eshop_stocks` | `(product_id, warehouse_id)` | StockService::adjustStock, POS stock lookup |
| `eshop_orders` | `(instance_id, status)` | Listing commandes, rapports ventes |
| `eshop_orders` | `(customer_id, created_at)` | Historique commandes client |
| `eshop_channel_product_prices` | `(channel_id, product_id)` | ProductPricingService, portail canal |
| `eshop_products` | `(instance_id, is_active, deleted_at)` | Catalogue, POS, recherche |

### 1.4 PricingEngine cache

| Avant | Apres |
|-------|-------|
| `Cache::store('redis')` hardcode | `Cache::store(config('cache.default'))` — compatible array/redis/file |
| Pas de fallback si Redis indisponible | Cache configurable, pas de crash |

## 2. Corrections de race conditions (deja appliquees P#1)

| Point | Methode | Protection |
|-------|---------|------------|
| Stock concurrent | `StockService::adjustStock()` | `lockForUpdate()` + transaction |
| Wallet concurrent | `FinanceService::creditWallet/debitWallet()` | `lockForUpdate()` + `withoutGlobalScopes()` |
| Commission double | `HRService::calculateCommissionForSale()` | Guard `exists()` + UNIQUE constraint |
| Webhook double | `WebhookService::dispatch()` | `deduplication_key` sha256 |
| Numero doublon | `OrderService`, `InvoiceService` | Retry loop + catch MySQL 1062 |
| reserved_quantity negative | `StockTransferController` | `max(0, reserved - qty)` |

## 3. Fichiers modifies

| Fichier | Modification |
|---------|-------------|
| `CartService.php` | reserveStock/releaseStock dans add/update/remove/clear |
| `ReportService.php` | Tag-based cache avec fallback manifest |
| `PricingCacheManager.php` | Cache driver configurable |
| `ReleaseExpiredCartReservations.php` | Nouvelle commande artisan |
| `Eshop360ServiceProvider.php` | Enregistrement commande + schedule 15min |
| Migration `500001` | 5 index de performance |

## 4. Recommandations futures

- Activer `CACHE_STORE=redis` en production pour beneficier du tag-based cache
- Monitorer les queries lentes avec `DB::listen()` ou Laravel Debugbar en staging
- Ajouter `php artisan b360:benchmark` pour mesurer les temps de reponse pages critiques
- Configurer des alertes si `reserved_quantity` totale > X% du stock total (indication de paniers abandonnes)
