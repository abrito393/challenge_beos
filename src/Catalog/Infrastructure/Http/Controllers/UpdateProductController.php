<?php

declare(strict_types=1);

namespace Src\Catalog\Infrastructure\Http\Controllers;

use Src\Catalog\Application\UpdateProduct\UpdateProductUseCase;
use Src\Catalog\Infrastructure\Http\Requests\UpdateProductRequest;
use Src\Catalog\Infrastructure\Http\Resources\ProductResource;

final class UpdateProductController
{
    /**
     * Update a product.
     *
     * Full replacement (PUT): every field is required.
     */
    public function __invoke(UpdateProductRequest $request, UpdateProductUseCase $useCase, int $product): ProductResource
    {
        return ProductResource::make($useCase->execute($request->toCommand($product)));
    }
}
