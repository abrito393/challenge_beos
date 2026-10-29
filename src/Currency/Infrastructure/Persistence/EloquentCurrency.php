<?php

declare(strict_types=1);

namespace Src\Currency\Infrastructure\Persistence;

use Database\Factories\CurrencyFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $code
 * @property string $name
 * @property string $symbol
 * @property string $exchange_rate
 */
final class EloquentCurrency extends Model
{
    /** @use HasFactory<CurrencyFactory> */
    use HasFactory;

    protected $table = 'currencies';

    /** @var list<string> */
    protected $fillable = ['code', 'name', 'symbol', 'exchange_rate'];

    protected static function newFactory(): CurrencyFactory
    {
        return CurrencyFactory::new();
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        // decimal:N casts return strings, never floats (ADR-0003).
        return ['exchange_rate' => 'decimal:8'];
    }
}
