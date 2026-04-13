# Eshop360 — Initialisation menu hierarchique & parametres

**Date :** 2026-04-13
**Branche :** eshop360
**Statut :** Approuve

---

## Probleme

Lors d'une nouvelle installation, quand on active le mode menu hierarchique dans Eshop360 :

1. Le menu hierarchique est **vide** — aucun channel n'existe (la table `eshop_distribution_channels` est vide)
2. Les **parametres eShop** (General, POS, Facturation, Imprimante, FNE) ne sont pas accessibles depuis le menu hierarchique — ils n'existent dans aucun `MODULE_GROUPS`
3. Les **menus d'administration de l'instance** (Utilisateurs, Roles, Modules, Parametres systeme) sont inaccessibles car la sidebar est remplacee par le menu hierarchique
4. Il n'y a **aucun hook d'initialisation** lors de l'activation du module — pas de creation automatique du hub central

## Solution

### 1. Service `EshopInitializer`

**Fichier :** `Modules/Eshop360/Services/EshopInitializer.php`

Service central qui encapsule toute la logique de creation hub + channels + settings dans une transaction DB.

**Interface :**

```php
class EshopInitializer
{
    public function initialize(int $instanceId, array $hubData, array $channels, array $baseSettings, User $admin): void;
    public function isInitialized(int $instanceId): bool;
}
```

**`initialize()` fait :**

1. Creer le hub central (`DistributionChannel`) avec toutes les features activees
2. Creer l'entrepot associe au hub (`Warehouse`)
3. Assigner l'utilisateur admin comme admin du hub (`ChannelUser`)
4. Creer 0 a N channels de distribution avec features, taux de marge, couleur
5. Creer les entrepots pour chaque channel
6. Sauvegarder les parametres de base (infos societe, devise, POS) via `EshopSettingsService`
7. Activer le toggle `hierarchical_menu = true` dans les settings generaux
8. Tout dans une transaction DB

**`isInitialized()` :** retourne `true` si au moins 1 `DistributionChannel` actif avec `is_hub = true` existe pour l'instance.

#### Migration requise

Ajout d'un champ `is_hub` (boolean, default `false`) sur la table `eshop_distribution_channels`. Ce champ remplace la detection par slug hardcode (`saphir-plus`). Le hub central est identifie par `is_hub = true`.

La constante `HierarchicalMenuService::GLOBAL_CHANNEL_SLUG` est remplacee par une methode `HierarchicalMenuService::isHubChannel(DistributionChannel $channel): bool` qui verifie `$channel->is_hub`.

**Donnees attendues :**

```php
$hubData = [
    'name' => 'Saphir Plus',
    'code' => 'SAPHIR',
    'theme_color' => '#4f46e5',
    'features' => ['sales' => true, 'stock' => true, ...],
];

$channels = [
    [
        'name' => 'CODIFARM',
        'code' => 'CDF',
        'theme_color' => '#2c3e50',
        'margin_rate' => 0.13,
        'features' => ['sales' => true, 'stock' => true, 'finance' => false, ...],
    ],
];

$baseSettings = [
    'company_name' => '...',
    'company_address' => '...',
    'company_phone' => '...',
    'company_email' => '...',
    'tax_number' => '...',
    'currency_symbol' => 'FCFA',
    'pos_layout' => 'layout1',
    'payment_methods' => ['cash', 'card'],
];
```

---

### 2. Wizard de configuration (3 etapes)

**Approche :** Blade multi-etapes avec session + Alpine.js minimal pour l'ajout dynamique de channels.

#### Routes

```
GET  /i/{slug}/eshop360/setup/hub          -> SetupWizardController@hub
POST /i/{slug}/eshop360/setup/hub          -> SetupWizardController@storeHub
GET  /i/{slug}/eshop360/setup/channels     -> SetupWizardController@channels
POST /i/{slug}/eshop360/setup/channels     -> SetupWizardController@storeChannels
GET  /i/{slug}/eshop360/setup/settings     -> SetupWizardController@settings
POST /i/{slug}/eshop360/setup/settings     -> SetupWizardController@storeSettings
```

Route names : `eshop360.setup.hub`, `eshop360.setup.hub.store`, `eshop360.setup.channels`, `eshop360.setup.channels.store`, `eshop360.setup.settings`, `eshop360.setup.settings.store`

Middleware : `web, auth, core.spatie.team` + permission `eshop.settings.manage`

#### Controller

**Fichier :** `Modules/Eshop360/Http/Controllers/Setup/SetupWizardController.php`

**Garde :** Chaque methode GET verifie `EshopInitializer::isInitialized()`. Si `true`, redirige vers `eshop360.nav.home` (empeche de relancer le wizard).

**Retour arriere :** Chaque etape pre-remplit les champs depuis la session si des donnees y existent deja (ex: retour de l'etape 2 vers l'etape 1 conserve les donnees saisies).

**Flow :**

1. `hub()` — Affiche formulaire hub. Valeurs pre-remplies : nom="Saphir Plus", code="SAPHIR", couleur=#4f46e5, toutes features cochees.
2. `storeHub()` — Valide, stocke en session `eshop360_setup.hub`, redirige vers etape 2.
3. `channels()` — Affiche formulaire channels (Alpine.js pour ajout dynamique). Bouton "Passer cette etape" disponible.
4. `storeChannels()` — Valide 0 a N channels, stocke en session `eshop360_setup.channels`, redirige vers etape 3.
5. `settings()` — Affiche formulaire parametres de base. Valeurs pre-remplies depuis `EshopSettingsService::defaults()`.
6. `storeSettings()` — Valide, appelle `EshopInitializer::initialize()` avec tout le contenu session, nettoie la session, redirige vers `eshop360.nav.home`.

#### Vues

**Fichiers :**

- `Modules/Eshop360/Resources/views/setup/layout.blade.php` — Layout commun avec barre de progression 3 etapes
- `Modules/Eshop360/Resources/views/setup/hub.blade.php` — Etape 1 : Hub central
- `Modules/Eshop360/Resources/views/setup/channels.blade.php` — Etape 2 : Channels additionnels (Alpine.js)
- `Modules/Eshop360/Resources/views/setup/settings.blade.php` — Etape 3 : Parametres de base

**Etape 1 (hub.blade.php) :**
- Champ nom (defaut: "Saphir Plus")
- Champ code (defaut: "SAPHIR")
- Color picker theme (defaut: #4f46e5)
- Checkboxes features regroupees — toutes cochees par defaut
- Bouton "Suivant"

**Etape 2 (channels.blade.php) :**
- Liste dynamique (Alpine.js) avec bouton "Ajouter un canal"
- Par channel : nom, code, couleur, taux de marge (%), checkboxes features
- Bouton "Supprimer" par channel
- Boutons "Passer cette etape" et "Suivant"

**Etape 3 (settings.blade.php) :**
- Infos societe : nom, adresse, telephone, email, n fiscal
- Devise (defaut: FCFA)
- POS : layout (select layout1-5), modes de paiement (checkboxes)
- Bouton "Terminer et activer"

---

### 3. Points d'entree vers le wizard

#### 3.1 Depuis ModuleController (activation du module)

Dans `ModuleController::toggle()`, apres l'enable de Eshop360, si `EshopInitializer::isInitialized()` retourne `false`, rediriger vers `eshop360.setup.hub` au lieu de la liste des modules.

Message flash : "Module Eshop360 active. Configurez votre espace de vente."

#### 3.2 Depuis les parametres generaux eShop

Dans `EshopSettingsController::updateGeneral()`, quand le toggle `hierarchical_menu` passe a `true` et que `EshopInitializer::isInitialized()` retourne `false`, rediriger vers `eshop360.setup.hub` au lieu de sauvegarder.

Si des channels existent deja, sauvegarder normalement le toggle.

#### 3.3 Depuis le menu hierarchique (fallback)

Dans `HierarchicalMenuController::home()`, si `getChannels()` retourne une collection vide ET le mode hierarchique est actif, rediriger vers `eshop360.setup.hub`.

---

### 4. Tuile "Administration" dans le menu hierarchique

#### 4.1 Affichage

Tuile speciale affichee au niveau 1 (page home), apres la grille des channels. Style distinct : icone engrenage (`ti ti-settings-2`), couleur `#475569`. Visible uniquement pour `super-admin` ou `instance-admin`.

#### 4.2 Routes

```
GET /i/{slug}/eshop360/nav/admin            -> HierarchicalMenuController@admin
GET /i/{slug}/eshop360/nav/admin/{section}  -> HierarchicalMenuController@adminSection
```

#### 4.3 Contenu

La methode `admin()` collecte depuis le `HookRegistry` :
- Menus du groupe `admin` (Utilisateurs, Roles, Modules, Parametres systeme, Billing)
- Items `eshop360.eshop_settings.*` (General, POS, Facturation, Imprimante, FNE)

Affichage en tuiles au niveau 2, meme style que `modules.blade.php`.

La methode `adminSection()` affiche les sous-items d'une section admin en tuiles au niveau 3.

#### 4.4 Visibilite

Tuile et routes protegees par verification de role : `super-admin` ou `instance-admin`. Les autres roles ne voient pas cette tuile.

---

### 5. Groupe "Parametres" dans le menu hierarchique (par channel)

#### 5.1 Nouveau MODULE_GROUP

Ajout dans `HierarchicalMenuService::MODULE_GROUPS` :

```php
'parametres' => [
    'label' => 'Parametres',
    'icon' => 'ti ti-settings',
    'color' => '#64748b',
    'sections' => ['eshop360.channel_settings'],
    'features' => ['settings'],
]
```

#### 5.2 Nouveaux items de menu dans le HooksProvider

Parent `eshop360.channel_settings` avec enfants :

- **Branding du canal** — nom societe, logo, en-tetes/pieds de facture (groupe `channel_branding` de `EshopSettingsService`)
- **Features du canal** — activer/desactiver les modules pour ce channel (groupe `features` de `EshopSettingsService`)
- **Membres du canal** — gerer qui a acces a ce channel

#### 5.3 Routes et controller

**Fichier :** `Modules/Eshop360/Http/Controllers/Settings/ChannelSettingsController.php`

```
GET  /i/{slug}/eshop360/channel-settings/{channel}/branding  -> ChannelSettingsController@branding
PUT  /i/{slug}/eshop360/channel-settings/{channel}/branding  -> ChannelSettingsController@updateBranding
GET  /i/{slug}/eshop360/channel-settings/{channel}/features  -> ChannelSettingsController@features
PUT  /i/{slug}/eshop360/channel-settings/{channel}/features  -> ChannelSettingsController@updateFeatures
```

Utilise `EshopSettingsService::getForChannel()` / `setForChannel()` qui existent deja.

#### 5.4 Distinction Administration vs Parametres channel

| Tuile "Parametres" (par channel) | Tuile "Administration" |
|---|---|
| Branding, features, membres du canal | General, POS, Facturation, Imprimante, FNE |
| Visible par admin du channel | Visible par super-admin / instance-admin |
| Scope au channel selectionne | Global a l'instance |
| Niveau 2 du menu hierarchique | Niveau 1 (a cote des channels) |

---

## Fichiers a creer

| Fichier | Type |
| --- | --- |
| `Modules/Eshop360/Database/Migrations/xxxx_add_is_hub_to_distribution_channels.php` | Migration |
| `Modules/Eshop360/Services/EshopInitializer.php` | Service |
| `Modules/Eshop360/Http/Controllers/Setup/SetupWizardController.php` | Controller |
| `Modules/Eshop360/Http/Controllers/Settings/ChannelSettingsController.php` | Controller |
| `Modules/Eshop360/Resources/views/setup/layout.blade.php` | Vue |
| `Modules/Eshop360/Resources/views/setup/hub.blade.php` | Vue |
| `Modules/Eshop360/Resources/views/setup/channels.blade.php` | Vue |
| `Modules/Eshop360/Resources/views/setup/settings.blade.php` | Vue |
| `Modules/Eshop360/Resources/views/hierarchical-menu/admin.blade.php` | Vue |
| `Modules/Eshop360/Resources/views/hierarchical-menu/admin-section.blade.php` | Vue |
| `Modules/Eshop360/Resources/views/channel-settings/branding.blade.php` | Vue |
| `Modules/Eshop360/Resources/views/channel-settings/features.blade.php` | Vue |

## Fichiers a modifier

| Fichier | Modification |
|---|---|
| `Modules/Eshop360/Services/HierarchicalMenuService.php` | Ajout groupe `parametres` dans MODULE_GROUPS + remplacement `GLOBAL_CHANNEL_SLUG` par `isHubChannel()` |
| `Modules/Eshop360/Providers/Eshop360HooksProvider.php` | Ajout items `eshop360.channel_settings.*` |
| `Modules/Eshop360/Http/Controllers/Navigation/HierarchicalMenuController.php` | Ajout methodes `admin()`, `adminSection()`, modification `home()` (tuile admin + fallback wizard) |
| `Modules/Eshop360/Resources/views/hierarchical-menu/home.blade.php` | Ajout tuile Administration |
| `Modules/Eshop360/Routes/web.php` | Ajout routes setup, admin, channel-settings |
| `Modules/Eshop360/Http/Controllers/Settings/EshopSettingsController.php` | Modification `updateGeneral()` pour redirection wizard |
| `Modules/ModuleManager/Http/Controllers/ModuleController.php` | Modification `toggle()` pour redirection wizard apres activation Eshop360 |
