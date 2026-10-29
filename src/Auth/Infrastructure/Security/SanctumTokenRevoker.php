<?php

declare(strict_types=1);

namespace Src\Auth\Infrastructure\Security;

use Laravel\Sanctum\PersonalAccessToken;
use Src\Auth\Application\RevokeToken\TokenRevoker;

final class SanctumTokenRevoker implements TokenRevoker
{
    public function revoke(int $tokenId): void
    {
        PersonalAccessToken::query()->whereKey($tokenId)->delete();
    }
}
