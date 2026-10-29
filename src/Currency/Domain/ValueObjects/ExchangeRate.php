<?php

declare(strict_types=1);

namespace Src\Currency\Domain\ValueObjects;

use Brick\Math\BigDecimal;
use Brick\Math\Exception\MathException;
use Src\Currency\Domain\Exceptions\InvalidExchangeRate;

/**
 * Units of this currency per 1 unit of the reference currency (ADR-0004).
 */
final readonly class ExchangeRate
{
    public const int SCALE = 8;

    /** Fits DECIMAL(18,8): up to 10 integer digits. */
    private const string MAX = '9999999999.99999999';

    private function __construct(private BigDecimal $value) {}

    public static function fromString(string $value): self
    {
        try {
            $decimal = BigDecimal::of(trim($value));
        } catch (MathException) {
            throw InvalidExchangeRate::because($value);
        }

        if (! $decimal->isPositive()
            || $decimal->strippedOfTrailingZeros()->getScale() > self::SCALE
            || $decimal->isGreaterThan(self::MAX)) {
            throw InvalidExchangeRate::because($value);
        }

        return new self($decimal->toScale(self::SCALE));
    }

    public function toDecimal(): BigDecimal
    {
        return $this->value;
    }

    public function value(): string
    {
        return (string) $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value->isEqualTo($other->value);
    }
}
