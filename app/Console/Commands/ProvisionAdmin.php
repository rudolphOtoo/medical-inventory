<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class ProvisionAdmin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'medtrack:provision-admin
                            {--force : Reset the password and profile when the administrator already exists}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Idempotently provision the initial administrator account from environment configuration.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $email = trim((string) config('medtrack.admin.email'));
        $password = (string) config('medtrack.admin.password');
        $name = trim((string) config('medtrack.admin.name'));

        $admin = $email !== ''
            ? User::query()->where('email', $email)->first()
            : null;

        if ($admin !== null && ! $this->option('force')) {
            $this->info("Administrator [{$email}] already exists; no changes made.");

            return self::SUCCESS;
        }

        $validator = Validator::make(
            ['email' => $email, 'password' => $password],
            [
                'email' => ['required', 'string', 'email:rfc', 'max:255'],
                'password' => ['required', 'string', Password::min(12)],
            ],
        );

        if ($validator->fails()) {
            $this->error('Administrator provisioning skipped: invalid configuration.');

            foreach ($validator->errors()->all() as $error) {
                $this->line('  - '.$error);
            }

            return self::FAILURE;
        }

        $attributes = [
            'name' => $name !== '' ? $name : 'Administrator',
            'password' => $password,
            'role' => UserRole::Admin,
            'department_id' => null,
            'is_active' => true,
            'email_verified_at' => now(),
        ];

        if ($admin === null) {
            $admin = new User;
            $admin->forceFill(['email' => $email, ...$attributes])->save();

            Log::info('Provisioned initial administrator account.', ['email' => $email]);
            $this->info("Administrator [{$email}] created.");

            return self::SUCCESS;
        }

        $admin->forceFill($attributes)->save();

        Log::info('Reset administrator account via provisioning.', ['email' => $email]);
        $this->info("Administrator [{$email}] updated.");

        return self::SUCCESS;
    }
}
