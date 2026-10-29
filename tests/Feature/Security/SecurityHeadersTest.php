<?php

declare(strict_types=1);

it('AC-004-09: adds security headers to every response, including errors', function (string $method, string $uri): void {
    $response = $this->json($method, $uri);

    $response->assertHeader('X-Content-Type-Options', 'nosniff')
        ->assertHeader('X-Frame-Options', 'DENY')
        ->assertHeader('Referrer-Policy', 'no-referrer')
        ->assertHeaderMissing('X-Powered-By');
})->with([
    'health check' => ['GET', '/up'],
    'unauthenticated' => ['GET', '/api/v1/auth/me'],
    'unknown route' => ['GET', '/api/v1/does-not-exist'],
    'validation error' => ['POST', '/api/v1/auth/token'],
]);

it('AC-004-09: enables HSTS only over HTTPS', function (): void {
    $this->get('https://localhost/up')->assertHeader('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
    $this->get('http://localhost/up')->assertHeaderMissing('Strict-Transport-Security');
});
