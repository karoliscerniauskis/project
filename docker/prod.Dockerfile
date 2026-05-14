FROM dunglas/frankenphp:1.11-php8.4 AS app

WORKDIR /app

RUN apt-get update \
    && apt-get install -y --no-install-recommends git unzip libzip-dev \
    && docker-php-ext-configure zip \
    && docker-php-ext-install zip \
    && install-php-extensions intl pdo_pgsql opcache \
    && rm -rf /var/lib/apt/lists/*

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        wget \
        fontconfig \
        libfreetype6 \
        libjpeg62-turbo \
        libpng16-16 \
        libx11-6 \
        libxcb1 \
        libxext6 \
        libxrender1 \
        xfonts-75dpi \
        xfonts-base \
    && wget https://github.com/wkhtmltopdf/packaging/releases/download/0.12.6.1-3/wkhtmltox_0.12.6.1-3.bookworm_arm64.deb \
    && apt-get install -y ./wkhtmltox_0.12.6.1-3.bookworm_arm64.deb \
    && rm wkhtmltox_0.12.6.1-3.bookworm_arm64.deb \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

COPY ./docker/php.prod.ini /usr/local/etc/php/php.ini
COPY ./docker/Caddyfile /etc/caddy/Caddyfile

COPY .env ./
COPY composer.json composer.lock symfony.lock ./
RUN composer install --no-dev --no-scripts --no-interaction --prefer-dist --optimize-autoloader

COPY bin ./bin
COPY config ./config
COPY migrations ./migrations
COPY public ./public
COPY src ./src
COPY templates ./templates

RUN composer dump-autoload --no-dev --classmap-authoritative \
    && mkdir -p var/cache var/log \
    && chmod -R 777 var

ENV APP_ENV=prod
ENV SERVER_NAME=:80

EXPOSE 80

FROM app AS cron

RUN apt-get update \
    && apt-get install -y --no-install-recommends cron \
    && rm -rf /var/lib/apt/lists/*

COPY ./docker/cron/crontab /etc/cron.d/app-cron
RUN chmod 0644 /etc/cron.d/app-cron && crontab /etc/cron.d/app-cron

COPY ./docker/cron/start.sh /usr/local/bin/start.sh
RUN chmod +x /usr/local/bin/start.sh

CMD ["/usr/local/bin/start.sh"]
