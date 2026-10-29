<?php

declare(strict_types=1);

namespace Src\Catalog\Infrastructure;

use Illuminate\Support\ServiceProvider;
use Src\Catalog\Application\CreateProductPrice\PriceConverter;
use Src\Catalog\Application\ListProducts\ProductSearcher;
use Src\Catalog\Application\ProductFinder;
use Src\Catalog\Application\ProductPriceFinder;
use Src\Catalog\Domain\ProductRepository;
use Src\Catalog\Infrastructure\Currency\CurrencyContextPriceConverter;
use Src\Catalog\Infrastructure\Persistence\EloquentProductFinder;
use Src\Catalog\Infrastructure\Persistence\EloquentProductPriceFinder;
use Src\Catalog\Infrastructure\Persistence\EloquentProductRepository;
use Src\Catalog\Infrastructure\Persistence\EloquentProductSearcher;
use Src\Catalog\Infrastructure\Persistence\Filters\CurrencyFilter;
use Src\Catalog\Infrastructure\Persistence\Filters\NameFilter;
use Src\Catalog\Infrastructure\Persistence\Filters\PriceRangeFilter;

final class CatalogServiceProvider extends ServiceProvider
{
    /** @var array<class-string, class-string> */
    public array $bindings = [
        ProductRepository::class => EloquentProductRepository::class,
        ProductFinder::class => EloquentProductFinder::class,
        ProductPriceFinder::class => EloquentProductPriceFinder::class,
        PriceConverter::class => CurrencyContextPriceConverter::class,
    ];

    public function register(): void
    {
        // New listing filters are added here, without touching the searcher (O/C).
        $this->app->bind(ProductSearcher::class, fn (): ProductSearcher => new EloquentProductSearcher([
            new NameFilter,
            new CurrencyFilter,
            new PriceRangeFilter,
        ]));
    }
}
