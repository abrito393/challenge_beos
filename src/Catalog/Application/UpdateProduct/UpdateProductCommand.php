<?php

declare(strict_types=1);

namespace Src\Catalog\Application\UpdateProduct;

final readonly class UpdateProductCommand
{
    public function __construct(
        public int $productId,
        public string $name,
        public string $description,
        public string $price,
        public int $currencyId,
        public string $taxCost,
        public string $manufacturingCost,
    ) {}
}
