---
name: storage-integration
description: Cloudflare R2, AWS S3, and Cloudinary media storage driver integration for Laravel. Trigger with /storage-integration.
---

# /storage-integration

## S3 & Cloudflare R2 S3-Compatible Driver Setup

### 1. `config/filesystems.php` R2 Disk:
```php
'r2' => [
    'driver' => 's3',
    'key' => env('CLOUDFLARE_R2_ACCESS_KEY_ID'),
    'secret' => env('CLOUDFLARE_R2_SECRET_ACCESS_KEY'),
    'region' => 'auto',
    'bucket' => env('CLOUDFLARE_R2_BUCKET_NAME'),
    'url' => env('CLOUDFLARE_R2_URL'),
    'endpoint' => env('CLOUDFLARE_R2_ENDPOINT'),
    'use_path_style_endpoint' => false,
    'throw' => true,
],
```

### 2. Environment Variables:
```env
CLOUDFLARE_R2_ACCESS_KEY_ID=xxx
CLOUDFLARE_R2_SECRET_ACCESS_KEY=yyy
CLOUDFLARE_R2_BUCKET_NAME=medtrack-assets
CLOUDFLARE_R2_ENDPOINT=https://<account-id>.r2.cloudflarestorage.com
CLOUDFLARE_R2_URL=https://pub-<id>.r2.dev
FILESYSTEM_DISK=r2
```
