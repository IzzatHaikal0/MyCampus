# Stage 1 - Build Frontend (Vite)
FROM node:18 AS frontend
WORKDIR /app
COPY package*.json ./
RUN npm install
COPY . .
RUN npm run build

# Stage 2 - Backend
FROM php:8.2-fpm AS backend

# Install system dependencies + gRPC requirements
RUN apt-get update && apt-get install -y \
    git curl unzip libpq-dev libonig-dev libzip-dev zip \
    zlib1g-dev libicu-dev g++ \
    && docker-php-ext-install pdo pdo_mysql mbstring zip

# Install gRPC and Protobuf (Required for Firebase/Firestore)
RUN pecl install grpc protobuf && \
    docker-php-ext-enable grpc protobuf

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
WORKDIR /var/www
COPY . .

# Copy Vite assets
COPY --from=frontend /app/public/build ./public/build

# Install PHP dependencies (Ensure composer.lock is fixed locally first!)
RUN composer install --no-dev --optimize-autoloader

# Laravel setup including the STORAGE LINK
RUN php artisan storage:link && \
    php artisan config:clear && \
    php artisan route:clear && \
    php artisan view:clear

# Fix permissions for Render
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

# Expose the port for Render
CMD php artisan serve --host=0.0.0.0 --port=10000