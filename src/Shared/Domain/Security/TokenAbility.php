<?php

declare(strict_types=1);

namespace Src\Shared\Domain\Security;

/**
 * Published language between contexts: Auth grants these abilities to tokens,
 * every other context declares which one each route requires (ADR-0008).
 */
enum TokenAbility: string
{
    case ProductsRead = 'products:read';
    case ProductsWrite = 'products:write';
}
