#!/bin/sh

# Jalankan composer install jika vendor belum ada
if [ ! -d "vendor" ]; then
    composer install --no-interaction --optimize-autoloader
fi

# Buat file .env jika belum ada
if [ ! -f ".env" ]; then
    cp .env.example .env
    php artisan key:generate
fi

# Jalankan caching dan migrasi
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "Running migrations..."
php artisan migrate --force

# Start Nginx in background
nginx

# Start PHP-FPM
exec php-fpm

