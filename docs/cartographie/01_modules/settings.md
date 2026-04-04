# Module : Settings

## 1. Description fonctionnelle
- **Objectif :** Paramétrage centralisé de l'application, organisé en groupes dynamiques via le système de hooks.
- **Périmètre métier :** Configuration système et instance.
- **Utilisateurs cibles :** Admins instance (paramétrage), super-admins (paramétrage système).

## 2. Liste des fonctionnalités

### 2.1 Fonctionnalités principales
- **[F1] Groupes de paramètres** — Les modules enregistrent des `SettingsGroup` via hooks → Settings les affiche dynamiquement.
- **[F2] Stockage clé/valeur** — `SettingsManager` persiste les paramètres avec cache.
- **[F3] Test email** — Envoi d'un email de test pour vérifier la configuration SMTP.

### 2.3 Cas d'usage clés
- Admin → Settings → voit les groupes (général, email, POS, imprimante…) enregistrés par Core, Eshop360, etc. → modifie → sauvegarde
- Admin → teste config email → reçoit email de confirmation

## 3. Composants techniques associés

| Type | Classe/Fichier | Rôle |
|------|----------------|------|
| Controller | `SettingsController` | index, group, updateGroup, testEmail |
| Service | `SettingsManager` | CRUD + cache des paramètres |
| Model | `Setting` | Modèle clé/valeur |
| Helper | `helpers.php` | Fonctions helper (probablement `setting()`) |

## 4. Modèle de données

### 4.1 Tables propres

| Table | Description | Colonnes clés |
|-------|-------------|---------------|
| `settings` | Paramètres clé/valeur | key, value, instance_id (scoping) |

## 5. Dépendances

| Vers le module | Type | Niveau de couplage | Justification |
|---------------|------|-------------------|---------------|
| Core | core | **Moyen** | HookRegistry settings_groups |

## 6. Points sensibles

- **Scope settings :** Le `settingsScope()` dans le controller détermine si les settings sont globaux ou par instance. La logique de scoping doit être cohérente avec le contexte middleware.
- **Cache invalidation :** Le `SettingsManager` utilise un cache — toute modification directe en DB sans passer par le service ne sera pas reflétée.
