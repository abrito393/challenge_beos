<?php

declare(strict_types=1);

namespace Src\Catalog\Application;

final readonly class CurrencySummary
{
    public function __construct(
        public int $id,
        public string $code,
        public string $name,
        public string $symbol,
    ) {}
}
