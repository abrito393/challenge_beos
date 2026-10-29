<?php

declare(strict_types=1);

namespace Src\Catalog\Infrastructure\Persistence;

use Src\Catalog\Application\ListProducts\ListProductsQuery;
use Src\Catalog\Application\ListProducts\ProductSearcher;
use Src\Catalog\Infrastructure\Persistence\Filters\ProductFilter;
use Src\Shared\Application\Page;

final readonly class EloquentProductSearcher implements ProductSearcher
{
    /**
     * @param  list<ProductFilter>  $filters  composed in CatalogServiceProvider (open for extension)
     */
    public function __construct(private array $filters) {}

    public function search(ListProductsQuery $query): Page
    {
        $builder = ProductViewQuery::base();

        foreach ($this->filters as $filter) {
            $filter->apply($builder, $query);
        }

        $direction = $query->sort->isDescending() ? 'desc' : 'asc';

        $paginator = $builder
            ->orderBy('products.'.$query->sort->field(), $direction)
            ->orderBy('products.id', $direction) // deterministic pagination on ties
            ->paginate(perPage: $query->perPage, page: $query->page);

        return new Page(
            items: array_values(array_map(ProductViewQuery::toView(...), $paginator->items())),
            total: $paginator->total(),
            perPage: $paginator->perPage(),
            currentPage: $paginator->currentPage(),
        );
    }
}
