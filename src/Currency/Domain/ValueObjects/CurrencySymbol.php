<?php

declare(strict_types=1);

namespace Src\Currency\Domain\ValueObjects;

use Src\Currency\Domain\Exceptions\InvalidCurrencySymbol;

final readonly class CurrencySymbol
{
    private const int MAX_LENGTH = 10;

    private string $value;

    public function __construct(string $value)
    {
        $trimmed = trim($value);

        if ($trimmed === '' || mb_strlen($trimmed) > self::MAX_LENGTH) {
            throw InvalidCurrencySymbol::because($value);
        }

        $this->value = $trimmed;
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
