<?php

declare(strict_types=1);

namespace Src\Auth\Domain;

use Src\Auth\Domain\ValueObjects\Email;
use Src\Auth\Domain\ValueObjects\UserId;
use Src\Shared\Domain\Security\TokenAbility;

final readonly class User
{
    public function __construct(
        private UserId $id,
        private Email $email,
        private UserRole $role,
        private string $passwordHash,
    ) {}

    public function id(): UserId
    {
        return $this->id;
    }

    public function email(): Email
    {
        return $this->email;
    }

    public function role(): UserRole
    {
        return $this->role;
    }

    public function passwordHash(): string
    {
        return $this->passwordHash;
    }

    /** @return list<TokenAbility> */
    public function abilities(): array
    {
        return $this->role->abilities();
    }
}
