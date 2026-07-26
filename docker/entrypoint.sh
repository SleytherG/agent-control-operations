#!/bin/sh
set -e

echo "AgenteFlow — starting deployment"

if [ "${APP_ENV}" = "production" ]; then
echo "Running migrations..."
php artisan migrate --force
    echo "Optimizing Laravel..."
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
else
    echo "Skipping production optimizations (APP_ENV=${APP_ENV})"
fi

echo "Starting PHP-FPM..."
php-fpm -D

echo "Starting Nginx..."
nginx -g "daemon off;"
