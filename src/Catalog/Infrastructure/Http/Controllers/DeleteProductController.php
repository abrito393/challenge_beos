<?php

declare(strict_types=1);

namespace Src\Catalog\Infrastructure\Http\Controllers;

use Illuminate\Http\Response;
use Src\Catalog\Application\DeleteProduct\DeleteProductCommand;
use Src\Catalog\Application\DeleteProduct\DeleteProductUseCase;

final class DeleteProductController
{
    /**
     * Delete a product.
     *
     * Soft delete: the product disappears from the API but is kept in the database.
     */
    public function __invoke(DeleteProductUseCase $useCase, int $product): Response
    {
        $useCase->execute(new DeleteProductCommand($product));

        return response()->noContent();
    }
}
