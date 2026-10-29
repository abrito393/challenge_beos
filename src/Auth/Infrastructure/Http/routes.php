<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Src\Auth\Infrastructure\Http\Controllers\GetAuthenticatedUserController;
use Src\Auth\Infrastructure\Http\Controllers\IssueTokenController;
use Src\Auth\Infrastructure\Http\Controllers\RevokeTokenController;

Route::prefix('auth')->name('auth.')->group(function (): void {
    Route::post('token', IssueTokenController::class)
        ->middleware('throttle:login')
        ->name('token.issue');

    Route::middleware(['auth:sanctum', 'throttle:api'])->group(function (): void {
        Route::delete('token', RevokeTokenController::class)->name('token.revoke');
        Route::get('me', GetAuthenticatedUserController::class)->name('me');
    });
});
