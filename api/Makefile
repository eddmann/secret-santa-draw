.PHONY: *
.DEFAULT_GOAL := help

SHELL := /bin/bash
COMPOSE := docker compose -f docker/compose.yaml -p secret-santa-api
APP := $(COMPOSE) exec -T app

##@ Setup

start: up composer db ## Start the application in development mode

restart: stop start ## Restart the application in development mode

up:
	[ -f docker/dev.env ] || cp docker/dev.env.example docker/dev.env
	cp app/.env.example app/.env
	$(COMPOSE) up -d --build --force-recreate

stop: ## Stop the application and clean up
	$(COMPOSE) down -v --remove-orphans

restart: stop start

composer: ## Install the latest Composer dependencies
  ifdef GITHUB_TOKEN
	@echo "Found GitHub access token, configuring Composer"
	@$(APP) composer config -g http-basic.github.com x-access-token ${GITHUB_TOKEN}
  endif
	$(APP) composer install --no-interaction --no-ansi

db:
	$(APP) php artisan migrate:refresh --seed

##@ Testing/Linting

can-release: security test lint ## Run all the same checks as CI to ensure this code will be releasable

security: ## Check dependencies for known vulnerabilities
	$(APP) composer audit

test: ## Run the test suite
	$(APP) php artisan test

lint: ## Run the linting tools
	$(APP) vendor/bin/pint --test

fmt: format
format: ## Fix style related code violations
	$(APP) vendor/bin/pint

##@ Running Instance

open: ## Open the API in the default browser
	open http://localhost:8000/

sh: shell
shell: ## Access a shell on the running container
	$(COMPOSE) exec app bash

logs: ## Tail the container logs
	$(COMPOSE) logs -f

ps: ## List the running containers
	$(COMPOSE) ps -a

##@ Build/Deploy

build: ## Build application for deployment
	docker build -t local/secret-santa-api - < docker/Dockerfile
	docker run --rm -v $(PWD)/app:/var/task local/secret-santa-api bash -c " \
		composer install --no-dev --no-interaction --no-ansi --classmap-authoritative --no-scripts; \
		rm -fr bootstrap/cache/*; \
		APP_ENV=production php artisan config:clear; \
	"

deploy: _require_AWS_ACCESS_KEY_ID _require_AWS_SECRET_ACCESS_KEY build ## Deploy application via CDK
	docker run --rm \
		-v $(PWD):/app:rw,delegated \
		-v /app/cdk/node_modules \
		-v /app/cdk/cdk.out \
		-w /app/cdk \
		-e JSII_SILENCE_WARNING_UNTESTED_NODE_VERSION=1 \
		-e AWS_ACCESS_KEY_ID \
		-e AWS_SECRET_ACCESS_KEY \
		docker.io/node:22.11.0-alpine3.19 \
		sh -c "npm install && npm run build && npm run cdk -- deploy --ci --require-approval=never"

_require_%:
	@_=$(or $($*),$(error "`$*` env var required"))

help:
	@awk 'BEGIN {FS = ":.*##"; printf "\nUsage:\n  make \033[36m<target>\033[0m\n"} /^[a-zA-Z_\-\/\/]+:.*?##/ { printf "  \033[36m%-20s\033[0m %s\n", $$1, $$2 } /^##@/ { printf "\n\033[1m%s\033[0m\n", substr($$0, 5) } ' $(MAKEFILE_LIST)
