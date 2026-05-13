FROM php:8.2-apache

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libonig-dev \
        libpng-dev \
        libzip-dev \
        libicu-dev \
        unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" mysqli mbstring gd zip intl \
    && a2enmod rewrite headers expires \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html

COPY web_app/ /var/www/html/

RUN mkdir -p /var/www/html/data/uploads /var/www/html/data/export /var/www/html/tmp/mpdf \
    && chown -R www-data:www-data /var/www/html

EXPOSE 80
