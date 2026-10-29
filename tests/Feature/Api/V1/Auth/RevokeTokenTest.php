<?php

declare(strict_types=1);

use Src\Auth\Infrastructure\Persistence\EloquentUser;

it('AC-004-04: revokes the current token so it can no longer be used', function (): void {
    EloquentUser::factory()->admin()->create(['email' => 'admin@example.com']);
    $token = issueToken('admin@example.com');

    $this->withToken($token)->deleteJson('/api/v1/auth/token')->assertNoContent();
    $this->assertDatabaseCount('personal_access_tokens', 0);

    app('auth')->forgetGuards();

    $this->withToken($token)->getJson('/api/v1/auth/me')->assertUnauthorized();
});

it('requires authentication to revoke a token', function (): void {
    $this->deleteJson('/api/v1/auth/token')->assertUnauthorized();
});
