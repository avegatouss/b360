# PROJECT DIGEST — B360

> Compression contextuelle pour les IA. Lis ce fichier en premier.
> Mise à jour : **2026-04-22 20:08:31    **

---

## En une phrase

B360 est une plateforme SaaS multi-tenant Laravel 12 modulaire (nwidart/laravel-modules), avec un noyau plateforme stable (12 modules système) et un module métier monolithique Eshop360 en cours de découpage progressif.

## Stack

- **Framework** : Laravel 12, PHP 8.2+
- **Modularité** : nwidart/laravel-modules v12
- **Multi-tenant** : Instance-based (`/i/{slug}/`), Spatie Permission avec teams
- **Front** : Blade + Bootstrap 5 + Tabler Icons (jQuery minimal)
- **Tests** : Pest / PHPUnit
- **Qualité** : Pint, PHPStan/Larastan, Deptrac, Rector

## État (2026-04-22)

- **Modules** : 13 actifs (Auth,Billing,Core,Currency,Dashboard,Demo,Eshop360,Installer,Instances,Lang,ModuleManager,Settings,Users)
- **Tests** : | Tests | 617 passed / 0 failed / 3 skipped (1590 assertions, 369 s) | ⬆️ +9 vs 12:15 (3 CashRegister + 6 MultiCurrency) |
- **Migrations** : 184 Ran / 0 Pending

## Architecture macro

```
Couche système     : Installer, Core, Auth, Users, Instances, Settings, ModuleManager
Couche transverse  : Billing, Lang, Currency, Dashboard, Demo
Couche métier      : Eshop360 (monolithique, en découpage)
Couche future      : InventoryX (squelette), Menuiserie360 (à concevoir)
```

## Mécanisme d'extension central

**HookRegistry** dans Core permet à chaque module de s'enregistrer dynamiquement pour :
`menu`, `widgets`, `settings_groups`, `permissions`, `features`, `payment_gateways`, `demo_providers`, `notification_types`.

## Risques critiques actifs

1. Race condition stock (R-001)
2. Webhook paiement double (R-002)
3. Solde négatif portefeuille (R-003)
4. Commissions RH dupliquées (R-004)
5. Eshop360 monolithique (R-101)

Détails : `docs/memory/OPEN_RISKS.md`.

## Zones protégées (modification = double review)

- Auth flow (login, 2FA, IP rules)
- Tenancy (InstanceManager, InstanceResolver, BelongsToInstance)
- Permissions (Spatie + policies métier)
- Pricing engine
- StockService (race conditions)
- CashRegisterService
- Billing webhooks
- AuditLog

Détails : `docs/governance/PROTECTED_AREAS.md`.

## Documents source de vérité

| Sujet | Fichier |
|---|---|
| État instantané | `docs/STATUS.md` |
| Cartographie modules | `docs/cartographie/` |
| Audit fonctionnel | `docs/AUDIT_COMPLET_B360.md` |
| Audit pré go-live | `docs/AUDIT-ARCHITECTURE-GO-LIVE.md` |
| Stratégie évolution | `docs/Ins/b360_evolution_strategy.md` |
| Calculs Eshop360 | `docs/GUIDE-CALCULS-ESHOP360.md` |
| Architecture cible | `ARCHITECTURE.md` |
| Roadmap | `docs/roadmap/ROADMAP_REBUILD.md` |

## Pour une IA qui démarre une intervention

Lis dans cet ordre :
1. Ce fichier (PROJECT_DIGEST.md)
2. `docs/memory/CURRENT_STATE.md`
3. `docs/memory/OPEN_RISKS.md` (filtré sur le module concerné)
4. `docs/memory/RECENT_DECISIONS.md`
5. `docs/governance/PROTECTED_AREAS.md` si tu touches au backend critique
6. ADR pertinents dans `docs/adr/`
7. Code seulement après ces lectures

Coût attendu : 2 000 à 4 000 tokens pour avoir le contexte global, vs 50 000+ pour relire le code.
