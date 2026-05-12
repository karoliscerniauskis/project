FROM dunglas/frankenphp:1.11-php8.4

# Install cron
RUN apt-get update \
    && apt-get install -y --no-install-recommends cron git unzip libzip-dev \
    && docker-php-ext-configure zip \
    && docker-php-ext-install zip \
    && rm -rf /var/lib/apt/lists/*

# PHP extensions
RUN install-php-extensions intl pdo_pgsql

# php.ini
COPY ./docker/php.ini /usr/local/etc/php/php.ini

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

# Copy crontab
COPY ./docker/cron/crontab /etc/cron.d/app-cron
RUN chmod 0644 /etc/cron.d/app-cron && crontab /etc/cron.d/app-cron

# Copy startup script
COPY ./docker/cron/start.sh /usr/local/bin/start.sh
RUN chmod +x /usr/local/bin/start.sh

CMD ["/usr/local/bin/start.sh"]
