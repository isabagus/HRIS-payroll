# -----------------------------------------------
# Stage 1: Build Frontend Assets (Vite)
# -----------------------------------------------
FROM node:20-alpine AS frontend_builder

WORKDIR /app
COPY package*.json ./
RUN npm install

COPY . .
RUN npm run build

# -----------------------------------------------
# Stage 2: Application Container (PHP 8.4.27 + Nginx + Supervisor)
# -----------------------------------------------
FROM php:8.4.27-fpm-alpine

# Install helper script ekstensi PHP resmi
ADD --chmod=0755 https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/

# Install sistem paket, Nginx, Supervisor, dan ekstensi PHP Laravel
RUN apk add --no-cache bash curl git nginx supervisor \
    && install-php-extensions pdo_mysql mbstring exif pcntl bcmath gd zip opcache

# Install Composer resmi
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# Copy source code project
COPY . /var/www

# Copy hasil build Vite dari Stage 1
COPY --from=frontend_builder /app/public/build /var/www/public/build

# Install dependencies composer (production mode)
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Konfigurasi Nginx & Supervisor
COPY docker/nginx/default.conf /etc/nginx/http.d/default.conf
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Atur permission untuk storage dan cache Laravel
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache \
    && chmod -R 775 /var/www/storage /var/www/bootstrap/cache

EXPOSE 80

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
