<?php

declare(strict_types=1);

namespace Src\Catalog\Application\ListProducts;

use Src\Catalog\Application\ProductView;
use Src\Shared\Application\Page;

final readonly class ListProductsUseCase
{
    public function __construct(private ProductSearcher $searcher) {}

    /** @return Page<ProductView> */
    public function execute(ListProductsQuery $query): Page
    {
        return $this->searcher->search($query);
    }
}
