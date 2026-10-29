<?php

declare(strict_types=1);

use Illuminate\Foundation\Http\FormRequest;

/*
| SOLID conventions (ADR-0010): one action = one class per layer, Value Objects
| are immutable, and every file is strictly typed.
*/

arch()->preset()->php();
arch()->preset()->security();

arch('every file declares strict types')
    ->expect(['Src', 'App'])
    ->toUseStrictTypes();

arch('no debugging leftovers')
    ->expect(['dd', 'dump', 'ray', 'var_dump'])
    ->not->toBeUsed();

foreach (['Catalog', 'Currency', 'Auth', 'Shared'] as $context) {
    arch("{$context}: controllers are final, invokable single-action classes")
        ->expect("Src\\{$context}\\Infrastructure\\Http\\Controllers")
        ->toBeFinal()
        ->toBeInvokable()
        ->toHaveSuffix('Controller');

    arch("{$context}: requests are one FormRequest per action")
        ->expect("Src\\{$context}\\Infrastructure\\Http\\Requests")
        ->toBeFinal()
        ->toExtend(FormRequest::class)
        ->toHaveSuffix('Request');

    arch("{$context}: value objects are final and immutable")
        ->expect("Src\\{$context}\\Domain\\ValueObjects")
        ->toBeFinal()
        ->toBeReadonly();
}

it('use cases are final and expose a single public execute() method', function (): void {
    $files = new RegexIterator(
        new RecursiveIteratorIterator(new RecursiveDirectoryIterator(dirname(__DIR__, 2).'/src')),
        '#/Application/.+UseCase\.php$#',
    );

    foreach ($files as $file) {
        $relative = str_replace([dirname(__DIR__, 2).'/src'.'/', '.php', '/'], ['', '', '\\'], $file->getPathname());
        $class = 'Src\\'.$relative;
        $reflection = new ReflectionClass($class);

        $publicMethods = array_values(array_map(
            fn (ReflectionMethod $method): string => $method->getName(),
            array_filter(
                $reflection->getMethods(ReflectionMethod::IS_PUBLIC),
                fn (ReflectionMethod $method): bool => ! $method->isConstructor()
                    && $method->getDeclaringClass()->getName() === $class,
            ),
        ));

        expect($reflection->isFinal())->toBeTrue("{$class} must be final")
            ->and($publicMethods)->toBe(['execute'], "{$class} must only expose execute()");
    }

    expect(true)->toBeTrue();
});
