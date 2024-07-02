#syntax=docker/dockerfile:1.7-labs

ARG PHP_VERSION=8.3
ARG COMPOSER_VERSION=2.7

FROM composer:${COMPOSER_VERSION} AS composer_image

FROM php:${PHP_VERSION}-cli-alpine AS php_dev
RUN set -eux; \
    apk update \
 && apk upgrade \
 && apk add --no-cache linux-headers \
 && apk add --update --no-cache --virtual .build-dependencies $PHPIZE_DEPS \
 && pecl install xdebug \
 && docker-php-ext-enable xdebug \
 && pecl clear-cache \
 && apk del .build-dependencies
RUN --mount=type=bind,from=docker-files,source=.,target=/tmp/docker-files \
    set -eux \
 && mv "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini" \
 && sed -i "s|\(max_execution_time =\).*|\1 60|" "$PHP_INI_DIR/php.ini" \
 && sed -i "s|\(memory_limit =\) [1-9]\+[a-zA-Z]\+|\1 512M|" "$PHP_INI_DIR/php.ini" \
 && cat /tmp/docker-files/xdebug.ini >> "$PHP_INI_DIR/conf.d/docker-php-ext-xdebug.ini"
COPY --from=composer_image --link /usr/bin/composer /usr/local/bin/composer
WORKDIR /usr/src/app/
LABEL org.opencontainers.image.description="Maxie Systems Web Library development environment" \
      org.opencontainers.image.vendor="Max Antipin <max.v.antpin@gmail.com>" \
      org.opencontainers.image.version="0.1.0"

FROM php_dev AS php_test
COPY --link --chmod=750 --from=docker-files run-tests.sh /usr/local/bin/run-tests
COPY --link composer.json ./
RUN set -eu; \
    composer install --no-autoloader --no-scripts --no-progress --audit
ENV XDEBUG_MODE=off
COPY --exclude=composer.json . .
RUN set -eu; \
    composer dump-autoload --classmap-authoritative --strict-psr; \
    composer validate --strict
