<?php

declare(strict_types=1);

namespace Src\Auth\Domain\ValueObjects;

use Src\Auth\Domain\Exceptions\InvalidDeviceName;

final readonly class DeviceName
{
    private const int MAX_LENGTH = 255;

    private string $value;

    public function __construct(string $value)
    {
        $trimmed = trim($value);

        if ($trimmed === '' || mb_strlen($trimmed) > self::MAX_LENGTH) {
            throw InvalidDeviceName::because($value);
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
