# IMPACT ANALYSIS — <nom du lot>

> Template à utiliser dans toute PR touchant un module métier ou une zone L1/L2.
> Snippet VSCode disponible : `b360-impact`.

**Date** : YYYY-MM-DD
**Auteur** : <Claude / Codex / humain>
**Branche** : `<feat/scope-subject>`
**Issue/Ticket** : #<numéro>

---

## Changement demandé (1-3 lignes)

<description précise du périmètre>

---

## Modules touchés directement

<table fichiers + module>

| Module | Fichiers | Type de modif |
|---|---|---|
| Eshop360 | Modules/Eshop360/Services/StockService.php | refactor |

## Modules touchés indirectement

(via événement, contrat, table partagée, hook)

| Module | Comment | Impact |
|---|---|---|
| Billing | Consomme l'event StockDecremented | nouvelle gestion d'erreur |

---

## Contrats API impactés

- [ ] Aucun
- [ ] Endpoints listés ci-dessous (avec breaking change ?)

| Endpoint | Méthode | Type d'impact (additif / breaking) |
|---|---|---|
| /api/v1/eshop/stocks | POST | additif (nouveau champ optionnel) |

---

## Permissions impactées

- [ ] Aucune
- [ ] Listées ci-dessous (Spatie keys)

| Permission | Action | Module |
|---|---|---|
| eshop.stock.adjust | nouvelle | Eshop360 |

→ Pense à régénérer `make audit-permissions`.

---

## Événements impactés

- [ ] Aucun
- [ ] Listés ci-dessous

| Événement | Publié par | Consommé par | Type |
|---|---|---|---|
| StockDecremented | StockService | (à venir : Billing) | nouveau |

→ Pense à régénérer `make audit-events`.

---

## Tables impactées

- [ ] Aucune
- [ ] Listées ci-dessous (avec type de modif)

| Table | Modification | Réversible ? | Migration |
|---|---|---|---|
| eshop_stocks | + colonne `last_locked_at` (additif) | oui | 2026_04_19_000001_add_last_locked_at_to_eshop_stocks |

→ Pense à régénérer `make audit-db`.

---

## Jobs / queues impactés

- [ ] Aucun
- [ ] Listés ci-dessous

| Job | Modification | Idempotent ? | Retry ? |
|---|---|---|---|
| ProcessStockMovement | nouveau | oui | 3 |

---

## Logs / audit

- [ ] Aucun ajout / modification
- [ ] Actions à journaliser :

| Action | Niveau | Audit log ? |
|---|---|---|
| stock_decrement | INFO | oui |
| stock_negative_attempt | WARN | oui |

---

## Tests à exécuter

- [ ] `php artisan test Modules/Eshop360/Tests --parallel`
- [ ] `php artisan test --filter=Stock`
- [ ] Test de concurrence : `php artisan test --filter=StockConcurrencyTest`
- [ ] Test multi-tenant : `php artisan test --filter=StockTenantIsolationTest`

---

## Risques de régression

| Niveau | Risque | Mitigation |
|---|---|---|
| Moyen | Performance impact du lockForUpdate | benchmark sur scénario haute charge prévu en lot suivant |
| Faible | Compatibilité avec POS offline | non concerné (POS écrit toujours via le service) |

---

## Documentation à mettre à jour

- [ ] `docs/memory/CURRENT_STATE.md`
- [ ] `docs/memory/RECENT_DECISIONS.md` (décision : `lockForUpdate` standard sur stock)
- [ ] `docs/memory/OPEN_RISKS.md` (R-001 → résolu)
- [ ] `CHANGELOG_ARCHITECTURAL.md` (si decision structurelle)
- [ ] `docs/index/EVENT_INDEX.md` (nouvel event)
- [ ] `docs/index/DB_INDEX.md` (nouvelle colonne)
- [ ] ADR à créer ? <oui : ADR-XXX-stock-locking-pattern.md / non>

---

## Zones protégées touchées

- [ ] Aucune
- [ ] L1 — CRITIQUE : `Modules/Eshop360/Services/StockService.php`
- [ ] L2 — SENSIBLE : `Modules/Eshop360/Models/Stock.php`

→ Si L1 : procédure renforcée obligatoire (cf `docs/governance/PROTECTED_AREAS.md`).

---

## Plan de rollback

En cas d'incident en production :

1. Rollback de la migration : `php artisan migrate:rollback --step=1`
2. Revert du commit : `git revert <sha>`
3. Re-déploiement
4. Vérification fonctionnelle : <scénario>

---

## Validation finale

- [ ] Pipeline qualité 100% vert (`make qa`)
- [ ] Tests étendus passants (concurrence, multi-tenant si L1)
- [ ] Mémoire à jour
- [ ] PR review effectuée par Claude Code
- [ ] Review humaine si zone L1
