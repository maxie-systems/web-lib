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
ENV COMPOSER_ROOT_VERSION=dev-main
RUN --mount=type=bind,source=.,target=/tmp/docker-files \
    set -eux \
 && mv "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini" \
 && sed -i "s|\(memory_limit =\) [1-9]\+[a-zA-Z]\+|\1 1024M|" "$PHP_INI_DIR/php.ini" \
 && cat /tmp/docker-files/xdebug.ini >> "$PHP_INI_DIR/conf.d/docker-php-ext-xdebug.ini"
COPY --from=composer_image --link /usr/bin/composer /usr/local/bin/composer
WORKDIR /usr/src/app/
LABEL org.opencontainers.image.description="Maxie Systems Web Library development environment" \
      org.opencontainers.image.vendor="Max Antipin <max.v.antpin@gmail.com>" \
      org.opencontainers.image.version="0.4.0"

FROM php_dev AS php_test
COPY --link --from=source composer.json .
RUN set -eu; \
    composer install --no-autoloader --no-scripts --no-progress --audit
COPY --from=source --exclude=composer.json . .
RUN set -eu; \
    composer dump-autoload --classmap-authoritative --strict-psr; \
    composer validate --strict
COPY --link --chmod=750 run-tests.sh /usr/local/bin/run-tests
ENV XDEBUG_MODE=off
CMD ["run-tests", "phpunit-no-coverage"]

FROM composer_image
RUN printf 'Attribute build.target is unspecified!' && exit 1
# A little trick to make users explicitly define target stage.