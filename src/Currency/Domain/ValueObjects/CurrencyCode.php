<?php

declare(strict_types=1);

namespace Src\Currency\Domain\ValueObjects;

use Src\Currency\Domain\Exceptions\InvalidCurrencyCode;

/** ISO 4217 alphabetic code, e.g. "USD". */
final readonly class CurrencyCode
{
    private string $value;

    public function __construct(string $value)
    {
        $normalized = strtoupper(trim($value));

        if (preg_match('/^[A-Z]{3}$/', $normalized) !== 1) {
            throw InvalidCurrencyCode::because($value);
        }

        $this->value = $normalized;
    }

    public function value(): string
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
