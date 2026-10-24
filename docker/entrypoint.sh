#!/bin/sh
set -e

# Buat symbolic link storage jika belum ada
if [ ! -L /var/www/public/storage ]; then
    php artisan storage:link || true
fi

# Optimasi cache Laravel untuk production (hanya jika .env sudah ada)
if [ -f /var/www/.env ]; then
    php artisan config:cache || true
    php artisan route:cache || true
    php artisan view:cache || true
fi

exec "$@"
