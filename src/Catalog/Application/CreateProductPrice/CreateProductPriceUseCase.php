<?php

declare(strict_types=1);

namespace Src\Catalog\Application\CreateProductPrice;

use LogicException;
use Src\Catalog\Application\ProductPriceFinder;
use Src\Catalog\Application\ProductPriceView;
use Src\Catalog\Domain\Exceptions\PriceAlreadyExists;
use Src\Catalog\Domain\Exceptions\PriceInBaseCurrency;
use Src\Catalog\Domain\Exceptions\ProductNotFound;
use Src\Catalog\Domain\ProductRepository;
use Src\Catalog\Domain\ValueObjects\ProductId;
use Src\Shared\Domain\ValueObjects\Amount;
use Src\Shared\Domain\ValueObjects\CurrencyId;
use Src\Shared\Domain\ValueObjects\Money;

final readonly class CreateProductPriceUseCase
{
    public function __construct(
        private ProductRepository $products,
        private PriceConverter $converter,
        private ProductPriceFinder $finder,
    ) {}

    /**
     * @throws ProductNotFound
     * @throws PriceInBaseCurrency
     * @throws PriceAlreadyExists
     */
    public function execute(CreateProductPriceCommand $command): ProductPriceView
    {
        $productId = new ProductId($command->productId);
        $product = $this->products->find($productId) ?? throw ProductNotFound::withId($command->productId);
        $currencyId = new CurrencyId($command->currencyId);

        $amount = $command->price !== null
            ? Amount::fromString($command->price)
            : $this->converter->convert($product->price(), $currencyId);

        $product->addPriceIn(new Money($amount, $currencyId));
        $this->products->save($product);

        return $this->finder->find($productId, $currencyId)
            ?? throw new LogicException('A price that was just stored could not be read back.');
    }
}
