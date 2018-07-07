WRITABLE-DIRS = var/cache var/logs var/sessions var/imports


ifneq ($(shell docker-compose -v 2>/dev/null),)

# IMAGE

DOCKER-COMPOSE = docker-compose
RUN = $(DOCKER-COMPOSE) run --rm -u `id -u`:`id -g`
EXEC = $(DOCKER-COMPOSE) exec
BASH ?= $(EXEC) web

build: env pull npm composer
	$(DOCKER-COMPOSE) build web

pull:
	docker-compose pull

push:
	docker-compose push web

env:
	cp --no-clobber .env.dist .env

up:
	$(DOCKER-COMPOSE) up -d web

bash: up
	$(EXEC) web /bin/bash

database: up
	$(EXEC) database psql -U spendingmonitor spendingmonitor

npm:
	$(RUN) npm npm install

composer:
	$(RUN) composer composer install --no-interaction

setfacl:
	setfacl -RL -m u:www-data:rwX -m u:`whoami`:rwX $(WRITABLE-DIRS)
	setfacl -dRL -m u:www-data:rwX -m u:`whoami`:rwX $(WRITABLE-DIRS)

clean:
	$(RUN) web rm -rf node_modules vendor var/cache/* var/logs/* var/sessions/* web/vendor

else

# APP

CONSOLE ?= bin/console
CS-FIX ?= bin/php-cs-fixer
PHPUNIT ?= bin/phpunit

init: cache database-init fixtures

update: cache database-update

cache: cache-clear writable
cache-clear:
	$(CONSOLE) cache:clear --env=prod
	$(CONSOLE) cache:clear --env=dev

writable:
	$(BASH) chown -R www-data:www-data $(WRITABLE-DIRS)

database-init:
	$(CONSOLE) doctrine:database:drop --force --if-exists
	$(CONSOLE) doctrine:database:create
	$(CONSOLE) doctrine:schema:create
	$(CONSOLE) doctrine:migrations:version --no-interaction --add --all

database-update:
	$(CONSOLE) doctrine:migration:migrate --no-interaction --allow-no-migration

fixtures:
	$(CONSOLE) doctrine:fixtures:load --no-interaction --no-interaction

cs-check:
	$(CS-FIX) fix --dry-run

cs-fix:
	$(CS-FIX) fix

test:
	$(PHPUNIT)

xdbon:
	ln -sf /usr/local/etc/php/mods-available/xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini
	apachectl -k graceful

xdboff:
	rm -f /usr/local/etc/php/conf.d/xdebug.ini
	apachectl -k graceful

endif
