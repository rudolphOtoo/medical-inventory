# syntax=docker/dockerfile:1

# Multi-stage build: app (PHP-FPM) + webserver (nginx) on PostgreSQL,
# mirroring the standard wis-cms Laravel deployment layout.

# ---- Stage: composer dependencies ----
# Resolved once and shared by the frontend build (Tailwind scans the framework
# pagination views) and the runtime image, which therefore never ships the
# composer binary. PHP extensions live in the runtime stage, not here.
FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --no-progress --prefer-dist \
        --no-autoloader --no-scripts --ignore-platform-reqs
COPY app ./app
COPY database ./database
RUN composer dump-autoload --no-dev --optimize --no-scripts

# ---- Stage: frontend assets ----
FROM node:20-alpine AS frontend
WORKDIR /app
COPY package*.json ./
RUN npm ci
COPY . .
COPY --from=vendor /app/vendor/laravel/framework/src/Illuminate/Pagination/resources/views \
    ./vendor/laravel/framework/src/Illuminate/Pagination/resources/views
RUN npm run build

# ---- Stage: app (php-fpm) ----
FROM php:8.4-fpm-alpine AS app

# Runtime shared libraries stay; headers are only needed to compile the
# extensions and are removed in the same layer.
RUN apk add --no-cache \
        libpq \
        sqlite-libs \
        libzip \
        libpng \
        libjpeg-turbo \
        freetype \
        icu-libs \
        oniguruma \
    && apk add --no-cache --virtual .build-deps \
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
        pdo_pgsql pgsql pdo_sqlite mbstring bcmath intl gd zip opcache pcntl \
    && apk del .build-deps \
    && php -r 'foreach (["pdo_pgsql", "pgsql", "pdo_sqlite", "mbstring", "bcmath", "intl", "gd", "zip", "pcntl"] as $ext) { extension_loaded($ext) || exit(1); }'

COPY docker/php.ini /usr/local/etc/php/conf.d/zz-app.ini

WORKDIR /app

COPY . .
COPY --from=vendor /app/vendor ./vendor
COPY --from=frontend /app/public/build ./public/build

# Build-time boot prerequisites: package discovery output must ship in the
# image (it is excluded via bootstrap/cache/*.php in .dockerignore), and the
# public/storage symlink must exist in the image so the webserver stage can
# serve uploads — nginx only receives this stage's public/ directory.
RUN mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views \
        storage/logs storage/app/public bootstrap/cache \
    && php artisan package:discover --ansi \
    && php artisan storage:link \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod +x docker/entrypoint.sh

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
