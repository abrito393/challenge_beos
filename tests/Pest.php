<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Src\Auth\Domain\UserRole;
use Src\Auth\Infrastructure\Persistence\EloquentUser;
use Src\Shared\Domain\Security\TokenAbility;
use Tests\TestCase;

/*
| Feature tests boot the framework and run against the isolated MySQL testing
| database (AC-000-02). Unit tests stay framework-free: they exercise the pure
| domain and application layers with in-memory adapters.
*/
pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

function actingAsRole(UserRole $role): EloquentUser
{
    $user = EloquentUser::factory()->create(['role' => $role]);

    Sanctum::actingAs($user, array_map(fn (TokenAbility $ability): string => $ability->value, $role->abilities()));

    return $user;
}

function actingAsAdmin(): EloquentUser
{
    return actingAsRole(UserRole::Admin);
}

function actingAsViewer(): EloquentUser
{
    return actingAsRole(UserRole::Viewer);
}

/** Issues a real Sanctum token through the API (for flows that need a persisted token). */
function issueToken(string $email, string $password = 'password'): string
{
    $token = test()->postJson('/api/v1/auth/token', [
        'email' => $email,
        'password' => $password,
        'device_name' => 'pest',
    ])->assertCreated()->json('data.token');

    // The guard caches the resolved user between requests of the same test.
    app('auth')->forgetGuards();

    return is_string($token) ? $token : throw new RuntimeException('Token was not issued.');
}
