<?php

declare(strict_types=1);

namespace Src\Currency\Domain;

use Src\Currency\Domain\ValueObjects\CurrencyCode;
use Src\Currency\Domain\ValueObjects\CurrencyName;
use Src\Currency\Domain\ValueObjects\CurrencySymbol;
use Src\Currency\Domain\ValueObjects\ExchangeRate;
use Src\Shared\Domain\ValueObjects\CurrencyId;

final readonly class Currency
{
    public function __construct(
        private CurrencyId $id,
        private CurrencyCode $code,
        private CurrencyName $name,
        private CurrencySymbol $symbol,
        private ExchangeRate $exchangeRate,
    ) {}

    public function id(): CurrencyId
    {
        return $this->id;
    }

    public function code(): CurrencyCode
    {
        return $this->code;
    }

    public function name(): CurrencyName
    {
        return $this->name;
    }

    public function symbol(): CurrencySymbol
    {
        return $this->symbol;
    }

    public function exchangeRate(): ExchangeRate
    {
        return $this->exchangeRate;
    }
}
