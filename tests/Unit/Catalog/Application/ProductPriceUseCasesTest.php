<?php

declare(strict_types=1);

use Src\Catalog\Application\CreateProduct\CreateProductCommand;
use Src\Catalog\Application\CreateProduct\CreateProductUseCase;
use Src\Catalog\Application\CreateProductPrice\CreateProductPriceCommand;
use Src\Catalog\Application\CreateProductPrice\CreateProductPriceUseCase;
use Src\Catalog\Application\CreateProductPrice\PriceConverter;
use Src\Catalog\Application\ListProductPrices\ListProductPricesQuery;
use Src\Catalog\Application\ListProductPrices\ListProductPricesUseCase;
use Src\Catalog\Domain\Exceptions\PriceAlreadyExists;
use Src\Catalog\Domain\Exceptions\PriceInBaseCurrency;
use Src\Catalog\Domain\Exceptions\ProductNotFound;
use Src\Shared\Domain\ValueObjects\Amount;
use Src\Shared\Domain\ValueObjects\CurrencyId;
use Src\Shared\Domain\ValueObjects\Money;
use Tests\Unit\Support\InMemoryProductRepository;

beforeEach(function (): void {
    $this->store = new InMemoryProductRepository;

    // Fake rates: USD(1) = 1, EUR(2) = 0.92 — enough to prove the port is used.
    $this->converter = new class implements PriceConverter
    {
        public int $calls = 0;

        public function convert(Money $from, CurrencyId $to): Amount
        {
            $this->calls++;

            return Amount::fromDecimal($from->amount()->toDecimal()->multipliedBy('0.92')->toScale(2));
        }
    };

    $this->productId = (new CreateProductUseCase($this->store, $this->store->finder()))->execute(new CreateProductCommand(
        name: 'Laptop', description: '14 inch', price: '100.00', currencyId: 1, taxCost: '0', manufacturingCost: '0',
    ))->id;

    $this->createPrice = new CreateProductPriceUseCase($this->store, $this->converter, $this->store->priceFinder());
});

it('AC-003-04: stores an explicit price as-is, without converting', function (): void {
    $view = $this->createPrice->execute(new CreateProductPriceCommand($this->productId, 2, '89.90'));

    expect($view->price)->toBe('89.90')
        ->and($view->currency->id)->toBe(2)
        ->and($this->converter->calls)->toBe(0);
});

it('AC-003-05: computes the price through the converter when omitted', function (): void {
    $view = $this->createPrice->execute(new CreateProductPriceCommand($this->productId, 2, null));

    expect($view->price)->toBe('92.00')
        ->and($this->converter->calls)->toBe(1);
});

it('AC-003-06: rejects a duplicated currency', function (): void {
    $this->createPrice->execute(new CreateProductPriceCommand($this->productId, 2, '89.90'));
    $this->createPrice->execute(new CreateProductPriceCommand($this->productId, 2, '95.00'));
})->throws(PriceAlreadyExists::class);

it('AC-003-07: rejects the base currency', function (): void {
    $this->createPrice->execute(new CreateProductPriceCommand($this->productId, 1, '100.00'));
})->throws(PriceInBaseCurrency::class);

it('AC-003-03: fails with ProductNotFound for unknown products', function (): void {
    $this->createPrice->execute(new CreateProductPriceCommand(999, 2, '1.00'));
})->throws(ProductNotFound::class);

it('AC-003-01/02: lists the prices of a product, or none', function (): void {
    $list = new ListProductPricesUseCase($this->store->finder(), $this->store->priceFinder());

    expect($list->execute(new ListProductPricesQuery($this->productId)))->toBe([]);

    $this->createPrice->execute(new CreateProductPriceCommand($this->productId, 2, '89.90'));

    expect($list->execute(new ListProductPricesQuery($this->productId)))->toHaveCount(1)
        ->and(fn () => $list->execute(new ListProductPricesQuery(999)))->toThrow(ProductNotFound::class);
});
