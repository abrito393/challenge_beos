<?php

declare(strict_types=1);

namespace Src\Auth\Infrastructure\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Src\Auth\Application\IssueToken\IssueTokenUseCase;
use Src\Auth\Infrastructure\Http\Requests\IssueTokenRequest;
use Src\Auth\Infrastructure\Http\Resources\IssuedTokenResource;
use Symfony\Component\HttpFoundation\Response;

final class IssueTokenController
{
    /**
     * Issue an API token.
     *
     * Exchanges valid credentials for a Bearer token whose abilities derive from the user role.
     *
     * @unauthenticated
     */
    public function __invoke(IssueTokenRequest $request, IssueTokenUseCase $useCase): JsonResponse
    {
        return IssuedTokenResource::make($useCase->execute($request->toCommand()))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }
}
