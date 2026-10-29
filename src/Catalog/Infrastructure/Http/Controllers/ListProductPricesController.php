<?php

declare(strict_types=1);

namespace Src\Catalog\Infrastructure\Http\Controllers;

use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Src\Catalog\Application\ListProductPrices\ListProductPricesQuery;
use Src\Catalog\Application\ListProductPrices\ListProductPricesUseCase;
use Src\Catalog\Infrastructure\Http\Resources\ProductPriceResource;

final class ListProductPricesController
{
    /**
     * List the prices of a product.
     *
     * Prices registered in currencies other than the product's base currency, ordered by currency code.
     */
    public function __invoke(ListProductPricesUseCase $useCase, int $product): AnonymousResourceCollection
    {
        return ProductPriceResource::collection($useCase->execute(new ListProductPricesQuery($product)));
    }
}
