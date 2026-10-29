<?php

declare(strict_types=1);

namespace Src\Catalog\Application\ListProducts;

use Src\Catalog\Application\ProductView;
use Src\Shared\Application\Page;

interface ProductSearcher
{
    /** @return Page<ProductView> */
    public function search(ListProductsQuery $query): Page;
}
