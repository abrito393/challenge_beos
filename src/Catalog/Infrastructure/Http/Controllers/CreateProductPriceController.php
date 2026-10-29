<?php

declare(strict_types=1);

namespace Src\Catalog\Infrastructure\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Src\Catalog\Application\CreateProductPrice\CreateProductPriceUseCase;
use Src\Catalog\Infrastructure\Http\Requests\CreateProductPriceRequest;
use Src\Catalog\Infrastructure\Http\Resources\ProductPriceResource;
use Symfony\Component\HttpFoundation\Response;

final class CreateProductPriceController
{
    /**
     * Register a price in another currency.
     *
     * Send `price` to set it explicitly, or omit it to compute it from the base price and current exchange rates.
     * One price per currency (409 on duplicates); the product's base currency is rejected (422).
     */
    public function __invoke(CreateProductPriceRequest $request, CreateProductPriceUseCase $useCase, int $product): JsonResponse
    {
        return ProductPriceResource::make($useCase->execute($request->toCommand($product)))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }
}
