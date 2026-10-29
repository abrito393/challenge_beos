<?php

declare(strict_types=1);

namespace Src\Catalog\Domain\Exceptions;

use Src\Shared\Domain\Exceptions\InvalidValue;

final class InvalidProductDescription extends InvalidValue
{
    public static function because(string $value): self
    {
        return new self('Product description must have between 1 and 2000 characters.');
    }

    public function errorCode(): string
    {
        return 'invalid-product-description';
    }
}
