# syntax=docker/dockerfile:1

# Multi-stage build: app (PHP-FPM) + webserver (nginx) on PostgreSQL,
# mirroring the standard wis-cms Laravel deployment layout.

# ---- Stage: frontend assets ----
FROM node:20-alpine AS frontend
WORKDIR /app
COPY package*.json ./
RUN npm ci
COPY . .
RUN npm run build

# ---- Stage: app (php-fpm) ----
FROM php:8.4-fpm-alpine AS app

RUN apk add --no-cache \
        postgresql-dev \
        sqlite-dev \
        libzip-dev \
        libpng-dev \
        libjpeg-turbo-dev \
        freetype-dev \
        icu-dev \
        oniguruma-dev \
    && docker-php-ext-configure gd --with-jpeg --with-freetype \
    && docker-php-ext-install -j"$(nproc)" \
        pdo_pgsql pgsql pdo_sqlite mbstring bcmath intl gd zip opcache pcntl

COPY docker/php.ini /usr/local/etc/php/conf.d/zz-app.ini

WORKDIR /app

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY composer.json composer.lock* ./
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader --no-scripts

COPY . .
COPY --from=frontend /app/public/build ./public/build

# Build-time boot prerequisites: package discovery output must ship in the
# image (it is excluded via bootstrap/cache/*.php in .dockerignore), and the
# public/storage symlink must exist in the image so the webserver stage can
# serve uploads — nginx only receives this stage's public/ directory.
RUN mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views \
        storage/logs storage/app/public bootstrap/cache \
    && php artisan package:discover --ansi \
    && php artisan storage:link \
    && chown -R www-data:www-data storage bootstrap/cache

COPY docker/entrypoint.sh /app/docker/entrypoint.sh
RUN chmod +x /app/docker/entrypoint.sh

# The entrypoint's .env seeding depends on the template being present in the
# image. .dockerignore explicitly un-ignores .env.example — assert it rather
# than discovering the breakage as a boot failure.
RUN test -f .env.example || (echo "FATAL: .env.example missing from image (check .dockerignore)" && exit 1)

ENV APP_ENV=production \
    APP_DEBUG=false

EXPOSE 9000

ENTRYPOINT ["/app/docker/entrypoint.sh"]
CMD ["php-fpm"]

# ---- Stage: webserver (nginx) ----
FROM nginx:1.27-alpine AS webserver
COPY docker/nginx.conf /etc/nginx/conf.d/default.conf
# Static assets (including the Vite build and the public/storage symlink)
# are baked in so nginx can serve them without touching the app container.
COPY --from=app /app/public /app/public
