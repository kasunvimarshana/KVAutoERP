# =============================================================================
# KV_SAAS – Inventory Management System – Makefile
# =============================================================================

.PHONY: install up down restart logs \
        shell-auth shell-tenant shell-inventory shell-order shell-payment \
        migrate seed test fresh \
        build pull ps health lint format \
        queue-work horizon flush

# ─── Variables ────────────────────────────────────────────────────────────────
DC            := docker compose
DC_FILE       := docker-compose.yml
PHP_SERVICES  := auth-service-fpm tenant-service-fpm inventory-service-fpm order-service-fpm payment-service-fpm

# Colours
CYAN  := \033[0;36m
GREEN := \033[0;32m
RESET := \033[0m

# ─── Bootstrap ───────────────────────────────────────────────────────────────

## Install: copy .env, build images, start services, install composer deps, migrate & seed
install: .env build up
	@echo "$(CYAN)Installing Composer dependencies in all PHP services...$(RESET)"
	@for svc in $(PHP_SERVICES); do \
		echo "$(GREEN)→ $$svc$(RESET)"; \
		$(DC) exec $$svc composer install --no-interaction --prefer-dist --optimize-autoloader 2>/dev/null || true; \
	done
	@$(MAKE) migrate
	@$(MAKE) seed
	@echo "$(GREEN)✔ Installation complete$(RESET)"

.env:
	@if [ ! -f .env ]; then \
		cp .env.example .env; \
		echo "$(CYAN).env created from .env.example – please review and update secrets$(RESET)"; \
	fi

# ─── Docker Lifecycle ────────────────────────────────────────────────────────

## Build: build (or rebuild) all service images
build:
	@echo "$(CYAN)Building images...$(RESET)"
	$(DC) -f $(DC_FILE) build --parallel

## Pull: pull latest base images
pull:
	$(DC) -f $(DC_FILE) pull

## Up: start all services in detached mode
up:
	@echo "$(CYAN)Starting services...$(RESET)"
	$(DC) -f $(DC_FILE) up -d --remove-orphans
	@echo "$(GREEN)✔ All services started$(RESET)"

## Down: stop and remove containers (keeps volumes)
down:
	@echo "$(CYAN)Stopping services...$(RESET)"
	$(DC) -f $(DC_FILE) down

## Restart: restart all services
restart: down up

## Ps: show running containers
ps:
	$(DC) -f $(DC_FILE) ps

## Health: show health status of all containers
health:
	@echo "$(CYAN)Service health status:$(RESET)"
	@docker ps --format "table {{.Names}}\t{{.Status}}\t{{.Ports}}" | grep kv_ || true

# ─── Logs ────────────────────────────────────────────────────────────────────

## Logs: tail logs for all services (or SERVICE=<name> for one)
logs:
ifdef SERVICE
	$(DC) -f $(DC_FILE) logs -f $(SERVICE)
else
	$(DC) -f $(DC_FILE) logs -f
endif

## Logs-gateway: tail api-gateway logs
logs-gateway:
	$(DC) -f $(DC_FILE) logs -f api-gateway

## Logs-auth: tail auth service logs
logs-auth:
	$(DC) -f $(DC_FILE) logs -f auth-service-fpm auth-service-nginx

## Logs-inventory: tail inventory service logs
logs-inventory:
	$(DC) -f $(DC_FILE) logs -f inventory-service-fpm inventory-service-nginx

## Logs-order: tail order service logs
logs-order:
	$(DC) -f $(DC_FILE) logs -f order-service-fpm order-service-nginx

## Logs-payment: tail payment service logs
logs-payment:
	$(DC) -f $(DC_FILE) logs -f payment-service-fpm payment-service-nginx

# ─── Shell Access ────────────────────────────────────────────────────────────

## Shell-auth: open a shell in the auth service FPM container
shell-auth:
	$(DC) -f $(DC_FILE) exec auth-service-fpm bash

## Shell-tenant: open a shell in the tenant service FPM container
shell-tenant:
	$(DC) -f $(DC_FILE) exec tenant-service-fpm bash

## Shell-inventory: open a shell in the inventory service FPM container
shell-inventory:
	$(DC) -f $(DC_FILE) exec inventory-service-fpm bash

## Shell-order: open a shell in the order service FPM container
shell-order:
	$(DC) -f $(DC_FILE) exec order-service-fpm bash

## Shell-payment: open a shell in the payment service FPM container
shell-payment:
	$(DC) -f $(DC_FILE) exec payment-service-fpm bash

## Shell-mysql: open a MySQL shell
shell-mysql:
	$(DC) -f $(DC_FILE) exec mysql mysql -u root -p$$MYSQL_ROOT_PASSWORD

## Shell-redis: open a Redis CLI
shell-redis:
	$(DC) -f $(DC_FILE) exec redis redis-cli -a $$REDIS_PASSWORD

# ─── Database ────────────────────────────────────────────────────────────────

## Migrate: run migrations in all PHP services
migrate:
	@echo "$(CYAN)Running migrations...$(RESET)"
	@for svc in $(PHP_SERVICES); do \
		echo "$(GREEN)→ $$svc$(RESET)"; \
		$(DC) exec $$svc php artisan migrate --force 2>/dev/null || true; \
	done

## Seed: run seeders in all PHP services
seed:
	@echo "$(CYAN)Running seeders...$(RESET)"
	@for svc in $(PHP_SERVICES); do \
		echo "$(GREEN)→ $$svc$(RESET)"; \
		$(DC) exec $$svc php artisan db:seed --force 2>/dev/null || true; \
	done

## Fresh: drop all tables, re-migrate, and re-seed (DESTRUCTIVE)
fresh:
	@echo "$(CYAN)Running fresh migrations...$(RESET)"
	@for svc in $(PHP_SERVICES); do \
		echo "$(GREEN)→ $$svc$(RESET)"; \
		$(DC) exec $$svc php artisan migrate:fresh --seed --force 2>/dev/null || true; \
	done

## Rollback: rollback last migration batch in all services
rollback:
	@for svc in $(PHP_SERVICES); do \
		$(DC) exec $$svc php artisan migrate:rollback --force 2>/dev/null || true; \
	done

# ─── Cache & Queues ──────────────────────────────────────────────────────────

## Flush: flush all caches in all PHP services
flush:
	@echo "$(CYAN)Flushing caches...$(RESET)"
	@for svc in $(PHP_SERVICES); do \
		$(DC) exec $$svc php artisan cache:clear 2>/dev/null || true; \
		$(DC) exec $$svc php artisan config:clear 2>/dev/null || true; \
		$(DC) exec $$svc php artisan route:clear 2>/dev/null || true; \
		$(DC) exec $$svc php artisan view:clear 2>/dev/null || true; \
	done

## Queue-work: start queue worker in a specific service (SERVICE=<fpm-name>)
queue-work:
ifndef SERVICE
	$(error SERVICE is not set. Usage: make queue-work SERVICE=inventory-service-fpm)
endif
	$(DC) exec $(SERVICE) php artisan queue:work --tries=3 --backoff=5

# ─── Testing ─────────────────────────────────────────────────────────────────

## Test: run PHPUnit tests in all PHP services
test:
	@echo "$(CYAN)Running tests...$(RESET)"
	@FAIL=0; for svc in $(PHP_SERVICES); do \
		echo "$(GREEN)→ $$svc$(RESET)"; \
		$(DC) exec $$svc php artisan test --parallel 2>/dev/null || FAIL=1; \
	done; \
	if [ $$FAIL -eq 1 ]; then echo "$(CYAN)Some tests failed$(RESET)"; exit 1; fi
	@echo "$(GREEN)✔ All tests passed$(RESET)"

## Test-coverage: run tests with coverage (requires Xdebug / PCOV)
test-coverage:
ifndef SERVICE
	$(error SERVICE is not set. Usage: make test-coverage SERVICE=inventory-service-fpm)
endif
	$(DC) exec $(SERVICE) php artisan test --coverage --min=80

# ─── Code Quality ────────────────────────────────────────────────────────────

## Lint: run PHP CS Fixer (dry-run) in all services
lint:
	@for svc in $(PHP_SERVICES); do \
		$(DC) exec $$svc ./vendor/bin/pint --test 2>/dev/null || true; \
	done

## Format: auto-format code with Laravel Pint
format:
	@for svc in $(PHP_SERVICES); do \
		$(DC) exec $$svc ./vendor/bin/pint 2>/dev/null || true; \
	done

# ─── Help ────────────────────────────────────────────────────────────────────

## Help: display this help message
help:
	@echo ""
	@echo "$(CYAN)KV_SAAS Inventory Management System$(RESET)"
	@echo ""
	@grep -E '^## ' Makefile | sed 's/## //' | awk -F': ' '{printf "  $(GREEN)%-25s$(RESET) %s\n", $$1, $$2}'
	@echo ""

.DEFAULT_GOAL := help
