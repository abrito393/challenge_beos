<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Src\Catalog\Infrastructure\Persistence\EloquentProduct;
use Src\Currency\Infrastructure\Persistence\EloquentCurrency;

/**
 * Demo catalog for reviewers. Never runs in production (see DatabaseSeeder).
 */
final class ProductSeeder extends Seeder
{
    private const array PRODUCTS = [
        ['Laptop Pro 14', 'Laptop de 14 pulgadas, 16 GB RAM', '1299.99', 'USD', '207.99', '650.00'],
        ['Monitor 27" 4K', 'Monitor IPS 4K de 27 pulgadas', '389.90', 'USD', '62.38', '180.00'],
        ['Teclado mecánico', 'Switches táctiles, layout ES', '89.00', 'EUR', '18.69', '35.50'],
        ['Silla ergonómica', 'Soporte lumbar ajustable', '5499.00', 'MXN', '879.84', '2100.00'],
        ['Audífonos inalámbricos', 'Cancelación activa de ruido', '650000.00', 'COP', '123500.00', '210000.00'],
    ];

    public function run(): void
    {
        $currencies = EloquentCurrency::query()->pluck('id', 'code');

        foreach (self::PRODUCTS as [$name, $description, $price, $code, $tax, $manufacturing]) {
            EloquentProduct::query()->updateOrCreate(['name' => $name], [
                'description' => $description,
                'price' => $price,
                'currency_id' => $currencies[$code],
                'tax_cost' => $tax,
                'manufacturing_cost' => $manufacturing,
            ]);
        }
    }
}
