<?php

declare(strict_types=1);

use Src\Currency\Application\ConvertAmount\ConvertAmountCommand;
use Src\Currency\Application\ConvertAmount\ConvertAmountUseCase;
use Src\Currency\Application\ListCurrencies\ListCurrenciesUseCase;
use Src\Currency\Domain\Currency;
use Src\Currency\Domain\CurrencyConverter;
use Src\Currency\Domain\Exceptions\CurrencyNotFound;
use Tests\Unit\Support\InMemoryCurrencyRepository as Currencies;

beforeEach(function (): void {
    $this->repository = (new Currencies)->add(
        Currencies::currency(3, 'MXN', '18.50'),
        Currencies::currency(1, 'USD', '1'),
        Currencies::currency(2, 'EUR', '0.92'),
    );
});

it('AC-001-03: converts primitives in, primitives out', function (): void {
    $converted = (new ConvertAmountUseCase($this->repository, new CurrencyConverter))
        ->execute(new ConvertAmountCommand('100', fromCurrencyId: 1, toCurrencyId: 2));

    expect($converted)->toBe('92.00');
});

it('fails with CurrencyNotFound for an unknown currency', function (): void {
    (new ConvertAmountUseCase($this->repository, new CurrencyConverter))
        ->execute(new ConvertAmountCommand('100', fromCurrencyId: 1, toCurrencyId: 99));
})->throws(CurrencyNotFound::class, 'Currency 99 was not found.');

it('lists currencies ordered by code', function (): void {
    $codes = array_map(
        fn (Currency $currency): string => $currency->code()->value(),
        (new ListCurrenciesUseCase($this->repository))->execute(),
    );

    expect($codes)->toBe(['EUR', 'MXN', 'USD']);
});

it('exposes a stable error code for missing currencies', function (): void {
    expect(CurrencyNotFound::withId(1)->errorCode())->toBe('currency-not-found');
});
