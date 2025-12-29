<?php

declare(strict_types=1);

namespace OffloadProject\Testerra;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use OffloadProject\Testerra\Events\BugReported;
use OffloadProject\Testerra\Listeners\CreateExternalIssueListener;
use OffloadProject\Testerra\Support\IssueTrackers\IssueTrackerManager;

final class TesterraServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/testerra.php', 'testerra');

        $this->app->singleton('testerra', function ($app) {
            return new Testerra($app['config']['testerra']);
        });

        $this->app->singleton(IssueTrackerManager::class, function ($app) {
            return new IssueTrackerManager(
                $app['config']['testerra']['issue_tracker'] ?? []
            );
        });
    }

    public function boot(): void
    {
        $this->publishConfig();
        $this->publishMigrations();
        $this->registerEventListeners();
    }

    protected function publishConfig(): void
    {
        $this->publishes([
            __DIR__.'/../config/testerra.php' => config_path('testerra.php'),
        ], 'testerra-config');
    }

    protected function publishMigrations(): void
    {
        $this->publishesMigrations([
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ], 'testerra-migrations');
    }

    protected function registerEventListeners(): void
    {
        Event::listen(BugReported::class, CreateExternalIssueListener::class);
    }
}
