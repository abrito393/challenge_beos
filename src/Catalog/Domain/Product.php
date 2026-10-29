<?php

declare(strict_types=1);

namespace Src\Catalog\Domain;

use Src\Catalog\Domain\ValueObjects\ManufacturingCost;
use Src\Catalog\Domain\ValueObjects\ProductDescription;
use Src\Catalog\Domain\ValueObjects\ProductId;
use Src\Catalog\Domain\ValueObjects\ProductName;
use Src\Catalog\Domain\ValueObjects\TaxCost;
use Src\Shared\Domain\ValueObjects\Money;

/**
 * Aggregate root of the Catalog context. Only accepts value objects, so an
 * instance can never hold an invalid name, a negative cost or a float price.
 */
final class Product
{
    private function __construct(
        private readonly ?ProductId $id,
        private ProductName $name,
        private ProductDescription $description,
        private Money $price,
        private TaxCost $taxCost,
        private ManufacturingCost $manufacturingCost,
    ) {}

    public static function create(
        ProductName $name,
        ProductDescription $description,
        Money $price,
        TaxCost $taxCost,
        ManufacturingCost $manufacturingCost,
    ): self {
        return new self(null, $name, $description, $price, $taxCost, $manufacturingCost);
    }

    public static function fromPersistence(
        ProductId $id,
        ProductName $name,
        ProductDescription $description,
        Money $price,
        TaxCost $taxCost,
        ManufacturingCost $manufacturingCost,
    ): self {
        return new self($id, $name, $description, $price, $taxCost, $manufacturingCost);
    }

    /** Full replacement of the product data (PUT semantics, AC-002-13). */
    public function update(
        ProductName $name,
        ProductDescription $description,
        Money $price,
        TaxCost $taxCost,
        ManufacturingCost $manufacturingCost,
    ): void {
        $this->name = $name;
        $this->description = $description;
        $this->price = $price;
        $this->taxCost = $taxCost;
        $this->manufacturingCost = $manufacturingCost;
    }

    /** Null until the product has been persisted. */
    public function id(): ?ProductId
    {
        return $this->id;
    }

    public function name(): ProductName
    {
        return $this->name;
    }

    public function description(): ProductDescription
    {
        return $this->description;
    }

    public function price(): Money
    {
        return $this->price;
    }

    public function taxCost(): TaxCost
    {
        return $this->taxCost;
    }

    public function manufacturingCost(): ManufacturingCost
    {
        return $this->manufacturingCost;
    }
}
