# php-cs-fixer
./vendor/bin/php-cs-fixer check --allow-risky=yes
./vendor/bin/php-cs-fixer fix
./vendor/bin/php-cs-fixer fix --dry-run --diff --allow-risky=yes

# Deptrac
./vendor/bin/deptrac

# PHPStan
./vendor/bin/phpstan analyse --level=10

# Lexik
php bin/console lexik:jwt:generate-keypair --skip-if-exists

# Commands
php bin/console app:outbox:process --watch --interval=3

# Tests
docker exec app composer test

# Paleidimas lokaliai prod env:

docker compose down

.env HIBP_API_KEY="<hibp-api-key>" change to your own key

docker compose -f docker-compose.prod.yml up -d --build

docker compose -f docker-compose.prod.yml exec app php bin/console doctrine:migrations:migrate --no-interaction

docker compose -f docker-compose.prod.yml exec app php bin/console lexik:jwt:generate-keypair --skip-if-exists

docker compose -f docker-compose.prod.yml exec app php bin/console app:admin:create example@email.com --password="strong123"

https://localhost:8443

# Paleidimas lokaliai dev env:

docker compose -f docker-compose.prod.yml down

.env HIBP_API_KEY="<hibp-api-key>" change to your own key

docker compose up -d --build

docker compose exec app php bin/console doctrine:migrations:migrate --no-interaction

docker compose exec app php bin/console lexik:jwt:generate-keypair --skip-if-exists

docker compose exec app php bin/console app:admin:create example@email.com --password="strong123"

http://localhost:5173/
