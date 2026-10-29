<?php

declare(strict_types=1);

namespace Src\Catalog\Infrastructure\Persistence;

use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property string $description
 * @property string $price
 * @property int $currency_id
 * @property string $tax_cost
 * @property string $manufacturing_cost
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon|null $deleted_at
 */
final class EloquentProduct extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory, SoftDeletes;

    protected $table = 'products';

    /** @var list<string> */
    protected $fillable = ['name', 'description', 'price', 'currency_id', 'tax_cost', 'manufacturing_cost'];

    protected static function newFactory(): ProductFactory
    {
        return ProductFactory::new();
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        // decimal:N casts return strings, never floats (ADR-0003).
        return [
            'price' => 'decimal:2',
            'currency_id' => 'integer',
            'tax_cost' => 'decimal:2',
            'manufacturing_cost' => 'decimal:2',
        ];
    }
}
