<?php

declare(strict_types=1);

namespace Src\Currency\Infrastructure\Http\Controllers;

use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Src\Currency\Application\ListCurrencies\ListCurrenciesUseCase;
use Src\Currency\Infrastructure\Http\Resources\CurrencyResource;

final class ListCurrenciesController
{
    /**
     * List currencies.
     *
     * Available currencies with their exchange rate against the reference currency (USD).
     */
    public function __invoke(ListCurrenciesUseCase $useCase): AnonymousResourceCollection
    {
        return CurrencyResource::collection($useCase->execute());
    }
}
