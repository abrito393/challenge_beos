<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

beforeEach(function (): void {
    Route::middleware(['api', 'auth:sanctum', 'abilities:products:write'])
        ->post('/api/v1/_test/write', fn (): array => ['ok' => true]);
});

it('AC-004-07: forbids a token without the required ability', function (): void {
    actingAsViewer();

    $this->postJson('/api/v1/_test/write')
        ->assertForbidden()
        ->assertHeader('Content-Type', 'application/problem+json')
        ->assertJsonPath('type', '/problems/forbidden');
});

it('AC-004-07: allows a token with the required ability', function (): void {
    actingAsAdmin();

    $this->postJson('/api/v1/_test/write')->assertOk();
});
