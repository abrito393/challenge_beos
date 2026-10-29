<?php

declare(strict_types=1);

use App\Providers\AppServiceProvider;
use Src\Auth\Infrastructure\AuthServiceProvider;
use Src\Currency\Infrastructure\CurrencyServiceProvider;
use Src\Shared\Infrastructure\SharedServiceProvider;

return [
    AppServiceProvider::class,
    SharedServiceProvider::class,
    AuthServiceProvider::class,
    CurrencyServiceProvider::class,
];
