<?php

declare(strict_types=1);

use Src\Auth\Infrastructure\Persistence\EloquentUser;

it('AC-004-05: rejects an expired token', function (): void {
    config(['sanctum.expiration' => 1440]);
    EloquentUser::factory()->create(['email' => 'viewer@example.com']);
    $token = issueToken('viewer@example.com');

    $this->withToken($token)->getJson('/api/v1/auth/me')->assertOk();
    app('auth')->forgetGuards();

    $this->travel(1441)->minutes();

    $this->withToken($token)->getJson('/api/v1/auth/me')->assertUnauthorized();
});
