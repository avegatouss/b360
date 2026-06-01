# PERMISSION_INDEX — B360

> Auto-généré par `make audit-permissions`. Mise à jour : **2026-05-06 14:01:26    **

> Source : extraction des chaînes ressemblant à des permissions Spatie dans les HooksProviders et PermissionGroup.

---

## Permissions détectées par module

### Auth

- `auth.ip-check`
- `auth.lockscreen`
- `instances.login`

### Billing

- `billing.feature`
- `eshop.feature`

### Core

- `billing.manage`
- `billing.view`
- `core.instance.bind`
- `core.instance.maintenance`
- `core.instance.member`
- `core.instance.resolved`
- `core.redirect.not_installed`
- `core.redirect.root_after_install`
- `core.root.superadmin`
- `core.spatie.team`
- `demo.guard`
- `hooks.providers`
- `minimal.view`
- `mod.manage`
- `mod.view`
- `permission.name`
- `temp.view`
- `users.manage`
- `users.view`

### Dashboard

- `config.php`
- `modules.namespace`
- `modules.paths.generator.config.path`
- `view.paths`

### Eshop360

- `eshop.channel.context`
- `eshop.channel.feature`
- `eshop.channel.member`
- `eshop.channel.resolve`
- `eshop.channel.role`
- `eshop.feature`
- `eshop.user.assignments`
- `layout.partials.sidebar`

### Installer

- `app.installed`
- `cache.default`
- `installer.not_installed`
- `session.driver`

### Lang

- `translation.loader`


## Permissions Spatie en base (si DB accessible)

```bash
php artisan tinker --execute="dump(\Spatie\Permission\Models\Permission::pluck('name'));"
```

---

## Convention

- format `<scope>.<resource>.<action>` (ex: `eshop.product.create`)
- toute permission est exposée via `HookRegistry` (groupe `PermissionGroup`)
- toute permission est rattachée à un check explicite dans une Policy ou un middleware
