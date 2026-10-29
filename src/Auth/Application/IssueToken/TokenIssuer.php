<?php

declare(strict_types=1);

namespace Src\Auth\Application\IssueToken;

use Src\Auth\Domain\User;
use Src\Auth\Domain\ValueObjects\DeviceName;

interface TokenIssuer
{
    public function issue(User $user, DeviceName $deviceName): IssuedToken;
}
