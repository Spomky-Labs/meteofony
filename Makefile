# Executables (local)
DOCKER_COMP = docker compose

# Docker containers
PHP_CONT = $(DOCKER_COMP) exec php
NODE_CONT = $(DOCKER_COMP) exec node

# Executables
PHP      = $(PHP_CONT) php
COMPOSER = $(PHP_CONT) composer
SYMFONY  = $(PHP_CONT) bin/console
PHPUNIT  = $(PHP_CONT) bin/phpunit
NPM      = $(NODE_CONT) npm
NPX      = $(NODE_CONT) npx

# Misc
.DEFAULT_GOAL = help

## —— 🎵 🐳 The Symfony Docker Makefile 🐳 🎵 ——————————————————————————————————
help: ## Outputs this help screen
	@grep -E '(^[a-zA-Z0-9\./_-]+:.*?##.*$$)|(^##)' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}{printf "\033[32m%-30s\033[0m %s\n", $$1, $$2}' | sed -e 's/\[32m##/[33m/'

## —— Docker 🐳 ————————————————————————————————————————————————————————————————
build: ## Builds the Docker images
	@$(DOCKER_COMP) build --pull --no-cache

up: ## Start the docker hub in detached mode (no logs)
	@$(DOCKER_COMP) up --detach

start: build up ## Build and start the containers

down: ## Stop the docker hub
	@$(DOCKER_COMP) down --remove-orphans

logs: ## Show live logs
	@$(DOCKER_COMP) logs --tail=0 --follow

restart: down up ## Restarts the containers

sh: ## Connect to the PHP FPM container
	@$(PHP_CONT) sh

## —— Composer 🧙 ——————————————————————————————————————————————————————————————
composer: ## Run composer, pass the parameter "c=" to run a given command, example: make composer c='req symfony/orm-pack'
	@$(eval c ?=)
	@$(COMPOSER) $(c)

vendor: ## Install vendors according to the current composer.lock file
vendor: c=install --prefer-dist --no-dev --no-progress --no-scripts --no-interaction
vendor: composer

## —— Symfony 🎵 ———————————————————————————————————————————————————————————————
sf: ## List all Symfony commands or pass the parameter "c=" to run a given command, example: make sf c=about
	@$(eval c ?=)
	@$(SYMFONY) $(c)

cc: c=c:c ## Clear the cache
cc: sf

## —— NPM/NPX 🎵 ———————————————————————————————————————————————————————————————
npx: ## NPX commands with the parameter "c=" to run a given command, example: make npx c='install --force'
	@$(eval c ?=)
	@$(NPX) $(c)

npm: ## NPM commands with the parameter "c=" to run a given command, example: make npm c='install --force'
	@$(eval c ?=)
	@$(NPM) $(c)

npm-build: ## Build the frontend
	@$(NPM) run build

npm-watch: ## Watch mode
	@$(NPM) run watch

node_modules: ## Install node_modules according to the current package-lock.json file
node_modules: c=install force
node_modules: npm

## —— Phpunit 🎵 ———————————————————————————————————————————————————————————————
test: ## Execute all tests
	@$(PHPUNIT)

## —— Quality 🎵 ———————————————————————————————————————————————————————————————
qa: ## Execute all Quality tools
	@$(PHP) vendor/bin/rector process
	@$(PHP) vendor/bin/ecs check --fix
	@$(PHP) vendor/bin/phpstan analyse --memory-limit=512M


## —— Project 🎵 ———————————————————————————————————————————————————————————————
init: ## Initialization of DB and the data
	@$(SYMFONY) d:d:c --if-not-exists
	@$(SYMFONY) d:m:m -n
	@$(SYMFONY) app:init:regions
	@$(SYMFONY) app:init:departments
	@$(SYMFONY) app:init:cities
	@$(SYMFONY) app:init:users
