<?php

declare(strict_types=1);

namespace Src\Catalog\Application\ListProductPrices;

use Src\Catalog\Application\ProductFinder;
use Src\Catalog\Application\ProductPriceFinder;
use Src\Catalog\Application\ProductPriceView;
use Src\Catalog\Domain\Exceptions\ProductNotFound;
use Src\Catalog\Domain\ValueObjects\ProductId;

final readonly class ListProductPricesUseCase
{
    public function __construct(
        private ProductFinder $products,
        private ProductPriceFinder $prices,
    ) {}

    /**
     * @return list<ProductPriceView>
     *
     * @throws ProductNotFound for missing or soft-deleted products (AC-003-03)
     */
    public function execute(ListProductPricesQuery $query): array
    {
        $productId = new ProductId($query->productId);

        if ($this->products->find($productId) === null) {
            throw ProductNotFound::withId($query->productId);
        }

        return $this->prices->forProduct($productId);
    }
}
