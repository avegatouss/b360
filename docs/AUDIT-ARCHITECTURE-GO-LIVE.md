# AUDIT ARCHITECTURE - PLAN DE DURCISSEMENT PRE-PRODUCTION

> Niveau : Audit senior | Approche : pragmatique, non-destructive
> Application : B360 - Laravel 12 multi-tenant modulaire
> Date : 2026-03-31 | Branche : eshop360

---

## TABLE DES MATIERES

1. [Resume Executif](#1-resume-executif)
2. [Issues Structurelles](#2-issues-structurelles)
3. [Points de Monitoring Critiques](#3-points-de-monitoring-critiques)
4. [Logs a Ajouter Avant Go-Live](#4-logs-a-ajouter-avant-go-live)
5. [Checklist de Sante Pre-Deploiement](#5-checklist-de-sante-pre-deploiement)
6. [Plan de Durcissement par Phase](#6-plan-de-durcissement-par-phase)

---

## 1. RESUME EXECUTIF

### Etat General : DEPLOIEMENT POSSIBLE AVEC CORRECTIFS CIBLES

L'application B360 presente une architecture modulaire solide avec un systeme de hooks extensible et une isolation multi-tenant bien concue. Cependant, **12 problemes structurels** ont ete identifies, dont **5 critiques** qui posent un risque financier direct en production.

### Risques Majeurs Identifies

| # | Risque | Severite | Impact Business |
|---|--------|----------|-----------------|
| 1 | Race condition sur le stock (deduction concurrente) | CRITIQUE | Stock negatif, commandes non honorables |
| 2 | Commissions employees dupliquees | CRITIQUE | Sur-paiement RH |
| 3 | Solde portefeuille/compte negatif | CRITIQUE | Credit non autorise, perte financiere |
| 4 | Webhook paiement traite 2 fois | CRITIQUE | Double comptabilisation |
| 5 | Deux caisses ouvertes simultanement | CRITIQUE | Reconciliation impossible |
| 6 | Double systeme paiement/facturation | HAUTE | Confusion, maintenance double |
| 7 | Jobs bulk sans retry ($tries=1) | HAUTE | Perte de communications |
| 8 | 7 modeles sans isolation instance | HAUTE | Fuite de donnees cross-tenant |
| 9 | Logs marges dupliques | HAUTE | Audit financier fausse |
| 10 | Numero facture non-atomique | MOYENNE | Collision de numeros |
| 11 | Queue afterCommit=false | MOYENNE | Jobs executes avant commit DB |
| 12 | Event PasswordReset orphelin | BASSE | Pas de trace audit reset MDP |

---

## 2. ISSUES STRUCTURELLES

---

### ISSUE-01 : Race Condition Stock - Deduction Concurrente

**Type :** Flow / Race Condition
**Severite :** CRITIQUE
**Modules Impliques :** Eshop360 (StockService, OrderService)

#### Probleme

`StockService.adjustStock()` lit la quantite stock, verifie qu'elle est suffisante, puis decremente - le tout SANS verrouillage pessimiste (`lockForUpdate`). Deux requetes concurrentes peuvent lire la meme quantite et les deux deduire, resultant en stock negatif malgre la validation.

#### Pourquoi C'est Dangereux

En production avec plusieurs caissiers/agents simultanement :
- Stock affiche : 10 unites
- Agent A commande 8 unites (stock lu = 10, OK)
- Agent B commande 8 unites (stock lu = 10, OK)
- Resultat : stock = -6 unites

Impact : commandes non honorables, plaintes clients, inventaire corrompu.

#### Flux d'Execution

```
Request A                          Request B
    |                                  |
StockService.adjustStock()        StockService.adjustStock()
    |                                  |
DB::transaction() {               DB::transaction() {
    |                                  |
    Stock::firstOrCreate()             Stock::firstOrCreate()
    quantity = 10                       quantity = 10  (MEME VALEUR)
    |                                  |
    10 - 8 = 2 >= 0 ? OK              10 - 8 = 2 >= 0 ? OK
    |                                  |
    stock->increment(-8)               stock->increment(-8)
    quantity = 2                        quantity = -6  !!
}                                 }
```

#### Correctif Safe (OBLIGATOIRE)

**Fichier :** `Modules/Eshop360/Services/StockService.php`, methode `adjustStock()`

Ajouter `lockForUpdate()` avant la lecture du stock :

```php
// AVANT (dangereux)
$stock = Stock::firstOrCreate([
    'product_id' => $product->id,
    'warehouse_id' => $warehouseId,
], ['quantity' => 0]);

// APRES (safe)
$stock = Stock::where('product_id', $product->id)
    ->where('warehouse_id', $warehouseId)
    ->lockForUpdate()
    ->first();

if (!$stock) {
    $stock = Stock::create([
        'product_id' => $product->id,
        'warehouse_id' => $warehouseId,
        'quantity' => 0,
    ]);
}
```

Egalement ajouter `lockForUpdate()` dans `getAvailableQuantity()` si appele dans un contexte transactionnel.

#### Direction Refactoring (Optionnel)

Ajouter une contrainte CHECK en DB : `ALTER TABLE eshop_stocks ADD CONSTRAINT chk_stock_positive CHECK (quantity >= 0);`

---

### ISSUE-02 : Commissions Employees Dupliquees

**Type :** Side Effect / Idempotency
**Severite :** CRITIQUE
**Modules Impliques :** Eshop360 (HRService, OrderService)

#### Probleme

`HRService.calculateCommissionForSale()` cree un `EmployeeCommission` a chaque appel sans verifier si une commission existe deja pour cette commande. Si l'endpoint de changement de statut est appele deux fois (double-clic, refresh, retry reseau), la commission est creee en double.

#### Pourquoi C'est Dangereux

- Chaque double-clic sur "Marquer comme complete" = double paiement de commission
- Pas de contrainte d'unicite en base (order_id, employee_id)
- Impact direct sur la paie : sur-paiement systematique

#### Flux d'Execution

```
User double-clic "Complete"
    |
    +-- Request 1: OrderController.updateStatus('completed')
    |       |
    |       HRService.calculateCommissionForSale($order)
    |       EmployeeCommission::create(order_id=42, amount=500)  // OK
    |
    +-- Request 2: OrderController.updateStatus('completed')
            |
            HRService.calculateCommissionForSale($order)
            EmployeeCommission::create(order_id=42, amount=500)  // DOUBLON!
```

#### Correctif Safe (OBLIGATOIRE)

**Fichier :** `Modules/Eshop360/Services/HRService.php`, methode `calculateCommissionForSale()`

```php
public function calculateCommissionForSale(Order $order): void
{
    // Guard : verifier si deja calculee
    $existing = EmployeeCommission::where('order_id', $order->id)->exists();
    if ($existing) {
        return; // Idempotent - deja traitee
    }

    // ... reste de la logique existante
}
```

**Migration complementaire :**

```php
Schema::table('eshop_employee_commissions', function (Blueprint $table) {
    $table->unique(['order_id', 'employee_id'], 'uq_commission_order_employee');
});
```

---

### ISSUE-03 : Solde Compte/Portefeuille Peut Devenir Negatif

**Type :** Flow / Race Condition
**Severite :** CRITIQUE
**Modules Impliques :** Eshop360 (FinanceService, WalletDriver)

#### Probleme

`FinanceService.withdraw()` decremente le solde SANS verifier qu'il est suffisant, et SANS verrouillage pessimiste. `WalletDriver.initiate()` verifie le solde AVANT la transaction mais sans lock - un autre thread peut debiter entre la verification et la deduction.

#### Pourquoi C'est Dangereux

- Comptes financiers avec solde negatif = credit non autorise
- Portefeuille client negatif = service offert gratuitement
- Deux paiements wallet simultanees = double debit

#### Correctif Safe (OBLIGATOIRE)

**Fichier 1 :** `Modules/Eshop360/Services/FinanceService.php`, methode `withdraw()`

```php
public function withdraw(Account $account, float $amount, ...): AccountTransaction
{
    return DB::transaction(function () use ($account, $amount, ...) {
        // Verrouiller et relire le solde
        $account = Account::lockForUpdate()->find($account->id);

        if ($account->balance < $amount) {
            throw new \InvalidArgumentException("Solde insuffisant");
        }

        $account->decrement('balance', $amount);
        return AccountTransaction::create([...]);
    });
}
```

**Fichier 2 :** `Modules/Eshop360/Services/Payment/Drivers/WalletDriver.php`, methode `initiate()`

```php
public function initiate(...): array
{
    return DB::transaction(function () use ($customer, $amount, ...) {
        $customer = Customer::lockForUpdate()->find($customer->id);

        if ((float) $customer->wallet_balance < $amount) {
            return ['success' => false, 'error' => 'Solde insuffisant'];
        }

        $customer->decrement('wallet_balance', $amount);
        // ... creer la transaction
        return ['success' => true, ...];
    });
}
```

---

### ISSUE-04 : Webhook Paiement Traite Deux Fois

**Type :** Integration / Idempotency
**Severite :** CRITIQUE
**Modules Impliques :** Billing (WebhookController), Eshop360 (WebhookService)

#### Probleme

Les webhooks de paiement (Stripe, CinetPay, etc.) sont frequemment retransmis par les passerelles (timeout sur l'ACK, retry automatique). Le `WebhookController` du module Billing ne verifie pas si un webhook a deja ete traite - il re-execute `updatePaymentStatus()` a chaque reception.

#### Pourquoi C'est Dangereux

- Stripe retry automatiquement jusqu'a 3 fois en 24h
- Un meme paiement peut etre marque "complete" plusieurs fois
- Si le webhook declenche des effets de bord (comptabilisation, notification), ceux-ci sont dupliques

#### Flux d'Execution

```
Stripe Webhook (tentative 1)          Stripe Webhook (retry)
    |                                       |
WebhookController.handle()            WebhookController.handle()
    |                                       |
findPayment($ref) -> Payment #42      findPayment($ref) -> Payment #42
    |                                       |
updatePaymentStatus('completed')       updatePaymentStatus('completed')
    |                                       |
invoice->update(paid)                  invoice->update(paid)  // REEXECUTE
```

#### Correctif Safe (OBLIGATOIRE)

**Fichier :** `Modules/Billing/Http/Controllers/WebhookController.php`

```php
private function markCompleted(Payment $payment): void
{
    // Guard idempotent
    if ($payment->status === 'completed') {
        return; // Deja traite
    }

    $payment->update(['status' => 'completed', 'paid_at' => now()]);

    $invoice = $payment->invoice;
    if ($invoice && !$invoice->isPaid()) {
        $invoice->update(['status' => 'paid', 'paid_at' => now()]);
    }
}
```

**Complement :** Ajouter dans `WebhookLog` un champ `idempotency_key` (hash du payload) :

```php
// Avant traitement
$idempotencyKey = hash('sha256', json_encode($payload));
if (WebhookLog::where('idempotency_key', $idempotencyKey)->exists()) {
    return response('Already processed', 200);
}
$log = WebhookLog::create([..., 'idempotency_key' => $idempotencyKey]);
```

---

### ISSUE-05 : Deux Caisses Ouvertes Simultanement

**Type :** Flow / Race Condition
**Severite :** CRITIQUE
**Modules Impliques :** Eshop360 (CashRegisterService)

#### Probleme

`CashRegisterService.open()` ferme la caisse precedente puis en ouvre une nouvelle, mais ces deux operations ne sont PAS dans une transaction. Deux clics rapides sur "Ouvrir caisse" creent deux caisses ouvertes pour le meme utilisateur.

#### Pourquoi C'est Dangereux

- Reconciliation de caisse impossible
- Commandes attribuees a la mauvaise caisse
- Audit trail casse

#### Correctif Safe (OBLIGATOIRE)

**Fichier :** `Modules/Eshop360/Services/CashRegisterService.php`, methode `open()`

```php
public function open(...): CashRegister
{
    return DB::transaction(function () use (...) {
        // Verrouiller les caisses ouvertes de l'utilisateur
        CashRegister::where('user_id', auth()->id())
            ->where('status', 'open')
            ->lockForUpdate()
            ->update(['status' => 'closed', 'closed_at' => now()]);

        return CashRegister::create([
            'user_id' => auth()->id(),
            'store_id' => $storeId,
            'opening_amount' => $amount,
            'status' => 'open',
            'opened_at' => now(),
        ]);
    });
}
```

---

### ISSUE-06 : Double Systeme Paiement/Facturation (Billing vs Eshop360)

**Type :** Legacy / Dual System
**Severite :** HAUTE
**Modules Impliques :** Billing, Eshop360

#### Probleme

Deux systemes complets de facturation et de paiement coexistent sans interoperabilite :

| Aspect | Billing Module | Eshop360 Module |
|--------|---------------|-----------------|
| Table factures | `invoices` (system DB) | `eshop_invoices` (instance DB) |
| Table paiements | `payments` (FK directe) | `eshop_payments` (polymorphique) |
| Passerelles | 8 via HookRegistry | 10 via drivers hardcodes |
| Interface | `Billing\Contracts\PaymentGatewayInterface` | `Eshop360\Services\Payment\PaymentGatewayInterface` |
| Webhooks | `billing_webhook_logs` | `eshop_webhook_logs` |
| Numerotation | `B360-INV-YYYY-NNNNN` | Configurable par instance |

#### Pourquoi C'est Dangereux

- Si CinetPay est configure dans les DEUX modules, un webhook arrive sur `/api/billing/webhooks/cinetpay` mais pas sur le handler Eshop360
- Maintenance double : tout changement de passerelle doit etre fait en 2 endroits
- Confusion developpeur : quel `Payment` model utiliser ?

#### Correctif Safe (NON-BREAKING)

**Phase 1 (immediate) :** Documenter clairement le perimetre de chaque systeme :
- Billing = facturation SaaS (abonnements plateforme)
- Eshop360 = facturation metier (ventes, achats, clients)
- **REGLE :** Chaque passerelle ne doit etre configuree que dans UN seul module

**Phase 2 (court terme) :** Creer une facade commune :

```php
// Modules/Core/Contracts/PaymentGatewayContract.php
interface PaymentGatewayContract
{
    public function initiate(PaymentRequest $request): PaymentResponse;
    public function verify(string $reference): PaymentStatus;
    public function handleWebhook(array $payload, array $headers): WebhookResult;
}
```

#### Direction Refactoring

A moyen terme, migrer les passerelles Billing vers le systeme Eshop360 (plus flexible car polymorphique) et utiliser le Billing module uniquement pour la gestion des abonnements/plans.

---

### ISSUE-07 : Jobs Bulk Email/SMS Sans Retry

**Type :** Flow / Resilience
**Severite :** HAUTE
**Modules Impliques :** Eshop360 (SendBulkEmail, SendBulkSms)

#### Probleme

Les deux jobs de communication en masse ont `$tries = 1` : une seule tentative, zero retry. Une erreur reseau transitoire = perte definitive du lot.

De plus, les increments `$log->increment('sent_count')` ne sont pas atomiques - des chunks concurrents peuvent provoquer des conditions de course sur les compteurs.

#### Pourquoi C'est Dangereux

- Campagne SMS a 1000 clients : si le serveur SMS timeout au recipient #501, les 499 restants sont perdus
- Pas de retry, pas de reprise - le statut passe a 'failed' definitivement
- Les compteurs sent/failed sont imprecis sous concurrence

#### Correctif Safe (OBLIGATOIRE)

**Fichier :** `Modules/Eshop360/Jobs/SendBulkEmail.php`

```php
// AVANT
public int $tries = 1;

// APRES
public int $tries = 3;
public array $backoff = [30, 120, 300]; // 30s, 2min, 5min
```

**Meme correctif pour** `SendBulkSms.php`

**Pour les compteurs :** utiliser `lockForUpdate()` ou des operations atomiques :

```php
// Atomique via DB
BulkMessageLog::where('id', $this->log->id)->increment('sent_count');
// Au lieu de :
$this->log->increment('sent_count');
```

---

### ISSUE-08 : 7 Modeles Sans Isolation Instance (BelongsToInstance)

**Type :** Boundary / Security
**Severite :** HAUTE
**Modules Impliques :** Eshop360

#### Probleme

7 modeles Eshop360 n'ont PAS le trait `BelongsToInstance` alors qu'ils contiennent des donnees specifiques a une instance :

1. `ApiLog`
2. `EshopModuleSetting`
3. `LoanSchedule`
4. `PersistentCart`
5. `TaskComment`
6. `UserAssignment`
7. `WebhookLog` (Eshop360)

#### Pourquoi C'est Dangereux

Sans le global scope `InstanceScope`, une requete sur ces modeles retourne les donnees de TOUTES les instances. En mode shared-database, cela constitue une fuite de donnees cross-tenant.

#### Correctif Safe (OBLIGATOIRE)

Pour chaque modele concerne, ajouter le trait :

```php
use Modules\Core\Database\Traits\BelongsToInstance;

class ApiLog extends Model
{
    use BelongsToInstance;
    // ...
}
```

**Verifier au prealable** que chaque table a bien une colonne `instance_id`. Si ce n'est pas le cas, creer une migration pour l'ajouter.

---

### ISSUE-09 : Logs Marges Dupliques (MarginService)

**Type :** Side Effect / Atomicity
**Severite :** HAUTE
**Modules Impliques :** Eshop360 (MarginService)

#### Probleme

`MarginService.syncTripartiteMargin()` supprime les anciens logs puis recree les nouveaux, mais ces deux operations ne sont PAS dans une meme transaction. Deux appels concurrents peuvent supprimer et recreer simultanement, resultant en doublons.

#### Correctif Safe (OBLIGATOIRE)

**Fichier :** `Modules/Eshop360/Services/MarginService.php`

```php
public function syncTripartiteMargin(Order $order, DistributionChannel $channel)
{
    return DB::transaction(function () use ($order, $channel) {
        $order->channelMarginLogs()->delete();
        return $this->calculateTripartiteMargin($order, $channel);
    });
}
```

**Migration complementaire :**

```php
Schema::table('eshop_channel_margin_logs', function (Blueprint $table) {
    $table->unique(['order_id', 'channel_id'], 'uq_margin_order_channel');
});
```

---

### ISSUE-10 : Numero Facture Non-Atomique

**Type :** Flow / Race Condition
**Severite :** MOYENNE
**Modules Impliques :** Eshop360 (InvoiceService)

#### Probleme

`InvoiceService.generateInvoiceNumber()` genere un numero base sur un suffixe aleatoire et verifie l'unicite avec un `while (exists())` loop - classique check-then-act non-atomique. Deux factures creees au meme instant peuvent obtenir le meme numero.

#### Correctif Safe

Utiliser une sequence DB ou un compteur atomique :

```php
public function generateInvoiceNumber(): string
{
    return DB::transaction(function () {
        $prefix = setting('eshop.invoice_prefix', 'INV');
        $date = now()->format('Ymd');

        // Compteur atomique
        $last = Invoice::where('invoice_number', 'like', "{$prefix}-{$date}-%")
            ->lockForUpdate()
            ->orderByDesc('invoice_number')
            ->value('invoice_number');

        $sequence = $last ? ((int) Str::afterLast($last, '-')) + 1 : 1;
        return sprintf('%s-%s-%04d', $prefix, $date, $sequence);
    });
}
```

---

### ISSUE-11 : Configuration Queue after_commit = false

**Type :** Flow / Timing
**Severite :** MOYENNE
**Modules Impliques :** Tous (config/queue.php)

#### Probleme

La configuration par defaut du driver queue `database` a `'after_commit' => false`. Cela signifie que les jobs sont dispatches AVANT que la transaction ne soit commitee. Un job peut commencer a traiter alors que les donnees ne sont pas encore visibles en DB.

#### Pourquoi C'est Dangereux

- Job demarre, lit une commande qui n'existe pas encore (transaction pas commitee)
- Le webhook `afterCommit()` dans `WebhookService` est correct, mais d'autres dispatches ne le sont pas

#### Correctif Safe

**Fichier :** `config/queue.php`

```php
'database' => [
    'driver' => 'database',
    'after_commit' => true, // CHANGER de false a true
    // ...
],
```

---

### ISSUE-12 : Event PasswordReset Orphelin

**Type :** Side Effect / Missing Handler
**Severite :** BASSE
**Modules Impliques :** Auth (ResetPasswordController)

#### Probleme

`ResetPasswordController` dispatch l'event `Illuminate\Auth\Events\PasswordReset` mais aucun listener n'est enregistre. Les resets de mot de passe ne sont pas traces dans le journal d'audit.

#### Correctif Safe

**Fichier :** `Modules/Auth/Providers/EventServiceProvider.php`

```php
protected $listen = [
    // Existants
    \Illuminate\Auth\Events\Login::class => [LogSuccessfulLogin::class],
    \Illuminate\Auth\Events\Failed::class => [LogFailedLogin::class],

    // AJOUTER
    \Illuminate\Auth\Events\PasswordReset::class => [LogPasswordReset::class],
];
```

**Creer :** `Modules/Auth/Listeners/LogPasswordReset.php`

```php
class LogPasswordReset
{
    public function handle(PasswordReset $event): void
    {
        LoginLog::create([
            'user_id' => $event->user->id,
            'instance_id' => CurrentInstance::get()?->id,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'status' => 'password_reset',
        ]);
    }
}
```

---

## 3. POINTS DE MONITORING CRITIQUES

### Metriques a Surveiller en Production

| Metrique | Seuil Alerte | Outil |
|----------|-------------|-------|
| Stock negatif (eshop_stocks.quantity < 0) | > 0 lignes | Query SQL periodique |
| Commissions dupliquees par commande | > 1 par (order_id, employee_id) | Query SQL |
| Comptes avec solde negatif | > 0 lignes | Query SQL |
| Webhooks en echec consecutif | > 5 par webhook | Table eshop_webhooks.failure_count |
| Jobs failed_jobs table | > 10 en 1h | Laravel Horizon / monitoring |
| Caisses ouvertes par utilisateur | > 1 par user_id | Query SQL |
| Temps de traitement commande | > 5 secondes | Sentry / APM |
| Taille queue jobs | > 100 en attente | Monitoring queue |

### Queries SQL de Sante (a executer quotidiennement)

```sql
-- Stock negatif
SELECT product_id, warehouse_id, quantity
FROM eshop_stocks WHERE quantity < 0;

-- Commissions dupliquees
SELECT order_id, employee_id, COUNT(*) as cnt
FROM eshop_employee_commissions
GROUP BY order_id, employee_id HAVING cnt > 1;

-- Comptes negatifs
SELECT id, name, balance FROM eshop_accounts WHERE balance < 0;

-- Clients avec portefeuille negatif
SELECT id, name, wallet_balance FROM eshop_customers WHERE wallet_balance < 0;

-- Caisses multiples ouvertes
SELECT user_id, COUNT(*) as cnt
FROM eshop_cash_registers WHERE status = 'open'
GROUP BY user_id HAVING cnt > 1;

-- Factures dupliquees par commande
SELECT order_id, COUNT(*) as cnt
FROM eshop_invoices WHERE order_id IS NOT NULL
GROUP BY order_id HAVING cnt > 1;

-- Margin logs dupliques
SELECT order_id, channel_id, COUNT(*) as cnt
FROM eshop_channel_margin_logs
GROUP BY order_id, channel_id HAVING cnt > 1;

-- Jobs echoues dans les 24h
SELECT COUNT(*) FROM failed_jobs
WHERE failed_at > NOW() - INTERVAL 24 HOUR;
```

---

## 4. LOGS A AJOUTER AVANT GO-LIVE

### Log Structure Recommandee

Utiliser un format structure JSON pour tous les nouveaux logs :

```php
Log::channel('business')->info('order.created', [
    'instance_id' => $order->instance_id,
    'channel_id' => $order->channel_id,
    'order_id' => $order->id,
    'total' => $order->total,
    'items_count' => $order->items->count(),
    'user_id' => auth()->id(),
]);
```

### Points de Log Manquants (a ajouter)

| Fichier | Methode | Log a Ajouter |
|---------|---------|--------------|
| `StockService.php` | `adjustStock()` | `stock.adjusted` avec product_id, warehouse_id, delta, new_quantity |
| `FinanceService.php` | `withdraw()` | `account.withdrawal` avec account_id, amount, new_balance |
| `FinanceService.php` | `transfer()` | `account.transfer` avec from_id, to_id, amount, fee |
| `CashRegisterService.php` | `open()` / `close()` | `register.opened` / `register.closed` avec amounts, difference |
| `HRService.php` | `calculateCommissionForSale()` | `commission.calculated` avec order_id, employee_id, amount |
| `WebhookController.php` | `handle()` | `webhook.received` avec gateway, idempotency_key, already_processed |
| `OrderService.php` | `createFromItems()` | `order.stock_deducted` avec details par produit |

### Canal de Log Dedie (config/logging.php)

```php
'business' => [
    'driver' => 'daily',
    'path' => storage_path('logs/business.log'),
    'level' => 'info',
    'days' => 90,
],
```

---

## 5. CHECKLIST DE SANTE PRE-DEPLOIEMENT

### Avant Chaque Deploiement

- [ ] Executer les queries de sante SQL (section 3)
- [ ] Verifier que la queue est vide (`php artisan queue:size`)
- [ ] Verifier qu'aucune caisse n'est ouverte (`SELECT COUNT(*) FROM eshop_cash_registers WHERE status = 'open'`)
- [ ] Verifier les migrations pending (`php artisan migrate:status`)
- [ ] Verifier les failed_jobs (`SELECT COUNT(*) FROM failed_jobs`)
- [ ] Verifier les webhooks desactives (`SELECT * FROM eshop_webhooks WHERE failure_count >= 10`)

### Apres Deploiement

- [ ] Verifier que les routes repondent (health check)
- [ ] Verifier que le worker queue est actif
- [ ] Creer une commande de test en staging
- [ ] Verifier les logs pour erreurs dans les 5 premieres minutes
- [ ] Verifier que Sentry ne remonte pas de nouvelles exceptions

### Tests de Non-Regression Critiques

- [ ] Creer une commande POS avec paiement cash
- [ ] Creer une commande avec deduction stock
- [ ] Ouvrir et fermer une caisse
- [ ] Traiter un paiement en ligne (sandbox gateway)
- [ ] Generer une facture depuis une commande
- [ ] Changer le statut d'une commande a "complete"
- [ ] Verifier l'isolation des donnees entre canaux

---

## 6. PLAN DE DURCISSEMENT PAR PHASE

### Phase 1 : Correctifs Critiques (Semaine 1) - BLOQUANT GO-LIVE

| # | Action | Fichier(s) | Effort |
|---|--------|-----------|--------|
| 1 | Ajouter `lockForUpdate()` dans StockService | StockService.php | 1h |
| 2 | Ajouter guard idempotent commissions | HRService.php + migration | 2h |
| 3 | Ajouter `lockForUpdate()` dans FinanceService.withdraw() | FinanceService.php | 1h |
| 4 | Ajouter `lockForUpdate()` dans WalletDriver | WalletDriver.php | 1h |
| 5 | Ajouter guard idempotent webhook | WebhookController.php | 2h |
| 6 | Wrapper transaction CashRegisterService.open() | CashRegisterService.php | 1h |
| 7 | Changer `after_commit` a true | config/queue.php | 5min |

**Effort total Phase 1 : ~8h de developpement + tests**

### Phase 2 : Correctifs Hauts (Semaine 2)

| # | Action | Fichier(s) | Effort |
|---|--------|-----------|--------|
| 8 | Ajouter BelongsToInstance aux 7 modeles | 7 fichiers model + migrations | 3h |
| 9 | Augmenter $tries jobs bulk a 3 | SendBulkEmail.php, SendBulkSms.php | 30min |
| 10 | Wrapper transaction MarginService.sync | MarginService.php + migration | 2h |
| 11 | Documenter perimetre Billing vs Eshop360 | Documentation | 2h |
| 12 | Ajouter timeouts SMS drivers | 7 driver files | 1h |

**Effort total Phase 2 : ~9h**

### Phase 3 : Ameliorations Moyennes (Semaine 3-4)

| # | Action | Fichier(s) | Effort |
|---|--------|-----------|--------|
| 13 | Atomiser generation numero facture | InvoiceService.php | 2h |
| 14 | Creer listener PasswordReset | Auth/Listeners/ + EventServiceProvider | 1h |
| 15 | Ajouter logs structures business | 7 services | 3h |
| 16 | Ajouter queries monitoring SQL | Script/cron | 2h |
| 17 | Ajouter retry + circuit breaker SMS | SmsManager.php | 4h |
| 18 | Idempotency check factures recurrentes | GenerateRecurringInvoices.php | 2h |

**Effort total Phase 3 : ~14h**

### Phase 4 : Refactoring Strategique (Post Go-Live)

| # | Action | Effort |
|---|--------|--------|
| 19 | Unifier PaymentGatewayContract entre Billing et Eshop360 | 2-3j |
| 20 | Nettoyer les 258 vues orphelines dans resources/views/ | 1j |
| 21 | Extraire query logic des controllers vers services | 2-3j |
| 22 | Supprimer module InventoryX (vide) | 30min |
| 23 | Ajouter contraintes CHECK en DB (stock >= 0, balance >= 0) | 2h |
| 24 | Supprimer FeatureGate deprecated | 1h |

---

## RESUME FINAL

### Ce qui est SOLIDE et ne doit PAS etre touche

- Architecture modulaire (nwidart/laravel-modules) bien structuree
- Systeme de hooks extensible (HookRegistry, DTOs)
- Isolation multi-tenant (BelongsToInstance, InstanceScope)
- Isolation par canal (BelongsToChannel, ChannelScope)
- Systeme d'assignation utilisateur (ScopedByUserAssignment)
- OrderService wrappee dans DB::transaction (a completer avec locks)
- Webhook dispatch avec `afterCommit()` dans WebhookService

### Ce qui DOIT etre corrige avant production

1. **5 race conditions critiques** (stock, wallet, compte, caisse, commission)
2. **Manque d'idempotency** (webhooks, commissions, factures recurrentes)
3. **7 modeles sans isolation tenant**
4. **Jobs bulk sans retry**
5. **Configuration queue after_commit**

### Estimation Globale

- **Phase 1 (bloquant)** : 8h dev + 4h tests = **2 jours**
- **Phase 2 (important)** : 9h dev + 3h tests = **2 jours**
- **Phase 3 (ameliorations)** : 14h dev + 4h tests = **3 jours**
- **Total pre-go-live** : **~7 jours de travail**

---

> Document produit par analyse statique exhaustive du code source.
> Chaque correctif est non-destructif et preserve le comportement existant.
> Aucune suppression de fonctionnalite, aucun rewrite propose.
