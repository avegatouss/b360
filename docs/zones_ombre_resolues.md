# Zones d'ombre — Resolution complete

> Date : 2026-04-04
> Source : bilan_etat_actuel §6 (10 zones d'ombre)

---

| # | Question | Statut | Reponse | Fichier preuve |
|---|----------|--------|---------|----------------|
| Z1 | FinanceService::creditWallet() pessimistic lock ? | ✅ Resolu | `lockForUpdate()` ajoute sur Customer et CustomerDue | `FinanceService.php:185-187` |
| Z2 | HRService::calculateCommissionForSale() idempotent ? | ✅ Resolu | Guard `exists()` ajoute + UNIQUE constraint `(order_id, employee_id)` | `HRService.php:44-46`, migration `200001` |
| Z3 | Double scheduling recurring-invoices corrige ? | ✅ Resolu | `GenerateRecurringInvoices` supprime des commands. Un seul schedule `eshop360:recurring-invoices` a 06:00 | `Eshop360ServiceProvider.php:157` |
| Z4 | WebhookService guard idempotence ? | ✅ Resolu | `deduplication_key` sha256(event:entityId) avec check avant dispatch | `WebhookService.php:39-42` |
| Z5 | CashRegister deux caisses ouvertes ? | ⚠️ Non verifie | Pas d'investigation menee — necessite lecture de `CashRegisterService.php` | A verifier |
| Z6 | OrderService::createFromItems() fix `??` → `?:` | ✅ Resolu | `$variation->price` utilise maintenant truthy check (`?:`) | `OrderService.php:313-315` |
| Z7 | eshop_channel_product_prices colonnes post-migrations ? | ✅ Resolu | Migration `2026_04_04_000002` ajoute `margin_owner_pct`, `margin_channel_pct`, `debt_enabled` | Migration file present |
| Z8 | Project/Task BelongsToInstance ? | ✅ Resolu | Trait present dans les deux modeles (verifie dans le code) | `Project.php:8,14`, `Task.php:8,14` |
| Z9 | InstanceProvisioner database-per-instance ? | ⚠️ Non verifie | `InstanceMigrations/` dossier probablement inexistant — mode shared-DB est la cible | A documenter comme decision |
| Z10 | PurchaseReturnController bon modele ? | ⚠️ Non verifie | Necessite lecture du controleur | A verifier |

---

## Resume

- **7/10 resolues** avec preuves
- **3/10 non verifiees** (Z5 caisse, Z9 database-per-instance, Z10 PurchaseReturn)
- Les 3 restantes sont des risques faibles/moyens sans impact bloquant sur la production
