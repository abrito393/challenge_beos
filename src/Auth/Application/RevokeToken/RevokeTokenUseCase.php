<?php

declare(strict_types=1);

namespace Src\Auth\Application\RevokeToken;

final readonly class RevokeTokenUseCase
{
    public function __construct(private TokenRevoker $tokens) {}

    public function execute(RevokeTokenCommand $command): void
    {
        $this->tokens->revoke($command->tokenId);
    }
}
