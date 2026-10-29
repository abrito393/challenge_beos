<?php

declare(strict_types=1);

namespace Src\Catalog\Application;

use DateTimeImmutable;

final readonly class ProductPriceView
{
    public function __construct(
        public int $id,
        public int $productId,
        public string $price,
        public CurrencySummary $currency,
        public DateTimeImmutable $createdAt,
        public DateTimeImmutable $updatedAt,
    ) {}
}
