# Dockerfile — Multi-stage build for Laravel on Render
# Stage 1: Build (composer, npm, vite)
FROM php:8.4-cli AS build

RUN apt-get update && apt-get install -y \
    git unzip libpq-dev libzip-dev nodejs npm \
    && docker-php-ext-install pdo pdo_pgsql zip opcache bcmath \
    && apt-get clean

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --optimize-autoloader --no-scripts

COPY . .

COPY package.json package-lock.json ./
RUN npm ci
COPY resources/ resources/
COPY vite.config.js ./
RUN npm run build

# Stage 2: Runtime (PHP-FPM + Nginx)
FROM php:8.4-fpm

RUN apt-get update && apt-get install -y \
    nginx libpq-dev libzip-dev \
    && docker-php-ext-install pdo pdo_pgsql zip opcache bcmath \
    && apt-get clean

WORKDIR /var/www

COPY --from=build /app /var/www
COPY docker/nginx.conf /etc/nginx/conf.d/default.conf
COPY docker/php.ini /usr/local/etc/php/conf.d/99-agenteflow.ini
COPY docker/php-fpm.conf /usr/local/etc/php-fpm.d/zz-agenteflow.conf
COPY docker/entrypoint.sh /entrypoint.sh

RUN chmod +x /entrypoint.sh \
    && rm -f /etc/nginx/sites-enabled/default \
    && chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache \
    && rm -f /var/www/bootstrap/cache/*.php

EXPOSE 80

ENTRYPOINT ["/entrypoint.sh"]
