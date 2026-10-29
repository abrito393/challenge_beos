<?php

declare(strict_types=1);

namespace Src\Catalog\Application\CreateProductPrice;

use Src\Shared\Domain\ValueObjects\Amount;
use Src\Shared\Domain\ValueObjects\CurrencyId;
use Src\Shared\Domain\ValueObjects\Money;

/**
 * What Catalog needs from currency conversion, in Catalog's own terms.
 * Implemented by an anti-corruption adapter over the Currency context.
 */
interface PriceConverter
{
    public function convert(Money $from, CurrencyId $to): Amount;
}
