# P0 Correction Report — Audit & Fix (2026-04-04)

> Prompt #1 du plan 15/25 → 25/25
> Criteres cibles : Performance 3→3.5 · Testabilite 2→2.5

---

## 1. Audit des 5 points [A VERIFIER]

| # | Point | Fichier | Resultat | Preuve |
|---|-------|---------|----------|--------|
| 1 | Wallet locking (`creditWallet`) | `FinanceService.php` | **KO → CORRIGE** | Aucun `lockForUpdate()` — ajout du lock sur Customer et CustomerDue |
| 2 | Commission idempotence | `HRService.php` | **KO → CORRIGE** | Aucun guard `exists()` — ajout + UNIQUE constraint (order_id, employee_id) |
| 3 | Webhook deduplication | `WebhookService.php` | **KO → CORRIGE** | Aucune deduplication — ajout deduplication_key (sha256 event:entityId) |
| 4 | Project BelongsToInstance | `Project.php` | **OK** | L8+L14 : `use Modules\Core\Database\Traits\BelongsToInstance` |
| 5 | Task BelongsToInstance | `Task.php` | **OK** | L8+L14 : `use Modules\Core\Database\Traits\BelongsToInstance` |
| 6 | Migrations 2026_04_04_* | — | **[A VERIFIER]** | Pas d'environnement PHP pour executer `migrate:status` |

---

## 2. Corrections appliquees

### 2.1 Wallet locking — FinanceService.php

**Avant :**
```php
// creditWallet() — aucun lock
$customer->increment('wallet_balance', $amount);
// debitWallet() — aucun lock
$walletBalance = (float) $customer->wallet_balance;
```

**Apres :**
```php
// creditWallet() — pessimistic lock avant lecture/ecriture
$customer = Customer::where('id', $customer->id)->lockForUpdate()->firstOrFail();
$customer->increment('wallet_balance', $amount);
// Auto-pay dues aussi lock
$pendingDues = CustomerDue::where(...)
    ->lockForUpdate()
    ->get();

// debitWallet() — pessimistic lock avant lecture solde
$customer = Customer::where('id', $customer->id)->lockForUpdate()->firstOrFail();
$walletBalance = (float) $customer->wallet_balance;
```

### 2.2 Commission idempotence — HRService.php

**Avant :**
```php
// calculateCommissionForSale() — create() sans guard
EmployeeCommission::create([...]);
```

**Apres :**
```php
// Guard idempotent
if (EmployeeCommission::where('order_id', $order->id)->where('employee_id', $employeeId)->exists()) {
    return;
}
EmployeeCommission::create([...]);
// + UNIQUE constraint (order_id, employee_id) via migration
```

### 2.3 Webhook deduplication — WebhookService.php

**Avant :**
```php
// dispatch() — envoi sans verification de doublon
foreach ($webhooks as $webhook) { dispatch(...); }
```

**Apres :**
```php
// Deduplication key basee sur event + entity
$deduplicationKey = hash('sha256', "{$event}:{$entityId}");
if (WebhookLog::where('deduplication_key', $deduplicationKey)->exists()) {
    return; // deja dispatche
}
// + colonne deduplication_key (varchar 64, indexed) via migration
```

### 2.4 Migration de securite — 2026_04_04_200001_add_p0_safety_guards.php

Ajouts :
- `eshop_webhook_logs.deduplication_key` (varchar 64, nullable, indexed)
- `eshop_employee_commissions` UNIQUE constraint `(order_id, employee_id)`

---

## 3. Corrections [NON TRAITE]

### 3.1 Double scheduling recurring-invoices

**Avant :** Deux commandes enregistrees :
- `RecurringInvoiceCommand` (`eshop360:recurring-invoices`) — schedulee a 06:00
- `GenerateRecurringInvoices` (`eshop:generate-recurring-invoices`) — enregistree, non schedulee

**Apres :** `GenerateRecurringInvoices` supprimee du tableau `$this->commands()`.
Une seule commande reste : `eshop360:recurring-invoices` a 06:00.

### 3.2 FeatureGate deprecated supprime

**Avant :**
- `FeatureGate` enregistre comme singleton (L80-82)
- `EnsurePaidFeature` dependait de `FeatureGate`

**Apres :**
- Singleton `FeatureGate` supprime du ServiceProvider
- `EnsurePaidFeature` reecrit : delegue a `FeatureResolver` si disponible, sinon pass-through
- `FeatureGate.php` conserve pour reference mais plus instancie

### 3.3 Variation prix = 0

**Avant :**
```php
$unitPrice = $variation && $variation->price !== null
    ? round((float) $variation->price, 2)  // prix 0 accepte comme override
```

**Apres :**
```php
$unitPrice = $variation && $variation->price
    ? round((float) $variation->price, 2)  // prix 0 traite comme "pas de prix"
```

---

## 4. Resume des fichiers modifies

| Fichier | Modification |
|---------|-------------|
| `Modules/Eshop360/Services/FinanceService.php` | lockForUpdate() dans creditWallet() et debitWallet() |
| `Modules/Eshop360/Services/HRService.php` | Guard idempotent dans calculateCommissionForSale() |
| `Modules/Eshop360/Services/WebhookService.php` | Deduplication key dans dispatch() et send() |
| `Modules/Eshop360/Services/OrderService.php` | Fix variation prix !== null → truthy |
| `Modules/Eshop360/Providers/Eshop360ServiceProvider.php` | Suppression FeatureGate singleton + GenerateRecurringInvoices |
| `Modules/Eshop360/Http/Middleware/EnsurePaidFeature.php` | Reecrit sans dependance FeatureGate |
| `Modules/Eshop360/Database/Migrations/2026_04_04_200001_add_p0_safety_guards.php` | Migration securite (dedup_key + unique commission) |

---

## 5. Actions restantes

- [ ] Executer `php artisan migrate` pour appliquer la migration 200001
- [ ] Verifier `php artisan migrate:status | grep "2026_04_04"` — toutes les migrations doivent etre Ran
- [ ] Ecrire et executer les 5 tests unitaires (voir section 6)
- [ ] Supprimer `FeatureGate.php` definitivement apres validation en production

## 6. Tests a ecrire

```
FinanceServiceTest::test_creditWallet_concurrent_ne_double_pas_le_paiement()
HRServiceTest::test_commission_idempotente_si_ordre_completed_deux_fois()
WebhookServiceTest::test_webhook_duplique_ignore()
EshopModelTest::test_project_appartient_a_instance()   → deja OK
EshopModelTest::test_task_appartient_a_instance()       → deja OK
```
