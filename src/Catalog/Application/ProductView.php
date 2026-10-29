<?php

declare(strict_types=1);

namespace Src\Catalog\Application;

use DateTimeImmutable;

/**
 * Read model of a product for presentation. Monetary values are decimal strings.
 */
final readonly class ProductView
{
    public function __construct(
        public int $id,
        public string $name,
        public string $description,
        public string $price,
        public CurrencySummary $currency,
        public string $taxCost,
        public string $manufacturingCost,
        public DateTimeImmutable $createdAt,
        public DateTimeImmutable $updatedAt,
    ) {}
}
