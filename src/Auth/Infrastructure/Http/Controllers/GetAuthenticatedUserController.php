<?php

declare(strict_types=1);

namespace Src\Auth\Infrastructure\Http\Controllers;

use Illuminate\Http\Request;
use Src\Auth\Infrastructure\Http\Resources\AuthenticatedUserResource;
use Src\Auth\Infrastructure\Persistence\EloquentUser;

final class GetAuthenticatedUserController
{
    /**
     * Get the authenticated user.
     */
    public function __invoke(Request $request): AuthenticatedUserResource
    {
        /** @var EloquentUser $user */
        $user = $request->user();

        return AuthenticatedUserResource::make($user);
    }
}
