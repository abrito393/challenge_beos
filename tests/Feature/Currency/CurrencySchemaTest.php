<?php

declare(strict_types=1);

use Illuminate\Database\QueryException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;

function insertCurrency(array $overrides = []): void
{
    DB::table('currencies')->insert([
        'code' => 'EUR', 'name' => 'Euro', 'symbol' => '€', 'exchange_rate' => '0.92', ...$overrides,
    ]);
}

it('AC-001-06: the database rejects non-positive exchange rates, bypassing the application', function (string $rate): void {
    insertCurrency(['exchange_rate' => $rate]);
})->throws(QueryException::class, 'currencies_exchange_rate_positive')->with(['0', '-1.5']);

it('AC-001-06: the database rejects duplicated codes and names', function (array $duplicate): void {
    insertCurrency();
    insertCurrency($duplicate);
})->throws(UniqueConstraintViolationException::class)->with([
    'same code' => [['name' => 'Euro 2']],
    'same name' => [['code' => 'EUX']],
]);
