<?php

declare(strict_types=1);

use Dedoc\Scramble\Generator;
use Dedoc\Scramble\Scramble;

beforeEach(function (): void {
    /** @var array<string, mixed> $document */
    $document = app(Generator::class)(Scramble::getGeneratorConfig(Scramble::DEFAULT_API));
    $this->document = $document;
});

it('documents every endpoint required by the challenge', function (): void {
    expect(array_keys($this->document['paths']))->toContain(
        '/products', '/products/{product}', '/products/{product}/prices', '/currencies', '/auth/token', '/auth/me',
    );
});

it('documents errors as RFC 9457 Problem Details, not Laravel defaults', function (): void {
    $responses = $this->document['components']['responses'];

    expect(array_keys($responses['ValidationException']['content']))->toBe(['application/problem+json'])
        ->and(array_keys($responses['AuthenticationException']['content']))->toBe(['application/problem+json'])
        ->and($this->document['components']['schemas'])->toHaveKeys(['ProblemDetails', 'ValidationProblemDetails']);
});

it('documents the error statuses static analysis cannot infer', function (): void {
    $paths = $this->document['paths'];

    expect($paths['/products/{product}/prices']['post']['responses'])->toHaveKeys(['401', '403', '404', '409', '422', '429'])
        ->and($paths['/auth/token']['post']['responses'])->toHaveKeys(['401', '422', '429'])
        ->and($paths['/auth/me']['get']['responses'])->not->toHaveKey('403');
});

it('marks only the login as public and everything else as Bearer-protected', function (): void {
    expect($this->document['paths']['/auth/token']['post']['security'])->toBe([])
        ->and($this->document['components']['securitySchemes'])->toHaveKey('http')
        ->and($this->document['paths']['/products']['get'])->not->toHaveKey('security');
});
