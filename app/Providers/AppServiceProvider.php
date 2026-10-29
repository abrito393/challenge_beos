<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;

final class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Lazy loading, silently discarded attributes and missing attributes throw outside production.
        Model::shouldBeStrict(! $this->app->isProduction());

        DB::prohibitDestructiveCommands($this->app->isProduction());
    }
}
