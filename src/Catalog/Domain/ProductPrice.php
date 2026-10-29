<?php

declare(strict_types=1);

namespace Src\Catalog\Domain;

use Src\Catalog\Domain\ValueObjects\ProductPriceId;
use Src\Shared\Domain\ValueObjects\Money;

/**
 * Price of a product in a currency other than its base one. Part of the
 * Product aggregate: only created through Product::addPriceIn().
 */
final readonly class ProductPrice
{
    public function __construct(
        private ?ProductPriceId $id,
        private Money $price,
    ) {}

    /** Null until persisted. */
    public function id(): ?ProductPriceId
    {
        return $this->id;
    }

    public function price(): Money
    {
        return $this->price;
    }
}
