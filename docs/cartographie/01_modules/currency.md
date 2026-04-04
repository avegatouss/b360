# Module : Currency

## 1. Description fonctionnelle
- **Objectif :** Gérer les devises de la plateforme — taux de change, devise par défaut, mise à jour automatique.
- **Périmètre métier :** Finance transversale.
- **Utilisateurs cibles :** Admins (configuration devises).

## 2. Liste des fonctionnalités

### 2.1 Fonctionnalités principales
- **[F1] CRUD devises** — Création, édition, suppression de devises (code, symbole, décimales, taux).
- **[F2] Devise par défaut** — Définition d'une devise de référence.
- **[F3] Mise à jour taux** — Commande artisan `UpdateExchangeRates` pour mise à jour automatique depuis API externe.

## 3. Composants techniques associés

| Type | Classe/Fichier | Rôle |
|------|----------------|------|
| Controller | `CurrencyController` | index, store, update, destroy, setDefault, updateRates |
| Service | `CurrencyManager` | Logique métier devises + conversion |
| Model | `Currency` | Modèle devise |
| Console | `UpdateExchangeRates` | Commande planifiée mise à jour taux |

## 4. Modèle de données

| Table | Description | Colonnes clés |
|-------|-------------|---------------|
| `currencies` | Devises | code (unique), name, symbol, decimals, rate, is_default, is_active, auto_update |

## 5. Dépendances

| Vers le module | Type | Niveau de couplage | Justification |
|---------------|------|-------------------|---------------|
| Core | core | **Faible** | HookRegistry (hook provider) |

## 6. Points sensibles

- **Isolation :** La table `currencies` ne semble pas scoped par instance (pas de `instance_id`). Si chaque instance a besoin de devises différentes, c'est une limitation.
- **API externe :** La mise à jour des taux dépend d'un service externe non identifié dans le code. [À VÉRIFIER]
