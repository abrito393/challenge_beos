<?php

declare(strict_types=1);

namespace Src\Auth\Domain\ValueObjects;

use Src\Auth\Domain\Exceptions\InvalidEmail;

final readonly class Email
{
    private const int MAX_LENGTH = 255;

    private string $value;

    public function __construct(string $value)
    {
        $normalized = mb_strtolower(trim($value));

        if (mb_strlen($normalized) > self::MAX_LENGTH || filter_var($normalized, FILTER_VALIDATE_EMAIL) === false) {
            throw InvalidEmail::because($value);
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
