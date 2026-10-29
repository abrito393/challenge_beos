<?php

declare(strict_types=1);

namespace Src\Catalog\Application\CreateProductPrice;

final readonly class CreateProductPriceCommand
{
    /**
     * @param  string|null  $price  null = compute it from the base price and exchange rates (RN-003-3)
     */
    public function __construct(
        public int $productId,
        public int $currencyId,
        public ?string $price,
    ) {}
}
