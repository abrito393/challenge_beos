<?php

declare(strict_types=1);

use Src\Catalog\Domain\Product;
use Src\Catalog\Domain\ValueObjects\ManufacturingCost;
use Src\Catalog\Domain\ValueObjects\ProductDescription;
use Src\Catalog\Domain\ValueObjects\ProductId;
use Src\Catalog\Domain\ValueObjects\ProductName;
use Src\Catalog\Domain\ValueObjects\TaxCost;
use Src\Shared\Domain\ValueObjects\Money;

function laptop(): Product
{
    return Product::create(
        new ProductName('Laptop'),
        new ProductDescription('14 inch'),
        Money::of('1299.99', 1),
        TaxCost::fromString('207.99'),
        ManufacturingCost::fromString('650'),
    );
}

it('has no identity until persisted', function (): void {
    expect(laptop()->id())->toBeNull();
});

it('is rebuilt from persistence with its identity', function (): void {
    $product = Product::fromPersistence(
        new ProductId(9), new ProductName('Phone'), new ProductDescription('5G'),
        Money::of('10', 2), TaxCost::fromString('1'), ManufacturingCost::fromString('2'),
    );

    expect($product->id()?->value())->toBe(9)
        ->and($product->price()->currencyId()->value())->toBe(2);
});

it('AC-002-12: replaces all its data on update', function (): void {
    $product = laptop();

    $product->update(
        new ProductName('Laptop Pro'),
        new ProductDescription('16 inch'),
        Money::of('1999.00', 2),
        TaxCost::fromString('300'),
        ManufacturingCost::fromString('900'),
    );

    expect($product->name()->value())->toBe('Laptop Pro')
        ->and($product->description()->value())->toBe('16 inch')
        ->and($product->price()->equals(Money::of('1999', 2)))->toBeTrue()
        ->and($product->taxCost()->value())->toBe('300.00')
        ->and($product->manufacturingCost()->value())->toBe('900.00');
});
