# ADR-018 — Extraction du sous-domaine HR d'Eshop360 (R-101 S10)

> Architectural Decision Record. Dixième sous-lot d'extraction du monolithe Eshop360 (R-101 S10).

## Statut

**Accepté** — 2026-04-24

## Contexte

Le sous-lot S10 extrait les 4 modèles du sous-domaine HR (Human Resources) : gestion des employés, commissions, salaires, pointages.

Volume et complexité modestes — 4 modèles, peu de dépendances externes.

## Décision

### Ce qui est extrait

- `Employee` : fiche employé (identité, contrat).
- `EmployeeCommission` : commission liée à une commande (pointe vers Order).
- `EmployeeSalary` : salaire.
- `Attendance` : pointage / présence.

### `$morphClass` pinning

Appliqué aux 4 canonicals (EmployeeCommission est potentiel morph target si audit logs ou reporting pointent dessus).

### Imports cross-sous-domaine

- `EmployeeCommission` +1 (Order, via EshopSales).
- Les 3 autres sont feuilles.

### Ruleset deptrac `EshopHR`

Passée de permissive → socles + `EshopSales` + Eshop360 transitoire.

## Conséquences

### Positives

- **10ᵉ sous-domaine délimité** sur 13 (77 %).
- Pattern bulk PowerShell rodé.
- 0 régression attendue.

### Négatives / coûts

- Dépendance `EshopHR → Eshop360` transitoire pour BelongsToChannel.

## Validation

- `pest` : 659 passed attendu.
- `phpstan` : baseline 3686 (+4 morphClass missingType).
- `deptrac` : 0 violations.

## Références

- ADR-008 — Stratégie.
- ADR-009..ADR-017 — S1..S9.
- Commit : branche `refactor/eshop360-s10-hr-extraction`.
