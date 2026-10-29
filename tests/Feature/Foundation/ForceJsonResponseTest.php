<?php

declare(strict_types=1);

it('AC-000-05: answers JSON under /api even when the client does not ask for it', function (): void {
    $this->get('/api/v1/this-route-does-not-exist', ['Accept' => 'text/html'])
        ->assertNotFound()
        ->assertHeader('Content-Type', 'application/problem+json');
});
