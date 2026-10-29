<?php

declare(strict_types=1);

namespace Src\Currency\Infrastructure;

use Illuminate\Support\ServiceProvider;
use Src\Currency\Domain\CurrencyRepository;
use Src\Currency\Infrastructure\Persistence\EloquentCurrencyRepository;

final class CurrencyServiceProvider extends ServiceProvider
{
    /** @var array<class-string, class-string> */
    public array $bindings = [
        CurrencyRepository::class => EloquentCurrencyRepository::class,
    ];
}
