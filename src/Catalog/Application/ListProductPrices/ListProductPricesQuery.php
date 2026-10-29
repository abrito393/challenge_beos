<?php

declare(strict_types=1);

namespace Src\Catalog\Application\ListProductPrices;

final readonly class ListProductPricesQuery
{
    public function __construct(public int $productId) {}
}
