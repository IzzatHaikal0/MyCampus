# Stage 1 - Build Frontend (Vite)
# Upgraded to Node 20 to support Vite 6+
FROM node:20 AS frontend
WORKDIR /app
COPY package*.json ./
RUN npm install
COPY . .
RUN npm run build

# Stage 2 - Backend (Laravel + PHP + Composer)
FROM php:8.2-fpm AS backend

# Install system dependencies + libraries needed for gRPC
RUN apt-get update && apt-get install -y \
    git curl unzip libpq-dev libonig-dev libzip-dev zip libz-dev \
    && docker-php-ext-install pdo pdo_mysql mbstring zip

# IMPORTANT: Install gRPC extension for Firestore
RUN pecl install grpc && docker-php-ext-enable grpc

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# Copy app files
COPY . .

# FIX: Vite builds into 'public/build', not 'public/dist'
COPY --from=frontend /app/public/build ./public/build

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader

# Laravel setup
RUN php artisan config:clear && \
    php artisan route:clear && \
    php artisan view:clear

# Fix permissions for Render
RUN chown -R www-data:www-data storage bootstrap/cache

# Change the last line from CMD ["php-fpm"] to:
CMD php artisan serve --host=0.0.0.0 --port=10000