# Module : ModuleManager

> ⚠️ **Archive historique (pré-R-101).** Certains constats ont été résolus depuis ; lire d'abord `docs/context/PROJECT_DIGEST.md`, `docs/memory/OPEN_RISKS.md`, `docs/memory/RECENT_DECISIONS.md` et les ADR pertinents. Voir aussi [`DOCUMENTATION_INDEX.md`](../../DOCUMENTATION_INDEX.md) pour la taxonomy.

## 1. Description fonctionnelle
- **Objectif :** Interface d'administration pour activer, désactiver, uploader et supprimer des modules.
- **Périmètre métier :** Gestion du cycle de vie des modules.
- **Utilisateurs cibles :** Super-admins uniquement.

## 2. Liste des fonctionnalités

### 2.1 Fonctionnalités principales
- **[F1] Liste des modules** — Affiche tous les modules avec leur statut (activé/désactivé) via `module.json`.
- **[F2] Toggle activation** — Active/désactive un module (mise à jour `modules_statuses.json` + table `modules`).
- **[F3] Upload module** — Upload d'un nouveau module (ZIP).
- **[F4] Suppression module** — Suppression d'un module installé.

## 3. Composants techniques associés

| Type | Classe/Fichier | Rôle |
|------|----------------|------|
| Controller | `ModuleController` | index, show, toggle, upload, destroy |
| Service | `ModuleInstaller` | Orchestration installation module |

## 4. Modèle de données

Utilise la table `modules` (Core) sur connection `system`.

## 5. Dépendances

| Vers le module | Type | Niveau de couplage | Justification |
|---------------|------|-------------------|---------------|
| Core | core | **Fort** | ModuleManager (Core), table `modules`, HookManager |

## 6. Points sensibles

- **Upload non sandboxé :** L'upload de modules est une opération potentiellement dangereuse (exécution de code arbitraire). Vérifier que `ModuleInstaller` valide la structure du ZIP. [À VÉRIFIER]
- **Pas de tests dédiés.**
