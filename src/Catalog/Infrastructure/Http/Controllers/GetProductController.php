<?php

declare(strict_types=1);

namespace Src\Catalog\Infrastructure\Http\Controllers;

use Src\Catalog\Application\GetProduct\GetProductQuery;
use Src\Catalog\Application\GetProduct\GetProductUseCase;
use Src\Catalog\Infrastructure\Http\Resources\ProductResource;

final class GetProductController
{
    /**
     * Get a product.
     */
    public function __invoke(GetProductUseCase $useCase, int $product): ProductResource
    {
        return ProductResource::make($useCase->execute(new GetProductQuery($product)));
    }
}
