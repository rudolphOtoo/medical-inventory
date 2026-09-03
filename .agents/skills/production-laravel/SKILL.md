---
name: production-laravel
description: High-performance, secure production Laravel configuration, caching, opcache, queues, and optimization guidelines. Trigger with /production-laravel.
---

# /production-laravel

## Production Checklist & Optimization Rules

### 1. Caching
Run before serving production traffic:
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

### 2. Environment Configuration
- `APP_ENV=production`
- `APP_DEBUG=false`
- `APP_URL=https://your-domain.com`
- Set `SESSION_SECURE_COOKIE=true` and `FORCE_HTTPS=true` if behind SSL reverse proxy.

### 3. Database & Connection Pooling
- Enable persistent connections or connection poolers (e.g. pgBouncer / WAL for SQLite).
- Ensure indexes exist on all foreign keys and frequently queried status columns.

### 4. Background Queues & Scheduler
- Run `php artisan queue:work --tries=3 --timeout=90` supervised via systemd / Horizon.
- Run `php artisan schedule:run` on a 1-minute system cron.

### 5. Deployment Script Sequence
```bash
git pull origin main
composer install --no-dev --optimize-autoloader
npm ci && npm run build
php artisan migrate --force
php artisan optimize
php artisan queue:restart
```
