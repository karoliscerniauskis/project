FROM dunglas/frankenphp:1.11-php8.4

# Git, zip, unzip
RUN apt-get update \
    && apt-get install -y --no-install-recommends git unzip libzip-dev \
    && docker-php-ext-configure zip \
    && docker-php-ext-install zip \
    && rm -rf /var/lib/apt/lists/*

# PHP extensions
RUN install-php-extensions intl pdo_pgsql

# Xdebug
RUN pecl install xdebug \
    && docker-php-ext-enable xdebug

# php.ini
COPY ./docker/php.ini /usr/local/etc/php/php.ini

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

# Symfony CLI
COPY --link --from=ghcr.io/symfony-cli/symfony-cli:latest /usr/local/bin/symfony /usr/local/bin/symfony
