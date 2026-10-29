<?php

declare(strict_types=1);

namespace Src\Currency\Domain;

use Src\Shared\Domain\ValueObjects\CurrencyId;

interface CurrencyRepository
{
    public function find(CurrencyId $id): ?Currency;

    /** @return list<Currency> ordered by code */
    public function all(): array;
}
