FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
    libmariadb-dev \
    libzip-dev \
    unzip \
    git \
    curl \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
    mysqli \
    pdo \
    pdo_mysql \
    zip \
    gd

FROM php:8.0.30-apache

RUN docker-php-ext-install pdo pdo_mysql

RUN a2enmod rewrite

RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

COPY uploads.ini /usr/local/etc/php/conf.d/uploads.ini

COPY --from=deps app/vendor/ /var/www/html/vendor

COPY . /var/www/html

USER www-data
