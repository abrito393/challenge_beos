<?php

declare(strict_types=1);

use Src\Auth\Domain\User;
use Src\Auth\Domain\UserRole;
use Src\Auth\Domain\ValueObjects\Email;
use Src\Auth\Domain\ValueObjects\UserId;
use Src\Shared\Domain\Security\TokenAbility;

it('exposes its identity and derives abilities from its role', function (): void {
    $user = new User(new UserId(3), new Email('admin@example.com'), UserRole::Admin, 'hash');

    expect($user->id()->value())->toBe(3)
        ->and($user->email()->value())->toBe('admin@example.com')
        ->and($user->role())->toBe(UserRole::Admin)
        ->and($user->passwordHash())->toBe('hash')
        ->and($user->abilities())->toBe([TokenAbility::ProductsRead, TokenAbility::ProductsWrite]);
});
