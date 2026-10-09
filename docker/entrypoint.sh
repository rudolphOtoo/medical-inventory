#!/bin/sh
set -eu

cd /app

ENV_STORE=/app/.env-store

# 1. Seed .env if missing: persisted copy (keeps APP_KEY stable across
#    container recreates) wins, otherwise fall back to the template.
if [ ! -s .env ]; then
    if [ -s "$ENV_STORE/.env" ]; then
        cp "$ENV_STORE/.env" .env
    else
        template="${ENV_TEMPLATE:-.env.example}"
        if [ ! -f "$template" ]; then
            template=".env.example"
        fi
        if [ ! -f "$template" ]; then
            template=".env.lan.example"
        fi
        if [ -f "$template" ]; then
            cp "$template" .env
        else
            touch .env
        fi
    fi
fi

mkdir -p database storage/framework/cache storage/framework/sessions storage/framework/views \
    storage/logs storage/app/public bootstrap/cache

# 2. Wait for PostgreSQL readiness (skipped when running on sqlite, which is
#    the local non-Docker fallback). Retries for up to 120s so the compose
#    healthcheck gate and this loop together cover both restart and cold-boot.
if [ "${DB_CONNECTION:-pgsql}" = "sqlite" ]; then
    if [ ! -f database/database.sqlite ]; then
        touch database/database.sqlite
    fi
    chmod 666 database/database.sqlite || true
else
    echo "Waiting for PostgreSQL at ${DB_HOST:-postgres}:${DB_PORT:-5432}/${DB_DATABASE:-medical_inventory}..."
    attempt=0
    until php -r '
        $dsn = sprintf(
            "pgsql:host=%s;port=%s;dbname=%s",
            getenv("DB_HOST") ?: "postgres",
            getenv("DB_PORT") ?: "5432",
            getenv("DB_DATABASE") ?: "medical_inventory"
        );
        try {
            new PDO($dsn, getenv("DB_USERNAME") ?: "medtrack_user", getenv("DB_PASSWORD") ?: "medtrack_secret", [PDO::ATTR_TIMEOUT => 3]);
            exit(0);
        } catch (Throwable $e) {
            exit(1);
        }'; do
        attempt=$((attempt + 1))
        if [ "$attempt" -ge 60 ]; then
            echo "ERROR: PostgreSQL not reachable after 120s" >&2
            exit 1
        fi
        sleep 2
    done
    echo "PostgreSQL is ready."
fi

# 3. Generate APP_KEY only when neither the environment nor .env carries one.
if [ -z "${APP_KEY:-}" ] && ! grep -qE '^APP_KEY=.+' .env; then
    php artisan key:generate --force
fi

# Persist .env so APP_KEY (and any operator overrides) survive recreates.
mkdir -p "$ENV_STORE"
cp .env "$ENV_STORE/.env"

# 4. Permissions: Laravel must be able to write everything here. The spec
#    mandates 777; tracked .gitignore files inside storage are restored to
#    644 afterwards because the bind mount would otherwise flip their mode
#    in the host git tree.
chmod -R 777 storage bootstrap/cache
find storage bootstrap/cache -name .gitignore -exec chmod 644 {} + 2>/dev/null || true

# 5. Migrations — safe and idempotent on every boot (runs before php-fpm
#    accepts traffic, so nginx's healthcheck only passes once the schema is up).
php artisan migrate --force

# 6. Public disk symlink + stale cache cleanup.
php artisan storage:link || true
php artisan config:clear
php artisan route:clear

# 7. Start the main web process (php-fpm; foreground via the image's
#    daemonize=no). Overridable: `docker compose run app <command>`.
if [ "$#" -eq 0 ]; then
    set -- php-fpm
fi
exec "$@"
