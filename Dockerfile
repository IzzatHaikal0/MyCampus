# frontend build
FROM node:18 AS frontend
WORKDIR /app
COPY package*.json ./
RUN npm install
COPY . .
RUN npm run build

# build backend
FROM php:8.4-fpm AS backend

# reqss
RUN apt-get update && apt-get install -y \
    git curl unzip libpq-dev libonig-dev libzip-dev zip \
    zlib1g-dev libicu-dev g++ \
    && docker-php-ext-install pdo pdo_mysql mbstring zip

# firebase reqqss???
RUN pecl install grpc protobuf && \
    docker-php-ext-enable grpc protobuf

#composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
WORKDIR /var/www
COPY . .

#vite assets
COPY --from=frontend /app/public/build ./public/build

#install php dependancies
RUN composer install --no-dev --optimize-autoloader

#laravel setup++config
RUN php artisan storage:link && \
    php artisan config:clear && \
    php artisan route:clear && \
    php artisan view:clear

# permission
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

# Expose the port for Render
CMD php artisan serve --host=0.0.0.0 --port=10000