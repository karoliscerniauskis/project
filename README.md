# docker
docker compose down
docker compose build --no-cache app
docker compose up -d
docker compose exec app bash

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
