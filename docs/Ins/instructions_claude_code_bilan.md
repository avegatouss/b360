# Instructions Claude Code — Bilan d'étape B360
# À coller tel quel dans Claude Code comme premier message

---

Tu es architecte technique senior spécialisé Laravel / SaaS multi-tenant.

Tu travailles sur le projet **B360**, une application Laravel 12 modulaire (nwidart/laravel-modules v12),
multi-tenant, dont le module métier principal est **Eshop360**.

## Documents de référence disponibles dans le dépôt

Avant de produire le bilan, lis les fichiers suivants dans cet ordre :

```
docs/cartographie/00_overview.md
docs/cartographie/01_modules/core.md
docs/cartographie/01_modules/eshop360.md
docs/cartographie/01_modules/billing.md
docs/cartographie/01_modules/auth.md
docs/cartographie/01_modules/currency.md
docs/cartographie/02_eshop360_focus.md
docs/cartographie/03_incoherences_et_optimisations.md
docs/cartographie/04_resume_strategique.md
docs/audits/00_overview.md
docs/audits/01_modules/eshop360.md
docs/GUIDE-CALCULS-ESHOP360.md
```

Si l'un de ces fichiers est absent, indique-le dans la section "Zones d'ombre" avec `[FICHIER MANQUANT]`.

Cherche également dans le dépôt tout fichier dont le nom contient :
- `currency` ou `multi_currency` ou `multi-currency` (stratégie multi-devises)
- `b360_evolution_strategy` (stratégie d'évolution SaaS)
- `eshop360_pricing_engine` (moteur de pricing)
- `shared_resources_strategy` (mutualisation des modules)
- `CONCEPTION_TECHNIQUE_MENUISERIE360` (module Menuiserie360)

Ces fichiers constituent les **livrables stratégiques** déjà produits. Intègre leur contenu dans le bilan.

---

## Ce que tu dois produire

Un seul fichier Markdown :

```
bilan_etat_actuel_avant_nouvelles_fonctionnalites.md
```

à créer à la racine du projet ou dans `docs/`.

---

## Structure obligatoire du fichier (9 sections)

### 1. Récapitulatif des livrables existants

- Liste tous les documents trouvés (cartographie, audits, stratégies)
- Indique pour chacun : nom du fichier, date si disponible, périmètre couvert
- Ce qui est **validé** (cohérent entre audit et code)
- Ce qui est **incertain** (contradictions entre documents ou non vérifié dans le code)

### 2. État des lieux de l'existant (synthèse cartographique)

Pour chaque module actif de B360 (Core, Auth, Users, Instances, Settings, Billing, Dashboard,
Lang, Currency, ModuleManager, Installer, Demo, Eshop360, Menuiserie360 si présent) :

| Module | Fonctionnalité clé | Niveau de confiance |
|--------|-------------------|---------------------|
| ...    | ...               | 🟢 Certain / 🟡 À vérifier / 🔴 Inconnu |

Puis :
- **Modèle de données** : liste les tables critiques et partagées identifiées dans les audits
  (notamment : `eshop_products`, `eshop_stocks`, `eshop_orders`, `eshop_distribution_channels`,
  `eshop_channel_product_prices`, `eshop_invoices`, `eshop_payments`, tables Billing, tables Core)
- **Dépendances critiques** : reprend le graphe de dépendances de `00_overview.md`
- **Points sensibles** : reprend la liste des 12 issues de l'audit (`ISSUE-01` à `ISSUE-12`)
  avec leur statut actuel (corrigé / en attente / non traité)

### 3. État d'avancement de la stratégie multi-devises

Réponds à ces 4 questions en t'appuyant uniquement sur les fichiers trouvés :

**3.1 Ce qui a été décidé** (architecture cible, tables proposées, services, events)
→ Cherche dans les fichiers `currency*.md` et `b360_evolution_strategy*.md`

**3.2 Ce qui est déjà implémenté** dans `Modules/Currency/`
→ Lis le code source du module Currency si accessible :
  `Modules/Currency/Models/*.php`
  `Modules/Currency/Services/*.php`
  `Modules/Currency/Database/Migrations/*.php`
  `Modules/Currency/Routes/web.php`

**3.3 Ce qui reste à faire** (delta entre décision et implémentation)

**3.4 Risques résiduels multi-devises** :
- Cohérence des taux historiques sur les commandes passées
- Comportement de `CostCalculatorService` face aux devises (actuellement en devise unique ?)
- Impact sur `eshop_channel_product_prices` (les prix canal sont-ils mono-devise ?)
- Performance si taux mis à jour toutes les heures (`currency:update-rates` job existant)

### 4. Évaluation de la maturité du système (scores 1-5)

| Critère | Note | Justification (1 ligne) | Source |
|---------|------|------------------------|--------|
| Modularité (isolation des modules) | /5 | ... | `03_incoherences*.md` |
| Testabilité (couverture, qualité) | /5 | ... | `00_overview.md` (219 passed / 16 failed) |
| Extensibilité (ajout de features) | /5 | ... | `04_resume_strategique.md` |
| Performance (volumétrie, race cond.) | /5 | ... | Audit ISSUE-01 à ISSUE-05 |
| Documentation (code + fonctionnelle) | /5 | ... | Audits |

**Score global** : X / 25 — verdict en 1 phrase.

### 5. Écarts entre l'existant et la cible multi-devises

Tableau des écarts :

| Écart identifié | Impact | Complexité | Urgence |
|-----------------|--------|-----------|---------|
| Ex: Pas de snapshot devise dans `eshop_order_items` | Haut | M | Haute |
| Ex: `CurrencyManager` sans contrat d'interface Core | Moyen | S | Moyenne |
| ... | ... | ... | ... |

Complexité : S (< 2h) / M (< 1 jour) / L (< 1 semaine) / XL (> 1 semaine)

### 6. Zones d'ombre et questions en suspens

Pour chaque point non clarifiable depuis les fichiers disponibles :

| Question | Pourquoi bloquant | Action de vérification |
|----------|-------------------|------------------------|
| Ex: Comment Menuiserie360 gère-t-il les prix en ce moment ? | Aucun fichier Menuiserie360 trouvé | Lire `Modules/Menuiserie360/Domain/Commercial/Services/DevisCalculatorService.php` |
| Ex: `eshop_channel_product_prices` : colonnes exactes ? | Audit partiel | `php artisan schema:dump` ou lire migration |
| ... | ... | ... |

Indique explicitement les fichiers que tu **aurais eu besoin** de lire mais qui n'étaient pas accessibles.

### 7. Recommandations immédiates avant d'ajouter d'autres fonctionnalités

Classe les recommandations en 4 catégories :

**🔴 Corrections bloquantes (P0 — à faire AVANT tout)**
→ Reprends les issues critiques de l'audit non encore corrigées
   (race condition stock ISSUE-01, TOCTOU numéros ISSUE-02, commissions dupliquées ISSUE-03, etc.)

**🟠 Refactorings légers (P1 — cette semaine)**
→ Ex: unifier `BelongsToInstance` trait, supprimer double `recurring-invoices` schedule,
   ajouter `tax_rate` sur `invoice_items`, supprimer `FeatureGate` deprecated

**🟡 Compléments de tests (P2 — dans les 2 semaines)**
→ Tests manquants sur les flux critiques identifiés dans l'audit
   (POS → Stock → Invoice → Margin, multi-tenant isolation, pricing canal)

**🔵 Documentation à mettre à jour**
→ README des modules modifiés, changelog, `[À VÉRIFIER]` résolus

### 8. Décisions à prendre avant de continuer

Liste les **décisions architecturales ouvertes** qui bloquent la suite :

| Décision | Options | Impact si non tranchée | Délai suggéré |
|----------|---------|----------------------|---------------|
| API de taux de change : open.er-api.com (existant) ou autre ? | Garder / Changer | Multi-devises incomplet | Avant sprint 1 |
| Commandes en cours au moment du changement de devise | Figer le taux / Recalculer | Incohérence comptable | Avant implémentation |
| Découpage Eshop360 : maintenant ou après stabilisation ? | Maintenant / Après | Toute nouvelle feature ajoute au monolithe | Go/no-go à décider |
| ... | ... | ... | ... |

### 9. Plan d'action pour la semaine suivante

**Maximum 5 tâches**, chacune faisable en 1-2 jours/homme :

| # | Tâche | Fichiers impactés | Responsable suggéré | Durée | Critère de succès |
|---|-------|------------------|--------------------|----|-----------------|
| 1 | Ajouter `lockForUpdate()` dans `StockService::adjustStock()` | `Modules/Eshop360/Services/StockService.php` | Lead dev | 2h | Test de non-régression stock passant |
| 2 | ... | ... | ... | ... | ... |

---

## Contraintes de rédaction

- **Ne pas inventer** : si une information n'est pas dans les fichiers lus, écris `[INCONNU]` ou `[À VÉRIFIER dans {fichier}]`
- **Ne pas proposer de nouvelles fonctionnalités** : ce document est un bilan, pas une spécification
- **Citer les sources** : chaque affirmation importante doit référencer le fichier source entre parenthèses
- **Être critique** : signale les incohérences entre documents sans les minimiser
- **Rester pragmatique** : chaque recommandation doit être réalisable en < 1 semaine/homme

---

## Rappel du contexte B360 (ne pas réinventer, juste mémoriser)

- **Stack** : Laravel 12, PHP 8.2+, nwidart/laravel-modules v12, Spatie Permission v6 (teams),
  MySQL, Redis, Blade + jQuery + Bootstrap 5
- **Multi-tenant** : résolution par `/i/{slug}/`, middleware `BindInstanceFromRoute`,
  trait `BelongsToInstance` (GlobalScope automatique), `SetSpatieTeamContext` (team_id = instance_id)
- **Module Currency existant** : `CurrencyManager`, taux mis à jour par `currency:update-rates`
  (job planifié quotidien via open.er-api.com), CRUD devises, format/convert
- **État Eshop360** : 83 modèles, 78 contrôleurs, 128 migrations, 219 tests passants / 16 failing
  au 2026-04-04. Monolithe dans le modulaire.
- **Issues critiques connues** : race condition stock (ISSUE-01), commissions dupliquées (ISSUE-02),
  solde wallet négatif (ISSUE-03), double webhook paiement (ISSUE-04), double caisse (ISSUE-05)
- **Livrables stratégiques déjà produits** :
  - `b360_evolution_strategy.md` (gouvernance multi-modules)
  - `eshop360_pricing_engine.md` (moteur de pricing + spec canaux/marges/min-max qty)
  - `shared_resources_strategy.md` (mutualisation données inter-modules)
  - `CONCEPTION_TECHNIQUE_MENUISERIE360.md` (module menuiserie aluminium)

---

Commence par lire les fichiers, puis produis le bilan.
Si un fichier manque, note-le et continue avec ce que tu as.
