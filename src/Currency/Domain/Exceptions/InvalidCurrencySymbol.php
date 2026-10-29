<?php

declare(strict_types=1);

namespace Src\Currency\Domain\Exceptions;

use Src\Shared\Domain\Exceptions\InvalidValue;

final class InvalidCurrencySymbol extends InvalidValue
{
    public static function because(string $value): self
    {
        return new self(sprintf('Currency symbol must have between 1 and 10 characters, got "%s".', $value));
    }

    public function errorCode(): string
    {
        return 'invalid-currency-symbol';
    }
}
