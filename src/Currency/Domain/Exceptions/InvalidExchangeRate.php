<?php

declare(strict_types=1);

namespace Src\Currency\Domain\Exceptions;

use Src\Shared\Domain\Exceptions\InvalidValue;

final class InvalidExchangeRate extends InvalidValue
{
    public static function because(string $value): self
    {
        return new self(sprintf('Exchange rate must be a positive number with up to 8 decimals, got "%s".', $value));
    }

    public function errorCode(): string
    {
        return 'invalid-exchange-rate';
    }
}
