<?php

declare(strict_types=1);

use Src\Auth\Domain\UserRole;
use Src\Shared\Domain\Security\TokenAbility;

it('AC-004-06: grants read and write abilities to admins', function (): void {
    expect(UserRole::Admin->abilities())->toBe([TokenAbility::ProductsRead, TokenAbility::ProductsWrite]);
});

it('AC-004-06: grants only read ability to viewers', function (): void {
    expect(UserRole::Viewer->abilities())->toBe([TokenAbility::ProductsRead]);
});
