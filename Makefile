up:
	@docker-compose --file .docker/docker-compose.yml up --build -d --remove-orphans
down:
	@docker-compose --file .docker/docker-compose.yml down
backend:
	@docker-compose --file .docker/docker-compose.yml run php composer install
	@docker-compose --file .docker/docker-compose.yml run php bin/console doctrine:migrations:migrate --no-interaction
	@docker-compose --file .docker/docker-compose.yml run php bin/console doctrine:migrations:migrate --env=test --no-interaction