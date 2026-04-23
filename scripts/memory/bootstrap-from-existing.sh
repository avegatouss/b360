#!/usr/bin/env bash
# scripts/memory/bootstrap-from-existing.sh [--force]
# Génère la mémoire projet B360 à partir de la documentation et du code existants.
#
# Par défaut : NON-DESTRUCTIF.
#   - OPEN_RISKS.md et RECENT_DECISIONS.md contiennent les éditions manuelles
#     (risques fermés, décisions récentes) et sont préservés s'ils existent déjà.
#   - Les autres fichiers (CURRENT_STATE, PROJECT_DIGEST, MODULE_INDEX, etc.)
#     sont régénérés car ils sont calculés depuis le code/git.
#
# Avec --force : écrase TOUS les fichiers (comportement du bootstrap initial).
#   À n'utiliser qu'après sauvegarde manuelle.

set -euo pipefail

REPO_ROOT="$(git rev-parse --show-toplevel 2>/dev/null || pwd)"
cd "$REPO_ROOT"

FORCE=""
for arg in "$@"; do
    case "$arg" in
        --force|-f) FORCE=1 ;;
    esac
done

GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

log() { echo -e "${GREEN}[bootstrap-memory]${NC} $1"; }
warn() { echo -e "${YELLOW}[bootstrap-memory]${NC} $1"; }

# Préserve un fichier sensible (éditions manuelles) sauf si --force
# Usage : if should_write "docs/memory/OPEN_RISKS.md"; then cat > ... fi
should_write() {
    local path="$1"
    if [ -n "$FORCE" ] || [ ! -f "$path" ]; then
        return 0
    fi
    warn "Préservé (édits manuels) : $path — utilise --force pour régénérer"
    return 1
}

mkdir -p docs/memory docs/index docs/context docs/architecture docs/governance

TODAY=$(date +%Y-%m-%d)
NOW=$(date '+%Y-%m-%d %H:%M:%S %Z')

# ════════════════════════════════════════════════════════════════════
# 1. Détection des modules
# ════════════════════════════════════════════════════════════════════
log "Détection des modules..."

MODULES=()
if [ -d "Modules" ]; then
    for dir in Modules/*/; do
        module_name=$(basename "$dir")
        if [ -f "${dir}module.json" ] || [ -f "${dir}Providers/${module_name}ServiceProvider.php" ]; then
            MODULES+=("$module_name")
        fi
    done
fi

N_MODULES=${#MODULES[@]}
log "Modules détectés : $N_MODULES (${MODULES[*]:-aucun})"

# ════════════════════════════════════════════════════════════════════
# 2. État des tests (lecture STATUS.md si présent, sinon php artisan test)
# ════════════════════════════════════════════════════════════════════
log "État des tests..."

TESTS_LINE="Non exécutés (lance \`make test\` pour mesurer)"
if [ -f "docs/STATUS.md" ]; then
    SUMMARY=$(grep -m1 -E '^\s*\|\s*\*\*Tests\*\*\s*\|' docs/STATUS.md 2>/dev/null || true)
    if [ -n "$SUMMARY" ]; then
        TESTS_LINE="$(echo "$SUMMARY" | sed -E 's/^\s*\|\s*\*\*Tests\*\*\s*\|\s*\*\*([^|]+)\*\*\s*\|.*$/\1/' | tr -d '*' | xargs)"
    fi
fi

# ════════════════════════════════════════════════════════════════════
# 3. État des migrations
# ════════════════════════════════════════════════════════════════════
log "État des migrations..."

MIGRATIONS_LINE="Non vérifié"
if [ -f "docs/STATUS.md" ]; then
    MIG=$(grep -m1 -E '^\s*\|\s*\*\*Migrations\*\*\s*\|' docs/STATUS.md 2>/dev/null || true)
    if [ -n "$MIG" ]; then
        MIGRATIONS_LINE="$(echo "$MIG" | sed -E 's/^\s*\|\s*\*\*Migrations\*\*\s*\|\s*\*\*([^|]+)\*\*\s*\|.*$/\1/' | tr -d '*' | xargs)"
    fi
fi

# ════════════════════════════════════════════════════════════════════
# 4. CURRENT_STATE.md
# ════════════════════════════════════════════════════════════════════
log "Génération de docs/memory/CURRENT_STATE.md..."

cat > docs/memory/CURRENT_STATE.md <<EOF
# CURRENT_STATE — B360

> Fichier auto-généré par \`make memory-refresh\`. Dernière mise à jour : **$NOW**
> Branche analysée : \`$(git rev-parse --abbrev-ref HEAD 2>/dev/null || echo 'inconnu')\`
> HEAD : \`$(git rev-parse --short HEAD 2>/dev/null || echo 'inconnu')\`

---

## Snapshot

| Indicateur | Valeur |
|---|---|
| Modules détectés | $N_MODULES |
| Tests | $TESTS_LINE |
| Migrations | $MIGRATIONS_LINE |
| Date du snapshot | $TODAY |

## Modules — état détaillé

| Module | Statut | Stabilité | Tests présents | Notes |
|---|---|---|---|---|
EOF

for module in "${MODULES[@]}"; do
    has_tests="non"
    [ -d "Modules/$module/Tests" ] && has_tests="oui"

    statut="actif"
    stabilite="à confirmer"

    case "$module" in
        Eshop360)
            statut="actif"
            stabilite="monolithique — découpage en cours"
            note="83 modèles / 78 contrôleurs / 128 migrations — candidat extraction"
            ;;
        Core|Auth|Users|Instances|Settings|Billing|Dashboard)
            statut="actif"
            stabilite="stable"
            note="socle plateforme"
            ;;
        *)
            note="—"
            ;;
    esac

    echo "| $module | $statut | $stabilite | $has_tests | $note |" >> docs/memory/CURRENT_STATE.md
done

cat >> docs/memory/CURRENT_STATE.md <<'EOF'

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
EOF

# ════════════════════════════════════════════════════════════════════
# 5. OPEN_RISKS.md (depuis l'audit existant) — préservé si édité manuellement
# ════════════════════════════════════════════════════════════════════
if should_write "docs/memory/OPEN_RISKS.md"; then
log "Génération de docs/memory/OPEN_RISKS.md..."

cat > docs/memory/OPEN_RISKS.md <<EOF
# OPEN_RISKS — B360

> Risques techniques connus, suivi vivant. Mise à jour : **$NOW**

---

## CRITIQUE

EOF

# Si l'audit go-live existe, on extrait les risques critiques
if [ -f "docs/AUDIT-ARCHITECTURE-GO-LIVE.md" ]; then
    cat >> docs/memory/OPEN_RISKS.md <<'EOF'
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

EOF
fi

cat >> docs/memory/OPEN_RISKS.md <<'EOF'

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

_(aucun risque moyen ouvert à la date de bootstrap — section préservée pour évolution future.)_

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
EOF
fi

# ════════════════════════════════════════════════════════════════════
# 6. RECENT_DECISIONS.md — préservé si édité manuellement
# ════════════════════════════════════════════════════════════════════
if should_write "docs/memory/RECENT_DECISIONS.md"; then
log "Génération de docs/memory/RECENT_DECISIONS.md..."

cat > docs/memory/RECENT_DECISIONS.md <<EOF
# RECENT_DECISIONS — B360

> Décisions structurantes récentes. Mise à jour : **$NOW**
> Pour les décisions complètes argumentées, voir \`docs/adr/\`.

---

## $TODAY — Installation du pack vibecoding

- **Décision** : adoption d'un pipeline qualité local (Pint + PHPStan + Deptrac + Pest) imposé par hooks Git
- **Impact** : tous les nouveaux commits doivent passer Pint, PHPStan, et la convention Conventional Commits avec scope obligatoire
- **Baseline** : Deptrac et PHPStan baselines capturent l'existant — seul le NOUVEAU code doit être propre
- **ADR** : à créer dans \`docs/adr/ADR-001-pipeline-qualite-local.md\`

## 2026-04-04 — Architecture cible plateforme modulaire

- **Source** : \`docs/Ins/b360_evolution_strategy.md\`
- **Décision** : ne pas réécrire Eshop360, mais le découper progressivement par sous-domaines
- **Phases** : Catalog/Channel → Inventory/Sales → Finance/CRM/HR
- **Règle** : nouveau module ne peut pas \`use\` un modèle Eloquent d'un autre module

## 2026-04-06 — Multi-currency MVP fixé

- **Source** : \`docs/STATUS.md\`
- **Décision** : symétrisation \`exchange_rate\` sur \`eshop_payments\`
- **Validation** : 18/18 tests Currency verts

## 2026-04-06 — Double caisse cross-channel corrigée

- **Source** : \`docs/STATUS.md\` D-1
- **Décision** : \`CashRegisterService::open()\` ferme TOUTES les caisses ouvertes du user (pas seulement celles du même channel)
- **Validation** : 3 nouveaux tests TDD

---

## Convention

Chaque entrée :
- Date
- Décision en une phrase
- Impact concret
- Source (ADR, audit, conversation, PR)
EOF
fi

# ════════════════════════════════════════════════════════════════════
# 7. PROJECT_DIGEST.md (compression IA) — regénéré (calculé depuis code/git)
# ════════════════════════════════════════════════════════════════════
log "Génération de docs/context/PROJECT_DIGEST.md..."

cat > docs/context/PROJECT_DIGEST.md <<EOF
# PROJECT DIGEST — B360

> Compression contextuelle pour les IA. Lis ce fichier en premier.
> Mise à jour : **$NOW**

---

## En une phrase

B360 est une plateforme SaaS multi-tenant Laravel 12 modulaire (nwidart/laravel-modules), avec un noyau plateforme stable ($((N_MODULES - 1)) modules système) et un module métier monolithique Eshop360 en cours de découpage progressif.

## Stack

- **Framework** : Laravel 12, PHP 8.2+
- **Modularité** : nwidart/laravel-modules v12
- **Multi-tenant** : Instance-based (\`/i/{slug}/\`), Spatie Permission avec teams
- **Front** : Blade + Bootstrap 5 + Tabler Icons (jQuery minimal)
- **Tests** : Pest / PHPUnit
- **Qualité** : Pint, PHPStan/Larastan, Deptrac, Rector

## État ($TODAY)

- **Modules** : $N_MODULES actifs ($(echo "${MODULES[*]}" | tr ' ' ',' ))
- **Tests** : $TESTS_LINE
- **Migrations** : $MIGRATIONS_LINE

## Architecture macro

\`\`\`
Couche système     : Installer, Core, Auth, Users, Instances, Settings, ModuleManager
Couche transverse  : Billing, Lang, Currency, Dashboard, Demo
Couche métier      : Eshop360 (monolithique, en découpage)
Couche future      : Menuiserie360 (à concevoir)
\`\`\`

## Mécanisme d'extension central

**HookRegistry** dans Core permet à chaque module de s'enregistrer dynamiquement pour :
\`menu\`, \`widgets\`, \`settings_groups\`, \`permissions\`, \`features\`, \`payment_gateways\`, \`demo_providers\`, \`notification_types\`.

## Risques critiques actifs

1. Race condition stock (R-001)
2. Webhook paiement double (R-002)
3. Solde négatif portefeuille (R-003)
4. Commissions RH dupliquées (R-004)
5. Eshop360 monolithique (R-101)

Détails : \`docs/memory/OPEN_RISKS.md\`.

## Zones protégées (modification = double review)

- Auth flow (login, 2FA, IP rules)
- Tenancy (InstanceManager, InstanceResolver, BelongsToInstance)
- Permissions (Spatie + policies métier)
- Pricing engine
- StockService (race conditions)
- CashRegisterService
- Billing webhooks
- AuditLog

Détails : \`docs/governance/PROTECTED_AREAS.md\`.

## Documents source de vérité

| Sujet | Fichier |
|---|---|
| État instantané | \`docs/STATUS.md\` |
| Cartographie modules | \`docs/cartographie/\` |
| Audit fonctionnel | \`docs/AUDIT_COMPLET_B360.md\` |
| Audit pré go-live | \`docs/AUDIT-ARCHITECTURE-GO-LIVE.md\` |
| Stratégie évolution | \`docs/Ins/b360_evolution_strategy.md\` |
| Calculs Eshop360 | \`docs/GUIDE-CALCULS-ESHOP360.md\` |
| Architecture cible | \`ARCHITECTURE.md\` |
| Roadmap | \`docs/roadmap/ROADMAP_REBUILD.md\` |

## Pour une IA qui démarre une intervention

Lis dans cet ordre :
1. Ce fichier (PROJECT_DIGEST.md)
2. \`docs/memory/CURRENT_STATE.md\`
3. \`docs/memory/OPEN_RISKS.md\` (filtré sur le module concerné)
4. \`docs/memory/RECENT_DECISIONS.md\`
5. \`docs/governance/PROTECTED_AREAS.md\` si tu touches au backend critique
6. ADR pertinents dans \`docs/adr/\`
7. Code seulement après ces lectures

Coût attendu : 2 000 à 4 000 tokens pour avoir le contexte global, vs 50 000+ pour relire le code.
EOF

# ════════════════════════════════════════════════════════════════════
# 8. MODULE_INDEX.md
# ════════════════════════════════════════════════════════════════════
log "Génération de docs/index/MODULE_INDEX.md..."

cat > docs/index/MODULE_INDEX.md <<EOF
# MODULE_INDEX — B360

> Index machine-friendly des modules. Mise à jour : **$NOW**

---

EOF

for module in "${MODULES[@]}"; do
    n_models=0
    n_controllers=0
    n_services=0
    n_migrations=0

    [ -d "Modules/$module/Models" ] && n_models=$(find "Modules/$module/Models" -name "*.php" -type f 2>/dev/null | wc -l | tr -d ' ')
    [ -d "Modules/$module/Http/Controllers" ] && n_controllers=$(find "Modules/$module/Http/Controllers" -name "*.php" -type f 2>/dev/null | wc -l | tr -d ' ')
    [ -d "Modules/$module/Services" ] && n_services=$(find "Modules/$module/Services" -name "*.php" -type f 2>/dev/null | wc -l | tr -d ' ')
    [ -d "Modules/$module/Database/Migrations" ] && n_migrations=$(find "Modules/$module/Database/Migrations" -name "*.php" -type f 2>/dev/null | wc -l | tr -d ' ')

    cat >> docs/index/MODULE_INDEX.md <<EOF
## $module

- **Chemin** : \`Modules/$module/\`
- **Modèles** : $n_models
- **Contrôleurs** : $n_controllers
- **Services** : $n_services
- **Migrations** : $n_migrations
- **Tests** : $([ -d "Modules/$module/Tests" ] && echo "oui (\`Modules/$module/Tests\`)" || echo "non")
- **module.json** : $([ -f "Modules/$module/module.json" ] && echo "oui" || echo "non")

EOF
done

# ════════════════════════════════════════════════════════════════════
# 9. MODULE_DEPENDENCY_MAP.md (basé sur la cartographie existante)
# ════════════════════════════════════════════════════════════════════
log "Génération de docs/architecture/MODULE_DEPENDENCY_MAP.md..."

cat > docs/architecture/MODULE_DEPENDENCY_MAP.md <<'EOF'
# MODULE_DEPENDENCY_MAP — B360

> Source de vérité des dépendances autorisées entre modules.
> Toute violation est bloquée par Deptrac.

---

## Règles fondatrices

1. **Aucun module ne peut importer un modèle Eloquent d'un autre module.** La communication passe par contrats, services applicatifs ou événements.
2. **Aucun accès DB direct** (`DB::table('eshop_*')` hors namespace `Eshop360`) bloqué par règle PHPStan custom.
3. **Pas de dépendance circulaire** vérifiée par Deptrac.
4. **Le Core ne dépend d'aucun module métier**.

---

## Couches autorisées (deptrac.yaml)

| Couche | Modules | Peut dépendre de |
|---|---|---|
| **L0 — Socle** | Core | (aucun) |
| **L1 — Identité** | Auth, Users, Instances | L0 |
| **L1 — Configuration** | Settings | L0 |
| **L2 — Transverse** | Billing | L0, L1 |
| **L2 — Support** | Lang, Currency, Dashboard, ModuleManager, Demo, Installer | L0, L1 |
| **L3 — Métier** | Eshop360 (catalog, pricing, inventory, sales, finance, crm, channel, hr, projects) | L0, L1, L2 |
| **L4 — Modules futurs** | Menuiserie360, etc. | L0, L1, L2, contrats Eshop360 (pas modèles) |

---

## Dépendances actuelles (constatées)

| Module source | Dépend de | Type |
|---|---|---|
| Auth | Core, app/User, app/Instance | core / fort |
| Users | Core, app/User, Instances | core / fort |
| Instances | Core, app/Instance, Settings | core / fort |
| Settings | Core | core / fort |
| Billing | Core, Settings, app/Instance | core / fort |
| Dashboard | Core | core / fort |
| Lang | Core, Settings | core / moyen |
| Currency | Core, Settings | core / moyen |
| ModuleManager | Core | core / fort |
| Demo | Core, Eshop360 | support / moyen |
| **Eshop360** | Core, Auth, Users, Settings, Billing, app/User, app/Instance | métier / très fort |

---

## Communications inter-modules autorisées

### Via HookRegistry (préféré)
- `menu` (MenuItem)
- `widgets` (DashboardWidget)
- `settings_groups` (SettingsGroup)
- `permissions` (PermissionGroup)
- `features` (BillableFeature)
- `payment_gateways` (PaymentGatewayDefinition)
- `demo_providers` (DemoDataProvider)
- `notification_types`

### Via Events (recommandé pour découpler)
- À documenter dans `docs/index/EVENT_INDEX.md`

### Via Contracts (pour services partagés)
- À documenter dans `docs/index/API_INDEX.md` section "Contracts internes"

---

## Interdictions strictes

- ❌ `use Modules\Eshop360\Models\Product` depuis Menuiserie360 → utiliser un contrat
- ❌ `DB::table('eshop_products')` hors namespace `Eshop360` → bloqué PHPStan
- ❌ Dupliquer un trait, modèle ou service déjà présent dans un autre module → factoriser
- ❌ Créer un module qui dépend de la **vue Blade** d'un autre module → utiliser composants UI partagés

---

## Pour étendre cette carte

Toute nouvelle dépendance doit :
1. être documentée ici
2. être ajoutée à `deptrac.yaml`
3. faire l'objet d'un ADR si traverse une couche
EOF

# ════════════════════════════════════════════════════════════════════
# 10. PROTECTED_AREAS.md
# ════════════════════════════════════════════════════════════════════
log "Génération de docs/governance/PROTECTED_AREAS.md..."

cat > docs/governance/PROTECTED_AREAS.md <<'EOF'
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
- Commissions employés (`Modules/Eshop360/Services/CommissionService`)
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
EOF

# ════════════════════════════════════════════════════════════════════
# 11. Indexes vides à régénérer plus tard
# ════════════════════════════════════════════════════════════════════
log "Création des index techniques (squelettes)..."

for index in PERMISSION_INDEX EVENT_INDEX API_INDEX DB_INDEX; do
    if [ ! -f "docs/index/$index.md" ]; then
        cat > "docs/index/$index.md" <<EOF
# $index — B360

> Auto-généré par \`make audit-${index,,}\`. Mise à jour : **$NOW**
> Régénère avec : \`make $(echo "$index" | tr '[:upper:]' '[:lower:]' | sed 's/_index/-index/' | sed 's/_/-/g')\`

(à régénérer)
EOF
    fi
done

log "${GREEN}Bootstrap terminé.${NC}"
log ""
log "Fichiers générés :"
log "  - docs/memory/CURRENT_STATE.md"
log "  - docs/memory/OPEN_RISKS.md"
log "  - docs/memory/RECENT_DECISIONS.md"
log "  - docs/context/PROJECT_DIGEST.md"
log "  - docs/index/MODULE_INDEX.md"
log "  - docs/architecture/MODULE_DEPENDENCY_MAP.md"
log "  - docs/governance/PROTECTED_AREAS.md"
log ""
log "Pour régénérer plus tard : make memory-refresh"
