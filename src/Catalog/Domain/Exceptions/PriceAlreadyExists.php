<?php

declare(strict_types=1);

namespace Src\Catalog\Domain\Exceptions;

use Src\Shared\Domain\Exceptions\Conflict;

final class PriceAlreadyExists extends Conflict
{
    public static function forCurrency(int $currencyId): self
    {
        return new self(sprintf('The product already has a price in currency %d.', $currencyId));
    }

    public function errorCode(): string
    {
        return 'price-already-exists';
    }
}
