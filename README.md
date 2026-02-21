# docker
docker compose down
docker compose build --no-cache app
docker compose up -d
docker compose exec app bash

# php-cs-fixer
./vendor/bin/php-cs-fixer check --allow-risky=yes
./vendor/bin/php-cs-fixer fix
