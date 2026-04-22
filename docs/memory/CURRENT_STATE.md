# CURRENT_STATE — B360

> Fichier auto-généré par `make memory-refresh`. Dernière mise à jour : **2026-04-22 19:08:13    **
> Branche analysée : `refactor/core-unify-belongs-to-instance`
> HEAD : `fd31585`

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
| Eshop360 | actif | monolithique — découpage en cours | oui | 83 modèles / 78 contrôleurs / 128 migrations — candidat extraction |
| Installer | actif | à confirmer | oui | — |
| Instances | actif | stable | oui | socle plateforme |
| Lang | actif | à confirmer | oui | — |
| ModuleManager | actif | à confirmer | oui | — |
| Settings | actif | stable | oui | socle plateforme |
| Users | actif | stable | oui | socle plateforme |

## En cours

- (vide — à remplir au fil des lots)

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
