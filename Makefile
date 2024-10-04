.PHONY: help reset cli pull build composer_install up run psalm psalm_strict clean run_scheduler

help: ## display this help message
	@grep -E '^[a-zA-Z0-9_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}'

pull: ## Build the docker images
	@docker compose pull --ignore-pull-failures

build: ## Build the docker images
	@docker compose build

reset: ## Reset (or create) the database
	@docker compose exec php composer reset

cli: ## Open a CLI in the PHP container. If you need this it means that I fucked up this Makefile.
	@docker compose exec php ash

composer_install: ## Uh ?
	@docker compose run --rm php composer install

up: ## Just turn-on the containers
	@mkdir -p var/data
	@docker compose up -d

.configured:
	test -f .configured || make first_run
	touch .configured

run: .configured up ## Run the project. Create the Database  and build the images if needed

infra/docker/tls/cert.pem:
	mkdir -p infra/docker/tls
	mkcert -cert-file infra/docker/tls/cert.pem -key-file=infra/docker/tls/cert.key localhost 127.0.0.1

first_run: infra/docker/tls/cert.pem pull build composer_install yarn_install up reset assets_build

psalm:
	@docker compose run --rm php ./vendor/bin/psalm

psalm_strict:
	@docker compose run --rm php ./vendor/bin/psalm --show-info=true

test:
	@docker compose run --rm php ./bin/phpunit

clean:
	docker compose down -v
	rm -rf .configured vendor /public/assets /assets/vendor public/bundles var/*

run_scheduler:
	docker compose exec php php bin/console messenger:consume scheduler_recurrent --no-debug -vv
