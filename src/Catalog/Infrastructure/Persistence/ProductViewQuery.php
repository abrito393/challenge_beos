<?php

declare(strict_types=1);

namespace Src\Catalog\Infrastructure\Persistence;

use Illuminate\Database\Eloquent\Builder;
use LogicException;
use Src\Catalog\Application\CurrencySummary;
use Src\Catalog\Application\ProductView;

/**
 * Read-side projection shared by the finder and the searcher: a single query
 * joining the currency, so listings never trigger N+1 queries (AC-002-09).
 * It joins by table name on purpose, without importing the Currency context.
 */
final class ProductViewQuery
{
    /** @return Builder<EloquentProduct> */
    public static function base(): Builder
    {
        return EloquentProduct::query()
            ->join('currencies', 'currencies.id', '=', 'products.currency_id')
            ->select([
                'products.*',
                'currencies.code as currency_code',
                'currencies.name as currency_name',
                'currencies.symbol as currency_symbol',
            ]);
    }

    public static function toView(EloquentProduct $row): ProductView
    {
        return new ProductView(
            id: $row->id,
            name: $row->name,
            description: $row->description,
            price: $row->price,
            currency: new CurrencySummary(
                $row->currency_id,
                self::joined($row, 'currency_code'),
                self::joined($row, 'currency_name'),
                self::joined($row, 'currency_symbol'),
            ),
            taxCost: $row->tax_cost,
            manufacturingCost: $row->manufacturing_cost,
            createdAt: $row->created_at->toImmutable(),
            updatedAt: $row->updated_at->toImmutable(),
        );
    }

    private static function joined(EloquentProduct $row, string $column): string
    {
        $value = $row->getAttribute($column);

        return is_string($value) ? $value : throw new LogicException("Missing joined column [{$column}].");
    }
}
