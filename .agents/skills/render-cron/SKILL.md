---
name: render-cron
description: External webhook and cron trigger architecture pattern for Laravel on Render and serverless hosts. Trigger with /render-cron.
---

# /render-cron

## Render & Serverless Scheduled Execution

### Pattern
Instead of running a 24/7 background worker process, use Render Cron Jobs or external webhooks (e.g., cron-job.org / GitHub Actions / Upstash QStash) to ping a secured route:

```php
// routes/api.php
Route::post('/cron/run', function (Request $request) {
    abort_unless($request->header('X-CRON-SECRET') === config('services.cron.secret'), 403);
    
    Artisan::call('schedule:run');
    return response()->json(['status' => 'executed', 'output' => Artisan::output()]);
});
```
