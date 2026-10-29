<?php

declare(strict_types=1);

use Src\Catalog\Domain\Exceptions\InvalidProductDescription;
use Src\Catalog\Domain\Exceptions\InvalidProductId;
use Src\Catalog\Domain\Exceptions\InvalidProductName;
use Src\Catalog\Domain\Exceptions\ProductNotFound;
use Src\Catalog\Domain\ValueObjects\ManufacturingCost;
use Src\Catalog\Domain\ValueObjects\ProductDescription;
use Src\Catalog\Domain\ValueObjects\ProductId;
use Src\Catalog\Domain\ValueObjects\ProductName;
use Src\Catalog\Domain\ValueObjects\TaxCost;
use Src\Shared\Domain\Exceptions\InvalidAmount;

it('trims names and descriptions', function (): void {
    expect((new ProductName('  Laptop  '))->value())->toBe('Laptop')
        ->and((new ProductDescription(" A laptop\n"))->value())->toBe('A laptop')
        ->and((new ProductName('Laptop'))->equals(new ProductName(' Laptop')))->toBeTrue()
        ->and((new ProductDescription('x'))->equals(new ProductDescription('y')))->toBeFalse();
});

it('AC-002-02: rejects invalid product values', function (Closure $build, string $exception): void {
    expect($build)->toThrow($exception);
})->with([
    'id zero' => [fn () => new ProductId(0), InvalidProductId::class],
    'empty name' => [fn () => new ProductName('   '), InvalidProductName::class],
    'long name' => [fn () => new ProductName(str_repeat('a', 256)), InvalidProductName::class],
    'empty description' => [fn () => new ProductDescription(''), InvalidProductDescription::class],
    'long description' => [fn () => new ProductDescription(str_repeat('a', 2001)), InvalidProductDescription::class],
    'negative tax cost' => [fn () => TaxCost::fromString('-1'), InvalidAmount::class],
    'over-precise manufacturing cost' => [fn () => ManufacturingCost::fromString('1.234'), InvalidAmount::class],
]);

it('represents costs as exact amounts', function (): void {
    expect(TaxCost::fromString('207.9')->value())->toBe('207.90')
        ->and(ManufacturingCost::fromString('650')->amount()->value())->toBe('650.00')
        ->and(TaxCost::fromString('1')->equals(TaxCost::fromString('1.00')))->toBeTrue()
        ->and(ManufacturingCost::fromString('1')->equals(ManufacturingCost::fromString('2')))->toBeFalse()
        ->and((new ProductId(5))->equals(new ProductId(5)))->toBeTrue();
});

it('exposes stable error codes', function (): void {
    expect(ProductNotFound::withId(1)->errorCode())->toBe('product-not-found')
        ->and(InvalidProductId::because('0')->errorCode())->toBe('invalid-product-id')
        ->and(InvalidProductName::because('')->errorCode())->toBe('invalid-product-name')
        ->and(InvalidProductDescription::because('')->errorCode())->toBe('invalid-product-description');
});
