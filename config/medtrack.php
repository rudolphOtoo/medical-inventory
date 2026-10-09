<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Initial Administrator Provisioning
    |--------------------------------------------------------------------------
    |
    | Credentials for the `medtrack:provision-admin` command, which the Docker
    | entrypoint runs once on boot to guarantee an administrator account exists
    | without shipping demo seed data to production. The command is idempotent:
    | it only creates the account when it is missing, and never overwrites an
    | existing password unless the `--force` flag is passed.
    |
    | Leave the email and password empty to disable automatic provisioning.
    |
    */

    'admin' => [
        'name' => env('MEDTRACK_ADMIN_NAME', 'Administrator'),
        'email' => env('MEDTRACK_ADMIN_EMAIL'),
        'password' => env('MEDTRACK_ADMIN_PASSWORD'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Demo Data Seeding
    |--------------------------------------------------------------------------
    |
    | Controls whether sample users and records are planted by the database
    | seeder. Demo data is always planted in the `local` environment; set this
    | flag to `true` to force it in any other environment (for example, a
    | pre-production demo instance).
    |
    */

    'seed_demo_users' => env('SEED_DEMO_USERS', false),

    /*
    |--------------------------------------------------------------------------
    | Email Verification Enforcement
    |--------------------------------------------------------------------------
    |
    | When enabled, the `active` middleware rejects authenticated requests from
    | accounts whose email has not been verified. Verification is disabled by
    | default because staff accounts are verified directly by administrators.
    |
    */

    'require_email_verification' => env('REQUIRE_EMAIL_VERIFICATION', false),

];
