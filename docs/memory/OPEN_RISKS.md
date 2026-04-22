# OPEN_RISKS — B360

> Risques techniques connus, suivi vivant. Mise à jour : **2026-04-22 14:57:35    **

---

## CRITIQUE

### R-001 — Race condition sur le stock

- **Source** : docs/AUDIT-ARCHITECTURE-GO-LIVE.md ISSUE-01
- **Module** : Eshop360 (StockService, OrderService)
- **Impact** : stock négatif, commandes non honorables
- **Statut** : à corriger
- **Mitigation prévue** : `lockForUpdate()` + transaction sur tous les `adjustStock()`
- **Tests à ajouter** : tests de concurrence (2 requêtes simultanées)

### R-002 — Webhook paiement traité deux fois

- **Source** : ISSUE-04
- **Module** : Billing, Eshop360 (paiements)
- **Impact** : double comptabilisation
- **Statut** : à corriger
- **Mitigation prévue** : table `webhook_events` avec contrainte unique sur (provider, event_id)

### R-003 — Solde portefeuille / compte négatif

- **Source** : ISSUE-03
- **Module** : Eshop360 (Wallet, CustomerAccount)
- **Impact** : crédit non autorisé, perte financière
- **Statut** : à corriger
- **Mitigation prévue** : contrainte CHECK + lock pessimiste

### R-004 — Commissions employés dupliquées

- **Source** : ISSUE-02
- **Module** : Eshop360 (HR, EmployeeCommission)
- **Impact** : sur-paiement RH
- **Statut** : à investiguer


## MAJEUR

### R-101 — Eshop360 monolithique

- **Source** : cartographie 2026-04-04
- **Constat** : 83 modèles, 78 contrôleurs, 128 migrations
- **Impact** : maintenance difficile, couplage fort
- **Plan** : extraction en sous-domaines (Catalog, Pricing, Inventory, Sales, Finance, CRM, Channel)
- **Statut** : roadmap définie, exécution à planifier

### R-102 — Double système FeatureGate / FeatureRegistry

- **Module** : Eshop360 (FeatureGate deprecated) vs Billing (FeatureRegistry actif)
- **Impact** : confusion, double maintenance
- **Plan** : migrer tous les appels vers FeatureRegistry, supprimer FeatureGate
- **Statut** : à programmer

### R-103 — Double système Codifarm / DistributionChannel

- **Tables** : `eshop_codifarm_margin_config` (legacy SAPHIR) vs `eshop_distribution_channels` (actif)
- **Impact** : double écriture de logs, source de vérité ambiguë
- **Plan** : migration documentée dans `docs/Ins/b360_evolution_strategy.md` §2.3

### R-104 — Trait BelongsToInstance dupliqué

- **Localisations** : `app/Models/Concerns/BelongsToInstance` ET `Modules/Core/Database/Traits/BelongsToInstance`
- **Impact** : risque divergence comportement
- **Plan** : unification sur `Modules/Core/Database/Traits/BelongsToInstance`
- **Lot pilote recommandé** : oui (faible risque, gain immédiat)

## MOYEN

### R-201 — InventoryX squelette non chargé

- **Constat** : dossier visible mais pas de `module.json` ni provider chargé
- **Plan** : décider — finaliser ou supprimer

### R-202 — Numéro facture non atomique

- **Source** : ISSUE-10 audit go-live
- **Plan** : utiliser séquence DB ou advisory lock

## FAIBLE

### R-301 — Event PasswordReset orphelin

- **Source** : ISSUE-12 audit go-live
- **Plan** : ajouter listener d'audit

---

## Convention

Chaque risque a :
- ID stable (R-XXX)
- Source (audit, ticket, observation)
- Module(s) concerné(s)
- Impact business
- Statut (ouvert / en cours / mitigé / fermé)
- Plan de mitigation
- Lien vers le PR de résolution si en cours
