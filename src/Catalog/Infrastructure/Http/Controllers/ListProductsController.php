<?php

declare(strict_types=1);

namespace Src\Catalog\Infrastructure\Http\Controllers;

use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Pagination\LengthAwarePaginator;
use Src\Catalog\Application\ListProducts\ListProductsUseCase;
use Src\Catalog\Infrastructure\Http\Requests\ListProductsRequest;
use Src\Catalog\Infrastructure\Http\Resources\ProductResource;

final class ListProductsController
{
    /**
     * List products.
     *
     * Paginated, filterable (`filter[name]`, `filter[currency_id]`, `filter[min_price]`, `filter[max_price]`)
     * and sortable (`sort=name|-name|price|-price|created_at|-created_at`, default `-created_at`).
     * Price filters compare each product's base price in its own base currency.
     */
    public function __invoke(ListProductsRequest $request, ListProductsUseCase $useCase): AnonymousResourceCollection
    {
        $page = $useCase->execute($request->toQuery());

        return ProductResource::collection(new LengthAwarePaginator(
            $page->items,
            $page->total,
            $page->perPage,
            $page->currentPage,
            ['path' => $request->url(), 'query' => $request->query()],
        ));
    }
}
