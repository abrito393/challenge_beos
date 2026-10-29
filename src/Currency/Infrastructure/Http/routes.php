<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Src\Currency\Infrastructure\Http\Controllers\ListCurrenciesController;
use Src\Shared\Domain\Security\TokenAbility;

Route::middleware(['auth:sanctum', 'throttle:api'])->group(function (): void {
    Route::get('currencies', ListCurrenciesController::class)
        ->middleware('abilities:'.TokenAbility::ProductsRead->value)
        ->name('currencies.index');
});
