<?php

declare(strict_types=1);

namespace Src\Currency\Domain\ValueObjects;

use Src\Currency\Domain\Exceptions\InvalidCurrencyName;

final readonly class CurrencyName
{
    private const int MAX_LENGTH = 100;

    private string $value;

    public function __construct(string $value)
    {
        $trimmed = trim($value);

        if ($trimmed === '' || mb_strlen($trimmed) > self::MAX_LENGTH) {
            throw InvalidCurrencyName::because($value);
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
