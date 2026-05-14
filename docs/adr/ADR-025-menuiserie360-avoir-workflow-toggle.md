# ADR-025 — Menuiserie360 V2 : workflow avoir avec toggle de validation Direction

> Architectural Decision Record. Définit le workflow de génération des avoirs (TypeFacture::AVOIR) en V2 du module Menuiserie360, avec un **toggle de configuration** permettant à chaque instance de choisir entre validation simple (Comptable seul) ou validation à 2 niveaux (Comptable → Direction).

## Statut

**Proposé** — 2026-05-14. Passera en **Accepté** au merge du sous-lot S4 (R-M-V2-S4 — IMPACT_ANALYSIS à créer en pré-S4).

## Contexte

Le V1 du module Menuiserie360 a livré l'enum `TypeFacture` comprenant `acompte`, `solde`, `avoir`. Les workflows acompte (`CreateAcompteOnDevisAccepte`) et solde (`CreateSoldeOnChantierTermine`) sont livrés et testés. **Aucune UI ni Action `CreateAvoirAction` n'existe** — l'enum `AVOIR` est présent mais inutilisable.

Le [CDC 2026](../Ins/CAHIER%20DE%20CHARGES%20WEB%20LOGICIEL%202026%20%20%285%29.pdf) §3.6 mentionne explicitement « Avoirs » dans la facturation client.

[ERP.md](../Ins/ERP.md) §11 (Sécurité — Audit) impose : « Opérations financières (facture, encaissement, paie) » doivent être loguées et faire l'objet de policies. Un avoir est par nature **destructeur** (annule un montant déjà comptabilisé) — sa génération est sensible.

Décision humaine Q3 du cadrage V2 : **workflow dynamique avec activation de validation ou non**. C'est‑à‑dire que **chaque instance** doit pouvoir choisir si l'émission d'un avoir requiert une validation Direction ou non, selon la maturité de son organisation (PME 5 personnes : Comptable seul ; PME 30 personnes : double signature).

## Décision

Nous introduisons un **toggle** dans `mnu_settings` qui pilote dynamiquement le workflow, et **deux Actions distinctes** orchestrées par un Service qui lit le toggle.

### 1. Setting `avoir_require_validation`

Clé `avoir_require_validation` dans `mnu_settings` :

- Type : booléen (`'0'` / `'1'` stocké en VARCHAR, lu via cast).
- Valeur par défaut : `false` (workflow simple) — choix conservateur pour ne pas bloquer les PME légères.
- Modifiable via UI Settings (page existante du module).
- Permission requise pour modifier : `menuiserie.settings.manage` (Direction + Admin).

### 2. Deux Actions atomiques

```php
final class CreateAvoirAction
{
    // Crée l'avoir en statut 'pending_validation' SI toggle ON
    // sinon en statut 'issued' directement.
    // Permission requise (caller) : menuiserie.invoice.create
    public function execute(CreateAvoirDto $dto): MenuiserieInvoice;
}

final class ValidateAvoirAction
{
    // Transition pending_validation → issued
    // Permission requise (caller) : menuiserie.invoice.validate
    // Idempotente : double-validation = no-op
    // Lève AvoirValidationNotRequiredException si toggle OFF
    public function execute(int $instanceId, int $avoirId, int $validatedByUserId): MenuiserieInvoice;
}
```

### 3. Service orchestrateur

`AvoirService` lit le toggle et expose 2 méthodes haut niveau :

```php
final class AvoirService
{
    public function shouldRequireValidation(int $instanceId): bool;
    public function workflow(int $instanceId): AvoirWorkflowDto; // { requireValidation, currentUserCanValidate, ... }
}
```

Consommé par le `FactureMenuiserieController` pour afficher (ou non) le bouton « Émettre » vs « Soumettre à validation ».

### 4. Statut intermédiaire

Enum `StatutFacture` enrichi (additif) :

```php
case PENDING_VALIDATION = 'pending_validation'; // nouvelle valeur
case ISSUED             = 'issued';             // existant
// ... autres existants
```

Migration additive : la colonne `mnu_invoices.status` (VARCHAR 30) accepte déjà les nouvelles valeurs sans schéma à toucher. Si la colonne est ENUM (à vérifier au moment de S4), migration `ALTER TABLE` MODIFY COLUMN sur MySQL — sinon no-op SQLite.

### 5. Permission `menuiserie.invoice.validate`

Ajoutée au PermissionGroup Finance via HookRegistry. Assignée par défaut au rôle Direction (et super-admin). **Pas** au Comptable — pour préserver la séparation des pouvoirs même si le toggle est OFF (cas futur où il serait activé sans recréer les permissions).

### 6. Audit obligatoire

Toute transition `pending_validation → issued` log dans Spatie ActivityLog (via S9 du V2). En V2 sans ActivityLog encore, on log au minimum dans la colonne `mnu_invoices.validated_by_user_id` + `validated_at` (2 colonnes additives, NULLABLE).

## Conséquences

### Positives

- **Flexibilité organisationnelle** : une PME légère démarre rapidement (toggle OFF), une PME structurée monte en maturité (toggle ON) sans changement de code.
- **Audit natif** : `validated_by` + `validated_at` captent la trace même hors Spatie ActivityLog.
- **Idempotence** : `ValidateAvoirAction` re-jeu inoffensif (déjà `issued` = no-op). Pattern ADR-006-like.
- **Permission preservée** : `menuiserie.invoice.validate` toujours assignée au bon rôle, même si non utilisée tant que toggle OFF.

### Négatives / Trade-offs

- **Setting global par instance** — pas de granularité « validation requise au-dessus de N XOF ». Un seuil monétaire (à l'instar de Devis seuil-direction §1.4 spec) reste **hors scope V2**. Si demandé V2.1, ajout d'un `avoir_validation_threshold_xof` qui complète le toggle.
- **Une seule étape de validation** — pas de validation à plusieurs niveaux (Comptable → Direction → CEO). Acceptable pour ERP PME. Multi-niveau = lot dédié si besoin émerge.
- **Statut `pending_validation` ne bloque pas le calcul `paid_amount`** — un avoir en attente de validation ne réduit pas encore le `due_amount` sur la facture parent. Le lien comptable se fait à `issued`.
- **Couplage Settings ↔ Action** — `AvoirService` interroge `mnu_settings` à chaque création. Pas de cache mémoïsé V2 (1 hit Cache::remember `menuiserie.config.{instance_id}` 3600s suffit ; pattern ERP.md §10).

### Alternatives rejetées

| Alternative | Pourquoi rejetée |
|---|---|
| Toggle global plateforme (config app) | Empêche la différenciation per-instance qui est le cœur de la décision Q3. |
| Workflow simple seul (validation Comptable uniquement) | Ne satisfait pas la cible PME structurée. Q3 explicite : « dynamique ». |
| Workflow double signature toujours actif | Force toutes les PME à monter en maturité dès J1, frein à l'adoption. |
| Statut `validated` ajouté en plus de `issued` | Pollue l'enum sans valeur métier — `issued` après validation = même état comptable. La trace passe par `validated_by_user_id` + `validated_at`. |
| Avoir = paiement négatif sur la facture (sans modèle dédié) | Casse la lisibilité comptable, ne permet pas d'éditer un PDF d'avoir indépendant, contradit le CDC qui demande des « avoirs » distincts. |

## Contraintes imposées au futur

1. Toute nouvelle valeur de `StatutFacture` passe par un ADR additif. `pending_validation` est la première valeur intermédiaire — toute future étape (`pending_approval`, `pending_audit`, etc.) doit s'articuler avec celle-ci, pas la remplacer.
2. La permission `menuiserie.invoice.validate` est **séparée** de `menuiserie.invoice.create` et le restera. Ne jamais fusionner.
3. Le calcul de marge / KPI Dashboard Direction doit **soustraire** les avoirs `issued` du CA, **pas** les avoirs `pending_validation` (équivalent à brouillon).
4. Si le toggle passe de OFF à ON sur une instance avec avoirs déjà `issued`, l'historique n'est pas rétroactivement requalifié (les avoirs existants restent `issued` sans `validated_by`).
5. L'export comptable CSV (`ExportComptableService`) doit traiter `pending_validation` comme « hors export » (filtré). Documenté dans le service en S4.

## Mise en œuvre

Lot S4 du V2 (R-M-V2-S4), ~4 j Codex. IMPACT_ANALYSIS à rédiger après S1 (S4 dépend de S1 pour `mnu_clients_menuiserie` enrichi — client référencé par avoir).

Plan séquencé :

1. Migration additive : `mnu_invoices.validated_by_user_id` (NULLABLE) + `mnu_invoices.validated_at` (NULLABLE). Si `status` est ENUM, ALTER pour ajouter `pending_validation`.
2. Setting `avoir_require_validation` : DemoSeeder Menuiserie360 + UI Settings (page existante).
3. `CreateAvoirAction` + `ValidateAvoirAction` + `AvoirService`.
4. Permission `menuiserie.invoice.validate` registrée dans `Menuiserie360HooksProvider`.
5. UI : bouton « Émettre avoir » sur facture (déjà `issued`) + workflow modal (motif + montant) + vue liste avoirs en attente (filtre `status = pending_validation`) pour les valideurs.
6. PDF avoir : template `pdf/avoir.blade.php` (variation `pdf/facture.blade.php`).
7. ExportComptableService : filtrer `pending_validation`.
8. Tests : 12+ tests (création toggle ON, toggle OFF, validation, double-validation idempotente, permission refus, exclude pending in export, scope instance, PDF generation, concurrence numérotation héritée ADR-006).

## Tests structurels associés

- `Modules/Menuiserie360/Tests/Feature/Avoir/CreateAvoirToggleOffTest` — 3 tests.
- `Modules/Menuiserie360/Tests/Feature/Avoir/CreateAvoirToggleOnTest` — 4 tests (création en pending, validation par Direction, refus Comptable, idempotence).
- `Modules/Menuiserie360/Tests/Feature/Avoir/AvoirExportFilterTest` — 2 tests (export n'inclut pas pending).
- `Modules/Menuiserie360/Tests/Unit/AvoirServiceTest` — 3 tests (shouldRequireValidation lit toggle, workflow DTO complet, cache instance).

## Références

- [ADR-006](ADR-006-invoice-numbering-atomicity.md) — numérotation atomique réutilisée pour le numéro de l'avoir.
- [ADR-024](ADR-024-menuiserie360-v2-models.md) — couvre les autres écarts CDC V2.
- [Spec Menuiserie360 v1.3](../Ins/CONCEPTION_TECHNIQUE_MENUISERIE360.md) §4.5 — BC-Finance autonome, modèle `MenuiserieInvoice` déjà livré.
- [PROTECTED_AREAS](../governance/PROTECTED_AREAS.md) L2 — numérotation factures (existant). Workflow validation = L2 SENSIBLE.
- [CDC 2026](../Ins/CAHIER%20DE%20CHARGES%20WEB%20LOGICIEL%202026%20%20%285%29.pdf) §3.6 — facturation client incl. avoirs.
- [ERP.md](../Ins/ERP.md) §11 — opérations financières auditées.

---

## Procédure de modification

ADR acceptée non modifiable. Toute évolution du workflow (seuil monétaire, multi-niveau, validation reverse-flow) → nouvelle ADR.
