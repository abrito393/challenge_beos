<?php

declare(strict_types=1);

use Src\Shared\Domain\Exceptions\InvalidCurrencyId;
use Src\Shared\Domain\ValueObjects\Money;

it('combines an amount with a currency', function (): void {
    $money = Money::of('19.9', 2);

    expect($money->amount()->value())->toBe('19.90')
        ->and($money->currencyId()->value())->toBe(2)
        ->and($money->equals(Money::of('19.90', 2)))->toBeTrue()
        ->and($money->equals(Money::of('19.90', 3)))->toBeFalse();
});

it('rejects an invalid currency id', function (): void {
    Money::of('1', 0);
})->throws(InvalidCurrencyId::class);
