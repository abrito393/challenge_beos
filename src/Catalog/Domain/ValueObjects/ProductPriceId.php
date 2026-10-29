<?php

declare(strict_types=1);

namespace Src\Catalog\Domain\ValueObjects;

use Src\Catalog\Domain\Exceptions\InvalidProductId;

final readonly class ProductPriceId
{
    public function __construct(private int $value)
    {
        if ($value < 1) {
            throw InvalidProductId::because((string) $value);
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
