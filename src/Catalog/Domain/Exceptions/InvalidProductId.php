<?php

declare(strict_types=1);

namespace Src\Catalog\Domain\Exceptions;

use Src\Shared\Domain\Exceptions\InvalidValue;

final class InvalidProductId extends InvalidValue
{
    public static function because(string $value): self
    {
        return new self(sprintf('Product id must be a positive integer, got %s.', $value));
    }

    public function errorCode(): string
    {
        return 'invalid-product-id';
    }
}
