<?php

declare(strict_types=1);

use Src\Auth\Domain\Exceptions\InvalidDeviceName;
use Src\Auth\Domain\ValueObjects\DeviceName;

it('trims the device name', function (): void {
    expect((new DeviceName('  postman '))->value())->toBe('postman');
});

it('rejects empty or too long device names', function (string $value): void {
    new DeviceName($value);
})->throws(InvalidDeviceName::class)->with(['', '   ', str_repeat('x', 256)]);

it('compares by value', function (): void {
    expect((new DeviceName('cli'))->equals(new DeviceName(' cli ')))->toBeTrue()
        ->and((new DeviceName('cli'))->equals(new DeviceName('web')))->toBeFalse();
});
