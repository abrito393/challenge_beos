<?php

declare(strict_types=1);

namespace Src\Catalog\Application\UpdateProduct;

use Src\Catalog\Application\ProductFinder;
use Src\Catalog\Application\ProductView;
use Src\Catalog\Domain\Exceptions\ProductNotFound;
use Src\Catalog\Domain\ProductRepository;
use Src\Catalog\Domain\ValueObjects\ManufacturingCost;
use Src\Catalog\Domain\ValueObjects\ProductDescription;
use Src\Catalog\Domain\ValueObjects\ProductId;
use Src\Catalog\Domain\ValueObjects\ProductName;
use Src\Catalog\Domain\ValueObjects\TaxCost;
use Src\Shared\Domain\ValueObjects\Money;

final readonly class UpdateProductUseCase
{
    public function __construct(
        private ProductRepository $products,
        private ProductFinder $finder,
    ) {}

    /** @throws ProductNotFound */
    public function execute(UpdateProductCommand $command): ProductView
    {
        $id = new ProductId($command->productId);
        $product = $this->products->find($id) ?? throw ProductNotFound::withId($command->productId);

        $product->update(
            new ProductName($command->name),
            new ProductDescription($command->description),
            Money::of($command->price, $command->currencyId),
            TaxCost::fromString($command->taxCost),
            ManufacturingCost::fromString($command->manufacturingCost),
        );

        $this->products->save($product);

        return $this->finder->find($id) ?? throw ProductNotFound::withId($command->productId);
    }
}
