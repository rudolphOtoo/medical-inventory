# Multi-stage build for production-grade FrankenPHP deployment
FROM node:20-alpine AS frontend
WORKDIR /app
COPY package*.json ./
RUN npm ci
COPY . .
RUN npm run build

# Final PHP Runtime Stage
FROM dunglas/frankenphp:1-php8.3-bookworm

ARG WWWUSER=1000
ARG WWWGROUP=1000

WORKDIR /app

RUN install-php-extensions \
    pdo_sqlite \
    pdo_mysql \
    intl \
    mbstring \
    bcmath \
    opcache \
    zip \
    gd

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY composer.json composer.lock* ./
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader --no-scripts

COPY . .
COPY --from=frontend /app/public/build ./public/build

RUN mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache

COPY docker/Caddyfile /etc/caddy/Caddyfile
COPY docker/entrypoint.sh /app/docker/entrypoint.sh
RUN chmod +x /app/docker/entrypoint.sh

ENV SERVER_NAME=:80
ENV APP_ENV=production
ENV APP_DEBUG=false

EXPOSE 80

ENTRYPOINT ["/app/docker/entrypoint.sh"]
