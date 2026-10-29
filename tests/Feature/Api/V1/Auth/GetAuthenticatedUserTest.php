<?php

declare(strict_types=1);

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Src\Auth\Infrastructure\Persistence\EloquentUser;

it('returns the authenticated user with the abilities of the current token', function (): void {
    EloquentUser::factory()->admin()->create(['name' => 'Admin', 'email' => 'admin@example.com']);

    $this->withToken(issueToken('admin@example.com'))
        ->getJson('/api/v1/auth/me')
        ->assertOk()
        ->assertJsonPath('data.email', 'admin@example.com')
        ->assertJsonPath('data.role', 'admin')
        ->assertJsonPath('data.abilities', ['products:read', 'products:write']);
});

it('AC-004-13: stores passwords hashed and never serializes them', function (): void {
    EloquentUser::factory()->create(['email' => 'viewer@example.com', 'password' => 'plain-secret']);

    $stored = DB::table('users')->where('email', 'viewer@example.com')->value('password');
    expect($stored)->toBeString()->not->toBe('plain-secret')
        ->and(Hash::check('plain-secret', (string) $stored))->toBeTrue();

    $response = $this->withToken(issueToken('viewer@example.com', 'plain-secret'))->getJson('/api/v1/auth/me');

    $response->assertOk()->assertJsonMissingPath('data.password');
    expect($response->getContent())->not->toContain('plain-secret')->not->toContain((string) $stored);
});
