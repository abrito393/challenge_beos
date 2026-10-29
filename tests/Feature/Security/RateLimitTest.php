<?php

declare(strict_types=1);

it('AC-004-08: limits authenticated users to 60 requests per minute', function (): void {
    actingAsViewer();

    foreach (range(1, 60) as $request) {
        $this->getJson('/api/v1/auth/me')->assertOk();
    }

    $this->getJson('/api/v1/auth/me')
        ->assertTooManyRequests()
        ->assertHeader('Retry-After')
        ->assertJsonPath('type', '/problems/too-many-requests');
});
