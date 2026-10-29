<?php

declare(strict_types=1);

namespace Src\Catalog\Application\DeleteProduct;

use Src\Catalog\Domain\Exceptions\ProductNotFound;
use Src\Catalog\Domain\ProductRepository;
use Src\Catalog\Domain\ValueObjects\ProductId;

final readonly class DeleteProductUseCase
{
    public function __construct(private ProductRepository $products) {}

    /**
     * Soft delete: the record is kept for history (AC-002-14).
     *
     * @throws ProductNotFound when the product does not exist or was already deleted (AC-002-15)
     */
    public function execute(DeleteProductCommand $command): void
    {
        $id = new ProductId($command->productId);

        if ($this->products->find($id) === null) {
            throw ProductNotFound::withId($command->productId);
        }

        $this->products->delete($id);
    }
}
