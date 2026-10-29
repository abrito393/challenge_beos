<?php

declare(strict_types=1);

namespace Tests\Unit\Support;

use Src\Currency\Domain\Currency;
use Src\Currency\Domain\CurrencyRepository;
use Src\Currency\Domain\ValueObjects\CurrencyCode;
use Src\Currency\Domain\ValueObjects\CurrencyName;
use Src\Currency\Domain\ValueObjects\CurrencySymbol;
use Src\Currency\Domain\ValueObjects\ExchangeRate;
use Src\Shared\Domain\ValueObjects\CurrencyId;

final class InMemoryCurrencyRepository implements CurrencyRepository
{
    /** @var array<int, Currency> */
    private array $currencies = [];

    public static function currency(int $id, string $code, string $rate): Currency
    {
        return new Currency(
            new CurrencyId($id),
            new CurrencyCode($code),
            new CurrencyName($code),
            new CurrencySymbol('$'),
            ExchangeRate::fromString($rate),
        );
    }

    public function add(Currency ...$currencies): self
    {
        foreach ($currencies as $currency) {
            $this->currencies[$currency->id()->value()] = $currency;
        }

        return $this;
    }

    public function find(CurrencyId $id): ?Currency
    {
        return $this->currencies[$id->value()] ?? null;
    }

    public function all(): array
    {
        $all = array_values($this->currencies);
        usort($all, fn (Currency $a, Currency $b): int => $a->code()->value() <=> $b->code()->value());

        return $all;
    }
}
