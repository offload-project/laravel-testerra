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

    /*
    |--------------------------------------------------------------------------
    | Issue Tracker Integration
    |--------------------------------------------------------------------------
    |
    | Configure external issue tracker integration for automatic bug syncing.
    | When enabled, bugs reported through Testerra will automatically create
    | issues in your configured issue tracker.
    |
    */
    'issue_tracker' => [
        'enabled' => env('TESTERRA_ISSUE_TRACKER_ENABLED', false),
        'default' => env('TESTERRA_ISSUE_TRACKER_DRIVER', 'jira'),

        // Set to true to create issues asynchronously via queue
        'queue' => env('TESTERRA_ISSUE_TRACKER_QUEUE', false),
        'queue_name' => env('TESTERRA_ISSUE_TRACKER_QUEUE_NAME'),

        'providers' => [
            'jira' => [
                'host' => env('TESTERRA_JIRA_HOST'),
                'email' => env('TESTERRA_JIRA_EMAIL'),
                'api_token' => env('TESTERRA_JIRA_API_TOKEN'),
                'project_key' => env('TESTERRA_JIRA_PROJECT_KEY'),
                'issue_type' => env('TESTERRA_JIRA_ISSUE_TYPE', 'Bug'),

                // Map Testerra severity to Jira priority names
                'priority_mapping' => [
                    'critical' => 'Highest',
                    'high' => 'High',
                    'medium' => 'Medium',
                    'low' => 'Low',
                ],
            ],

            'github' => [
                'token' => env('TESTERRA_GITHUB_TOKEN'),
                'owner' => env('TESTERRA_GITHUB_OWNER'),
                'repo' => env('TESTERRA_GITHUB_REPO'),

                // Default labels applied to all issues
                'labels' => ['bug'],

                // Map Testerra severity to GitHub labels
                'severity_labels' => [
                    'critical' => 'priority: critical',
                    'high' => 'priority: high',
                    'medium' => 'priority: medium',
                    'low' => 'priority: low',
                ],
            ],
        ],
    ],
];
