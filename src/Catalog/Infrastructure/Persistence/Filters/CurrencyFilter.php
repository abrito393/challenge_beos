<?php

declare(strict_types=1);

namespace Src\Catalog\Infrastructure\Persistence\Filters;

use Illuminate\Database\Eloquent\Builder;
use Src\Catalog\Application\ListProducts\ListProductsQuery;

final class CurrencyFilter implements ProductFilter
{
    public function apply(Builder $builder, ListProductsQuery $query): void
    {
        if ($query->currencyId !== null) {
            $builder->where('products.currency_id', $query->currencyId);
        }
    }
}
