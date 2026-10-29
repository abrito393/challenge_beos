<?php

declare(strict_types=1);

/*
| Bounded contexts talk to each other only through the provider's Application
| layer or through the Shared kernel (ADR-0009).
*/

$businessContexts = ['Catalog', 'Currency', 'Auth'];

foreach ($businessContexts as $consumer) {
    foreach ($businessContexts as $provider) {
        if ($consumer === $provider) {
            continue;
        }

        arch("{$consumer} does not reach into {$provider} Domain/Infrastructure")
            ->expect("Src\\{$consumer}")
            ->not->toUse(["Src\\{$provider}\\Domain", "Src\\{$provider}\\Infrastructure"]);
    }
}

arch('Shared kernel does not depend on any business context')
    ->expect('Src\Shared')
    ->not->toUse(array_map(fn (string $context): string => "Src\\{$context}", $businessContexts));
