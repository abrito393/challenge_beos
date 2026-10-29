<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Src\Catalog\Infrastructure\Http\Controllers\CreateProductController;
use Src\Catalog\Infrastructure\Http\Controllers\CreateProductPriceController;
use Src\Catalog\Infrastructure\Http\Controllers\DeleteProductController;
use Src\Catalog\Infrastructure\Http\Controllers\GetProductController;
use Src\Catalog\Infrastructure\Http\Controllers\ListProductPricesController;
use Src\Catalog\Infrastructure\Http\Controllers\ListProductsController;
use Src\Catalog\Infrastructure\Http\Controllers\UpdateProductController;
use Src\Shared\Domain\Security\TokenAbility;

$read = 'abilities:'.TokenAbility::ProductsRead->value;
$write = 'abilities:'.TokenAbility::ProductsWrite->value;

Route::middleware(['auth:sanctum', 'throttle:api'])->prefix('products')->name('products.')->group(function () use ($read, $write): void {
    Route::get('/', ListProductsController::class)->middleware($read)->name('index');
    Route::post('/', CreateProductController::class)->middleware($write)->name('store');

    Route::whereNumber('product')->group(function () use ($read, $write): void {
        Route::get('{product}', GetProductController::class)->middleware($read)->name('show');
        Route::put('{product}', UpdateProductController::class)->middleware($write)->name('update');
        Route::delete('{product}', DeleteProductController::class)->middleware($write)->name('destroy');

        Route::get('{product}/prices', ListProductPricesController::class)->middleware($read)->name('prices.index');
        Route::post('{product}/prices', CreateProductPriceController::class)->middleware($write)->name('prices.store');
    });
});
