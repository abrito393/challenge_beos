<?php

declare(strict_types=1);

use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\Route;
use Src\Shared\Domain\Exceptions\Conflict;
use Src\Shared\Domain\Exceptions\InvalidValue;
use Src\Shared\Domain\Exceptions\NotFound;

const PROBLEM_SHAPE = ['type', 'title', 'status', 'detail', 'instance'];

function throwingRoute(string $uri, Throwable $exception): void
{
    Route::middleware('api')->get("/api/v1/_test/{$uri}", fn () => throw $exception);
}

it('AC-004-10: hides internals of unexpected errors when debug is off', function (): void {
    config(['app.debug' => false]);
    throwingRoute('boom', new RuntimeException('SQLSTATE[42S02] in /var/www/html/src/Secret.php'));

    $response = $this->getJson('/api/v1/_test/boom');

    $response->assertStatus(500)
        ->assertHeader('Content-Type', 'application/problem+json')
        ->assertJsonStructure(PROBLEM_SHAPE)
        ->assertJsonPath('detail', 'An unexpected error occurred.')
        ->assertJsonMissingPath('debug');

    expect($response->getContent())->not->toContain('SQLSTATE')->not->toContain('/var/www');
});

it('AC-004-11: renders every error status as RFC 9457 Problem Details', function (string $method, string $uri, int $status, string $type, ?Closure $arrange = null): void {
    if ($arrange !== null) {
        $arrange();
    }

    $this->json($method, $uri)
        ->assertStatus($status)
        ->assertHeader('Content-Type', 'application/problem+json')
        ->assertJsonStructure(PROBLEM_SHAPE)
        ->assertJsonPath('status', $status)
        ->assertJsonPath('type', $type)
        ->assertJsonPath('instance', parse_url($uri, PHP_URL_PATH));
})->with([
    '401 unauthenticated' => ['GET', '/api/v1/auth/me', 401, '/problems/unauthenticated'],
    '404 unknown route' => ['GET', '/api/v1/missing', 404, '/problems/not-found'],
    '405 wrong method' => ['GET', '/api/v1/auth/token', 405, '/problems/method-not-allowed'],
    '409 unique violation' => ['GET', '/api/v1/_test/unique', 409, '/problems/conflict', fn () => throwingRoute(
        'unique', new UniqueConstraintViolationException('mysql', 'insert into x', [], new PDOException('Duplicate entry')),
    )],
    '422 validation' => ['POST', '/api/v1/auth/token', 422, '/problems/validation-error'],
]);

it('AC-004-11: maps domain exception categories to HTTP without leaking framework details', function (Throwable $exception, int $status, string $type): void {
    throwingRoute('domain', $exception);

    $this->getJson('/api/v1/_test/domain')
        ->assertStatus($status)
        ->assertHeader('Content-Type', 'application/problem+json')
        ->assertJsonPath('type', $type)
        ->assertJsonPath('detail', $exception->getMessage());
})->with([
    'not found' => fn () => [new class('Product 9 was not found.') extends NotFound
    {
        public function errorCode(): string
        {
            return 'product-not-found';
        }
    }, 404, '/problems/product-not-found'],
    'conflict' => fn () => [new class('Price already exists.') extends Conflict
    {
        public function errorCode(): string
        {
            return 'price-already-exists';
        }
    }, 409, '/problems/price-already-exists'],
    'invalid value' => fn () => [new class('Amount cannot be negative.') extends InvalidValue
    {
        public function errorCode(): string
        {
            return 'invalid-amount';
        }
    }, 422, '/problems/invalid-amount'],
]);

it('AC-004-12: answers unknown API routes with a JSON 404, never HTML', function (): void {
    $this->get('/api/v1/nothing-here', ['Accept' => 'text/html'])
        ->assertNotFound()
        ->assertHeader('Content-Type', 'application/problem+json')
        ->assertJsonPath('detail', 'The requested resource was not found.');
});

it('includes debugging context for unexpected errors only when debug is on', function (): void {
    config(['app.debug' => true]);
    throwingRoute('debug', new RuntimeException('boom'));

    $this->getJson('/api/v1/_test/debug')
        ->assertStatus(500)
        ->assertJsonPath('debug.exception', RuntimeException::class)
        ->assertJsonPath('debug.message', 'boom');
});
