---
name: vercel-laravel
description: Serverless Laravel deployment on Vercel using PHP runtime with static asset handling and serverless storage patterns. Trigger with /vercel-laravel.
---

# /vercel-laravel

## Serverless Laravel on Vercel Guidelines

### 1. `vercel.json` Configuration
```json
{
  "version": 2,
  "framework": null,
  "functions": {
    "api/index.php": { "runtime": "vercel-php@0.7.2" }
  },
  "routes": [
    { "src": "/build/(.*)", "dest": "/public/build/$1" },
    { "src": "/favicon.ico", "dest": "/public/favicon.ico" },
    { "src": "/robots.txt", "dest": "/public/robots.txt" },
    { "src": "/(.*)", "dest": "/api/index.php" }
  ]
}
```

### 2. Bridge Entrypoint (`api/index.php`)
```php
<?php
require __DIR__ . '/../public/index.php';
```

### 3. Ephemeral Filesystem Rules
- Vercel functions are stateless and read-only (except `/tmp`).
- Set `VIEW_COMPILED_PATH=/tmp/storage/framework/views`
- Set `SESSION_DRIVER=cookie` or `database`
- Set `CACHE_STORE=database` or `redis`
- Direct uploads to Cloudflare R2, S3, or Cloudinary.
