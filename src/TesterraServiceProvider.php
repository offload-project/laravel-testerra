<?php

declare(strict_types=1);

namespace OffloadProject\Testerra;

use Illuminate\Support\ServiceProvider;

final class TesterraServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/testerra.php', 'testerra');

        $this->app->singleton('testerra', function ($app) {
            return new Testerra($app['config']['testerra']);
        });
    }

    public function boot(): void
    {
        $this->publishConfig();
        $this->publishMigrations();
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
}
