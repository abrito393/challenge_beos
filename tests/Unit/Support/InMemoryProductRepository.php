<?php

declare(strict_types=1);

namespace Tests\Unit\Support;

use DateTimeImmutable;
use Src\Catalog\Application\CurrencySummary;
use Src\Catalog\Application\ProductFinder;
use Src\Catalog\Application\ProductPriceFinder;
use Src\Catalog\Application\ProductPriceView;
use Src\Catalog\Application\ProductView;
use Src\Catalog\Domain\Product;
use Src\Catalog\Domain\ProductRepository;
use Src\Catalog\Domain\ValueObjects\ProductId;
use Src\Shared\Domain\ValueObjects\CurrencyId;

/** Write port over an in-memory store; finder() exposes the read port over the same data. */
final class InMemoryProductRepository implements ProductRepository
{
    /** @var array<int, Product> */
    public array $products = [];

    private int $nextId = 1;

    public function find(ProductId $id): ?Product
    {
        return $this->products[$id->value()] ?? null;
    }

    public function save(Product $product): ProductId
    {
        $id = $product->id() ?? new ProductId($this->nextId++);

        $this->products[$id->value()] = Product::fromPersistence(
            $id, $product->name(), $product->description(), $product->price(), $product->taxCost(), $product->manufacturingCost(),
            $product->prices(),
        );

        return $id;
    }

    public function delete(ProductId $id): void
    {
        unset($this->products[$id->value()]);
    }

    public function finder(): ProductFinder
    {
        return new readonly class($this) implements ProductFinder
        {
            public function __construct(private InMemoryProductRepository $store) {}

            public function find(ProductId $id): ?ProductView
            {
                $product = $this->store->find($id);

                if ($product === null) {
                    return null;
                }

                $currencyId = $product->price()->currencyId()->value();
                $now = new DateTimeImmutable('2026-10-29T12:00:00Z');

                return new ProductView(
                    $id->value(),
                    $product->name()->value(),
                    $product->description()->value(),
                    $product->price()->amount()->value(),
                    new CurrencySummary($currencyId, 'C'.$currencyId, 'Currency '.$currencyId, '$'),
                    $product->taxCost()->value(),
                    $product->manufacturingCost()->value(),
                    $now,
                    $now,
                );
            }
        };
    }

    public function priceFinder(): ProductPriceFinder
    {
        return new readonly class($this) implements ProductPriceFinder
        {
            public function __construct(private InMemoryProductRepository $store) {}

            public function forProduct(ProductId $productId): array
            {
                $product = $this->store->find($productId);
                $views = [];

                foreach ($product?->prices() ?? [] as $index => $price) {
                    $currencyId = $price->price()->currencyId()->value();
                    $now = new DateTimeImmutable('2026-10-29T12:00:00Z');
                    $views[] = new ProductPriceView(
                        $index + 1,
                        $productId->value(),
                        $price->price()->amount()->value(),
                        new CurrencySummary($currencyId, 'C'.$currencyId, 'Currency '.$currencyId, '$'),
                        $now,
                        $now,
                    );
                }

                return $views;
            }

            public function find(ProductId $productId, CurrencyId $currencyId): ?ProductPriceView
            {
                foreach ($this->forProduct($productId) as $view) {
                    if ($view->currency->id === $currencyId->value()) {
                        return $view;
                    }
                }

                return null;
            }
        };
    }
}
