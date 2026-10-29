<?php

declare(strict_types=1);

namespace Src\Auth\Domain\Exceptions;

use Src\Shared\Domain\Exceptions\InvalidValue;

final class InvalidDeviceName extends InvalidValue
{
    public static function because(string $value): self
    {
        return new self(sprintf('Device name must have between 1 and 255 characters, got "%s".', $value));
    }

    public function errorCode(): string
    {
        return 'invalid-device-name';
    }
}
