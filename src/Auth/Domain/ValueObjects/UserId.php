<?php

declare(strict_types=1);

namespace Src\Auth\Domain\ValueObjects;

use Src\Auth\Domain\Exceptions\InvalidUserId;

final readonly class UserId
{
    public function __construct(private int $value)
    {
        if ($value < 1) {
            throw InvalidUserId::because($value);
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
