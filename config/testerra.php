<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Table Prefix
    |--------------------------------------------------------------------------
    |
    | The prefix used for all Testerra database tables.
    |
    */
    'table_prefix' => 'testerra_',

    /*
    |--------------------------------------------------------------------------
    | User Model
    |--------------------------------------------------------------------------
    |
    | The user model class that has the HasTestAssignments trait.
    |
    */
    'user_model' => App\Models\User::class,

    /*
    |--------------------------------------------------------------------------
    | Screenshot Storage
    |--------------------------------------------------------------------------
    |
    | Configure where bug screenshots are stored.
    |
    */
    'screenshots' => [
        'disk' => env('TESTERRA_SCREENSHOT_DISK', 'local'),
        'path' => 'testerra/screenshots',
    ],

    /*
    |--------------------------------------------------------------------------
    | Waitlist Integration
    |--------------------------------------------------------------------------
    |
    | Optional integration with offload-project/laravel-waitlist package.
    | Set enabled to true and specify the waitlist name to use.
    |
    */
    'waitlist' => [
        'enabled' => false,
        'name' => 'testers',
    ],

    /*
    |--------------------------------------------------------------------------
    | Invitations (via laravel-invite-only)
    |--------------------------------------------------------------------------
    |
    | Invitation settings are managed through the invite-only config.
    | Publish the invite-only config to customize invitation behavior:
    | php artisan vendor:publish --tag="invite-only-config"
    |
    | See: https://github.com/offload-project/laravel-invite-only
    |
    */
    'invitations' => [
        // Additional testerra-specific invitation settings can be added here
    ],
];
