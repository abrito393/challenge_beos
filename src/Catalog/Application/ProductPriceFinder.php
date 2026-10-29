<?php

declare(strict_types=1);

namespace Src\Catalog\Application;

use Src\Catalog\Domain\ValueObjects\ProductId;
use Src\Shared\Domain\ValueObjects\CurrencyId;

interface ProductPriceFinder
{
    /** @return list<ProductPriceView> ordered by currency code */
    public function forProduct(ProductId $productId): array;

    public function find(ProductId $productId, CurrencyId $currencyId): ?ProductPriceView;
}
