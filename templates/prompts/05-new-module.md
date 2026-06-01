# 05 — Création d'un nouveau module B360

## À utiliser quand

Tu veux créer un nouveau module métier (ex: Menuiserie360, ChantierManager, etc.) ou un nouveau module plateforme (ex: Observability, Workflow).

## Procédure recommandée

### Phase 1 — Cadrage par Claude (prompt à coller)

```
[BOOTSTRAP - colle d'abord templates/prompts/00-bootstrap-claude.md]

Je veux créer un nouveau module nommé : <NomModule>

### But métier ou technique
<2-3 lignes>

### Capacités principales
- <capacité 1>
- <capacité 2>
- <capacité 3>

### Dépendances pressenties
- <module amont>
- <module amont>

### Consommateurs pressentis
- <module / front>

### Données principales
- <entité 1>
- <entité 2>

### Question : peut-on l'implémenter sans toucher aux modules existants ?

---

## Ce que je veux de toi

1. Lis :
   - `MODULE_BLUEPRINT.md`
   - `docs/architecture/MODULE_DEPENDENCY_MAP.md`
   - `docs/governance/PROTECTED_AREAS.md`
   - `docs/Ins/b360_evolution_strategy.md` (pour voir comment les modules futurs ont été pensés)
   - les modules amont mentionnés (au moins leurs PROVIDER et leur module.json)

2. Produis le **cadrage du module** au format suivant :

   ## Cadrage module — <NomModule>

   ### Identité
   - Nom : <NomModule>
   - Domaine : <Plateforme | Métier | Connecteur | Interface>
   - Couche : <L0 | L1 | L2 | L3 | L3+>
   - Propriétaire fonctionnel : <à définir>
   - Propriétaire technique : <à définir>

   ### Justification de l'existence
   - Pourquoi un module séparé plutôt qu'une extension d'un module existant ?
   - Quel sous-domaine métier représente-t-il ?

   ### Capacités exposées
   - <capacité + endpoint/action>

   ### Dépendances amont (modules dont il dépend)
   - <module> (via : contrat / event / contrôleur ?)

   ### Dépendances aval (modules qui le consommeront)
   - <module ou front> (via : contrat / event / endpoint ?)

   ### Tables proposées
   - <table> (préfixe : <prefix_>, scope : tenant + ?)

   ### Permissions à exposer (HookRegistry)
   - <permission 1>
   - <permission 2>

   ### Features à exposer (FeatureRegistry)
   - <feature 1>

   ### Événements publiés
   - <event 1>

   ### Événements consommés
   - <event 1>

   ### Settings exposés (HookRegistry)
   - <group de settings>

   ### Routes proposées
   - <prefix /i/{slug}/<module>/...>

   ### Migration plan (par phases)
   - Phase 1 : squelette + provider + tables initiales + permissions + tests smoke
   - Phase 2 : <…>
   - Phase 3 : <…>

   ### Risques identifiés
   - <risque + mitigation>

   ### Compatibilité avec deptrac
   - Couche cible : <Lx>
   - Modules autorisés : <liste>

   ### ADR nécessaire ?
   - <oui/non + raison>

3. Si une dépendance pressentie viole les règles d'architecture, **propose un contrat à créer** (interface Modules\X\Contracts\) plutôt qu'un import direct.

4. Si le module doit consommer un modèle d'un autre module (Eshop360 par exemple), **propose un contrat de lecture** (Repository / QueryBus) plutôt qu'un use direct.

5. Conclus par un hand-off Codex pour Phase 1 (squelette) au format documenté dans AGENTS.md.
```

### Phase 2 — Squelette par Codex

```
[BOOTSTRAP - colle d'abord templates/prompts/00-bootstrap-codex.md]

Voici le cadrage produit par Claude pour le nouveau module <NomModule>.

[COLLER ICI LE CADRAGE]

---

## Ce que je veux de toi

1. Crée le squelette via :
   ```
   php artisan module:make <NomModule>
   ```

2. Adapte la structure générée pour respecter la convention B360 :
   ```
   Modules/<NomModule>/
     Application/
       Commands/
       Queries/
       Services/
       DTO/
       Policies/
     Domain/
       Models/
       ValueObjects/
       Events/
       Exceptions/
       Contracts/
     Infrastructure/
       Persistence/
       Repositories/
       Providers/
       Mappers/
       Jobs/
     Http/
       Controllers/
       Requests/
       Resources/
       Middleware/
     Database/
       Migrations/
       Seeders/
       Factories/
     Console/
     Listeners/
     Routes/
       web.php
       api.php
     Resources/
       views/
       lang/
     Tests/
       Unit/
       Feature/
       Integration/
     Config/
       config.php
       hooks.php
     module.json
     composer.json
     README.md
     SPEC.md
     CHANGELOG.md
   ```

3. **Module.json** : déclare nom, version, dépendances, providers.

4. **Service Provider principal** : enregistre les hooks (menu, permissions, features, settings).

5. **Migrations initiales** : crée les tables avec scope tenant (`instance_id` + index + foreign key).

6. **Permissions** : déclare via HookRegistry dans le Provider.

7. **Routes vides** : prépare web.php et api.php avec le prefix d'instance.

8. **Tests smoke** :
   - test que le module est bien chargé
   - test que le ServiceProvider enregistre les hooks
   - test que les permissions sont exposées
   - test que les routes répondent (au moins une)

9. **README module** : ce que fait le module, ses dépendances, sa version.

10. **SPEC** : reprend le cadrage de Claude.

11. **Adapte deptrac.yaml** : ajoute le module en couche cible avec ses dépendances autorisées.

12. **Met à jour** :
    - `docs/index/MODULE_INDEX.md`
    - `docs/architecture/MODULE_DEPENDENCY_MAP.md`
    - `docs/memory/CURRENT_STATE.md`
    - `docs/memory/RECENT_DECISIONS.md`
    - `CHANGELOG_ARCHITECTURAL.md`

13. **Lance le pipeline qualité** :
    ```
    make qa
    ```

14. **Rends le hand-off à Claude pour review** au format AGENTS.md.

## Important

Ne mets **aucune logique métier fictive** dans le squelette. Pas de "TODO implémenter ici". Le squelette doit être propre, vide, prêt à recevoir les capacités lot par lot.
```

### Phase 3 — Review et ADR par Claude

Utilise `templates/prompts/03-claude-review.md` standard, en exigeant en plus :

- Vérification que deptrac est bien à jour
- Création/validation d'un ADR si le module introduit une nouvelle couche
- Mise à jour de `docs/architecture/MODULE_DEPENDENCY_MAP.md`
- Vérification que le module respecte le `MODULE_BLUEPRINT.md`

### Phases 4+ — Implémentation lot par lot

Chaque capacité du module est ensuite implémentée comme un lot normal (cycle Claude → Codex → Claude).
