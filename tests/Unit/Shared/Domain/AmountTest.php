<?php

declare(strict_types=1);

use Brick\Math\BigDecimal;
use Src\Shared\Domain\Exceptions\InvalidAmount;
use Src\Shared\Domain\ValueObjects\Amount;

it('normalizes to exactly 2 decimals', function (string $input, string $expected): void {
    expect(Amount::fromString($input)->value())->toBe($expected);
})->with([
    ['19.9', '19.90'],
    ['7', '7.00'],
    ['0', '0.00'],
    [' 10.50 ', '10.50'],
    ['12.300', '12.30'],
    ['9999999999999.99', '9999999999999.99'],
]);

it('rejects invalid amounts', function (string $input, string $reason): void {
    expect(fn () => Amount::fromString($input))->toThrow(InvalidAmount::class, $reason);
})->with([
    ['abc', 'is not a valid amount'],
    ['', 'is not a valid amount'],
    ['-0.01', 'cannot be negative'],
    ['1.001', 'more than 2 decimals'],
    ['10000000000000.00', 'exceeds the maximum'],
]);

it('never goes through floating point (0.1 + 0.2 stays exact)', function (): void {
    $sum = Amount::fromDecimal(Amount::fromString('0.1')->toDecimal()->plus(Amount::fromString('0.2')->toDecimal()));

    expect($sum->value())->toBe('0.30')
        ->and($sum->equals(Amount::fromString('0.3')))->toBeTrue();
});

it('exposes the underlying decimal and a zero value', function (): void {
    expect(Amount::zero()->value())->toBe('0.00')
        ->and(Amount::fromString('5')->toDecimal())->toEqual(BigDecimal::of('5.00'))
        ->and((new InvalidAmount)->errorCode())->toBe('invalid-amount');
});
