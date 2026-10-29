<?php

declare(strict_types=1);

namespace Src\Currency\Domain\Exceptions;

use Src\Shared\Domain\Exceptions\InvalidValue;

final class InvalidCurrencyName extends InvalidValue
{
    public static function because(string $value): self
    {
        return new self(sprintf('Currency name must have between 1 and 100 characters, got "%s".', $value));
    }

    public function errorCode(): string
    {
        return 'invalid-currency-name';
    }
}
