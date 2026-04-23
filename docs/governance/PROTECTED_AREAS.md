# PROTECTED_AREAS — B360

> Zones du code dont la modification doit suivre une procédure renforcée.
> Le hook `post-commit` détecte automatiquement les commits qui touchent à ces zones.

---

## Niveaux

- **L1 — CRITIQUE** : modification = analyse d'impact obligatoire + double review (Claude + Codex) + tests étendus + ADR si structurel
- **L2 — SENSIBLE** : modification = revue par un humain + tests ciblés étendus
- **L3 — STANDARD** : pipeline qualité normal

---

## L1 — CRITIQUE

### Multi-tenant et isolation des données

- `Modules/Core/Database/Traits/BelongsToInstance.php`
- `Modules/Core/Services/InstanceManager.php`
- `Modules/Core/Services/InstanceResolver.php`
- `Modules/Core/Http/Middleware/InstanceMiddleware.php`
- `Modules/Core/Http/Middleware/BindInstanceFromRoute.php`
- `Modules/Core/Http/Middleware/EnsureInstanceResolved.php`
- `Modules/Core/Http/Middleware/SetSpatieTeamContextFromInstance.php`
- `app/Instances/`
- `app/Models/User.php`

**Risques si dégradé** : fuite de données cross-tenant, contournement de l'isolation, corruption d'audit trail.

### Authentification

- `Modules/Auth/` (tout)
- 2FA, login flows, IP rules, lockscreen, sessions

**Risques si dégradé** : bypass d'authentification, élévation de privilèges.

### Permissions et autorisations

- Tous les `Policy` Laravel
- `Modules/Users/Services/RoleService`
- HookRegistry section `permissions`
- Configuration Spatie Permission

**Risques si dégradé** : élévation de privilèges, accès non autorisé aux données.

### Pricing engine

- `Modules/Eshop360/Services/Pricing/`
- `Modules/Eshop360/Models/ChannelProductPrice.php`
- Calculs `pght`, `wholesale_price`, `pharmacy_price`, override manuel

**Risques si dégradé** : pertes financières, marges incorrectes, prix erronés en POS.

### Stock transactionnel

- `Modules/Eshop360/Services/StockService.php`
- `Modules/Eshop360/Services/StockMovementService.php`
- `Modules/Eshop360/Models/Stock.php`
- `Modules/Eshop360/Models/StockMovement.php`

**Risques si dégradé** : stock négatif, ruptures invisibles, vente de produits non disponibles.

### Caisse (CashRegister)

- `Modules/Eshop360/Services/CashRegisterService.php`
- `Modules/Eshop360/Models/CashRegister.php`

**Risques si dégradé** : double caisse ouverte, réconciliation impossible.

### Billing webhooks et paiements

- `Modules/Billing/Webhooks/`
- `Modules/Billing/Services/PaymentService.php`
- `Modules/Billing/Services/SubscriptionManager.php`
- `Modules/Eshop360/Services/Payment*` (paiements métier)

**Risques si dégradé** : double comptabilisation, paiement non enregistré, abonnement actif sans paiement.

### Audit trail

- `Modules/Core/Models/AuditLog.php`
- `Modules/Core/Services/AuditLogger.php`

**Risques si dégradé** : perte de traçabilité légale/comptable, violations RGPD.

---

## L2 — SENSIBLE

- Numérotation factures (`InvoiceNumberGenerator`)
- Wallet / soldes clients (`Modules/Eshop360/Services/WalletService`)
- Commissions employés (`Modules/Eshop360/Services/HRService::calculateCommissionForSale` — cf. ADR-005)
- Migrations de schéma sur tables Eshop360 critiques (products, orders, invoices, stocks)
- HookRegistry (modification du registre central)
- ModuleManager (activation/désactivation modules)
- Configuration FeatureRegistry / FeatureGate
- Code de DistributionChannel et calcul des marges canal

---

## L3 — STANDARD

Tout le reste : vues Blade, traductions, seeders de démo, controllers de lecture seule, formats d'export, etc.

---

## Procédure de modification d'une zone L1

1. **Cadrage** par Claude Code avec lecture du code concerné, des tests existants, et de l'historique Git de la zone (`git log --follow <fichier>`).
2. **IMPACT_ANALYSIS** rédigé dans la PR (template `templates/prompts/impact-analysis.md`).
3. **Implémentation** par Codex sur une branche dédiée, avec ajout/extension de tests :
   - tests de concurrence si race condition possible
   - tests multi-tenant explicites
   - tests de permission explicites
   - tests d'idempotence si webhook ou job
4. **Review croisée** : Claude relit le diff complet ; un humain valide.
5. **ADR** si la modification change une décision d'architecture.
6. **Mise à jour mémoire** : `CURRENT_STATE.md`, `RECENT_DECISIONS.md`, `CHANGELOG_ARCHITECTURAL.md` si applicable.
7. **Merge** uniquement après pipeline qualité 100% vert.

## Procédure de modification d'une zone L2

Idem L1 sans l'ADR obligatoire, mais avec IMPACT_ANALYSIS et review humaine.

## Détection automatique

Le hook `.git/hooks/post-commit` rappelle les fichiers de mémoire à mettre à jour quand une zone L1 ou L2 a été touchée.

Le script `scripts/quality/check-protected-areas.sh` peut être lancé manuellement : `make audit-protected`.
