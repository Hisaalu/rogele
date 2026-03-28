# syntax=docker/dockerfile:1

FROM composer:lts AS deps
WORKDIR /app
RUN --mount=type=bind,source=composer.json,target=composer.json \
    --mount=type=cache,target=/tmp/cache \
    composer install --no-dev --no-interaction

# Base image
FROM php:8.0.30-apache

#Installing the pdo and pdo_mysql extensions
RUN docker-php-ext-install pdo pdo_mysql

# Enable Apache modules
RUN a2enmod rewrite

# Use the default production configuration for PHP runtime arguments, see
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

COPY --from=deps app/vendor/ /var/www/html/vendor

# Copy app files from the app directory.
COPY . /var/www/html

# Switch to a non-privileged user (defined in the base image) that the app will run under.
# See https://docs.docker.com/go/dockerfile-user-best-practices/
USER www-data