# Module : Users

## 1. Description fonctionnelle
- **Objectif :** Gérer le cycle de vie des utilisateurs, les rôles/permissions (Spatie), les memberships multi-instances et les préférences utilisateur.
- **Périmètre métier :** Administration des identités et droits d'accès.
- **Utilisateurs cibles :** Super-admins (CRUD tous users), instance-admins (CRUD users de leur instance), tous les utilisateurs (préférences).

## 2. Liste des fonctionnalités

### 2.1 Fonctionnalités principales
- **[F1] CRUD utilisateurs** — Création, édition, suppression, activation/désactivation, blocage/déblocage.
- **[F2] Gestion des rôles** — CRUD rôles avec assignation de permissions (via Spatie Permission + teams).
- **[F3] Memberships multi-instances** — Sync des appartenances utilisateur ↔ instances avec statut (active/invited/disabled) et rôle.
- **[F4] Préférences utilisateur** — Stockage préférences personnelles (thème, langue, notifications).

### 2.2 Sous-fonctionnalités
- Toggle block/active sur les utilisateurs
- `UserPolicy` empêche l'auto-suppression
- `TeamRoleAssigner` gère l'assignation de rôles dans le contexte Spatie teams

### 2.3 Cas d'usage clés
- Instance-admin → créer un utilisateur → assigner rôle "agent" → l'utilisateur reçoit les permissions du rôle sur cette instance
- Super-admin → sync memberships → ajoute un user à 3 instances avec rôles différents → `MembershipService` crée les pivots
- Utilisateur → édite ses préférences → langue, thème, préférences de notification sauvegardés

## 3. Composants techniques associés

| Type | Classe/Fichier | Rôle |
|------|----------------|------|
| Controller | `UserController` | CRUD users + toggleBlock + toggleActive |
| Controller | `RoleController` | CRUD rôles + assignation permissions |
| Controller | `UserMembershipController` | Sync memberships |
| Controller | `UserPreferenceController` | Edit/update préférences |
| Service | `MembershipService` | Orchestration membership (pivot instance_user) |
| Service | `TeamRoleAssigner` | Assignation rôle dans contexte Spatie team |
| Service | `UserPreferenceService` | CRUD préférences |
| Model | `UserPreference` | Stockage préférences |
| Policy | `UserPolicy` | viewAny, view, create, update, delete, manageMembership |
| Request | `UserStoreRequest` | full_name, username, email, password |
| Request | `UserUpdateRequest` | full_name, username, email, password (opt), is_active, is_blocked |
| Request | `MembershipSyncRequest` | memberships[] (instance_id, status, role) |
| Provider | `UsersAuthServiceProvider` | Registration Gate policies |

## 4. Modèle de données

### 4.1 Tables propres au module

| Table | Description | Colonnes clés |
|-------|-------------|---------------|
| `user_preferences` | Préférences utilisateur | user_id, key, value (selon implémentation) |

### 4.2 Tables partagées

| Table | Utilisée par | Champ de jointure |
|-------|-------------|-------------------|
| `users` | Users, Auth, Core, Eshop360 | id |
| `instance_user` | Users (membership), Core | instance_id + user_id |
| `roles` / `model_has_roles` | Users (Spatie), Core | team_id = instance_id |
| `permissions` / `role_has_permissions` | Users (Spatie), Core | permission_id |

## 5. Dépendances

| Vers le module | Type | Niveau de couplage | Justification |
|---------------|------|-------------------|---------------|
| Core | core | **Fort** | User model, instance_user, Spatie Permissions, TeamContext |

## 6. Points sensibles

- **Zone critique :** `TeamRoleAssigner` manipule `setPermissionsTeamId()` — la valeur `0` (global) vs `N` (instance) est critique. Un mauvais team_id corrompt les assignations de rôles.
- **Convention B360 :** `instance_id = 0` = rôle cross-instance (super-admin). C'est une convention non standard de Spatie qui doit être documentée et respectée partout.
- **Pas de tests dédiés :** Le module Users n'a pas de tests unitaires/feature propres identifiés dans la suite de tests.
