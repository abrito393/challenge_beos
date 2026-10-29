<?php

declare(strict_types=1);

namespace Src\Auth\Domain;

use Src\Auth\Domain\ValueObjects\Email;

interface UserRepository
{
    public function findByEmail(Email $email): ?User;
}
