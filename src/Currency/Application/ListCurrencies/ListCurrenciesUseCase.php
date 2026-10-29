<?php

declare(strict_types=1);

namespace Src\Currency\Application\ListCurrencies;

use Src\Currency\Domain\Currency;
use Src\Currency\Domain\CurrencyRepository;

final readonly class ListCurrenciesUseCase
{
    public function __construct(private CurrencyRepository $currencies) {}

    /** @return list<Currency> */
    public function execute(): array
    {
        return $this->currencies->all();
    }
}
