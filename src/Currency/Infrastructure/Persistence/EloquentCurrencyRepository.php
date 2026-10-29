<?php

declare(strict_types=1);

namespace Src\Currency\Infrastructure\Persistence;

use Src\Currency\Domain\Currency;
use Src\Currency\Domain\CurrencyRepository;
use Src\Currency\Domain\ValueObjects\CurrencyCode;
use Src\Currency\Domain\ValueObjects\CurrencyName;
use Src\Currency\Domain\ValueObjects\CurrencySymbol;
use Src\Currency\Domain\ValueObjects\ExchangeRate;
use Src\Shared\Domain\ValueObjects\CurrencyId;

final class EloquentCurrencyRepository implements CurrencyRepository
{
    public function find(CurrencyId $id): ?Currency
    {
        $model = EloquentCurrency::query()->find($id->value());

        return $model === null ? null : self::toDomain($model);
    }

    public function all(): array
    {
        return array_values(array_map(
            self::toDomain(...),
            EloquentCurrency::query()->orderBy('code')->get()->all(),
        ));
    }

    private static function toDomain(EloquentCurrency $model): Currency
    {
        return new Currency(
            new CurrencyId($model->id),
            new CurrencyCode($model->code),
            new CurrencyName($model->name),
            new CurrencySymbol($model->symbol),
            ExchangeRate::fromString($model->exchange_rate),
        );
    }
}
