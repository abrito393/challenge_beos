<?php

declare(strict_types=1);

namespace Src\Shared\Domain\ValueObjects;

/**
 * An amount expressed in a specific currency.
 */
final readonly class Money
{
    public function __construct(
        private Amount $amount,
        private CurrencyId $currencyId,
    ) {}

    public static function of(string $amount, int $currencyId): self
    {
        return new self(Amount::fromString($amount), new CurrencyId($currencyId));
    }

    public function amount(): Amount
    {
        return $this->amount;
    }

    public function currencyId(): CurrencyId
    {
        return $this->currencyId;
    }

    public function equals(self $other): bool
    {
        return $this->amount->equals($other->amount) && $this->currencyId->equals($other->currencyId);
    }
}
