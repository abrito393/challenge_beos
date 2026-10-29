<?php

declare(strict_types=1);

namespace Src\Catalog\Application\GetProduct;

use Src\Catalog\Application\ProductFinder;
use Src\Catalog\Application\ProductView;
use Src\Catalog\Domain\Exceptions\ProductNotFound;
use Src\Catalog\Domain\ValueObjects\ProductId;

final readonly class GetProductUseCase
{
    public function __construct(private ProductFinder $finder) {}

    /** @throws ProductNotFound */
    public function execute(GetProductQuery $query): ProductView
    {
        return $this->finder->find(new ProductId($query->productId))
            ?? throw ProductNotFound::withId($query->productId);
    }
}
