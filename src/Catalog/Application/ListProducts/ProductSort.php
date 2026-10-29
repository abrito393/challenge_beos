<?php

declare(strict_types=1);

namespace Src\Catalog\Application\ListProducts;

/** Whitelisted sort options; a leading "-" means descending (AC-002-07). */
enum ProductSort: string
{
    case NameAsc = 'name';
    case NameDesc = '-name';
    case PriceAsc = 'price';
    case PriceDesc = '-price';
    case CreatedAtAsc = 'created_at';
    case CreatedAtDesc = '-created_at';

    public const self DEFAULT = self::CreatedAtDesc;

    public function field(): string
    {
        return ltrim($this->value, '-');
    }

    public function isDescending(): bool
    {
        return str_starts_with($this->value, '-');
    }

    /** @return list<string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
