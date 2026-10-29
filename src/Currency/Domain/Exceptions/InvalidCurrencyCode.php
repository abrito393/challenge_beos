<?php

declare(strict_types=1);

namespace Src\Currency\Domain\Exceptions;

use Src\Shared\Domain\Exceptions\InvalidValue;

final class InvalidCurrencyCode extends InvalidValue
{
    public static function because(string $value): self
    {
        return new self(sprintf('"%s" is not a valid ISO 4217 currency code.', $value));
    }

    public function errorCode(): string
    {
        return 'invalid-currency-code';
    }
}
