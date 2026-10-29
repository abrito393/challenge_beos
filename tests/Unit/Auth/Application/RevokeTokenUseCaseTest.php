<?php

declare(strict_types=1);

use Src\Auth\Application\RevokeToken\RevokeTokenCommand;
use Src\Auth\Application\RevokeToken\RevokeTokenUseCase;
use Src\Auth\Application\RevokeToken\TokenRevoker;

it('AC-004-04: revokes the given token', function (): void {
    $revoker = new class implements TokenRevoker
    {
        /** @var list<int> */
        public array $revoked = [];

        public function revoke(int $tokenId): void
        {
            $this->revoked[] = $tokenId;
        }
    };

    (new RevokeTokenUseCase($revoker))->execute(new RevokeTokenCommand(42));

    expect($revoker->revoked)->toBe([42]);
});
