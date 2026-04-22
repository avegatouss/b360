# ARCHITECTURE — B360

Document de référence sur l'architecture B360.

## Documents de référence

- [Cartographie fonctionnelle](docs/CARTOGRAPHIE-FONCTIONNELLE.md)
- [Audit architecture Go-Live](docs/AUDIT-ARCHITECTURE-GO-LIVE.md)
- [Audit complet B360](docs/AUDIT_COMPLET_B360.md)
- [Guide calculs Eshop360](docs/GUIDE-CALCULS-ESHOP360.md)
- [Map des dépendances modules](docs/architecture/MODULE_DEPENDENCY_MAP.md)
- [Zones protégées](docs/governance/PROTECTED_AREAS.md)

## Stack

- Laravel 12 + nwidart/laravel-modules v12
- PHP 8.2+, MySQL, Redis
- Multi-tenant via instance (middleware `InstanceMiddleware`, contexte `CurrentInstance`)
- Spatie Permission avec teams scope par `instance_id`
- Blade + Bootstrap 5 + Tabler Icons

## Modules actifs

Voir [docs/index/MODULE_INDEX.md](docs/index/MODULE_INDEX.md) pour l'état courant.

## Couches d'architecture (Deptrac)

- **L0** — Socle : Core
- **L1** — Fondations : Auth, Users, Instances, Settings, Installer
- **L2** — Services : Billing, Dashboard, Lang, Currency, ModuleManager
- **L3** — Métier : Eshop360, Demo

Voir `deptrac.yaml` pour les règles exactes.
