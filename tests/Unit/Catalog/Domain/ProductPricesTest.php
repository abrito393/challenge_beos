<?php

declare(strict_types=1);

use Src\Catalog\Domain\Exceptions\PriceAlreadyExists;
use Src\Catalog\Domain\Exceptions\PriceInBaseCurrency;
use Src\Catalog\Domain\Product;
use Src\Catalog\Domain\ProductPrice;
use Src\Catalog\Domain\ValueObjects\ManufacturingCost;
use Src\Catalog\Domain\ValueObjects\ProductDescription;
use Src\Catalog\Domain\ValueObjects\ProductId;
use Src\Catalog\Domain\ValueObjects\ProductName;
use Src\Catalog\Domain\ValueObjects\ProductPriceId;
use Src\Catalog\Domain\ValueObjects\TaxCost;
use Src\Shared\Domain\ValueObjects\Money;

const USD = 1;
const EUR = 2;

function productInUsd(array $prices = []): Product
{
    return Product::fromPersistence(
        new ProductId(1), new ProductName('Laptop'), new ProductDescription('14 inch'),
        Money::of('100.00', USD), TaxCost::fromString('0'), ManufacturingCost::fromString('0'), $prices,
    );
}

it('AC-003-04: adds a price in another currency', function (): void {
    $product = productInUsd();

    $price = $product->addPriceIn(Money::of('89.90', EUR));

    expect($price->id())->toBeNull()
        ->and($price->price()->equals(Money::of('89.90', EUR)))->toBeTrue()
        ->and($product->prices())->toBe([$price]);
});

it('AC-003-06: rejects a second price in the same currency (RN-003-1)', function (): void {
    $product = productInUsd([new ProductPrice(new ProductPriceId(7), Money::of('90', EUR))]);

    expect(fn () => $product->addPriceIn(Money::of('95', EUR)))
        ->toThrow(PriceAlreadyExists::class, 'The product already has a price in currency 2.')
        ->and($product->prices())->toHaveCount(1);
});

it('AC-003-07: rejects a price in the base currency (RN-003-2)', function (): void {
    productInUsd()->addPriceIn(Money::of('100', USD));
})->throws(PriceInBaseCurrency::class);

it('exposes stable error codes for price rules', function (): void {
    expect(PriceAlreadyExists::forCurrency(1)->errorCode())->toBe('price-already-exists')
        ->and(PriceInBaseCurrency::forCurrency(1)->errorCode())->toBe('price-in-base-currency')
        ->and((new ProductPriceId(3))->equals(new ProductPriceId(3)))->toBeTrue()
        ->and((new ProductPriceId(3))->value())->toBe(3);
});
