<?php

declare(strict_types=1);

namespace Src\Currency\Application\ConvertAmount;

final readonly class ConvertAmountCommand
{
    public function __construct(
        public string $amount,
        public int $fromCurrencyId,
        public int $toCurrencyId,
    ) {}
}
