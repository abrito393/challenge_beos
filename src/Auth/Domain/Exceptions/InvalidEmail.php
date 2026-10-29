<?php

declare(strict_types=1);

namespace Src\Auth\Domain\Exceptions;

use Src\Shared\Domain\Exceptions\InvalidValue;

final class InvalidEmail extends InvalidValue
{
    public static function because(string $value): self
    {
        return new self(sprintf('"%s" is not a valid email address.', $value));
    }

    public function errorCode(): string
    {
        return 'invalid-email';
    }
}
