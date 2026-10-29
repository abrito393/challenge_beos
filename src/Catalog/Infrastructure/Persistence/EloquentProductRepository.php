<?php

declare(strict_types=1);

namespace Src\Catalog\Infrastructure\Persistence;

use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\UniqueConstraintViolationException;
use Src\Catalog\Domain\Exceptions\PriceAlreadyExists;
use Src\Catalog\Domain\Product;
use Src\Catalog\Domain\ProductPrice;
use Src\Catalog\Domain\ProductRepository;
use Src\Catalog\Domain\ValueObjects\ManufacturingCost;
use Src\Catalog\Domain\ValueObjects\ProductDescription;
use Src\Catalog\Domain\ValueObjects\ProductId;
use Src\Catalog\Domain\ValueObjects\ProductName;
use Src\Catalog\Domain\ValueObjects\ProductPriceId;
use Src\Catalog\Domain\ValueObjects\TaxCost;
use Src\Shared\Domain\ValueObjects\Money;

final readonly class EloquentProductRepository implements ProductRepository
{
    public function __construct(private ConnectionInterface $db) {}

    public function find(ProductId $id): ?Product
    {
        $model = EloquentProduct::query()->with('prices')->find($id->value());

        return $model === null ? null : self::toDomain($model);
    }

    /**
     * Persists the whole aggregate (product + new prices) atomically.
     *
     * @throws PriceAlreadyExists when a concurrent request stored the same currency first (AC-003-09)
     */
    public function save(Product $product): ProductId
    {
        /** @var ProductId $id */
        $id = $this->db->transaction(function () use ($product): ProductId {
            $model = $product->id() === null
                ? new EloquentProduct
                : EloquentProduct::query()->findOrFail($product->id()->value());

            $model->fill([
                'name' => $product->name()->value(),
                'description' => $product->description()->value(),
                'price' => $product->price()->amount()->value(),
                'currency_id' => $product->price()->currencyId()->value(),
                'tax_cost' => $product->taxCost()->value(),
                'manufacturing_cost' => $product->manufacturingCost()->value(),
            ])->save();

            foreach ($product->prices() as $price) {
                if ($price->id() === null) {
                    $this->insertPrice($model, $price);
                }
            }

            return new ProductId($model->id);
        });

        return $id;
    }

    public function delete(ProductId $id): void
    {
        EloquentProduct::query()->whereKey($id->value())->delete();
    }

    private function insertPrice(EloquentProduct $model, ProductPrice $price): void
    {
        try {
            $model->prices()->create([
                'currency_id' => $price->price()->currencyId()->value(),
                'price' => $price->price()->amount()->value(),
            ]);
        } catch (UniqueConstraintViolationException) {
            // The domain check passed on a stale aggregate: same answer as the regular duplicate.
            throw PriceAlreadyExists::forCurrency($price->price()->currencyId()->value());
        }
    }

    private static function toDomain(EloquentProduct $model): Product
    {
        return Product::fromPersistence(
            new ProductId($model->id),
            new ProductName($model->name),
            new ProductDescription($model->description),
            Money::of($model->price, $model->currency_id),
            TaxCost::fromString($model->tax_cost),
            ManufacturingCost::fromString($model->manufacturing_cost),
            array_values($model->prices->map(fn (EloquentProductPrice $price): ProductPrice => new ProductPrice(
                new ProductPriceId($price->id),
                Money::of($price->price, $price->currency_id),
            ))->all()),
        );
    }
}
