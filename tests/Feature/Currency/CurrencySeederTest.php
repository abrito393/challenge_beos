<?php

declare(strict_types=1);

use Database\Seeders\CurrencySeeder;
use Src\Currency\Infrastructure\Persistence\EloquentCurrency;

it('AC-001-07: seeds the base currencies idempotently', function (): void {
    $this->seed(CurrencySeeder::class);
    $this->seed(CurrencySeeder::class);

    expect(EloquentCurrency::query()->orderBy('code')->pluck('code')->all())
        ->toBe(['COP', 'EUR', 'MXN', 'USD', 'VES'])
        ->and(EloquentCurrency::query()->where('code', 'USD')->value('exchange_rate'))->toBe('1.00000000');
});

it('RN-001-1: fails loudly when the configured reference currency is not seeded with rate 1', function (): void {
    config(['currency.reference' => 'EUR']);

    $this->seed(CurrencySeeder::class);
})->throws(RuntimeException::class, 'Reference currency [EUR] must exist with exchange_rate = 1.');
