FROM php:8.2-cli
RUN apt-get update \
    && apt-get install -y zlib1g-dev libzip-dev unzip \
    && docker-php-ext-install zip
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer
WORKDIR /usr/src/app