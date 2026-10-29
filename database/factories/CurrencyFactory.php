<?php

declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Src\Currency\Infrastructure\Persistence\EloquentCurrency;

/**
 * @extends Factory<EloquentCurrency>
 */
final class CurrencyFactory extends Factory
{
    protected $model = EloquentCurrency::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'code' => strtoupper(fake()->unique()->lexify('???')),
            'name' => fake()->unique()->words(2, true),
            'symbol' => '¤',
            'exchange_rate' => sprintf('%d.%04d', fake()->numberBetween(1, 99), fake()->numberBetween(0, 9999)),
        ];
    }

    public function usd(): static
    {
        return $this->state(['code' => 'USD', 'name' => 'US Dollar', 'symbol' => '$', 'exchange_rate' => '1']);
    }

    public function eur(): static
    {
        return $this->state(['code' => 'EUR', 'name' => 'Euro', 'symbol' => '€', 'exchange_rate' => '0.92']);
    }

    public function mxn(): static
    {
        return $this->state(['code' => 'MXN', 'name' => 'Mexican Peso', 'symbol' => '$', 'exchange_rate' => '18.50']);
    }
}
