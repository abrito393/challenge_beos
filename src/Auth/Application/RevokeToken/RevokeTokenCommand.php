<?php

declare(strict_types=1);

namespace Src\Auth\Application\RevokeToken;

final readonly class RevokeTokenCommand
{
    public function __construct(public int $tokenId) {}
}
