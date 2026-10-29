<?php

declare(strict_types=1);

use Src\Auth\Domain\Exceptions\InvalidEmail;
use Src\Auth\Domain\ValueObjects\Email;

it('normalizes the email to trimmed lowercase', function (): void {
    expect((new Email('  Admin@Example.COM '))->value())->toBe('admin@example.com');
});

it('rejects invalid emails', function (string $value): void {
    new Email($value);
})->throws(InvalidEmail::class)->with(['', 'not-an-email', 'a@', str_repeat('a', 250).'@x.com']);

it('compares by value', function (): void {
    expect((new Email('a@b.com'))->equals(new Email('A@B.com')))->toBeTrue();
});
