<?php

declare(strict_types=1);

namespace Src\Shared\Domain\ValueObjects;

use Src\Shared\Domain\Exceptions\InvalidCurrencyId;

final readonly class CurrencyId
{
    public function __construct(private int $value)
    {
        if ($value < 1) {
            throw InvalidCurrencyId::because((string) $value);
        }
    }

    public function value(): int
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
