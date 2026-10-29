<?php

declare(strict_types=1);

namespace Src\Catalog\Infrastructure\Persistence\Filters;

use Illuminate\Database\Eloquent\Builder;
use Src\Catalog\Application\ListProducts\ListProductsQuery;

/** Case-insensitive "contains" (utf8mb4_unicode_ci). LIKE wildcards in the input are escaped. */
final class NameFilter implements ProductFilter
{
    public function apply(Builder $builder, ListProductsQuery $query): void
    {
        if ($query->name === null || $query->name === '') {
            return;
        }

        $escaped = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $query->name);

        $builder->where('products.name', 'like', "%{$escaped}%");
    }
}
