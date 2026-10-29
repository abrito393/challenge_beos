<?php

declare(strict_types=1);

namespace Src\Catalog\Domain\ValueObjects;

use Src\Catalog\Domain\Exceptions\InvalidProductName;

final readonly class ProductName
{
    public const int MAX_LENGTH = 255;

    private string $value;

    public function __construct(string $value)
    {
        $trimmed = trim($value);

        if ($trimmed === '' || mb_strlen($trimmed) > self::MAX_LENGTH) {
            throw InvalidProductName::because($value);
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
