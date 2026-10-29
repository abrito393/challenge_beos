<?php

declare(strict_types=1);

namespace Src\Catalog\Infrastructure\Persistence;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $product_id
 * @property int $currency_id
 * @property string $price
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
final class EloquentProductPrice extends Model
{
    protected $table = 'product_prices';

    /** @var list<string> */
    protected $fillable = ['product_id', 'currency_id', 'price'];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'product_id' => 'integer',
            'currency_id' => 'integer',
            'price' => 'decimal:2',
        ];
    }
}
