<?php

declare(strict_types=1);

namespace Src\Catalog\Domain\Exceptions;

use Src\Shared\Domain\Exceptions\InvalidValue;

final class InvalidProductName extends InvalidValue
{
    public static function because(string $value): self
    {
        return new self('Product name must have between 1 and 255 characters.');
    }

    public function errorCode(): string
    {
        return 'invalid-product-name';
    }
}
