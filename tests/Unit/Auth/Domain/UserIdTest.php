<?php

declare(strict_types=1);

use Src\Auth\Domain\Exceptions\InvalidUserId;
use Src\Auth\Domain\ValueObjects\UserId;

it('accepts positive integers', function (): void {
    expect((new UserId(7))->value())->toBe(7);
});

it('rejects zero and negative ids', function (int $value): void {
    new UserId($value);
})->throws(InvalidUserId::class)->with([0, -1]);

it('compares by value', function (): void {
    expect((new UserId(1))->equals(new UserId(1)))->toBeTrue()
        ->and((new UserId(1))->equals(new UserId(2)))->toBeFalse();
});
