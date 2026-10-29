<?php

declare(strict_types=1);

namespace Src\Catalog\Application\ListProducts;

final readonly class ListProductsQuery
{
    public const int DEFAULT_PER_PAGE = 15;

    public const int MAX_PER_PAGE = 100;

    public function __construct(
        public ?string $name = null,
        public ?int $currencyId = null,
        public ?string $minPrice = null,
        public ?string $maxPrice = null,
        public ProductSort $sort = ProductSort::DEFAULT,
        public int $page = 1,
        public int $perPage = self::DEFAULT_PER_PAGE,
    ) {}
}
