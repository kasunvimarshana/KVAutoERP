# =============================================================================
#  SaaS Inventory Management System – Developer Makefile
# =============================================================================
.DEFAULT_GOAL := help
COMPOSE        := docker compose
PHP_SERVICES   := auth-service tenant-service inventory-service order-service saga-orchestrator
ALL_SERVICES   := $(PHP_SERVICES) notification-service

# Colour helpers
CYAN  := \033[0;36m
RESET := \033[0m

.PHONY: help up down restart build install migrate seed test \
        test-auth test-tenant test-inventory test-order test-saga \
        logs logs-follow shell-auth shell-tenant shell-inventory \
        shell-order shell-saga shell-notification fresh ps \
        queue-work cache-clear route-list config-cache

help: ## Show this help message
	@echo ""
	@echo "  SaaS Inventory Management – Make Targets"
	@echo ""
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) \
	  | sort \
	  | awk 'BEGIN {FS = ":.*?## "}; {printf "  $(CYAN)%-28s$(RESET) %s\n", $$1, $$2}'
	@echo ""

# ─── Core Lifecycle ──────────────────────────────────────────────────────────

up: ## Start all containers in detached mode
	@echo "▶  Starting SaaS platform..."
	$(COMPOSE) up -d
	@echo "✔  All services are up. Gateway → http://localhost"

down: ## Stop and remove all containers (keeps volumes)
	@echo "▶  Stopping SaaS platform..."
	$(COMPOSE) down --remove-orphans
	@echo "✔  All services stopped."

restart: ## Restart all containers
	$(COMPOSE) restart

build: ## Build (or rebuild) all Docker images
	@echo "▶  Building images..."
	$(COMPOSE) build --parallel --no-cache
	@echo "✔  Build complete."

build-nocache: ## Build images without layer cache
	$(COMPOSE) build --no-cache --parallel

ps: ## Show running container status
	$(COMPOSE) ps

# ─── Dependency Installation ─────────────────────────────────────────────────

install: ## Install Composer & npm dependencies for all services
	@echo "▶  Installing PHP dependencies..."
	@for svc in $(PHP_SERVICES); do \
	  echo "  → $$svc"; \
	  $(COMPOSE) exec -T $$svc composer install --no-interaction --prefer-dist --optimize-autoloader; \
	done
	@echo "▶  Installing Node.js dependencies..."
	$(COMPOSE) exec -T notification-service npm ci --prefer-offline
	@echo "✔  All dependencies installed."

install-dev: ## Install dependencies with dev packages (local development)
	@for svc in $(PHP_SERVICES); do \
	  echo "  → $$svc (dev)"; \
	  $(COMPOSE) exec -T $$svc composer install --no-interaction --prefer-dist; \
	done
	$(COMPOSE) exec -T notification-service npm install

# ─── Database ─────────────────────────────────────────────────────────────────

migrate: ## Run database migrations for all PHP services
	@echo "▶  Running migrations..."
	@for svc in $(PHP_SERVICES); do \
	  echo "  → $$svc"; \
	  $(COMPOSE) exec -T $$svc php artisan migrate --force; \
	done
	@echo "✔  Migrations complete."

migrate-rollback: ## Rollback last migration batch for all services
	@for svc in $(PHP_SERVICES); do \
	  echo "  → $$svc rollback"; \
	  $(COMPOSE) exec -T $$svc php artisan migrate:rollback --force; \
	done

seed: ## Run database seeders for all PHP services
	@echo "▶  Seeding databases..."
	@for svc in $(PHP_SERVICES); do \
	  echo "  → $$svc"; \
	  $(COMPOSE) exec -T $$svc php artisan db:seed --force; \
	done
	@echo "✔  Seeding complete."

fresh: ## Drop all tables and re-run all migrations + seeders (DESTRUCTIVE)
	@echo "⚠  Refreshing all databases (data will be lost)..."
	@read -p "  Continue? [y/N]: " confirm && [ "$$confirm" = "y" ] || exit 1
	@for svc in $(PHP_SERVICES); do \
	  echo "  → $$svc"; \
	  $(COMPOSE) exec -T $$svc php artisan migrate:fresh --seed --force; \
	done
	@echo "✔  All databases refreshed."

# ─── Testing ──────────────────────────────────────────────────────────────────

test: ## Run the full test suite for all services
	@echo "▶  Running tests for all services..."
	@$(MAKE) test-auth
	@$(MAKE) test-tenant
	@$(MAKE) test-inventory
	@$(MAKE) test-order
	@$(MAKE) test-saga
	@$(MAKE) test-notification
	@echo "✔  All tests complete."

test-auth: ## Run auth-service tests
	@echo "  → auth-service"
	$(COMPOSE) exec -T auth-service php artisan test --parallel --coverage-text 2>&1

test-tenant: ## Run tenant-service tests
	@echo "  → tenant-service"
	$(COMPOSE) exec -T tenant-service php artisan test --parallel --coverage-text 2>&1

test-inventory: ## Run inventory-service tests
	@echo "  → inventory-service"
	$(COMPOSE) exec -T inventory-service php artisan test --parallel --coverage-text 2>&1

test-order: ## Run order-service tests
	@echo "  → order-service"
	$(COMPOSE) exec -T order-service php artisan test --parallel --coverage-text 2>&1

test-saga: ## Run saga-orchestrator tests
	@echo "  → saga-orchestrator"
	$(COMPOSE) exec -T saga-orchestrator php artisan test --parallel --coverage-text 2>&1

test-notification: ## Run notification-service tests (Jest)
	@echo "  → notification-service"
	$(COMPOSE) exec -T notification-service npm test -- --forceExit --ci 2>&1

test-coverage: ## Generate HTML coverage reports for all PHP services
	@for svc in $(PHP_SERVICES); do \
	  echo "  → $$svc coverage"; \
	  $(COMPOSE) exec -T $$svc php artisan test \
	    --coverage-html coverage-report \
	    --coverage-clover coverage.xml; \
	done

# ─── Logs ─────────────────────────────────────────────────────────────────────

logs: ## Tail last 100 lines of logs from all services
	$(COMPOSE) logs --tail=100

logs-follow: ## Follow live log output from all services
	$(COMPOSE) logs -f

logs-auth: ## Follow auth-service logs
	$(COMPOSE) logs -f auth-service

logs-tenant: ## Follow tenant-service logs
	$(COMPOSE) logs -f tenant-service

logs-inventory: ## Follow inventory-service logs
	$(COMPOSE) logs -f inventory-service

logs-order: ## Follow order-service logs
	$(COMPOSE) logs -f order-service

logs-notification: ## Follow notification-service logs
	$(COMPOSE) logs -f notification-service

logs-saga: ## Follow saga-orchestrator logs
	$(COMPOSE) logs -f saga-orchestrator

logs-gateway: ## Follow api-gateway (Nginx) logs
	$(COMPOSE) logs -f api-gateway

# ─── Interactive Shells ───────────────────────────────────────────────────────

shell-auth: ## Open a shell in auth-service
	$(COMPOSE) exec auth-service bash

shell-tenant: ## Open a shell in tenant-service
	$(COMPOSE) exec tenant-service bash

shell-inventory: ## Open a shell in inventory-service
	$(COMPOSE) exec inventory-service bash

shell-order: ## Open a shell in order-service
	$(COMPOSE) exec order-service bash

shell-saga: ## Open a shell in saga-orchestrator
	$(COMPOSE) exec saga-orchestrator bash

shell-notification: ## Open a shell in notification-service
	$(COMPOSE) exec notification-service sh

shell-mysql: ## Open a MySQL root shell
	$(COMPOSE) exec mysql mysql -u root -p

shell-redis: ## Open a Redis CLI shell
	$(COMPOSE) exec redis redis-cli -a $${REDIS_PASSWORD}

# ─── Queue Workers ────────────────────────────────────────────────────────────

queue-work: ## Start queue workers in all PHP services (blocking)
	@echo "▶  Starting queue workers (CTRL+C to stop)..."
	@for svc in $(PHP_SERVICES); do \
	  $(COMPOSE) exec -d $$svc php artisan queue:work \
	    --sleep=3 --tries=3 --timeout=90 --max-jobs=1000; \
	done

queue-restart: ## Gracefully restart all queue workers
	@for svc in $(PHP_SERVICES); do \
	  $(COMPOSE) exec -T $$svc php artisan queue:restart; \
	done

# ─── Artisan / Cache Helpers ──────────────────────────────────────────────────

cache-clear: ## Clear all application caches
	@for svc in $(PHP_SERVICES); do \
	  echo "  → $$svc"; \
	  $(COMPOSE) exec -T $$svc php artisan cache:clear; \
	  $(COMPOSE) exec -T $$svc php artisan config:clear; \
	  $(COMPOSE) exec -T $$svc php artisan route:clear; \
	  $(COMPOSE) exec -T $$svc php artisan view:clear; \
	done

config-cache: ## Warm config/route/view cache for production
	@for svc in $(PHP_SERVICES); do \
	  echo "  → $$svc"; \
	  $(COMPOSE) exec -T $$svc php artisan config:cache; \
	  $(COMPOSE) exec -T $$svc php artisan route:cache; \
	  $(COMPOSE) exec -T $$svc php artisan view:cache; \
	done

route-list: ## List all routes for a service (usage: make route-list SVC=auth-service)
	$(COMPOSE) exec -T $(SVC) php artisan route:list

passport-install: ## Install Laravel Passport keys for auth-service
	$(COMPOSE) exec -T auth-service php artisan passport:install --force

# ─── Environment Setup ────────────────────────────────────────────────────────

env-copy: ## Copy .env.example to .env if .env does not exist
	@[ -f .env ] && echo ".env already exists, skipping." || (cp .env.example .env && echo "✔  .env created from .env.example")

key-generate: ## Generate APP_KEY for all PHP services
	@for svc in $(PHP_SERVICES); do \
	  echo "  → $$svc"; \
	  $(COMPOSE) exec -T $$svc php artisan key:generate --force; \
	done

wait-healthy: ## Wait until all service healthchecks pass (max 3 min)
	@echo "▶  Waiting for all services to become healthy..."
	@timeout 180 bash -c '\
	  until [ "$$($(COMPOSE) ps --format json \
	    | python3 -c "import sys,json; d=sys.stdin.read(); \
	      rows=[json.loads(l) for l in d.strip().splitlines() if l]; \
	      unhealthy=[r[\"Name\"] for r in rows if r.get(\"Health\",\"healthy\") not in (\"healthy\",\"\")]; \
	      print(len(unhealthy))")" = "0" ]; do \
	    echo "  ... still waiting"; sleep 5; \
	  done'
	@echo "✔  All services healthy."

setup: env-copy build up wait-healthy install migrate seed passport-install config-cache ## Full first-time environment setup
	@echo ""
	@echo "✔  Setup complete! Platform is running at http://localhost"
	@echo ""
