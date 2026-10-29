<?php

declare(strict_types=1);

namespace Src\Auth\Infrastructure\Security;

use Illuminate\Contracts\Hashing\Hasher;
use Src\Auth\Domain\PasswordHasher;

final class LaravelPasswordHasher implements PasswordHasher
{
    private static ?string $dummyHash = null;

    public function __construct(private readonly Hasher $hasher) {}

    public function verify(#[\SensitiveParameter] string $plainPassword, ?string $hash): bool
    {
        // Unknown user: check against a throwaway hash so the request costs the same.
        $hashToCheck = $hash ?? (self::$dummyHash ??= $this->hasher->make('timing-attack-mitigation'));

        return $this->hasher->check($plainPassword, $hashToCheck) && $hash !== null;
    }
}
