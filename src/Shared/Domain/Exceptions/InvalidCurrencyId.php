<?php

declare(strict_types=1);

namespace Src\Shared\Domain\Exceptions;

final class InvalidCurrencyId extends InvalidValue
{
    public static function because(string $value): self
    {
        return new self(sprintf('Currency id must be a positive integer, got %s.', $value));
    }

    public function errorCode(): string
    {
        return 'invalid-currency-id';
    }
}
