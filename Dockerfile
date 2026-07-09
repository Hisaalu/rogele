# syntax=docker/dockerfile:1

FROM composer:lts AS deps
WORKDIR /app

COPY composer.json composer.lock* ./

RUN composer install --no-dev --no-interaction --prefer-dist

FROM php:8.0.30-apache

RUN apt-get update && \
    apt-get install -y --no-install-recommends ca-certificates && \
    update-ca-certificates && \
    rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install pdo pdo_mysql

RUN a2enmod rewrite

RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

COPY uploads.ini /usr/local/etc/php/conf.d/uploads.ini

COPY --from=deps /app/vendor /var/www/html/vendor
COPY . /var/www/html

RUN mkdir -p /var/www/html/public/uploads/lessons && \
    chown -R www-data:www-data /var/www/html/public/uploads && \
    chmod -R 775 /var/www/html/public/uploads

USER www-data