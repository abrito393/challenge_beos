<?php

declare(strict_types=1);

namespace Src\Shared\Infrastructure\Http\ProblemDetails;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Src\Shared\Domain\Exceptions\AuthenticationFailed;
use Src\Shared\Domain\Exceptions\Conflict;
use Src\Shared\Domain\Exceptions\DomainException;
use Src\Shared\Domain\Exceptions\InvalidValue;
use Src\Shared\Domain\Exceptions\NotFound;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

/**
 * Single place that decides what an API client sees when something fails
 * (RFC 9457, ADR-0005). Domain exceptions are mapped by *category*, so adding
 * a new business error never requires touching this class (ADR-0010, O/C).
 */
final readonly class ProblemDetailsRenderer
{
    public const string CONTENT_TYPE = 'application/problem+json';

    /** Generic, non-leaking details: framework messages may expose class names or internals. */
    private const array GENERIC_DETAILS = [
        401 => 'Authentication is required to access this resource.',
        403 => 'You are not allowed to perform this action.',
        404 => 'The requested resource was not found.',
        405 => 'The HTTP method is not allowed for this resource.',
        409 => 'The request conflicts with the current state of the resource.',
        422 => 'The given data was invalid.',
        429 => 'Too many requests. Please retry later.',
        500 => 'An unexpected error occurred.',
    ];

    public function __construct(private Config $config) {}

    public function __invoke(Throwable $e, Request $request): ?JsonResponse
    {
        if (! $request->is('api/*')) {
            return null;
        }

        return match (true) {
            $e instanceof ValidationException => $this->problem($request, 422, 'validation-error', extra: ['errors' => $e->errors()]),
            $e instanceof AuthenticationException => $this->problem($request, 401, 'unauthenticated'),
            $e instanceof DomainException => $this->fromDomain($request, $e),
            $e instanceof UniqueConstraintViolationException => $this->problem($request, 409, 'conflict'),
            $e instanceof HttpExceptionInterface => $this->fromHttp($request, $e),
            default => $this->serverError($request, $e),
        };
    }

    private function fromDomain(Request $request, DomainException $e): JsonResponse
    {
        $status = match (true) {
            $e instanceof NotFound => 404,
            $e instanceof Conflict => 409,
            $e instanceof InvalidValue => 422,
            $e instanceof AuthenticationFailed => 401,
            default => 400,
        };

        // Domain messages are authored for clients, so they are safe to expose.
        return $this->problem($request, $status, $e->errorCode(), $e->getMessage());
    }

    private function fromHttp(Request $request, HttpExceptionInterface $e): JsonResponse
    {
        $status = $e->getStatusCode();

        return $this->problem($request, $status, $this->slug($status), headers: $e->getHeaders());
    }

    private function serverError(Request $request, Throwable $e): JsonResponse
    {
        $extra = $this->config->get('app.debug') === true
            ? ['debug' => ['exception' => $e::class, 'message' => $e->getMessage()]]
            : [];

        return $this->problem($request, 500, null, extra: $extra);
    }

    /**
     * @param  array<string, mixed>  $extra
     * @param  array<array-key, mixed>  $headers  forwarded as-is (e.g. Retry-After on 429)
     */
    private function problem(
        Request $request,
        int $status,
        ?string $type,
        ?string $detail = null,
        array $extra = [],
        array $headers = [],
    ): JsonResponse {
        $body = [
            'type' => $type === null ? 'about:blank' : "/problems/{$type}",
            'title' => Response::$statusTexts[$status] ?? 'Error',
            'status' => $status,
            'detail' => $detail ?? self::GENERIC_DETAILS[$status] ?? (Response::$statusTexts[$status] ?? 'Error'),
            'instance' => '/'.ltrim($request->path(), '/'),
            ...$extra,
        ];

        return new JsonResponse($body, $status, ['Content-Type' => self::CONTENT_TYPE, ...$headers]);
    }

    private function slug(int $status): string
    {
        return match ($status) {
            401 => 'unauthenticated',
            403 => 'forbidden',
            422 => 'validation-error',
            default => strtolower(str_replace(' ', '-', Response::$statusTexts[$status] ?? 'error')),
        };
    }
}
