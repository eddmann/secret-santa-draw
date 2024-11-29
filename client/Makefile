.PHONY: *
.DEFAULT_GOAL := help

SHELL := /bin/bash
DOCKER := docker run --rm -v $(PWD)/app:/app -w /app
IMAGE := docker.io/node:22.11.0-alpine3.19

##@ Setup

start: install ## Start the application in development mode
	$(DOCKER) -p 5173:5173 $(IMAGE) npm run dev -- --host 0.0.0.0

install: ## Install the latest NPM dependencies
	$(DOCKER) $(IMAGE) npm install

##@ Testing/Linting

lint: ## Run the linting tools
	$(DOCKER) $(IMAGE) npm run lint

fmt: format
format: ## Fix style related code violations
	$(DOCKER) $(IMAGE) npm run lint:js:fix

##@ Running Instance

open: ## Open the API in the default browser
	open http://localhost:5173/

sh: shell
shell: ## Access a shell of the development environment
	$(DOCKER) -it --entrypoint=sh $(IMAGE)

##@ Build/Deploy

build: ## Build application for deployment
	$(DOCKER) $(IMAGE) npm run build

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
