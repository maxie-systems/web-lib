#syntax=docker/dockerfile:1.6

ARG PHP_VERSION=8.2
ARG COMPOSER_VERSION=2.6

FROM composer:${COMPOSER_VERSION} AS composer_image

FROM php:${PHP_VERSION}-cli-alpine
LABEL org.opencontainers.image.description="Maxie Systems Web Library development environment" \
      org.opencontainers.image.vendor="Max Antipin <max.v.antpin@gmail.com>" \
      org.opencontainers.image.version="0.0.5"
RUN set -eux; \
    apk update \
 && apk upgrade \
 && apk add --no-cache linux-headers \
 && apk add --update --no-cache --virtual .build-dependencies $PHPIZE_DEPS \
 && pecl install xdebug \
 && docker-php-ext-enable xdebug \
 && pecl clear-cache \
 && apk del .build-dependencies
RUN --mount=type=bind,source=.docker,target=/tmp/docker \
    set -eux \
 && mv "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini" \
 && cat /tmp/docker/xdebug.ini >> "$PHP_INI_DIR/conf.d/docker-php-ext-xdebug.ini"
COPY --from=composer_image --link /usr/bin/composer /usr/local/bin/composer
WORKDIR /usr/src/app/
