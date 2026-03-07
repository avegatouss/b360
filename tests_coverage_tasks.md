# Backlog couverture tests (fonctionnels + unitaires)

Date: 2026-03-07

## P0 - Critique (securite / isolation / auth)
- [x] Auth: login global (success/fail), login instance (membership actif / inactif).
- [x] Auth: selection d'instance (0/1/n instances actives), route guards.
- [x] Middlewares Core: bind instance, ensure instance resolved, ensure membership active.
- [x] Auth: logout global/instance.
- [x] Users: autorisations users.view/users.manage + scoping instance.
- [x] Installer: requirements, test DB, validation config/admin, start/stream SSE.

## P1 - Important (gestion plateforme)
- [x] Instances: CRUD + protections instance root + toggle active.
- [x] Instances: provisioning DB par instance (strategies shared/dedicated).
- [x] ModuleManager: liste + toggle + protections modules core.
- [x] Settings: controller group/update + hooks filtering end-to-end.

## P2 - Confort / regression
- [x] Dashboard: compteurs membres actifs/total users par instance.
- [x] ModuleManager: upload ZIP + migrations + uninstall.

## Unit tests manquants (services/support)
- [x] Core: CurrentInstance get/set.
- [x] Auth: LoginRedirector (activeInstances + redirections).
- [x] Users: MembershipService (add/update/remove/status + validation).
- [x] Users: TeamRoleAssigner (whitelist roles + default user).
- [ ] Core: ModuleManager (unit tests ciblant isEnabled + cache)
- [ ] Settings: SettingsManager (tests unit sur extractGroup/extractKey)
- [ ] Installer: helpers internes (si decoupes en services)
