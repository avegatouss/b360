# ADR-003 — Stratégie d'idempotence des webhooks (R-002)

> Architectural Decision Record. Fixe la stratégie anti-retraitement sur tous les webhooks entrants et sortants.

## Statut

**Accepté** — 2026-04-22

## Contexte

B360 traite deux flux de webhooks de paiement :

- **Billing (SaaS subscriptions)** : `POST /api/billing/webhooks/{gateway}` reçoit les événements des passerelles (Stripe, CinetPay, PayPal, InetPay, Wave, OrangeMoney, MtnMomo, ManualGateway) pour mettre à jour `Payment`, `Invoice`, `Subscription`.
- **Eshop360 (paiements métier)** : `PublicPaymentController::webhook()` reçoit les callbacks des drivers métier (les mêmes passerelles pour les commandes e-commerce) ; `WebhookService::dispatch()` émet en interne des événements vers les webhooks clients abonnés.

Tous les principaux fournisseurs de paiement **retransmettent** un webhook en cas de timeout ou de 5xx (Stripe : jusqu'à 3 retries sur 24 h ; CinetPay : mécanisme similaire). Sans idempotence, le même webhook peut déclencher plusieurs fois :

- la mise à jour de `Payment.status` et `paid_at` (drift de timestamp, pollution de logs),
- la mise à jour de `Invoice.status` (idempotente en final state mais triggers Observer multiples → notifications, entrées comptables, dispatch d'événements aval),
- le dispatch d'un webhook sortant vers un endpoint client (effet utilisateur visible).

L'audit go-live (ISSUE-04) a identifié ce risque comme **CRITIQUE** et recommande :

> « Table `webhook_events` avec contrainte UNIQUE sur `(provider, event_id)` »

Une première mitigation partielle a été livrée côté Eshop360 avec la migration P0 `2026_04_04_200001_add_p0_safety_guards` :

- ajout de la colonne `deduplication_key` sur `eshop_webhook_logs`,
- index (non-unique) sur cette colonne,
- check applicatif `WebhookLog::where('deduplication_key', …)->exists()` dans `WebhookService::dispatch()`.

Ce dispositif laisse ouverte une **race window** entre la lecture et l'insertion : deux requêtes simultanées peuvent toutes deux voir « pas de log existant », toutes deux insérer, et donc contourner la protection. Par ailleurs, le module **Billing n'avait AUCUNE protection** : pas de colonne d'idempotence, pas d'index, pas de guard applicatif.

## Décision

Nous adoptons une **défense en profondeur à trois couches, identique pour Billing et Eshop360** :

1. **Couche 1 — Contrainte UNIQUE SGBD sur la clé d'idempotence.**
   - Billing : nouvelle colonne `billing_webhook_logs.idempotency_key VARCHAR(128) NULLABLE UNIQUE` (migration `2026_04_22_100001_add_idempotency_key_to_billing_webhook_logs`).
   - Eshop360 : conversion de l'index existant sur `eshop_webhook_logs.deduplication_key` en contrainte UNIQUE (migration `2026_04_22_100002_add_unique_to_webhook_deduplication_key`).

   La **colonne reste nullable** : MySQL autorise plusieurs NULL sous UNIQUE (NULL ≠ NULL), ce qui préserve la rétrocompatibilité avec les logs pré-R-002 dont la clé n'avait pas été calculée.

2. **Couche 2 — Guard applicatif fast-path.**
   - Billing : `WebhookController::handle()` wrap la création du log dans un try/catch `UniqueConstraintViolationException`. Sur violation (= replay), le controller retourne immédiatement `200 OK (replay)` sans appeler `gatewayManager->handleWebhook()` ni `updatePaymentStatus()`.
   - Eshop360 : le check applicatif `where('deduplication_key', …)->exists()` de `WebhookService::dispatch()` est conservé en tant qu'optimisation (évite le dispatch des webhooks sortants sur une dédup triviale) ; la contrainte DB sert de filet de sécurité final.

3. **Couche 3 — Signature HMAC (déjà en place).**
   - Billing : chaque driver de passerelle vérifie la signature (`X-Stripe-Signature`, HMAC CinetPay, certificats PayPal). Inchangé.
   - Eshop360 : HMAC signing sur les webhooks sortants (`X-Webhook-Signature` via `$webhook->secret`). Inchangé.

### Construction de la clé d'idempotence (Billing)

Ordre de préférence (premier trouvé gagne), préfixé par le nom de la passerelle pour éviter toute collision inter-providers :

1. `payload['id']` — convention Stripe, format `evt_xxx`
2. `payload['event_id']` — convention générique (PayPal, Wave)
3. `payload['transaction_id']` — convention fallback
4. `payload['cpm_trans_id']` — spécifique CinetPay
5. `headers['stripe-signature']` — rare mais utilisable si id manquant
6. **Fallback** : `hash('sha256', raw_body)` — garantit qu'une clé existe toujours, même pour les webhooks manuels ou legacy

La clé finale est tronquée à 128 caractères pour tenir dans la colonne VARCHAR.

### Construction de la clé d'idempotence (Eshop360)

Définition inchangée depuis la migration P0 : `hash('sha256', "{$event}:{$entityId}")` où `entityId` est extrait de `payload['id']` / `order_id` / `invoice_id`. Si aucun identifiant exploitable n'est présent, la clé reste `null` et le log est traité sans dédup (comportement legacy préservé).

## Conséquences

### Positives

- **Race-safe par construction** : la contrainte SGBD ferme la fenêtre de race window que le check applicatif seul laisse ouverte. Même deux requêtes parallèles sur le même webhook produisent un seul log, donc un seul traitement.
- **Fast-path conservé** : le guard applicatif évite de dispatcher un webhook sortant avant de heurter la DB (latence réseau gaspillée).
- **Rétrocompatibilité** : colonne nullable → aucune migration de données requise pour les logs historiques.
- **Uniformité Billing ↔ Eshop360** : mêmes patterns, mêmes garanties, ADR commune. Facilite l'audit et la maintenance.
- **Traçabilité** : le log webhook initial reste persisté même sur replay (le replay retourne 200 OK sans nouveau log). L'identifiant gateway, la clé d'idempotence et le payload sont auditables.

### Négatives / coûts

- **Migration Billing modifie une table existante** : ajout de colonne + UNIQUE. Sur MySQL prod avec volume élevé, l'ALTER TABLE peut être long (copy-then-swap). Pas un problème attendu ici (table de logs, volumétrie raisonnable) mais à monitorer lors du déploiement.
- **Guard SGBD remonte une exception typée** (`UniqueConstraintViolationException`) — le controller doit la traiter explicitement, couplage avec le driver DB. Accepté : l'exception est stable et portée par Laravel.
- **`GatewayManager` dé-finalisé** pour permettre le mocking dans les tests. Impact négligeable : la classe reste conçue comme service singleton par DI, non-extensible en pratique.
- **Dépendance au calcul de clé** : un gateway qui changerait son format d'event_id (ex. Stripe migrant de `evt_xxx` vers un autre schéma) produirait des clés différentes → pas de protection contre les replays sur la période de migration. Mitigation : le fallback hash-du-body reste toujours opérant, même si sub-optimal.

### Neutres

- Les tests continuent de fonctionner sous SQLite (UNIQUE sur NULL nullable est supportée).
- La taille des logs augmente légèrement (colonne supplémentaire 128 chars max).

## Alternatives considérées

### Alternative A : guard applicatif seul (sans contrainte DB)

Approche de la migration P0 originale : check `where(...)->exists()` avant insert, sans UNIQUE côté DB.

**Rejetée parce que** : la race window entre la lecture et l'insertion est exploitable dès que deux requêtes arrivent dans la même fenêtre (< 50 ms typique). Sous forte charge (bursts Stripe lors d'un incident réseau), cette race est observable et produit des duplicates.

### Alternative B : table `webhook_events` séparée avec `(provider, event_id)` UNIQUE

Conforme à la lettre de la recommandation d'audit : une table dédiée juste pour l'idempotence, disjointe de `webhook_logs`.

**Rejetée parce que** : introduit une table de plus pour un même concept (événement webhook). `billing_webhook_logs` contenait déjà le payload et les métadonnées ; y ajouter une colonne est plus simple qu'ajouter une table satellite. La sémantique `(provider, event_id)` est conservée via la concaténation dans `idempotency_key`. Même raisonnement pour Eshop360 où la colonne existait déjà.

### Alternative C : queue idempotente (AWS SQS FIFO, Redis consumer group)

Pousser chaque webhook dans une queue qui dédoublonne par message-id et consomme en sérialisé.

**Rejetée parce que** : opérationnellement lourd (dépendance externe, monitoring, backpressure). Pas nécessaire pour la charge actuelle (SaaS petite/moyenne échelle). Un guard DB + fast-path applicatif couvre largement le besoin, sans nouveau composant.

### Alternative D : verrou applicatif via `Cache::lock()` (Redis)

Prendre un lock distribué court sur une clé dérivée du webhook avant traitement.

**Rejetée parce que** : ajoute une dépendance Redis obligatoire (actuellement optionnelle), ne résiste pas aux redémarrages (le lock expire), et laisse quand même une race théorique si le traitement dépasse la TTL. La garantie DB est strictement meilleure.

## Implications opérationnelles

- **Code** :
  - `Modules/Billing/Http/Controllers/WebhookController.php` : ajout du calcul de clé + guard try/catch
  - `Modules/Billing/Models/WebhookLog.php` : ajout de `idempotency_key` dans `$fillable`
  - `Modules/Billing/Services/GatewayManager.php` : retrait du `final` (pour mocking en test)
  - `Modules/Billing/Database/Migrations/2026_04_22_100001_*` + `Modules/Eshop360/Database/Migrations/2026_04_22_100002_*`
- **Tests** :
  - `Modules/Billing/Tests/Feature/WebhookIdempotenceTest.php` — 5 tests : replay, fallback hash, logs distincts, webhook malformé, contrainte DB.
  - `Modules/Eshop360/Tests/Feature/WebhookServiceIdempotenceTest.php` — 2 tests : UNIQUE enforcement, NULLs coexistent.
  - `Modules/Eshop360/Tests/Unit/P0SafetyGuardsTest.php::test_webhook_duplique_est_ignore` — conservé, teste le fast-path applicatif.
- **Documentation** :
  - `docs/governance/PROTECTED_AREAS.md` — zone L1 « Billing webhooks » inchangée.
  - `docs/memory/OPEN_RISKS.md` — R-002 fermé.
- **Migration** : deux migrations additives. Le déploiement en prod nécessite le `php artisan migrate`. Pas de downtime attendu (ajout colonne nullable + UNIQUE peut être fait en ligne sur MySQL 8+).
- **Formation** : tout développeur qui touche à un webhook handler doit (1) persister un `WebhookLog` avec une `idempotency_key` calculée avant tout side effect, (2) traiter `UniqueConstraintViolationException` comme un replay silencieux (200 OK), (3) ne jamais bypasser le guard « pour simplifier ».

## Contraintes imposées au futur

1. **Toute nouvelle route webhook dans B360 doit persister un log avec `idempotency_key` UNIQUE avant tout side effect métier.** Les nouveaux modules doivent suivre le pattern `Modules/Billing/Http/Controllers/WebhookController::handle()`.
2. **La contrainte UNIQUE sur `idempotency_key` / `deduplication_key` ne doit jamais être retirée.** Toute suppression est considérée comme régression L1.
3. **Le guard `try/catch UniqueConstraintViolationException` doit rester en place dans `WebhookController::handle()`.** Un test structurel dédié pourrait être ajouté si les regressions deviennent fréquentes.
4. **Le calcul de `idempotency_key` doit inclure le nom de la passerelle en préfixe** pour éviter toute collision inter-providers (deux gateways utilisant le même format d'id).
5. **Aucune suppression manuelle de `WebhookLog` ne doit être possible depuis l'UI sans audit trail.** Le log d'un webhook est la preuve de son traitement unique.

## Références

- `docs/AUDIT-ARCHITECTURE-GO-LIVE.md` — ISSUE-04 (identification du risque)
- `docs/memory/OPEN_RISKS.md` — R-002
- `docs/governance/PROTECTED_AREAS.md` — zone L1 Billing webhooks
- `Modules/Billing/Http/Controllers/WebhookController.php` — guard idempotence
- `Modules/Billing/Database/Migrations/2026_04_22_100001_add_idempotency_key_to_billing_webhook_logs.php`
- `Modules/Eshop360/Database/Migrations/2026_04_22_100002_add_unique_to_webhook_deduplication_key.php`
- `Modules/Eshop360/Services/WebhookService.php` — fast-path applicatif
- ADR-002 (même pattern « lock + transaction » pour R-001 stock)
- Commit de clôture : branche `feat/billing-webhook-idempotence`

---

## Procédure de modification

Une ADR acceptée n'est jamais modifiée. Si la stratégie d'idempotence évolue (ex. migration vers une queue dédiée), créer une nouvelle ADR qui remplace celle-ci, et marquer celle-ci `Remplacé par ADR-XXX`.
