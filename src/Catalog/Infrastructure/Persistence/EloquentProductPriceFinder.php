<?php

declare(strict_types=1);

namespace Src\Catalog\Infrastructure\Persistence;

use Illuminate\Database\Eloquent\Builder;
use LogicException;
use Src\Catalog\Application\CurrencySummary;
use Src\Catalog\Application\ProductPriceFinder;
use Src\Catalog\Application\ProductPriceView;
use Src\Catalog\Domain\ValueObjects\ProductId;
use Src\Shared\Domain\ValueObjects\CurrencyId;

final class EloquentProductPriceFinder implements ProductPriceFinder
{
    public function forProduct(ProductId $productId): array
    {
        return array_values(array_map(
            self::toView(...),
            self::query()->where('product_prices.product_id', $productId->value())->orderBy('currencies.code')->get()->all(),
        ));
    }

    public function find(ProductId $productId, CurrencyId $currencyId): ?ProductPriceView
    {
        $row = self::query()
            ->where('product_prices.product_id', $productId->value())
            ->where('product_prices.currency_id', $currencyId->value())
            ->first();

        return $row === null ? null : self::toView($row);
    }

    /** @return Builder<EloquentProductPrice> */
    private static function query(): Builder
    {
        return EloquentProductPrice::query()
            ->join('currencies', 'currencies.id', '=', 'product_prices.currency_id')
            ->select([
                'product_prices.*',
                'currencies.code as currency_code',
                'currencies.name as currency_name',
                'currencies.symbol as currency_symbol',
            ]);
    }

    private static function toView(EloquentProductPrice $row): ProductPriceView
    {
        return new ProductPriceView(
            id: $row->id,
            productId: $row->product_id,
            price: $row->price,
            currency: new CurrencySummary(
                $row->currency_id,
                self::joined($row, 'currency_code'),
                self::joined($row, 'currency_name'),
                self::joined($row, 'currency_symbol'),
            ),
            createdAt: $row->created_at->toImmutable(),
            updatedAt: $row->updated_at->toImmutable(),
        );
    }

    private static function joined(EloquentProductPrice $row, string $column): string
    {
        $value = $row->getAttribute($column);

        return is_string($value) ? $value : throw new LogicException("Missing joined column [{$column}].");
    }
}
