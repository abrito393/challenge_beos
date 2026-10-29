<?php

declare(strict_types=1);

namespace Src\Shared\Domain\Exceptions;

final class InvalidAmount extends InvalidValue
{
    public static function notNumeric(string $value): self
    {
        return new self(sprintf('"%s" is not a valid amount.', $value));
    }

    public static function negative(string $value): self
    {
        return new self(sprintf('Amount cannot be negative, got %s.', $value));
    }

    public static function tooPrecise(string $value): self
    {
        return new self(sprintf('Amount cannot have more than 2 decimals, got %s.', $value));
    }

    public static function tooLarge(string $value): self
    {
        return new self(sprintf('Amount %s exceeds the maximum allowed.', $value));
    }

    public function errorCode(): string
    {
        return 'invalid-amount';
    }
}
