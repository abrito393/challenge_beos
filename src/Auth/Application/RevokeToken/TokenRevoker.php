<?php

declare(strict_types=1);

namespace Src\Auth\Application\RevokeToken;

interface TokenRevoker
{
    public function revoke(int $tokenId): void;
}
