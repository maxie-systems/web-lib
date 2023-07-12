FROM php:8.2-cli
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer
WORKDIR /usr/src/app