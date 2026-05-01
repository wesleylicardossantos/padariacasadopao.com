FROM php:8.2-fpm

RUN apt-get update && apt-get install -y     git curl zip unzip libzip-dev libpng-dev libjpeg62-turbo-dev libfreetype6-dev     libonig-dev libicu-dev libxml2-dev libpq-dev libssl-dev supervisor     && docker-php-ext-configure gd --with-freetype --with-jpeg     && docker-php-ext-install pdo pdo_mysql mbstring zip exif pcntl bcmath intl gd opcache     && pecl install redis     && docker-php-ext-enable redis opcache

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY docker/php/local.ini /usr/local/etc/php/conf.d/local.ini
COPY docker/php/opcache.ini /usr/local/etc/php/conf.d/opcache.ini
COPY docker/supervisor/laravel-worker.conf /etc/supervisor/conf.d/laravel-worker.conf

WORKDIR /var/www/html
