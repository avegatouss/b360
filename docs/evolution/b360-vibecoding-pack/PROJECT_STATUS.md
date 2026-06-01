# PROJECT_STATUS — B360

> Source de vérité courante de l'état du projet. Régénéré automatiquement par `make memory-refresh`.
> Pour le détail historique, voir `docs/STATUS.md` (existant) et `docs/memory/CURRENT_STATE.md`.

---

## Snapshot rapide

| Indicateur | Valeur |
|---|---|
| Date du snapshot | (généré par make memory-refresh) |
| Branche | (généré) |
| Modules actifs | 13 |
| Tests | 617 passed / 0 failed / 3 skipped (au 2026-04-06) |
| Migrations | 184 Ran / 0 Pending |
| Dette technique majeure | Eshop360 monolithique (R-101) |

## Phases du projet

- [x] **Phase 0 — Stabilisation** : 617 tests verts, plateforme go-live possible
- [ ] **Phase 1 — Pack vibecoding** : en cours d'installation (CHG-2026-04-19-001)
- [ ] **Phase 2 — Lot pilote** : unification BelongsToInstance (R-104)
- [ ] **Phase 3 — Résolution risques L1** : R-001 (stock), R-002 (webhooks), R-003 (wallet), R-004 (commissions)
- [ ] **Phase 4 — Découpage Eshop360** : extraction Catalog / Channel
- [ ] **Phase 5 — Découpage Eshop360 (suite)** : Inventory / Sales
- [ ] **Phase 6 — Découpage Eshop360 (fin)** : Finance / CRM / HR
- [ ] **Phase 7 — Découplage front/back** : API stable + premier front externe pilote
- [ ] **Phase 8 — Industrialisation modules futurs** : Menuiserie360 ou autre

## Risques actifs majeurs

- R-001 : race condition stock — CRITIQUE
- R-002 : webhook paiement double — CRITIQUE
- R-003 : solde négatif portefeuille — CRITIQUE
- R-004 : commissions employés dupliquées — CRITIQUE
- R-101 : Eshop360 monolithique — MAJEUR
- R-102 : double FeatureGate / FeatureRegistry — MAJEUR
- R-103 : double Codifarm / DistributionChannel — MAJEUR
- R-104 : trait BelongsToInstance dupliqué — MAJEUR

Détails : `docs/memory/OPEN_RISKS.md`.

## Lots en cours

(rien — début de la phase pack)

## Prochaines actions priorisées

1. Installer le pack vibecoding (en cours)
2. Lot pilote : unifier BelongsToInstance (R-104) — faible risque, valide le pipeline
3. Lot critique : protéger StockService avec lockForUpdate (R-001)
4. Lot critique : idempotence webhook paiements (R-002)
5. Démarrer le découpage Eshop360 (Catalog en premier)

## Documents source

- `docs/STATUS.md` — status board historique détaillé
- `docs/memory/CURRENT_STATE.md` — état machine-readable
- `docs/cartographie/` — cartographie modules
- `docs/AUDIT_COMPLET_B360.md` — audit fonctionnel complet
- `docs/AUDIT-ARCHITECTURE-GO-LIVE.md` — issues pré-prod
- `docs/Ins/b360_evolution_strategy.md` — stratégie d'évolution
- `ARCHITECTURE.md` — architecture cible

---

> Pour rafraîchir : `make memory-refresh`
> Pour voir la branche courante : `make branch-info`
