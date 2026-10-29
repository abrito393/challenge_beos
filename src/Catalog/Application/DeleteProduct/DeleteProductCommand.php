<?php

declare(strict_types=1);

namespace Src\Catalog\Application\DeleteProduct;

final readonly class DeleteProductCommand
{
    public function __construct(public int $productId) {}
}
