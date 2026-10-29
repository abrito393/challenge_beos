.DEFAULT_GOAL := help

DC   := docker compose
EXEC := $(DC) exec -T app
export UID := $(shell id -u)
export GID := $(shell id -g)

# Published ports: shell env > .env > default (same precedence docker compose uses).
APP_PORT ?= $(or $(shell grep -E '^APP_PORT=' .env 2>/dev/null | cut -d= -f2),8080)
FORWARD_DB_PORT ?= $(or $(shell grep -E '^FORWARD_DB_PORT=' .env 2>/dev/null | cut -d= -f2),33060)
export APP_PORT FORWARD_DB_PORT

.PHONY: help setup up down restart build install env migrate fresh seed test lint fix stan check docs e2e shell logs

help: ## Show available commands
	@grep -E '^[a-zA-Z_-]+:.*?## ' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "  \033[36m%-10s\033[0m %s\n", $$1, $$2}'

setup: env build up install key migrate ## First run: build, install, migrate and seed
	@echo "\n✅ API ready at http://localhost:$(APP_PORT)/api/v1 — Swagger UI at http://localhost:$(APP_PORT)/docs/api"

env: # Creates .env once, persisting the chosen ports so later make/compose calls agree
	@test -f .env || { cp .env.example .env && sed -i.bak \
		-e 's|^APP_PORT=.*|APP_PORT=$(APP_PORT)|' \
		-e 's|^FORWARD_DB_PORT=.*|FORWARD_DB_PORT=$(FORWARD_DB_PORT)|' \
		-e 's|^APP_URL=.*|APP_URL=http://localhost:$(APP_PORT)|' .env && rm -f .env.bak; }

build: ## Build images
	$(DC) build

up: ## Start containers (waits until healthy)
	$(DC) up -d --wait

down: ## Stop containers
	$(DC) down

restart: down up ## Restart containers

install: ## Install Composer dependencies (retried once: bind mounts on Docker Desktop can fail transiently while extracting)
	$(EXEC) composer install --no-interaction --prefer-dist --no-progress \
		|| $(EXEC) composer install --no-interaction --prefer-dist --no-progress

key:
	@grep -q '^APP_KEY=base64' .env || $(EXEC) php artisan key:generate --ansi

migrate: ## Run migrations and seeders
	$(EXEC) php artisan migrate --seed --force

fresh: ## Drop all tables, re-migrate and seed
	$(EXEC) php artisan migrate:fresh --seed --force

test: ## Run the test suite (isolated testing database)
	$(EXEC) php artisan test

lint: ## Check code style (Pint)
	$(EXEC) vendor/bin/pint --test

fix: ## Fix code style (Pint)
	$(EXEC) vendor/bin/pint

stan: ## Static analysis (Larastan, level max)
	$(EXEC) vendor/bin/phpstan analyse --memory-limit=1G --no-progress

check: lint stan test ## Definition of done: lint + stan + tests

docs: ## Export OpenAPI spec to docs/api/openapi.json
	$(EXEC) php artisan scramble:export --path=docs/api/openapi.json

e2e: fresh ## Run the Postman collection end-to-end against the running stack (Newman in Docker)
	docker run --rm --add-host=host.docker.internal:host-gateway -v "$(CURDIR)/docs/api:/etc/newman:ro" \
		postman/newman:6-alpine run postman_collection.json --env-var baseUrl=http://host.docker.internal:$(APP_PORT)/api/v1

shell: ## Open a shell in the app container
	$(DC) exec app sh

logs: ## Tail container logs
	$(DC) logs -f
