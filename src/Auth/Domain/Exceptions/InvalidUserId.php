<?php

declare(strict_types=1);

namespace Src\Auth\Domain\Exceptions;

use Src\Shared\Domain\Exceptions\InvalidValue;

final class InvalidUserId extends InvalidValue
{
    public static function because(int $value): self
    {
        return new self(sprintf('User id must be a positive integer, got %d.', $value));
    }

    public function errorCode(): string
    {
        return 'invalid-user-id';
    }
}
