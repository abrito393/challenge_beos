<?php

declare(strict_types=1);

/*
| Dependency rule (ADR-0009): Infrastructure -> Application -> Domain.
| Dependencies only point inwards; the domain knows nothing about Laravel.
*/

const CONTEXTS = ['Catalog', 'Currency', 'Auth', 'Shared'];

$allApplicationLayers = array_map(fn (string $context): string => "Src\\{$context}\\Application", CONTEXTS);
$allInfrastructureLayers = array_map(fn (string $context): string => "Src\\{$context}\\Infrastructure", CONTEXTS);

foreach (CONTEXTS as $context) {
    arch("{$context}: domain is pure PHP (no framework, no outer layers)")
        ->expect("Src\\{$context}\\Domain")
        ->not->toUse([
            'Illuminate',
            'Laravel',
            'Carbon',
            ...$allApplicationLayers,
            ...$allInfrastructureLayers,
        ]);

    arch("{$context}: application does not depend on infrastructure or HTTP/Eloquent")
        ->expect("Src\\{$context}\\Application")
        ->not->toUse([
            'Illuminate\Http',
            'Illuminate\Database',
            'Illuminate\Support\Facades',
            ...$allInfrastructureLayers,
        ]);
}
