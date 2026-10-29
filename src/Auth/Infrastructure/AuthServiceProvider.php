<?php

declare(strict_types=1);

namespace Src\Auth\Infrastructure;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Src\Auth\Application\IssueToken\TokenIssuer;
use Src\Auth\Application\RevokeToken\TokenRevoker;
use Src\Auth\Domain\PasswordHasher;
use Src\Auth\Domain\UserRepository;
use Src\Auth\Infrastructure\Persistence\EloquentUserRepository;
use Src\Auth\Infrastructure\Security\LaravelPasswordHasher;
use Src\Auth\Infrastructure\Security\SanctumTokenIssuer;
use Src\Auth\Infrastructure\Security\SanctumTokenRevoker;

final class AuthServiceProvider extends ServiceProvider
{
    /** @var array<class-string, class-string> */
    public array $bindings = [
        UserRepository::class => EloquentUserRepository::class,
        PasswordHasher::class => LaravelPasswordHasher::class,
        TokenIssuer::class => SanctumTokenIssuer::class,
        TokenRevoker::class => SanctumTokenRevoker::class,
    ];

    public function boot(): void
    {
        // Brute-force protection: 5 attempts per minute per email + IP (AC-004-03).
        RateLimiter::for('login', fn (Request $request): Limit => Limit::perMinute(5)->by(
            mb_strtolower($request->string('email')->toString()).'|'.$request->ip(),
        ));
    }
}
