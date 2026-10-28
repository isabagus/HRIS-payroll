#!/bin/sh
set -e

# Buat symbolic link storage jika belum ada
if [ ! -L /var/www/public/storage ]; then
    php artisan storage:link || true
fi

# Pastikan izin folder upload gambar dan storage selalu aman
mkdir -p /var/www/public/images
chown -R www-data:www-data /var/www/public/images /var/www/storage /var/www/bootstrap/cache
chmod -R 775 /var/www/public/images /var/www/storage /var/www/bootstrap/cache

# Optimasi cache Laravel untuk production (hanya jika .env sudah ada)
if [ -f /var/www/.env ]; then
    php artisan config:cache || true
    php artisan route:cache || true
    php artisan view:cache || true
fi

exec "$@"
