<?php

declare(strict_types=1);

namespace Database\Seeders;

use Brick\Math\BigDecimal;
use Illuminate\Database\Seeder;
use RuntimeException;
use Src\Currency\Infrastructure\Persistence\EloquentCurrency;

/**
 * Reference data: runs in every environment and is idempotent.
 * Rates are illustrative, expressed per 1 USD (ADR-0004).
 */
final class CurrencySeeder extends Seeder
{
    private const array CURRENCIES = [
        ['code' => 'USD', 'name' => 'US Dollar', 'symbol' => '$', 'exchange_rate' => '1.00000000'],
        ['code' => 'EUR', 'name' => 'Euro', 'symbol' => '€', 'exchange_rate' => '0.92000000'],
        ['code' => 'MXN', 'name' => 'Mexican Peso', 'symbol' => '$', 'exchange_rate' => '18.50000000'],
        ['code' => 'COP', 'name' => 'Colombian Peso', 'symbol' => '$', 'exchange_rate' => '4100.00000000'],
        ['code' => 'VES', 'name' => 'Venezuelan Bolívar', 'symbol' => 'Bs.', 'exchange_rate' => '36.50000000'],
    ];

    public function run(): void
    {
        foreach (self::CURRENCIES as $currency) {
            EloquentCurrency::query()->updateOrCreate(['code' => $currency['code']], $currency);
        }

        $this->assertReferenceCurrency();
    }

    /** RN-001-1: the configured reference currency must exist with rate 1. */
    private function assertReferenceCurrency(): void
    {
        $reference = config()->string('currency.reference');
        $rate = EloquentCurrency::query()->where('code', $reference)->value('exchange_rate');

        if (! is_string($rate) || ! BigDecimal::of($rate)->isEqualTo(1)) {
            throw new RuntimeException("Reference currency [{$reference}] must exist with exchange_rate = 1.");
        }
    }
}
