FROM php:8.2-cli
RUN apt-get update \
    && apt-get install -y zlib1g-dev libzip-dev unzip \
    && docker-php-ext-install zip \
    && pecl install xdebug-3.2.1 \
	&& docker-php-ext-enable xdebug \
    && mv "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini"
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer
WORKDIR /usr/src/app/