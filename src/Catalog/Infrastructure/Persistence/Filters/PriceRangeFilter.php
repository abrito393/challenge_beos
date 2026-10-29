<?php

declare(strict_types=1);

namespace Src\Catalog\Infrastructure\Persistence\Filters;

use Illuminate\Database\Eloquent\Builder;
use Src\Catalog\Application\ListProducts\ListProductsQuery;

/** Compares the base price of each product, in its own base currency. */
final class PriceRangeFilter implements ProductFilter
{
    public function apply(Builder $builder, ListProductsQuery $query): void
    {
        if ($query->minPrice !== null) {
            $builder->where('products.price', '>=', $query->minPrice);
        }

        if ($query->maxPrice !== null) {
            $builder->where('products.price', '<=', $query->maxPrice);
        }
    }
}
