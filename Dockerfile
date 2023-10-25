#syntax=docker/dockerfile:1.4

FROM php:8.2-cli

LABEL org.opencontainers.image.vendor="Max Antipin <max.v.antpin@gmail.com>" \
      org.opencontainers.image.version="0.0.1"

RUN set -eux; \
    apt-get update; \
    apt-get install -y --no-install-recommends \
        zlib1g-dev libzip-dev unzip; \
    docker-php-ext-install zip; \
    pecl install xdebug-3.2.1; \
	docker-php-ext-enable xdebug; \
    rm -rf /var/lib/apt/lists/*; \
    mv "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini"
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer
WORKDIR /usr/src/app/
