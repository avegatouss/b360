# Audit documentaire B360 — 2026-05-06

> Perimetre : audit de l'ensemble du dossier `docs/`, incluant les documents Markdown, les indexes auto-generes, les ADR, les dossiers d'audit/cartographie, les specs, les runbooks, les artefacts UI et le pack vibecoding archive.
> Branche observee : `refactor/eshop360-s12-closure`.
> Nature du lot : audit documentaire, aucune modification du code applicatif.

---

## IMPACT_ANALYSIS

### Changement demande

Produire une lecture consolidee de `docs/` et deux livrables : un rapport d'audit et un plan d'action/prevision pour la suite.

### Fichiers crees

- `docs/AUDIT_DOCUMENTAIRE_2026-05-06.md`
- `docs/PLAN_ACTION_DOCUMENTAIRE_2026-05-06.md`

### Fichiers volontairement non modifies

- `docs/memory/CURRENT_STATE.md`
- `docs/memory/OPEN_RISKS.md`
- `docs/memory/RECENT_DECISIONS.md`
- `docs/index/*.md`
- fichiers sources applicatifs

### Modules touches

Aucun module applicatif. Le perimetre est documentaire.

### Zones protegees touchees

Aucune zone L1/L2 de code. Les zones critiques sont seulement referencees.

### Risques de regression

Faible. Les deux nouveaux fichiers n'alterent aucun contrat runtime. Le risque principal est documentaire : creer une source supplementaire qui divergerait si elle n'est pas traitee comme audit date.

---

## Resume executif

B360 dispose d'une documentation exceptionnellement abondante pour un projet Laravel modulaire : audits fonctionnels, cartographies, ADR, memoire IA, guides de calcul, specs produit et runbooks. Le probleme n'est plus l'absence de documentation, mais sa stratification : des documents de mars/debut avril decrivent un Eshop360 fragile, tandis que les ADR et la memoire du 22 avril au 5 mai actent la fermeture des risques R-001..R-104, R-201..R-301 et surtout R-101.

La source la plus fiable pour l'etat actuel est le triptyque `docs/context/PROJECT_DIGEST.md`, `docs/memory/OPEN_RISKS.md`, `docs/memory/RECENT_DECISIONS.md`, complete par `docs/adr/ADR-020-eshop360-r101-closure.md`. En revanche, `docs/STATUS.md` et `docs/README.md` restent arretes au 2026-04-06 et ne reflent pas completement le chantier R-101 cloture le 2026-05-05.

Le dossier contient aussi des artefacts qui ne devraient pas vivre durablement dans `docs/` : trois backups `.env.bak.*` avec noms de variables sensibles, un zip de pack, des doublons exacts (`AUDIT_COMPLET_B3601.md`, `IMPLEMENTATION_PLAN1.md`) et de grands fichiers generes. Ils augmentent le bruit, le risque de fuite et la charge cognitive des agents.

Verdict : la documentation est globalement exploitable, mais elle doit etre gouvernee comme un systeme vivant. Sans classement "source de verite / archive / spec / artefact", les prochaines sessions risquent de reprendre de vieux constats deja resolus, notamment Codifarm, FeatureGate, multi-currency MVP, risques P0 et decoupage Eshop360.

---

## Inventaire observe

| Famille | Constat |
|---|---|
| Total fichiers sous `docs/` | 196 fichiers recenses |
| Markdown | 145 fichiers `.md` |
| Racines documentaires | `adr`, `architecture`, `audits`, `cartographie`, `context`, `eshop`, `evolution`, `extraction-ccc360`, `governance`, `index`, `Ins`, `memory`, `runbooks`, `superpowers`, `ui` |
| Artefacts volumineux | `docs/ui/selects-inventory.json` (~524 Ko), `docs/evolution/b360-vibecoding-pack.zip` (~135 Ko), plan superpowers (~80 Ko), specs multi-currency (~71 Ko) |
| Doublons exacts detectes | `AUDIT_COMPLET_B360.md` = `AUDIT_COMPLET_B3601.md`; `IMPLEMENTATION_PLAN.md` = `IMPLEMENTATION_PLAN1.md` |
| Index auto-generes | `API_INDEX.md`, `DB_INDEX.md`, `EVENT_INDEX.md`, `PERMISSION_INDEX.md` existent mais contiennent `(a regenerer)` |
| Backups sensibles | `docs/.env.bak.20260103175909`, `docs/.env.bak.20260103181443`, `docs/.env.bak.20260103182019` |

---

## Sources de verite actuelles

| Niveau | Fichiers | Fiabilite | Remarque |
|---|---|---:|---|
| S0 — Memoire IA courante | `docs/context/PROJECT_DIGEST.md`, `docs/memory/CURRENT_STATE.md`, `docs/memory/OPEN_RISKS.md`, `docs/memory/RECENT_DECISIONS.md` | Haute | Meilleure synthese actuelle, mais timestamps partiellement incoherents avec des entrees du 2026-05-05 |
| S1 — ADR | `docs/adr/ADR-001..020` | Tres haute | Historique decisionnel propre, surtout ADR-020 pour R-101 |
| S1 — Gouvernance | `docs/governance/PROTECTED_AREAS.md`, `AGENTS.md`, `CODEX.md`, `CONTRIBUTING.md` | Haute | Encadre correctement les interventions futures |
| S2 — Status board | `docs/STATUS.md`, `docs/README.md` | Moyenne | Tres utile, mais date du 2026-04-06 et donc pre-R-101 final |
| S2 — Cartographie/audits | `docs/cartographie/`, `docs/audits/`, `docs/audit_comparatif_final.md` | Moyenne | Bon historique, plusieurs constats sont resolus depuis |
| S3 — Specs futures | `docs/Ins/`, `docs/extraction-ccc360/`, `docs/superpowers/specs/` | Variable | Beaucoup de propositions, pas toutes decidees |
| S4 — Artefacts | `docs/ui/*.json`, zip pack, backups `.env` | Faible comme documentation | A archiver/deplacer hors documentation vivante |

---

## Etat projet reconstruit depuis la doc

### Plateforme

B360 est une plateforme SaaS Laravel 12 multi-tenant, modulee avec nwidart/laravel-modules. Les 13 modules actifs sont : Auth, Billing, Core, Currency, Dashboard, Demo, Eshop360, Installer, Instances, Lang, ModuleManager, Settings, Users.

Le noyau est considere stable : Core, Auth, Users, Instances, Settings, Billing et Dashboard sont documentes comme socle. Le mecanisme d'extension central est `HookRegistry` : menu, widgets, settings, permissions, features, gateways de paiement, demo providers et notifications.

### Eshop360

Eshop360 etait le principal risque de complexite. Les documents historiques parlent d'un monolithe a plus de 80 modeles, 80 controleurs et 140 migrations. La memoire recente et ADR-020 indiquent que R-101 est fermee : 13 sous-domaines ont ete extraits sous `Modules/Eshop360/Domain/<Sub>/Models/`, 88 stubs legacy supprimes, morph map centralise, imports canoniques et tests structurels ajoutes.

Le dossier `Modules/Eshop360/Models/` ne doit plus contenir que deux non-stubs : `EshopModuleSetting` et `UserAssignment`. Toute nouvelle classe morphique Domain doit etre enregistree dans le morph map central.

### Risques

`OPEN_RISKS.md` declare aucun risque critique, majeur, moyen ou faible ouvert. Les risques R-001 a R-004, R-101 a R-104, R-201, R-202 et R-301 sont fermes. Les risques fonctionnels encore visibles dans les anciens audits doivent donc etre lus comme historiques sauf preuve inverse recente.

### Tests

Les chiffres divergent selon les epoques :

- 2026-03-16 : 396 passed / 0 failed / 3 skipped.
- 2026-04-06 : 617 passed / 0 failed / 3 skipped dans `STATUS.md`.
- 2026-04-22 : 617 passed / 0 failed / 3 skipped dans `CURRENT_STATE.md`.
- 2026-05-05 : ADR-020 indique suite sequentielle 666 passed / 2 failed pre-existants / 5 skipped pour S12.

Conclusion : le chiffre public a afficher doit etre revalide par une execution actuelle avant toute communication externe.

---

## Incoherences et dette documentaire

### D-001 — Timestamps de memoire incoherents

`PROJECT_DIGEST.md`, `CURRENT_STATE.md` et `MODULE_INDEX.md` affichent une mise a jour au 2026-04-22, mais incluent des faits du 2026-05-05 (R-101 fermee, ADR-020, S12). Cela indique une mise a jour manuelle ou partielle sans regeneration complete.

Impact : un agent peut croire que la memoire est ancienne alors qu'elle contient des faits recents, ou inversement lui faire trop confiance sans verifier les indexes.

### D-002 — Index auto-generes vides

`API_INDEX.md`, `DB_INDEX.md`, `EVENT_INDEX.md` et `PERMISSION_INDEX.md` contiennent uniquement `(a regenerer)`.

Impact : les consignes demandent de s'appuyer sur les indexes, mais ceux-ci ne fournissent pas encore de veritable information. Cela force les agents a revenir au code, ce que la gouvernance veut eviter.

### D-003 — Status board pre-R-101

`STATUS.md` est tres riche, mais son snapshot principal date du 2026-04-06. Il ne dit pas explicitement que R-101/S12 a ete cloture le 2026-05-05.

Impact : la page se presente comme "source unique de verite" mais n'est plus la source la plus recente.

### D-004 — Doublons exacts

`AUDIT_COMPLET_B3601.md` duplique `AUDIT_COMPLET_B360.md`. `IMPLEMENTATION_PLAN1.md` duplique `IMPLEMENTATION_PLAN.md`.

Impact : bruit, recherche moins fiable, risque qu'un agent modifie ou cite le mauvais fichier.

### D-005 — Documents historiques non marques comme archives

Plusieurs documents anciens contiennent des constats resolus : FeatureGate encore charge, Codifarm actif, tests Eshop inexistants, InventoryX present, risques P0 ouverts, multi-currency non integre. Ces constats etaient valides a leur date, mais ne sont plus valides apres les ADR et la memoire recente.

Impact : reprise de chantiers clos, double travail, contradictions apparentes.

### D-006 — Backups `.env` dans `docs/`

Trois fichiers `.env.bak.*` sont presents sous `docs/` et contiennent des cles de variables sensibles : `APP_KEY`, `DB_PASSWORD`, `AWS_SECRET_ACCESS_KEY`, `MAIL_PASSWORD`, `REDIS_PASSWORD`, `SENTRY_LARAVEL_DSN`, `VAPID_PRIVATE_KEY`.

Impact : risque securite et hygiene Git. Meme si les valeurs n'ont pas ete reproduites dans ce rapport, la presence de backups d'environnement dans `docs/` est anormale.

### D-007 — Artefacts pack et zip dans la documentation vivante

`docs/evolution/b360-vibecoding-pack/` et `docs/evolution/b360-vibecoding-pack.zip` documentent le pack installe. C'est utile comme archive, mais ce contenu melange templates, scripts, hooks et docs de pack avec la documentation projet.

Impact : le volume de fichiers sous `docs/` gonfle artificiellement et brouille les recherches.

### D-008 — Roadmap source mentionnee mais absente

`PROJECT_DIGEST.md` reference `docs/roadmap/ROADMAP_REBUILD.md`, mais le fichier est absent dans l'arborescence observee.

Impact : lien de source de verite casse pour la priorisation.

### D-009 — Gouvernance Codex vs demande directe

`CODEX.md` dit que Codex ne doit pas ecrire sans hand-off Claude complet, tandis que la demande utilisateur directe demande la production de rapports. Pour ce lot documentaire, j'ai considere que la demande humaine borne le perimetre et ne necessite pas de modification applicative.

Impact : a clarifier dans la gouvernance pour les lots "audit documentaire uniquement".

### D-010 — Absence de statut "archive / vivant / spec"

La documentation ne porte pas systematiquement un statut explicite. Certains fichiers ont une date et un contexte, d'autres non. Les docs `Ins/` melangent decisions, hypotheses, prompts et plans futurs.

Impact : un lecteur ne sait pas toujours si un document est prescriptif, descriptif, historique ou obsolet.

---

## Audit par domaine documentaire

### Memoire et contexte

Points forts :
- Excellente synthese IA dans `PROJECT_DIGEST.md`.
- `OPEN_RISKS.md` est detaille, stable et actionnable.
- `RECENT_DECISIONS.md` retrace tres bien R-101.

Faiblesses :
- Dates de generation en retard.
- `CURRENT_STATE.md` a des champs "En cours" et "Prochaines actions" vides.
- `MODULE_HEALTH.md` est mentionne dans `AGENTS.md`, mais non observe dans l'inventaire.

### ADR

Points forts :
- Suite ADR-001..020 coherente.
- Les decisions critiques sont bien motivees.
- ADR-020 est le document de reference pour la nouvelle architecture Eshop360.

Faiblesses :
- ADR-001 est annonce "a creer" dans une ancienne entree de `RECENT_DECISIONS.md`, alors qu'il existe.
- Les ADR intermediaires R-101 sont nombreux ; une page index "ADR par theme" aiderait.

### Audits et cartographie

Points forts :
- Couverture large des modules et d'Eshop360.
- Bon historique des faiblesses initiales.
- `audit_comparatif_final.md` est tres utile pour comprendre la chronologie.

Faiblesses :
- Beaucoup de constats ne sont plus actuels.
- Les fichiers racine `AUDIT_COMPLET_B360*.md` indiquent "0 test Eshop360" et "12 modules" : archive a marquer fortement.
- Les dossiers `audits/` et `cartographie/` se recoupent fortement.

### Eshop

Points forts :
- `eshop/99-chantier-remediation.md` trace tres bien les lots de mars.
- Les fiches par sous-domaine donnent une bonne lecture produit.

Faiblesses :
- Les fiches ne sont pas toutes realignees apres R-101/S12.
- Certaines limites restantes (Codifarm, FeatureGate, multi-currency) sont resolues partiellement ou totalement dans les docs plus recentes.

### Specs et evolution

Points forts :
- `Ins/b360_evolution_strategy.md`, `shared_resources_strategy.md`, `eshop360_pricing_engine.md` et `currency_multi_currency_evolution.md` donnent une trajectoire claire.
- Les specs Menuiserie360 et CCC360 sont riches.

Faiblesses :
- Elles datent souvent du 2026-04-04/06 et doivent etre rebasees sur R-101 ferme.
- Plusieurs elements sont propositions, pas decisions.
- Risque de demarrer Menuiserie360 ou CCC360 sur des contrats Eshop qui ont change pendant S12.

### Index et automation

Points forts :
- Le pack vibecoding prevoit des scripts de regeneration.
- `MODULE_INDEX.md` donne des compteurs utiles.

Faiblesses :
- API/DB/Event/Permission indexes sont vides.
- Pas de validation de liens/document status observee.
- La roadmap referencee est manquante.

### UI inventory

Points forts :
- `selects-inventory.md/json` fournit une base utile pour rationaliser les selects en dur.

Faiblesses :
- Le JSON volumineux est un artefact genere ; il doit etre considere comme output regenerable, pas comme documentation narrative.

---

## Risques actuels deduits de la documentation

| ID audit | Risque | Severite | Justification | Action recommandee |
|---|---|---:|---|---|
| DOC-R1 | Source de verite confuse apres R-101 | Haute | `STATUS.md` pre-R-101 vs memoire/ADR post-R-101 | Rebaser `STATUS.md`, `README.md`, indexes |
| DOC-R2 | Backups `.env` dans `docs/` | Haute | Variables sensibles detectees | Deplacer hors repo ou supprimer avec rotation secrets si necessaire |
| DOC-R3 | Index vides | Haute | `API/DB/EVENT/PERMISSION_INDEX` inutilisables | Lancer regeneration et verifier contenu |
| DOC-R4 | Doublons exacts | Moyenne | Deux paires de fichiers identiques | Archiver/supprimer les copies suffixees `1` |
| DOC-R5 | Specs futures non rebasees | Moyenne | Menuiserie360, CCC360, shared resources pre-S12 | Revalider contracts sur Domain FQN + morph map |
| DOC-R6 | Roadmap absente | Moyenne | `ROADMAP_REBUILD.md` referencee mais absente | Creer ou corriger la reference |
| DOC-R7 | Anciennes dettes "ouvertes" dans archives | Moyenne | vieux audits citent FeatureGate/Codifarm/InventoryX | Ajouter bannieres d'archive et liens vers ADR |

---

## Prevision technique et produit

### Court terme

Le prochain bon mouvement n'est pas d'ajouter une grosse fonctionnalite. C'est de stabiliser la memoire documentaire post-R-101 : regenerer indexes, mettre a jour le status board, marquer les archives, nettoyer les artefacts sensibles, et revalider une suite de tests actuelle.

### Moyen terme

Une fois la documentation vivante alignee, les chantiers a plus forte valeur sont :

1. Tightening deptrac cross-EshopX : transformer progressivement les 198 `skip_violations` R-101 en rulesets explicites.
2. Clarifier le futur des morph keys : garder legacy FQN durablement ou planifier migration short keys.
3. Finaliser l'industrialisation Eshop360 : gating premium, POS secondaires, portail grossiste/public, logistique plus riche, API documentee.
4. Rebaser les specs Menuiserie360/CCC360 sur les contrats Domain post-S12.

### Long terme

B360 peut evoluer vers une plateforme multi-metiers credible si le Core reste mince, si Eshop360 expose des contrats plutot que des modeles, et si les nouveaux modules consomment les capacites par HookRegistry, events, services applicatifs ou contracts. Le risque majeur serait de reintroduire un "mega module metier" sous un autre nom.

---

## Conclusion

La documentation de B360 est un actif fort, pas un simple depot de notes. Mais elle doit maintenant passer d'une logique "journal de chantier" a une logique "systeme documentaire gouverne". Le projet a corrige beaucoup de risques que les anciens audits signalent encore. La prochaine qualite a gagner est donc la lisibilite : une source de verite nette, des archives marquees, des indexes utiles et une roadmap actuelle.

