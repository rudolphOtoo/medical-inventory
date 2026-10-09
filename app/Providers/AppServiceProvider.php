<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\ClinicalNote;
use App\Models\SparePart;
use App\Models\User;
use App\Policies\ClinicalNotePolicy;
use App\Policies\SparePartPolicy;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();

        Gate::define('admin', fn (User $user): bool => $user->isAdmin());

        Gate::define('manage-backups', fn (User $user): bool => $user->isAdmin());

        Gate::define('manage-departments', fn (User $user): bool => $user->isAdmin());

        Gate::define('manage-users', fn (User $user): bool => $user->isAdmin());

        Gate::define('view-reports', fn (User $user): bool => $user->isAdmin());

        Gate::policy(ClinicalNote::class, ClinicalNotePolicy::class);
        Gate::policy(SparePart::class, SparePartPolicy::class);

        Blade::anonymousComponentPath(resource_path('views/layouts'), 'layouts');
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
