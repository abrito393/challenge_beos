<?php

declare(strict_types=1);

namespace Src\Shared\Domain\ValueObjects;

use Brick\Math\BigDecimal;
use Brick\Math\Exception\MathException;
use Src\Shared\Domain\Exceptions\InvalidAmount;

/**
 * Non-negative monetary quantity with exactly 2 decimals, backed by an
 * arbitrary-precision decimal — never a float (ADR-0003).
 */
final readonly class Amount
{
    public const int SCALE = 2;

    /** Fits DECIMAL(15,2): up to 13 integer digits. */
    private const string MAX = '9999999999999.99';

    private function __construct(private BigDecimal $value) {}

    public static function fromString(string $value): self
    {
        try {
            $decimal = BigDecimal::of(trim($value));
        } catch (MathException) {
            throw InvalidAmount::notNumeric($value);
        }

        return self::fromDecimal($decimal);
    }

    public static function fromDecimal(BigDecimal $decimal): self
    {
        if ($decimal->isNegative()) {
            throw InvalidAmount::negative((string) $decimal);
        }

        if ($decimal->strippedOfTrailingZeros()->getScale() > self::SCALE) {
            throw InvalidAmount::tooPrecise((string) $decimal);
        }

        if ($decimal->isGreaterThan(self::MAX)) {
            throw InvalidAmount::tooLarge((string) $decimal);
        }

        return new self($decimal->toScale(self::SCALE));
    }

    public static function zero(): self
    {
        return new self(BigDecimal::zero()->toScale(self::SCALE));
    }

    public function toDecimal(): BigDecimal
    {
        return $this->value;
    }

    /** Canonical string representation, e.g. "19.90". */
    public function value(): string
    {
        return (string) $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value->isEqualTo($other->value);
    }
}
