<?php

declare(strict_types=1);

namespace Src\Catalog\Infrastructure\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Src\Catalog\Application\CreateProduct\CreateProductUseCase;
use Src\Catalog\Infrastructure\Http\Requests\CreateProductRequest;
use Src\Catalog\Infrastructure\Http\Resources\ProductResource;
use Symfony\Component\HttpFoundation\Response;

final class CreateProductController
{
    /**
     * Create a product.
     *
     * Monetary fields accept up to 2 decimals; sending them as strings (e.g. "19.99") is recommended.
     */
    public function __invoke(CreateProductRequest $request, CreateProductUseCase $useCase): JsonResponse
    {
        $product = $useCase->execute($request->toCommand());

        return ProductResource::make($product)
            ->response()
            ->setStatusCode(Response::HTTP_CREATED)
            ->header('Location', route('products.show', ['product' => $product->id]));
    }
}
