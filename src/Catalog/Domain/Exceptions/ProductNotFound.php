<?php

declare(strict_types=1);

namespace Src\Catalog\Domain\Exceptions;

use Src\Shared\Domain\Exceptions\NotFound;

final class ProductNotFound extends NotFound
{
    public static function withId(int $id): self
    {
        return new self(sprintf('Product %d was not found.', $id));
    }

    public function errorCode(): string
    {
        return 'product-not-found';
    }
}
