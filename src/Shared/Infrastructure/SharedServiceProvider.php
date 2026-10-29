<?php

declare(strict_types=1);

namespace Src\Shared\Infrastructure;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

final class SharedServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // 60 requests per minute per authenticated user, falling back to IP (AC-004-08).
        RateLimiter::for('api', function (Request $request): Limit {
            $userId = $request->user()?->getAuthIdentifier();

            return Limit::perMinute(60)->by(is_scalar($userId) ? 'user:'.$userId : 'ip:'.$request->ip());
        });
    }
}
