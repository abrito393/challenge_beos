<?php

declare(strict_types=1);

namespace Src\Catalog\Domain\Exceptions;

use Src\Shared\Domain\Exceptions\InvalidValue;

final class PriceInBaseCurrency extends InvalidValue
{
    public static function forCurrency(int $currencyId): self
    {
        return new self(sprintf(
            'Currency %d is the base currency of the product; its price is the product price itself.',
            $currencyId,
        ));
    }

    public function errorCode(): string
    {
        return 'price-in-base-currency';
    }
}
