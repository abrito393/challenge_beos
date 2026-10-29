<?php

declare(strict_types=1);

use Src\Currency\Domain\Exceptions\InvalidCurrencyCode;
use Src\Currency\Domain\Exceptions\InvalidCurrencyName;
use Src\Currency\Domain\Exceptions\InvalidCurrencySymbol;
use Src\Currency\Domain\Exceptions\InvalidExchangeRate;
use Src\Currency\Domain\ValueObjects\CurrencyCode;
use Src\Currency\Domain\ValueObjects\CurrencyName;
use Src\Currency\Domain\ValueObjects\CurrencySymbol;
use Src\Currency\Domain\ValueObjects\ExchangeRate;
use Src\Shared\Domain\Exceptions\InvalidCurrencyId;
use Src\Shared\Domain\ValueObjects\CurrencyId;

it('normalizes ISO 4217 codes to uppercase', function (): void {
    expect((new CurrencyCode(' eur '))->value())->toBe('EUR')
        ->and((new CurrencyCode('eur'))->equals(new CurrencyCode('EUR')))->toBeTrue();
});

it('rejects malformed currency codes', function (string $code): void {
    new CurrencyCode($code);
})->throws(InvalidCurrencyCode::class)->with(['', 'EU', 'EURO', 'E1R', '€€€']);

it('stores exchange rates with 8 decimals', function (): void {
    $rate = ExchangeRate::fromString('0.92');

    expect($rate->value())->toBe('0.92000000')
        ->and($rate->equals(ExchangeRate::fromString('0.920')))->toBeTrue();
});

it('AC-001-06: rejects non-positive or over-precise exchange rates', function (string $rate): void {
    ExchangeRate::fromString($rate);
})->throws(InvalidExchangeRate::class)->with(['0', '-1', 'abc', '0.000000001', '10000000000']);

it('validates ids, names and symbols', function (Closure $build, string $exception): void {
    expect($build)->toThrow($exception);
})->with([
    'id zero' => [fn () => new CurrencyId(0), InvalidCurrencyId::class],
    'empty name' => [fn () => new CurrencyName('  '), InvalidCurrencyName::class],
    'long name' => [fn () => new CurrencyName(str_repeat('x', 101)), InvalidCurrencyName::class],
    'empty symbol' => [fn () => new CurrencySymbol(''), InvalidCurrencySymbol::class],
    'long symbol' => [fn () => new CurrencySymbol(str_repeat('$', 11)), InvalidCurrencySymbol::class],
]);

it('compares simple value objects by value', function (): void {
    expect((new CurrencyId(1))->equals(new CurrencyId(1)))->toBeTrue()
        ->and((new CurrencyName('Euro'))->equals(new CurrencyName(' Euro ')))->toBeTrue()
        ->and((new CurrencySymbol('€'))->value())->toBe('€');
});
