# ADR-005 — Stratégie d'idempotence des commissions employés (R-004)

> Architectural Decision Record. Fixe la stratégie anti-duplication sur la création de commissions RH à partir des ventes.

## Statut

**Accepté** — 2026-04-23

## Contexte

Une commission (`eshop_employee_commissions`) est calculée automatiquement quand une commande (`eshop_orders`) passe au statut `completed` et qu'un `employee_id` est renseigné. Le flow unique déclencheur est :

```
OrderService::updateStatus($order, 'completed')
    └─ HRService::calculateCommissionForSale($order)
       └─ EmployeeCommission::create(...)
```

**Scenarios de duplication** :

1. **Double-click utilisateur** sur un bouton "Marquer comme complété" → deux appels successifs à `updateStatus`.
2. **Retry réseau** d'une requête lente → deux appels concurrents.
3. **Revert + re-apply de statut** (workflow rare, ex. annulation puis ré-activation).
4. **Appel programmatique direct** à `calculateCommissionForSale` depuis un autre service/job futur.

Sans guard, chaque déclenchement crée une ligne `EmployeeCommission`, ce qui se traduit par un **sur-paiement RH** lors de la clôture mensuelle. Le modèle financier de B360 n'a pas de réconciliation qui rattraperait automatiquement ces doublons — ils persistent.

L'audit `docs/AUDIT-ARCHITECTURE-GO-LIVE.md` ISSUE-02 a identifié ce risque (R-004) avec deux recommandations :
1. Guard applicatif : `exists()` avant `create()`.
2. Contrainte DB : `UNIQUE (order_id, employee_id)`.

**État avant ce lot** :
- ✅ Guard applicatif en place dans `HRService::calculateCommissionForSale()` depuis la correction P0.
- ✅ Contrainte UNIQUE en DB via migration `2026_04_04_200001_add_p0_safety_guards`.
- ❌ **Aucun catch** de `UniqueConstraintViolationException` → une race qui gagnerait entre `exists()` et `create()` remonterait en erreur 500 client au lieu d'être absorbée silencieusement.
- ❌ **Méthode orpheline** `HRService::recordCommission()` (zéro appelant prod) sans guard — piège pour évolution future.

## Décision

Nous adoptons une **défense en profondeur à 2 couches, avec absorption gracieuse de la race** :

1. **Couche 1 — Guard applicatif fast-path.**
   `HRService::calculateCommissionForSale()` vérifie d'abord si une commission existe déjà pour la paire `(order_id, employee_id)` via `EmployeeCommission::where(...)->exists()`. Si oui, `return` immédiat. Évite 99 % des duplications (double-click, retry) sans toucher à la DB côté insert.

2. **Couche 2 — Contrainte UNIQUE SGBD (filet final).**
   `ALTER TABLE eshop_employee_commissions ADD UNIQUE (order_id, employee_id)` via la migration P0. Garantit qu'aucune ligne duplicate ne peut être persistée, même si le guard applicatif est contourné ou si deux requêtes concurrentes passent toutes deux le check avant d'insérer.

3. **Absorption de la race** via `try/catch UniqueConstraintViolationException` autour du `create()`.
   Si la contrainte DB bloque l'insertion (race gagnée par une autre requête), l'exception est **journalisée** (`Log::info`) et **swallowed** — la sémantique métier est respectée : le résultat final est bien « une commission pour cet ordre », que ce soit notre requête qui l'ait créée ou la concurrente.

**Suppression de l'attache orpheline** : `HRService::recordCommission()` (66 lignes sans guard, 0 appelant prod) est supprimée. La méthode publique à utiliser reste `calculateCommissionForSale()`. Si un besoin ultérieur apparaît (création manuelle depuis l'admin par exemple), on réécrira avec les mêmes garanties — pas de fallback dangereux disponible par erreur.

## Conséquences

### Positives

- **Zéro commission duplicate possible en production** (sauf intervention directe `INSERT` brute en DB ou désactivation manuelle de la contrainte).
- **Zéro erreur 500 client** sur la race : l'exception est absorbée, le client voit un succès (comportement attendu puisque la commission existe effectivement).
- **Observabilité** : les races absorbées sont journalisées, permettant de mesurer la fréquence réelle des courses concurrentes en prod.
- **API surface minimale** : une seule méthode publique `calculateCommissionForSale()`. La suppression de `recordCommission()` élimine un pattern non protégé.
- **Compatibilité P0SafetyGuardsTest** : le test d'idempotence existant (`test_commission_idempotente_si_ordre_completed_deux_fois`) continue de passer sans modification — le comportement observable est inchangé.
- **Rétrocompatibilité** : aucun appelant de `recordCommission()` en production (vérifié par grep), aucune régression fonctionnelle.

### Négatives / coûts

- **Couplage à Laravel `UniqueConstraintViolationException`** : le code Business dépend d'une classe Framework. Accepté (Laravel est sous-jacent au projet, pas de plan de migration hors Laravel).
- **Le test de race "gracieuse" est partiellement couvert** : en SQLite :memory: (single-threaded), on ne peut pas reproduire physiquement une race OS-level. Les tests valident la structure du code + le comportement applicatif via insertions successives. La vérité runtime sous MySQL est validée par la contrainte DB existante.
- **Suppression de `recordCommission()` impacte les PR en cours** qui pourraient référencer cette méthode. Mitigation : commit documenté, recherche globale avant suppression confirme 0 appelant prod.

### Neutres

- Les timestamps `created_at` / `updated_at` restent gérés par Laravel (pas de changement).
- Le calcul du montant (`$order->total * commission_rate / 100`) reste identique.
- La zone `PROTECTED_AREAS.md` reste L2 SENSIBLE (pas promu L1 : le filet SGBD + guard + test couvrent le risque).

## Alternatives considérées

### Alternative A : guard applicatif seul (sans contrainte DB)

S'appuyer uniquement sur le check `exists()`.

**Rejetée parce que** : la race window entre `exists()` et `create()` est exploitable en quelques millisecondes sous charge. Risque de sur-paiement RH même à faible fréquence.

### Alternative B : contrainte UNIQUE seule (sans guard applicatif)

Laisser toutes les tentatives atteindre la DB et traiter l'exception à chaque fois.

**Rejetée parce que** : impact performance négligeable mais comportement bruyant (exceptions journalisées à chaque double-click, ce qui masque les vraies races dans les logs).

### Alternative C : `firstOrCreate()` au lieu de `create()`

Utiliser `EmployeeCommission::firstOrCreate(['order_id' => …, 'employee_id' => …], [...attributs])`.

**Rejetée parce que** : `firstOrCreate` fait un `SELECT` + `INSERT` sans transaction atomique — il a la même race window que `exists() + create()`. Combiné avec la contrainte DB, ça marche, mais ne simplifie pas réellement le code. Le pattern explicite `exists() + try/catch` est plus lisible et se documente mieux.

### Alternative D : verrou pessimiste `lockForUpdate()` sur `Order`

Acquérir un lock sur la ligne Order avant de calculer la commission.

**Rejetée parce que** : overkill pour un seul INSERT idempotent. La contrainte UNIQUE + le try/catch est plus léger et résout le même problème sans contention sur Order.

## Implications opérationnelles

- **Code modifié** :
  - `Modules/Eshop360/Services/HRService.php` :
    - ajout `use Illuminate\Database\UniqueConstraintViolationException;` et `use Illuminate\Support\Facades\Log;`
    - `calculateCommissionForSale()` : ajout try/catch autour du `EmployeeCommission::create()`
    - suppression de la méthode orpheline `recordCommission()`
    - organisation des imports par ordre alphabétique (Pint)

- **Tests** :
  - `Modules/Eshop360/Tests/Feature/CommissionIdempotenceTest.php` — 3 tests nouveaux : structurel (guard + catch présents), DB-level (UNIQUE enforced), graceful (appel répété sur commission existante).
  - `Modules/Eshop360/Tests/Unit/P0SafetyGuardsTest::test_commission_idempotente_si_ordre_completed_deux_fois` — conservé, valide le fast-path applicatif.

- **Documentation** :
  - `docs/governance/PROTECTED_AREAS.md` — correction ligne 94 : le chemin `Modules/Eshop360/Services/CommissionService` n'existe pas, la logique est dans `HRService`. Corrigé.
  - `docs/memory/OPEN_RISKS.md` — R-004 déplacé en FERMÉ.
  - `CHANGELOG_ARCHITECTURAL.md` — CHG-2026-04-23-001.

- **Migration** : aucune nouvelle migration. La contrainte UNIQUE existe déjà (P0 2026-04-04).

- **Formation** : tout développeur qui ajoute un nouveau point de création de commission doit (1) passer par `HRService::calculateCommissionForSale()` ou suivre strictement le pattern `exists() + try/catch UniqueConstraintViolationException`, (2) ne jamais inserer directement dans `eshop_employee_commissions` sans ces garanties.

## Contraintes imposées au futur

1. **Aucune création de `EmployeeCommission` hors `HRService::calculateCommissionForSale()`.** Le test structurel `test_hr_service_source_uses_guard_and_unique_constraint_catch` bloque toute PR qui retirerait le guard, le catch ou l'import de l'exception.
2. **La contrainte UNIQUE `(order_id, employee_id)` doit rester en place.** Toute suppression est considérée comme régression financière.
3. **Le flow `updateStatus($order, 'completed') → calculateCommissionForSale()`** reste le seul déclencheur automatique. Tout nouvel événement (webhook, job cron) qui voudrait créer des commissions doit passer par le même service.
4. **Les méthodes orphelines ne doivent pas être ressuscitées sans guards équivalents.** `recordCommission()` a été supprimée ; si un cas d'usage réapparaît, réécrire avec le pattern complet.

## Références

- `docs/AUDIT-ARCHITECTURE-GO-LIVE.md` — ISSUE-02 (identification R-004)
- `docs/p0_correction_report.md` §2.2 — contexte P0 migration
- `docs/memory/OPEN_RISKS.md` — R-004
- `docs/governance/PROTECTED_AREAS.md` — zone L2 Commissions employés
- `Modules/Eshop360/Services/HRService.php:36` — `calculateCommissionForSale()`
- `Modules/Eshop360/Services/OrderService.php:189` — déclencheur unique
- `Modules/Eshop360/Database/Migrations/2026_04_04_200001_add_p0_safety_guards.php:20-24` — contrainte UNIQUE
- ADR-003 (même pattern try/catch UniqueConstraintViolationException pour R-002 webhooks)
- ADR-004 (même pattern défense en profondeur pour R-003 wallet)
- Commit de clôture : branche `feat/eshop360-commission-idempotence-hardening`

---

## Procédure de modification

Une ADR acceptée n'est jamais modifiée. Si la stratégie évolue (ex. migration vers événements idempotents), créer une nouvelle ADR qui remplace celle-ci, et marquer celle-ci `Remplacé par ADR-XXX`.
