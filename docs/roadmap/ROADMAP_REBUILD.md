# B360 — Roadmap

> Roadmap consolidée référencée depuis [`context/PROJECT_DIGEST.md`](../context/PROJECT_DIGEST.md).
> Mise à jour : 2026-05-08.
> Statut : **Vivant** — à mettre à jour à chaque sprint terminé ou décision majeure.

## Principe

Cette roadmap est la version courte. Les détails sont dans :
- [`PLAN_ACTION_DOCUMENTAIRE_2026-05-06.md`](../PLAN_ACTION_DOCUMENTAIRE_2026-05-06.md) §P3 pour les chantiers applicatifs identifiés
- [`Ins/b360_evolution_strategy.md`](../Ins/b360_evolution_strategy.md) pour la stratégie d'évolution long terme
- [`adr/`](../adr/) pour les décisions structurantes

## État au 2026-05-08

### Terminé
- ✅ R-101 — découpage Eshop360 (13/13 sous-domaines, ADR-020, fermé 2026-05-05)
- ✅ R-001..R-004 — risques P0 (stock, webhooks, wallet, commissions)
- ✅ R-102..R-104 — FeatureGate, BelongsToInstance, Codifarm
- ✅ R-201, R-202, R-301 — InventoryX squelette, atomicité factures, audit PasswordReset
- ✅ Pricing Engine v2 implémenté
- ✅ Multi-currency phases 1+2 implémentées (module Currency standalone)

### En cours (sprint pré-Menuiserie360)
- 🟡 Lot 1 — sécurité backups .env
- 🟡 Lot 2 — rebase doc post-R-101 (STATUS, README, dates memory, taxonomy, bannières archive, cette roadmap)
- 🟡 Lot 3 — ADR-021 contrats inter-modules pour modules métier futurs
- 🟡 Lot 4 — rebase spec Menuiserie360 v1.0 → v1.1
- Plan détaillé : [`superpowers/plans/2026-05-08-pre-menuiserie360-sprint.md`](../superpowers/plans/2026-05-08-pre-menuiserie360-sprint.md)

### Décision en attente après ce sprint
- ⏸️ **Démarrage Menuiserie360** — décision humaine après lecture spec v1.1 rebasée.
- ⏸️ **Démarrage extraction CCC360** — décision en attente depuis 2026-04-06 (cf. RECENT_DECISIONS).

### Chantiers candidats (post-Menuiserie360 ou parallèle)
- Tightening deptrac post-R-101 — transformer 198 `skip_violations` en rulesets explicites (cf. plan d'action §P3 chantier A)
- Migration morph keys legacy FQN → short names (cf. ADR-020 §contraintes ; recommandation : ne pas faire tant qu'il n'y a pas de douleur concrète)
- Industrialisation Eshop360 (gating premium, POS multi, portail grossiste, API documentée — cf. plan d'action §P3 chantier C)
- Multi-currency phase 3 dans Eshop360 (colonnes `currency_code`/`exchange_rate` sur orders/invoices/payments — pas bloquant Menuiserie360)
- Money objects (avant phase 3 multi-currency)

### Décisions stratégiques en attente
| Décision | Recommandation | Source |
|---|---|---|
| API taux de change : ajouter fallback ? | OUI (open.er-api.com seul = fragile) | audit_comparatif §5.5 |
| Database-per-instance : activer ou abandonner ? | ABANDONNER OU CORRIGER (S-7) | audit_comparatif §5.5 |
| Money objects vs float | OUI moneyphp/money avant phase 3 | audit_comparatif §5.5 |
| Billing dépendance obligatoire ? | À trancher selon usage | audit_comparatif §5.5 |
