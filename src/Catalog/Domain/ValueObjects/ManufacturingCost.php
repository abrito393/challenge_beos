<?php

declare(strict_types=1);

namespace Src\Catalog\Domain\ValueObjects;

use Src\Shared\Domain\ValueObjects\Amount;

/** Non-negative cost in the product's base currency. */
final readonly class ManufacturingCost
{
    public function __construct(private Amount $amount) {}

    public static function fromString(string $value): self
    {
        return new self(Amount::fromString($value));
    }

    public function amount(): Amount
    {
        return $this->amount;
    }

    public function value(): string
    {
        return $this->amount->value();
    }

    public function equals(self $other): bool
    {
        return $this->amount->equals($other->amount);
    }
}
