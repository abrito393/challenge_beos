<?php

declare(strict_types=1);

namespace Src\Auth\Domain;

interface PasswordHasher
{
    /**
     * Implementations must spend the same time when $hash is null (unknown user)
     * as when it is a real hash, so response timing cannot reveal which emails exist.
     */
    public function verify(string $plainPassword, ?string $hash): bool;
}
