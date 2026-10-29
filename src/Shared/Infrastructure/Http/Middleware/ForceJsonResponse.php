<?php

declare(strict_types=1);

namespace Src\Shared\Infrastructure\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Makes every API response JSON — including framework errors — regardless of the
 * client's Accept header (Constitution VI.1, AC-000-05).
 */
final class ForceJsonResponse
{
    public function handle(Request $request, Closure $next): Response
    {
        $request->headers->set('Accept', 'application/json');

        /** @var Response */
        return $next($request);
    }
}
