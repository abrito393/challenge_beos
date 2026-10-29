<?php

declare(strict_types=1);

use Src\Currency\Domain\CurrencyConverter;
use Src\Shared\Domain\ValueObjects\Amount;
use Tests\Unit\Support\InMemoryCurrencyRepository as Currencies;

beforeEach(function (): void {
    $this->usd = Currencies::currency(1, 'USD', '1');
    $this->eur = Currencies::currency(2, 'EUR', '0.92');
    $this->mxn = Currencies::currency(3, 'MXN', '18.50');
    $this->converter = new CurrencyConverter;
});

it('AC-001-03: converts from the reference currency', function (): void {
    expect($this->converter->convert(Amount::fromString('100.00'), $this->usd, $this->eur)->value())->toBe('92.00');
});

it('AC-001-04: converts between two non-reference currencies without precision loss', function (): void {
    expect($this->converter->convert(Amount::fromString('92.00'), $this->eur, $this->mxn)->value())->toBe('1850.00');
});

it('AC-001-05: returns the same amount for the same currency', function (): void {
    $amount = Amount::fromString('42.42');

    expect($this->converter->convert($amount, $this->eur, $this->eur))->toBe($amount);
});

it('rounds HALF_UP only once, at the end', function (): void {
    // 10 EUR -> USD = 10 / 0.92 = 10.869565... -> 10.87
    expect($this->converter->convert(Amount::fromString('10.00'), $this->eur, $this->usd)->value())->toBe('10.87')
        // 0.01 USD -> EUR = 0.0092 -> 0.01 (half up), never 0.00
        ->and($this->converter->convert(Amount::fromString('0.01'), $this->usd, $this->eur)->value())->toBe('0.01')
        // 0.05 EUR -> USD = 0.0543478... -> 0.05
        ->and($this->converter->convert(Amount::fromString('0.05'), $this->eur, $this->usd)->value())->toBe('0.05');
});
