export COMPOSE_DOCKER_CLI_BUILD = 1
export DOCKER_BUILDKIT = 1

include .env

SHELL := /bin/bash

ENV ?= dev

ifeq ($(ENV),prod)
    DOCKER_COMPOSE_FILE = docker-compose.prod.yaml
else
    DOCKER_COMPOSE_FILE = docker-compose.yaml
endif

DOCKER_COMPOSE_APP_CONTAINER = app
DOCKER_COMPOSE_DATABASE_CONTAINER = database

CURRENT_USER_ID = $(shell id --user)
CURRENT_USER_GROUP_ID = $(shell id --group)
CURRENT_DIR = $(shell pwd)

DATABASE_USERNAME=community-with-legends
TEST_DATABASE_NAME=community-with-legends-test

init: check-env-file
	@make build \
    && make run \
	&& docker compose --file ${DOCKER_COMPOSE_FILE} exec --user "${CURRENT_USER_ID}:${CURRENT_USER_GROUP_ID}" ${DOCKER_COMPOSE_APP_CONTAINER} bash "./environment/dev/scripts/init.sh" \
	&& make create-test-db

check-env-file:
	@if [ ! -f ".env" ]; then \
	  echo "Create .env file and adjust." ;\
	  exit 1;\
	fi; \

build:
	@docker compose --file ${DOCKER_COMPOSE_FILE} build --pull

run:
	@docker compose --file ${DOCKER_COMPOSE_FILE} up --detach

stop:
	@docker compose --file ${DOCKER_COMPOSE_FILE} stop

restart: stop run

shell:
	@docker compose --file ${DOCKER_COMPOSE_FILE} exec --user "${CURRENT_USER_ID}:${CURRENT_USER_GROUP_ID}" ${DOCKER_COMPOSE_APP_CONTAINER} bash

shell-root:
	@docker compose --file ${DOCKER_COMPOSE_FILE} exec ${DOCKER_COMPOSE_APP_CONTAINER} bash

test:
	@docker compose --file ${DOCKER_COMPOSE_FILE} exec --user "${CURRENT_USER_ID}:${CURRENT_USER_GROUP_ID}" ${DOCKER_COMPOSE_APP_CONTAINER} composer test
	@docker compose --file ${DOCKER_COMPOSE_FILE} exec --user "${CURRENT_USER_ID}:${CURRENT_USER_GROUP_ID}" ${DOCKER_COMPOSE_APP_CONTAINER} composer test-features

fix:
	@docker compose --file ${DOCKER_COMPOSE_FILE} exec --user "${CURRENT_USER_ID}:${CURRENT_USER_GROUP_ID}" ${DOCKER_COMPOSE_APP_CONTAINER} bash -c 'composer csf'

analyse:
	@docker compose --file ${DOCKER_COMPOSE_FILE} exec --user "${CURRENT_USER_ID}:${CURRENT_USER_GROUP_ID}" ${DOCKER_COMPOSE_APP_CONTAINER} bash -c 'composer analyse'

dev:
	@docker compose --file ${DOCKER_COMPOSE_FILE} exec --user "${CURRENT_USER_ID}:${CURRENT_USER_GROUP_ID}" ${DOCKER_COMPOSE_APP_CONTAINER} bash -c 'npm run dev'

queue:
	@docker compose --file ${DOCKER_COMPOSE_FILE} exec --user "${CURRENT_USER_ID}:${CURRENT_USER_GROUP_ID}" ${DOCKER_COMPOSE_APP_CONTAINER} php artisan queue:work

migrate:
	@docker compose --file ${DOCKER_COMPOSE_FILE} exec --user "${CURRENT_USER_ID}:${CURRENT_USER_GROUP_ID}" ${DOCKER_COMPOSE_APP_CONTAINER} php artisan migrate:fresh --seed

create-test-db:
	@docker compose --file ${DOCKER_COMPOSE_FILE} exec ${DOCKER_COMPOSE_DATABASE_CONTAINER} bash -c 'createdb --username=${DATABASE_USERNAME} ${TEST_DATABASE_NAME} &> /dev/null && echo "Created database for tests (${TEST_DATABASE_NAME})." || echo "Database for tests (${TEST_DATABASE_NAME}) exists."'

.PHONY: init check-env-file build run stop restart shell shell-root test fix create-test-db queue analyse dev
