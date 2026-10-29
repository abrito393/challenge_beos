<?php

declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Src\Catalog\Infrastructure\Persistence\EloquentProduct;
use Src\Currency\Infrastructure\Persistence\EloquentCurrency;

/**
 * @extends Factory<EloquentProduct>
 */
final class ProductFactory extends Factory
{
    protected $model = EloquentProduct::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'name' => rtrim(fake()->sentence(3), '.'),
            'description' => fake()->sentence(),
            'price' => self::money(10, 2000),
            'currency_id' => EloquentCurrency::factory(),
            'tax_cost' => self::money(1, 300),
            'manufacturing_cost' => self::money(1, 1000),
        ];
    }

    /** Monetary strings built without floats (ADR-0003). */
    private static function money(int $min, int $max): string
    {
        return sprintf('%d.%02d', fake()->numberBetween($min, $max), fake()->numberBetween(0, 99));
    }
}
