<?php

declare(strict_types=1);

namespace Src\Catalog\Infrastructure\Persistence\Filters;

use Illuminate\Database\Eloquent\Builder;
use Src\Catalog\Application\ListProducts\ListProductsQuery;
use Src\Catalog\Infrastructure\Persistence\EloquentProduct;

interface ProductFilter
{
    /** @param  Builder<EloquentProduct>  $builder */
    public function apply(Builder $builder, ListProductsQuery $query): void;
}
