#!/bin/sh
set -eu

cd /app

ENV_STORE=/app/.env-store

# Run a setup command and abort with a descriptive message if it fails, so a
# broken boot is never a silent crash-loop.
run_step() {
    step_label="$1"
    shift
    echo "[entrypoint] ${step_label}..."
    "$@" || {
        status=$?
        echo "[entrypoint] FATAL: ${step_label} failed (exit ${status}). Aborting." >&2
        exit "$status"
    }
}

# 1. Seed .env if missing: persisted copy (keeps APP_KEY stable across
#    container recreates) wins, otherwise fall back to the template.
if [ ! -s .env ]; then
    if [ -s "$ENV_STORE/.env" ]; then
        echo "[entrypoint] Restoring persisted .env from ${ENV_STORE}"
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
            echo "[entrypoint] Seeding .env from ${template}"
            cp "$template" .env
        else
            echo "[entrypoint] WARNING: no .env template found; creating an empty .env" >&2
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
else
    echo "[entrypoint] Waiting for PostgreSQL at ${DB_HOST:-postgres}:${DB_PORT:-5432}/${DB_DATABASE:-medical_inventory}..."
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
            echo "[entrypoint] FATAL: PostgreSQL not reachable after 120s" >&2
            exit 1
        fi
        sleep 2
    done
    echo "[entrypoint] PostgreSQL is ready."
fi

# 3. Generate APP_KEY only when neither the environment nor .env carries one.
if [ -z "${APP_KEY:-}" ] && ! grep -qE '^APP_KEY=.+' .env; then
    run_step "Generating application key" php artisan key:generate --force
fi

# Persist .env so APP_KEY (and any operator overrides) survive recreates.
mkdir -p "$ENV_STORE"
cp .env "$ENV_STORE/.env"

# 4. Migrations — safe and idempotent on every boot (runs before php-fpm
#    accepts traffic, so nginx's healthcheck only passes once the schema is up).
#    Guard first: an empty migrations directory means a host bind mount is
#    shadowing the image's /app/database (the zero-clone deploy footgun), and
#    running migrate would silently report "Nothing to migrate".
migration_count=$(find database/migrations -maxdepth 1 -name '*.php' 2>/dev/null | wc -l | tr -d '[:space:]')
if [ "${migration_count:-0}" -eq 0 ]; then
    echo "[entrypoint] FATAL: database/migrations is empty inside the container." >&2
    echo "[entrypoint] A host bind mount is likely shadowing the image's database directory." >&2
    echo "[entrypoint] With docker-compose.deploy.yml, remove the './database:/app/database' mount." >&2
    exit 1
fi
run_step "Running database migrations (${migration_count} files)" php artisan migrate --force

# 4a. Plant the baseline organisational structure on the very first boot.
#     DatabaseSeeder only ever creates the structural baseline in production
#     (demo records stay gated behind SEED_DEMO_USERS), and the seeder itself
#     uses firstOrCreate, so this is safe and cheap to guard on emptiness.
department_count=$(php artisan tinker --execute='echo \App\Models\Department::query()->count();' 2>/dev/null | tr -d '[:space:]' || true)
case "$department_count" in
    0)
        run_step "Seeding baseline structure" php artisan db:seed --force
        ;;
    "")
        echo "[entrypoint] WARNING: could not read department count; skipping baseline seed." >&2
        ;;
    *)
        echo "[entrypoint] Baseline data already present (${department_count} departments)."
        ;;
esac

# 4b. Provision the initial administrator when credentials are supplied.
#     Idempotent: creates the account only when it is missing and never
#     resets an existing password (rotate explicitly with `--force`). Runs
#     only when both MEDTRACK_ADMIN_EMAIL and MEDTRACK_ADMIN_PASSWORD are set,
#     so demo seed data is never shipped to production.
if [ -n "${MEDTRACK_ADMIN_EMAIL:-}" ] && [ -n "${MEDTRACK_ADMIN_PASSWORD:-}" ]; then
    run_step "Provisioning initial administrator" php artisan medtrack:provision-admin --no-interaction
fi

# 5. Public disk symlink + framework caches. `optimize` rebuilds config, route,
#    event and view caches from the current environment, so a changed .env is
#    picked up on every container restart.
if [ ! -L public/storage ]; then
    echo "[entrypoint] Linking public storage..."
    php artisan storage:link || echo "[entrypoint] WARNING: storage:link failed (continuing)." >&2
fi
run_step "Caching framework bootstrap files" php artisan optimize

# 6. Permissions, applied last so files the root-run artisan steps above just
#    created (daily log, caches) are handed to php-fpm's www-data user too.
#    Least privilege: owner/group write only (775 dirs, 664 files). Tracked
#    .gitignore files keep 644 so bind mounts do not dirty the host git tree.
#    Non-fatal: some bind-mount filesystems reject chown/chmod.
echo "[entrypoint] Fixing storage permissions..."
{
    chown -R www-data:www-data storage bootstrap/cache \
        && find storage bootstrap/cache -type d -exec chmod 775 {} + \
        && find storage bootstrap/cache -type f -exec chmod 664 {} + \
        && find storage bootstrap/cache -name .gitignore -exec chmod 644 {} +
} || echo "[entrypoint] WARNING: could not fully apply storage permissions." >&2
if [ "${DB_CONNECTION:-pgsql}" = "sqlite" ]; then
    # SQLite writes journal/WAL files beside the database, so the directory
    # (not just the file) must be writable. Not recursive: ./database is
    # bind-mounted from the host in the build-based compose files.
    chown www-data:www-data database database/database.sqlite* \
        && chmod 775 database && chmod 664 database/database.sqlite* \
        || echo "[entrypoint] WARNING: could not set SQLite database permissions." >&2
fi

# 7. Start the main web process (php-fpm; foreground via the image's
#    daemonize=no). Overridable: `docker compose run app <command>`.
if [ "$#" -eq 0 ]; then
    set -- php-fpm
fi
echo "[entrypoint] Startup complete; starting: $*"
exec "$@"
