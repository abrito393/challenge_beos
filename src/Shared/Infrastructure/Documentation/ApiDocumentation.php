<?php

declare(strict_types=1);

namespace Src\Shared\Infrastructure\Documentation;

use Dedoc\Scramble\Support\Generator\OpenApi;
use Dedoc\Scramble\Support\Generator\Operation;
use Dedoc\Scramble\Support\Generator\Reference;
use Dedoc\Scramble\Support\Generator\Response;
use Dedoc\Scramble\Support\Generator\Schema;
use Dedoc\Scramble\Support\Generator\Types\ArrayType;
use Dedoc\Scramble\Support\Generator\Types\IntegerType;
use Dedoc\Scramble\Support\Generator\Types\ObjectType;
use Dedoc\Scramble\Support\Generator\Types\StringType;
use Dedoc\Scramble\Support\Generator\Types\Type;
use Src\Shared\Infrastructure\Http\ProblemDetails\ProblemDetailsRenderer;

/**
 * Aligns the generated OpenAPI document with what the API really returns:
 * RFC 9457 Problem Details for every error (ADR-0005), plus the error statuses
 * static analysis cannot infer (403 abilities, 404 domain, 409 conflicts, 429 throttling).
 */
final class ApiDocumentation
{
    private const string DESCRIPTION = <<<'MD'
        API RESTful para gestionar productos y sus precios en múltiples divisas.

        ## Autenticación
        1. `POST /auth/token` con `email`, `password` y `device_name` → devuelve un token Bearer.
        2. Enviar `Authorization: Bearer <token>` en cada request.
        3. Los permisos dependen del rol: **admin** → `products:read` + `products:write`; **viewer** → sólo `products:read`.

        Usuarios demo: `admin@example.com` / `password` y `viewer@example.com` / `password`.

        ## Dinero
        Todos los montos viajan como **string decimal con 2 decimales** (`"1299.99"`), nunca como float.
        Las tasas de cambio usan 8 decimales y se expresan como unidades de la divisa por 1 USD.

        ## Errores
        Todos los errores usan [RFC 9457 Problem Details](https://www.rfc-editor.org/rfc/rfc9457) (`application/problem+json`).
        El campo `type` identifica el error de forma estable (ej. `/problems/price-already-exists`).

        ## Límites
        60 requests/min por usuario; 5 intentos de login/min por email + IP.
        MD;

    public function __invoke(OpenApi $openApi): void
    {
        $openApi->info->setVersion('1.0.0')->setDescription(self::DESCRIPTION);

        $problem = $openApi->components->addSchema('ProblemDetails', self::schema(self::problemType()));
        $validationProblem = $openApi->components->addSchema('ValidationProblemDetails', self::schema(self::validationProblemType()));

        // Replace Laravel's default error shapes inferred by Scramble.
        $openApi->components->responses['ValidationException'] = self::response('Validation error.', $validationProblem);
        $openApi->components->responses['AuthenticationException'] = self::response('Missing, invalid, expired or revoked token.', $problem);

        foreach ($openApi->paths as $path) {
            foreach ($path->operations as $operation) {
                $this->documentErrors($operation, $problem);
            }
        }
    }

    private function documentErrors(Operation $operation, Reference $problem): void
    {
        $isPublic = $operation->security === [];
        $isAuthEndpoint = str_contains($operation->path, 'auth/');

        if ($isPublic && $isAuthEndpoint) {
            $operation->addResponse(self::response('Invalid credentials (same answer for unknown email and wrong password).', $problem)->setCode(401));
        }

        if (! $isPublic && ! $isAuthEndpoint) {
            $operation->addResponse(self::response('The token lacks the required ability.', $problem)->setCode(403));
        }

        if (str_contains($operation->path, '{product}')) {
            $operation->addResponse(self::response('The product does not exist or was deleted.', $problem)->setCode(404));
        }

        if ($operation->method === 'post' && str_ends_with($operation->path, '/prices')) {
            $operation->addResponse(self::response('The product already has a price in that currency.', $problem)->setCode(409));
        }

        $operation->addResponse(self::response('Rate limit exceeded; see the `Retry-After` header.', $problem)->setCode(429));
    }

    private static function response(string $description, Reference $schema): Response
    {
        return Response::make(null)
            ->setDescription($description)
            ->setContent(ProblemDetailsRenderer::CONTENT_TYPE, $schema);
    }

    private static function schema(Type $type): Schema
    {
        $schema = new Schema;
        $schema->type = $type;

        return $schema;
    }

    // Scramble's fluent builders are untyped, so they are called as statements to keep static analysis strict.
    private static function problemType(): ObjectType
    {
        $problem = new ObjectType;
        $problem->addProperty('type', (new StringType)->setDescription('Stable error identifier.')->example('/problems/product-not-found'));
        $problem->addProperty('title', (new StringType)->example('Not Found'));
        $problem->addProperty('status', (new IntegerType)->example(404));
        $problem->addProperty('detail', (new StringType)->example('Product 9 was not found.'));
        $problem->addProperty('instance', (new StringType)->example('/api/v1/products/9'));
        $problem->setRequired(['type', 'title', 'status', 'detail', 'instance']);

        return $problem;
    }

    private static function validationProblemType(): ObjectType
    {
        $messages = new ArrayType;
        $messages->setItems(new StringType);

        $errors = new ObjectType;
        $errors->additionalProperties($messages);
        $errors->example(['price' => ['The price field must have 0-2 decimal places.']]);

        $problem = self::problemType();
        $problem->addProperty('errors', $errors);

        return $problem;
    }
}
