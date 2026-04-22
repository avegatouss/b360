## B360 — Makefile racine
## Point d'entrée unifié pour toutes les opérations qualité, mémoire, git.
## Convention : chaque cible doit être idempotente et auto-documentée.

.DEFAULT_GOAL := help
SHELL := /bin/bash

# Couleurs terminal
GREEN  := \033[0;32m
YELLOW := \033[0;33m
RED    := \033[0;31m
BLUE   := \033[0;34m
NC     := \033[0m

# Détection environnement
PHP        := $(shell command -v php 2>/dev/null)
COMPOSER   := $(shell command -v composer 2>/dev/null)
NODE       := $(shell command -v node 2>/dev/null)
GIT        := $(shell command -v git 2>/dev/null)

##@ Aide

.PHONY: help
help: ## Affiche cette aide
	@awk 'BEGIN {FS = ":.*##"; printf "\n${BLUE}B360 — Commandes disponibles${NC}\n  make ${YELLOW}<cible>${NC}\n"} \
		/^[a-zA-Z_-]+:.*?##/ { printf "  ${YELLOW}%-25s${NC} %s\n", $$1, $$2 } \
		/^##@/ { printf "\n${GREEN}%s${NC}\n", substr($$0, 5) }' $(MAKEFILE_LIST)

##@ Setup initial

.PHONY: setup
setup: check-prereqs install-deps install-hooks bootstrap-memory verify-setup ## Installation complète depuis zéro
	@echo "${GREEN}[OK]${NC} Setup complet terminé. Tu peux commencer à coder."

.PHONY: check-prereqs
check-prereqs: ## Vérifie que PHP 8.2+, Composer, Node, Git sont installés
	@bash scripts/ci/check-prerequisites.sh

.PHONY: install-deps
install-deps: ## composer install + npm install
	@echo "${BLUE}>>> composer install${NC}"
	@composer install --no-interaction --prefer-dist
	@if [ -f package.json ]; then echo "${BLUE}>>> npm install${NC}"; npm install; fi

.PHONY: install-hooks
install-hooks: ## Installe les hooks Git (pre-commit, commit-msg, pre-push, post-commit)
	@bash scripts/git/install-hooks.sh

.PHONY: bootstrap-memory
bootstrap-memory: ## Génère la mémoire projet à partir de l'existant
	@bash scripts/memory/bootstrap-from-existing.sh

.PHONY: verify-setup
verify-setup: ## Vérifie que toute la gouvernance est en place
	@bash scripts/ci/verify-setup.sh

##@ Pipeline qualité

.PHONY: qa
qa: lint phpstan deptrac test ## Pipeline qualité complet (avant push)
	@echo "${GREEN}[QA OK]${NC} Tu peux pusher."

.PHONY: qa-fast
qa-fast: lint phpstan ## Pipeline rapide (sans tests, sans deptrac) — pour itérations courtes
	@echo "${GREEN}[QA-FAST OK]${NC}"

.PHONY: qa-staged
qa-staged: ## Lint + phpstan UNIQUEMENT sur les fichiers stagés (utilisé par pre-commit)
	@bash scripts/quality/qa-staged-only.sh

.PHONY: lint
lint: ## Vérifie le formatage Pint (sans modifier)
	@echo "${BLUE}>>> Pint (test)${NC}"
	@vendor/bin/pint --test

.PHONY: lint-fix
lint-fix: ## Corrige le formatage Pint
	@echo "${BLUE}>>> Pint (fix)${NC}"
	@vendor/bin/pint

.PHONY: phpstan
phpstan: ## Analyse statique PHPStan + Larastan
	@echo "${BLUE}>>> PHPStan${NC}"
	@vendor/bin/phpstan analyse --memory-limit=1G --no-progress

.PHONY: phpstan-baseline
phpstan-baseline: ## Régénère la baseline PHPStan (sécurise l'existant)
	@vendor/bin/phpstan analyse --generate-baseline=tools/phpstan/baseline.neon --memory-limit=1G

.PHONY: deptrac
deptrac: ## Vérifie les règles d'architecture entre modules
	@echo "${BLUE}>>> Deptrac${NC}"
	@vendor/bin/deptrac analyse --no-progress

.PHONY: deptrac-baseline
deptrac-baseline: ## Régénère la baseline Deptrac
	@vendor/bin/deptrac analyse --formatter=baseline --output=tools/deptrac/baseline.yaml

.PHONY: deptrac-graph
deptrac-graph: ## Génère le graphe SVG des dépendances inter-modules
	@vendor/bin/deptrac analyse --formatter=graphviz-image --output=docs/architecture/dependency-graph.svg
	@echo "${GREEN}[OK]${NC} Graphe : docs/architecture/dependency-graph.svg"

.PHONY: rector
rector: ## Modernisations Rector (dry-run)
	@vendor/bin/rector process --dry-run

.PHONY: rector-apply
rector-apply: ## Applique les modernisations Rector (à utiliser avec précaution)
	@vendor/bin/rector process

.PHONY: insights
insights: ## PHP Insights (qualité globale)
	@vendor/bin/phpinsights --no-interaction --min-quality=85 --min-complexity=70 --min-architecture=85 --min-style=90

##@ Tests

.PHONY: test
test: ## Suite de tests complète en parallèle
	@echo "${BLUE}>>> Tests parallèles${NC}"
	@php artisan test --parallel

.PHONY: test-coverage
test-coverage: ## Tests avec couverture (Xdebug requis)
	@php artisan test --coverage --min=70

.PHONY: test-module
test-module: ## Tests d'un module : make test-module MODULE=Eshop360
	@if [ -z "$(MODULE)" ]; then echo "${RED}Usage: make test-module MODULE=NomDuModule${NC}"; exit 1; fi
	@php artisan test Modules/$(MODULE)/Tests --parallel

.PHONY: test-changed
test-changed: ## Tests des modules touchés par la branche courante
	@bash scripts/quality/test-changed-modules.sh

.PHONY: test-filter
test-filter: ## Tests filtrés : make test-filter FILTER=stock_lock
	@if [ -z "$(FILTER)" ]; then echo "${RED}Usage: make test-filter FILTER=motcle${NC}"; exit 1; fi
	@php artisan test --filter=$(FILTER)

##@ Base de données

.PHONY: migrate-status
migrate-status: ## État des migrations
	@php artisan migrate:status

.PHONY: migrate-pretend
migrate-pretend: ## Simule les migrations en attente (sans exécuter)
	@php artisan migrate --pretend

.PHONY: migrate
migrate: ## Lance les migrations en attente
	@php artisan migrate --force

.PHONY: migrate-rollback
migrate-rollback: ## Rollback du dernier batch
	@php artisan migrate:rollback --step=1

.PHONY: db-fresh-test
db-fresh-test: ## Reset DB de test + seeders + tests
	@php artisan migrate:fresh --env=testing --seed
	@php artisan test --parallel

##@ Modules

.PHONY: module-list
module-list: ## Liste les modules
	@php artisan module:list

.PHONY: module-routes
module-routes: ## Routes d'un module : make module-routes MODULE=eshop360
	@if [ -z "$(MODULE)" ]; then echo "${RED}Usage: make module-routes MODULE=nom${NC}"; exit 1; fi
	@php artisan route:list --name=$(MODULE)

.PHONY: module-make
module-make: ## Crée un nouveau module : make module-make NAME=NewModule
	@if [ -z "$(NAME)" ]; then echo "${RED}Usage: make module-make NAME=NomModule${NC}"; exit 1; fi
	@php artisan module:make $(NAME)
	@bash scripts/quality/scaffold-new-module.sh $(NAME)

##@ Mémoire projet

.PHONY: memory-refresh
memory-refresh: ## Régénère docs/memory/CURRENT_STATE.md
	@bash scripts/memory/refresh-current-state.sh

.PHONY: memory-check
memory-check: ## Vérifie la fraîcheur de la mémoire projet
	@bash scripts/memory/check-freshness.sh

.PHONY: memory-snapshot
memory-snapshot: ## Snapshot horodaté de la mémoire (pour rollback)
	@bash scripts/memory/snapshot.sh

.PHONY: digest-refresh
digest-refresh: ## Régénère docs/context/PROJECT_DIGEST.md (compression IA)
	@bash scripts/memory/refresh-digest.sh

##@ Audit & sécurité

.PHONY: audit-deps
audit-deps: ## Audit des dépendances Composer (CVE)
	@composer audit

.PHONY: audit-protected
audit-protected: ## Vérifie les zones protégées touchées par la branche
	@bash scripts/quality/check-protected-areas.sh

.PHONY: audit-permissions
audit-permissions: ## Régénère docs/index/PERMISSION_INDEX.md
	@bash scripts/memory/refresh-permission-index.sh

.PHONY: audit-events
audit-events: ## Régénère docs/index/EVENT_INDEX.md
	@bash scripts/memory/refresh-event-index.sh

.PHONY: audit-api
audit-api: ## Régénère docs/index/API_INDEX.md
	@bash scripts/memory/refresh-api-index.sh

.PHONY: audit-db
audit-db: ## Régénère docs/index/DB_INDEX.md
	@bash scripts/memory/refresh-db-index.sh

.PHONY: audit-all
audit-all: audit-deps audit-permissions audit-events audit-api audit-db memory-refresh ## Audit complet + index
	@echo "${GREEN}[OK]${NC} Audit complet + tous les index régénérés."

##@ Git workflow

.PHONY: new-feat
new-feat: ## Nouvelle branche feature : make new-feat scope=pricing subject=channel-engine
	@if [ -z "$(scope)" ] || [ -z "$(subject)" ]; then \
		echo "${RED}Usage: make new-feat scope=eshop360 subject=fix-stock-lock${NC}"; exit 1; \
	fi
	@bash scripts/git/new-branch.sh feat $(scope) $(subject)

.PHONY: new-fix
new-fix: ## Nouvelle branche fix : make new-fix scope=inventory subject=race-condition
	@if [ -z "$(scope)" ] || [ -z "$(subject)" ]; then \
		echo "${RED}Usage: make new-fix scope=inventory subject=race-condition${NC}"; exit 1; \
	fi
	@bash scripts/git/new-branch.sh fix $(scope) $(subject)

.PHONY: new-refactor
new-refactor: ## Nouvelle branche refactor : make new-refactor scope=core subject=extract-belongs-to-instance
	@if [ -z "$(scope)" ] || [ -z "$(subject)" ]; then \
		echo "${RED}Usage: make new-refactor scope=core subject=description${NC}"; exit 1; \
	fi
	@bash scripts/git/new-branch.sh refactor $(scope) $(subject)

.PHONY: new-chore
new-chore: ## Nouvelle branche chore : make new-chore scope=ci subject=add-deptrac
	@if [ -z "$(scope)" ] || [ -z "$(subject)" ]; then \
		echo "${RED}Usage: make new-chore scope=ci subject=description${NC}"; exit 1; \
	fi
	@bash scripts/git/new-branch.sh chore $(scope) $(subject)

.PHONY: new-docs
new-docs: ## Nouvelle branche docs : make new-docs scope=architecture subject=adr-005-events
	@if [ -z "$(scope)" ] || [ -z "$(subject)" ]; then \
		echo "${RED}Usage: make new-docs scope=architecture subject=description${NC}"; exit 1; \
	fi
	@bash scripts/git/new-branch.sh docs $(scope) $(subject)

.PHONY: branch-info
branch-info: ## Infos sur la branche courante (fichiers, modules touchés, zones protégées)
	@bash scripts/git/branch-info.sh

##@ Cache & maintenance

.PHONY: cache-clear
cache-clear: ## Vide tous les caches Laravel
	@php artisan optimize:clear
	@php artisan view:clear
	@php artisan route:clear
	@php artisan config:clear
	@php artisan cache:clear

.PHONY: clean
clean: cache-clear ## Nettoie + supprime les caches d'analyse
	@rm -rf .phpunit.cache .phpunit.result.cache
	@rm -rf storage/framework/views/*
	@echo "${GREEN}[OK]${NC} Nettoyage terminé."

##@ Pré-commit / pré-push

.PHONY: pre-commit
pre-commit: qa-staged ## Vérifications qu'exécute le hook pre-commit (override possible)

.PHONY: pre-push
pre-push: lint phpstan deptrac test-changed memory-check ## Vérifications qu'exécute le hook pre-push

##@ Documentation

.PHONY: docs-serve
docs-serve: ## Serve la doc en local (si MkDocs ou similaire installé)
	@if command -v mkdocs >/dev/null 2>&1; then mkdocs serve; else echo "${YELLOW}MkDocs non installé. cd docs/ && python -m http.server 8080${NC}"; fi

.PHONY: docs-validate-links
docs-validate-links: ## Vérifie les liens dans la documentation
	@bash scripts/quality/validate-doc-links.sh

##@ Claude Code & Codex

.PHONY: claude-bootstrap
claude-bootstrap: ## Lance Claude Code avec contexte pré-chargé
	@cat templates/prompts/00-bootstrap-claude.md
	@echo ""
	@echo "${YELLOW}>>> Lance maintenant : claude${NC}"
	@echo "${YELLOW}>>> Puis colle le prompt ci-dessus${NC}"

.PHONY: codex-bootstrap
codex-bootstrap: ## Lance Codex avec contexte pré-chargé
	@cat templates/prompts/00-bootstrap-codex.md
	@echo ""
	@echo "${YELLOW}>>> Lance maintenant : codex${NC}"
	@echo "${YELLOW}>>> Puis colle le prompt ci-dessus${NC}"
