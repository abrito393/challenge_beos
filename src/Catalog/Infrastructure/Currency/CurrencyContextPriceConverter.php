<?php

declare(strict_types=1);

namespace Src\Catalog\Infrastructure\Currency;

use Src\Catalog\Application\CreateProductPrice\PriceConverter;
use Src\Currency\Application\ConvertAmount\ConvertAmountCommand;
use Src\Currency\Application\ConvertAmount\ConvertAmountUseCase;
use Src\Shared\Domain\ValueObjects\Amount;
use Src\Shared\Domain\ValueObjects\CurrencyId;
use Src\Shared\Domain\ValueObjects\Money;

/**
 * Anti-corruption layer: the only place where Catalog talks to the Currency
 * context, and only through its public application API (ADR-0009).
 */
final readonly class CurrencyContextPriceConverter implements PriceConverter
{
    public function __construct(private ConvertAmountUseCase $convertAmount) {}

    public function convert(Money $from, CurrencyId $to): Amount
    {
        return Amount::fromString($this->convertAmount->execute(new ConvertAmountCommand(
            amount: $from->amount()->value(),
            fromCurrencyId: $from->currencyId()->value(),
            toCurrencyId: $to->value(),
        )));
    }
}
