# Module : Instances

> ⚠️ **Archive historique (pré-R-101).** Certains constats ont été résolus depuis ; lire d'abord `docs/context/PROJECT_DIGEST.md`, `docs/memory/OPEN_RISKS.md`, `docs/memory/RECENT_DECISIONS.md` et les ADR pertinents. Voir aussi [`DOCUMENTATION_INDEX.md`](../../DOCUMENTATION_INDEX.md) pour la taxonomy.

## 1. Description fonctionnelle
- **Objectif :** Provisionnement et gestion des instances (tenants) de la plateforme.
- **Périmètre métier :** Administration multi-tenancy.
- **Utilisateurs cibles :** Super-admins uniquement.

## 2. Liste des fonctionnalités

### 2.1 Fonctionnalités principales
- **[F1] CRUD instances** — Création, édition, suppression d'instances avec slug, domain, subdomain, database.
- **[F2] Provisionnement DB** — `InstanceProvisioner` crée la base de données de l'instance et exécute les migrations.
- **[F3] Toggle activation** — Activation/désactivation d'instance.

### 2.3 Cas d'usage clés
- Super-admin → crée instance "pharmacie-abidjan" → slug auto → DB créée → migrations exécutées → instance active
- Super-admin → désactive une instance → les users de cette instance ne peuvent plus se connecter

## 3. Composants techniques associés

| Type | Classe/Fichier | Rôle |
|------|----------------|------|
| Controller | `InstanceController` | index, create, store, show, edit, update, destroy, toggle |
| Service | `InstanceProvisioner` | Provisionnement DB + migrations |
| Request | `InstanceStoreRequest` | name, slug, domain, subdomain, database, is_active |
| Request | `InstanceUpdateRequest` | name, domain, subdomain, is_active |

## 4. Modèle de données

### 4.1 Tables partagées

| Table | Rôle |
|-------|------|
| `instances` | Table principale (dans DB system) — id, uuid, name, slug, domain, subdomain, path, database, db_driver, is_active, is_maintenance, meta (json) |

## 5. Dépendances

| Vers le module | Type | Niveau de couplage | Justification |
|---------------|------|-------------------|---------------|
| Core | core | **Fort** | Instance model (défini dans `app/Instances/`), InstanceManager, InstanceResolver |

## 6. Points sensibles

- **Note architecturale :** Le modèle `Instance` est dans `app/Instances/Instance.php`, pas dans le module Instances. Le module est surtout un wrapper UI/CRUD autour de la logique Core.
- **Provisionnement :** La création de DB est une opération destructive (irréversible si mal configurée). Le `InstanceProvisioner` doit être robuste face aux erreurs de connexion.
