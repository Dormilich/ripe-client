start: ## Start containers
	docker compose up -d

stop: ## Stop containers
	docker compose stop

test: ## Run PHPUnit tests
	docker compose exec php composer test

sh: ## Open a shell in the container
	docker compose exec php sh
