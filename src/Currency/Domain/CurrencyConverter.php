<?php

declare(strict_types=1);

namespace Src\Currency\Domain;

use Brick\Math\RoundingMode;
use Src\Shared\Domain\ValueObjects\Amount;

/**
 * Cross conversion through the reference currency: amount × rate_to / rate_from
 * (RN-001-2). The multiplication is exact; the result is rounded HALF_UP to
 * 2 decimals only once, at the very end.
 */
final class CurrencyConverter
{
    public function convert(Amount $amount, Currency $from, Currency $to): Amount
    {
        if ($from->id()->equals($to->id())) {
            return $amount;
        }

        return Amount::fromDecimal(
            $amount->toDecimal()
                ->multipliedBy($to->exchangeRate()->toDecimal())
                ->dividedBy($from->exchangeRate()->toDecimal(), Amount::SCALE, RoundingMode::HalfUp),
        );
    }
}
