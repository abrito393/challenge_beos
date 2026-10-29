<?php

declare(strict_types=1);

namespace Src\Catalog\Infrastructure\Persistence;

use Src\Catalog\Domain\Product;
use Src\Catalog\Domain\ProductRepository;
use Src\Catalog\Domain\ValueObjects\ManufacturingCost;
use Src\Catalog\Domain\ValueObjects\ProductDescription;
use Src\Catalog\Domain\ValueObjects\ProductId;
use Src\Catalog\Domain\ValueObjects\ProductName;
use Src\Catalog\Domain\ValueObjects\TaxCost;
use Src\Shared\Domain\ValueObjects\Money;

final class EloquentProductRepository implements ProductRepository
{
    public function find(ProductId $id): ?Product
    {
        $model = EloquentProduct::query()->find($id->value());

        return $model === null ? null : self::toDomain($model);
    }

    public function save(Product $product): ProductId
    {
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

        return new ProductId($model->id);
    }

    public function delete(ProductId $id): void
    {
        EloquentProduct::query()->whereKey($id->value())->delete();
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
        );
    }
}
