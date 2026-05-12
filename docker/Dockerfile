FROM dunglas/frankenphp:1.11-php8.4

# Git, zip, unzip
RUN apt-get update \
    && apt-get install -y --no-install-recommends git unzip libzip-dev \
    && docker-php-ext-configure zip \
    && docker-php-ext-install zip \
    && rm -rf /var/lib/apt/lists/*

# wkhtmltoimage for HTML to image conversion
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
