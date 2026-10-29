<?php

declare(strict_types=1);

namespace Src\Catalog\Application\CreateProduct;

final readonly class CreateProductCommand
{
    public function __construct(
        public string $name,
        public string $description,
        public string $price,
        public int $currencyId,
        public string $taxCost,
        public string $manufacturingCost,
    ) {}
}
