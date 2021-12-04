up:
	@docker-compose --file .docker/docker-compose.dev.yml up --build -d --remove-orphans

down:
	@docker-compose --file .docker/docker-compose.dev.yml down

backend:
	@docker-compose --file .docker/docker-compose.dev.yml exec php composer install
	@docker-compose --file .docker/docker-compose.dev.yml exec php bin/console doctrine:migrations:migrate --no-interaction
	@docker-compose --file .docker/docker-compose.dev.yml exec php bin/console doctrine:migrations:migrate --env=test --no-interaction

phpunit:
	@docker-compose --file .docker/docker-compose.dev.yml exec php ./vendor/bin/phpunit

backend_shell:
	@docker-compose --file .docker/docker-compose.dev.yml run php /bin/sh

cs:
	@docker-compose --file .docker/docker-compose.dev.yml exec php vendor/bin/phpstan analyse src --level 7
	@docker-compose --file .docker/docker-compose.dev.yml exec php vendor/bin/ecs check src tests
