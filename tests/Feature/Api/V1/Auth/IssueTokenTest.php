<?php

declare(strict_types=1);

use Src\Auth\Domain\UserRole;
use Src\Auth\Infrastructure\Persistence\EloquentUser;

beforeEach(function (): void {
    EloquentUser::factory()->admin()->create(['email' => 'admin@example.com']);
    EloquentUser::factory()->viewer()->create(['email' => 'viewer@example.com']);
});

it('AC-004-01: issues a bearer token for valid credentials', function (): void {
    $this->postJson('/api/v1/auth/token', [
        'email' => 'admin@example.com',
        'password' => 'password',
        'device_name' => 'postman',
    ])
        ->assertCreated()
        ->assertJsonStructure(['data' => ['token', 'token_type', 'abilities', 'expires_at']])
        ->assertJsonPath('data.token_type', 'Bearer')
        ->assertJsonPath('data.abilities', ['products:read', 'products:write']);

    $this->assertDatabaseHas('personal_access_tokens', ['name' => 'postman']);
});

it('AC-004-01: sets the token expiration from configuration', function (): void {
    $this->freezeSecond();
    config(['sanctum.expiration' => 60]);

    $this->postJson('/api/v1/auth/token', [
        'email' => 'admin@example.com', 'password' => 'password', 'device_name' => 'cli',
    ])->assertJsonPath('data.expires_at', now()->addMinutes(60)->toAtomString());
});

it('AC-004-02: answers the same generic 401 for a wrong password and an unknown email', function (string $email, string $password): void {
    $this->postJson('/api/v1/auth/token', ['email' => $email, 'password' => $password, 'device_name' => 'cli'])
        ->assertUnauthorized()
        ->assertHeader('Content-Type', 'application/problem+json')
        ->assertJsonPath('type', '/problems/invalid-credentials')
        ->assertJsonPath('detail', 'The provided credentials are incorrect.');
})->with([
    'wrong password' => ['admin@example.com', 'wrong-password'],
    'unknown email' => ['ghost@example.com', 'password'],
]);

it('AC-004-03: throttles login attempts after 5 per minute for the same email and IP', function (): void {
    foreach (range(1, 5) as $attempt) {
        $this->postJson('/api/v1/auth/token', ['email' => 'admin@example.com', 'password' => 'nope', 'device_name' => 'cli'])
            ->assertUnauthorized();
    }

    $this->postJson('/api/v1/auth/token', ['email' => 'admin@example.com', 'password' => 'password', 'device_name' => 'cli'])
        ->assertTooManyRequests()
        ->assertHeader('Retry-After')
        ->assertHeader('Content-Type', 'application/problem+json');
});

it('AC-004-06: issues viewer tokens with read-only ability', function (): void {
    $this->postJson('/api/v1/auth/token', [
        'email' => 'viewer@example.com', 'password' => 'password', 'device_name' => 'cli',
    ])->assertCreated()->assertJsonPath('data.abilities', ['products:read']);

    $abilities = EloquentUser::query()->where('role', UserRole::Viewer)->firstOrFail()->tokens()->firstOrFail()->abilities;

    expect($abilities)->toBe(['products:read']);
});

it('rejects an invalid payload with field errors', function (): void {
    $this->postJson('/api/v1/auth/token', ['email' => 'not-an-email'])
        ->assertUnprocessable()
        ->assertJsonPath('type', '/problems/validation-error')
        ->assertJsonValidationErrors(['email', 'password', 'device_name']);
});
