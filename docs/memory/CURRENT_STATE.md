# CURRENT_STATE — B360

> Fichier auto-généré par `make memory-refresh`. Dernière régénération automatique : **2026-04-22 20:08:31**. Faits ajoutés manuellement post-R-101 jusqu'au **2026-05-05** (ADR-020).
> Branche analysée : `chore/pack-suspense-fixes`
> HEAD : `1bdbf0b`

---

## Snapshot

| Indicateur | Valeur |
|---|---|
| Modules détectés | 13 |
| Tests | | Tests | 617 passed / 0 failed / 3 skipped (1590 assertions, 369 s) | ⬆️ +9 vs 12:15 (3 CashRegister + 6 MultiCurrency) | |
| Migrations | 184 Ran / 0 Pending |
| Date du snapshot | 2026-04-22 |

## Modules — état détaillé

| Module | Statut | Stabilité | Tests présents | Notes |
|---|---|---|---|---|
| Auth | actif | stable | oui | socle plateforme |
| Billing | actif | stable | oui | socle plateforme |
| Core | actif | stable | oui | socle plateforme |
| Currency | actif | à confirmer | oui | — |
| Dashboard | actif | stable | oui | socle plateforme |
| Demo | actif | à confirmer | non | — |
| Eshop360 | actif | stable — découpage R-101 terminé (S0..S12 livrés 2026-05-05, ADR-020) | oui | 88 modèles canoniques sous `Domain/<Sub>/Models/` + 2 non-stubs retenus dans `Models/` (EshopModuleSetting, UserAssignment) ; morph map central dans le provider |
| Installer | actif | à confirmer | oui | — |
| Instances | actif | stable | oui | socle plateforme |
| Lang | actif | à confirmer | oui | — |
| ModuleManager | actif | à confirmer | oui | — |
| Settings | actif | stable | oui | socle plateforme |
| Users | actif | stable | oui | socle plateforme |
| Menuiserie360 | actif | stable — V2 S1 livré (ADR-023 accepté, BC-Clients autonome, R-403 fermé) | oui | Module L3 autonome ; aucun import `Modules\Eshop360\*` |

## En cours

- Menuiserie360 V2 : S1 autonomie BC-Clients livré ; prochains sous-lots S2..S10 à prioriser selon ADR-024/025.

## Prochaines actions priorisées

- (vide — voir docs/roadmap/ROADMAP_REBUILD.md)

## Zones sensibles actuelles

Voir `docs/governance/PROTECTED_AREAS.md` pour la liste complète et les règles.

## Risques ouverts

Voir `docs/memory/OPEN_RISKS.md`.

## Décisions récentes

Voir `docs/memory/RECENT_DECISIONS.md`.

---

> **Règle** : ce fichier doit être à jour à chaque PR mergée sur `develop`. Le hook `pre-push` vérifie sa fraîcheur.
