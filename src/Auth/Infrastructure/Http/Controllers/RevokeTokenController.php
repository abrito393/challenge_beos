<?php

declare(strict_types=1);

namespace Src\Auth\Infrastructure\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Src\Auth\Application\RevokeToken\RevokeTokenCommand;
use Src\Auth\Application\RevokeToken\RevokeTokenUseCase;
use Src\Auth\Infrastructure\Persistence\EloquentUser;

final class RevokeTokenController
{
    /**
     * Revoke the current token.
     */
    public function __invoke(Request $request, RevokeTokenUseCase $useCase): Response
    {
        /** @var EloquentUser $user */
        $user = $request->user();

        /** @var int $tokenId */
        $tokenId = $user->currentAccessToken()->getKey();

        $useCase->execute(new RevokeTokenCommand($tokenId));

        return response()->noContent();
    }
}
