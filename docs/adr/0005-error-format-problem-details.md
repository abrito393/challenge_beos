# ADR-0005: Errores en formato RFC 9457 Problem Details

- **Estado:** Accepted
- **Fecha:** 2026-10-29
- **Specs relacionadas:** 000, 004

## Contexto

Laravel devuelve errores con formatos distintos según la excepción (`{"message": ...}`, `{"message", "errors"}`, páginas HTML de debug). Un cliente necesita un contrato único para manejar errores, y la constitution exige que los errores no filtren información interna.

## Alternativas consideradas

1. **Formato por defecto de Laravel:** cero esfuerzo, pero inconsistente entre tipos de error.
2. **Envelope propio (`{success, error: {code, message}}`):** consistente, pero no estándar.
3. **RFC 9457 Problem Details (`application/problem+json`):** estándar IETF (sucesor de RFC 7807), con soporte en herramientas y OpenAPI.

## Decisión

Usaremos RFC 9457. Un único renderer en `bootstrap/app.php` (`withExceptions`) mapea las excepciones:

| Excepción | status | `type` |
|---|---|---|
| `ValidationException` | 422 | `/problems/validation-error` (+ extensión `errors`) |
| `AuthenticationException` | 401 | `/problems/unauthenticated` |
| `AuthorizationException`, `MissingAbilityException` | 403 | `/problems/forbidden` |
| `ModelNotFoundException`, `NotFoundHttpException` | 404 | `/problems/not-found` |
| Dominio: categoría `NotFound` / `Conflict` / `InvalidValue` / `AuthenticationFailed` | 404 / 409 / 422 / 401 | `/problems/{errorCode}` (ej. `/problems/invalid-credentials`) |
| `UniqueConstraintViolationException` (carrera concurrente) | 409 | `/problems/conflict` |
| `ThrottleRequestsException` | 429 | `/problems/too-many-requests` (+ `Retry-After`) |
| `Throwable` (resto) | 500 | `about:blank`, `detail` genérico |

## Consecuencias

- ✅ Un solo contrato de error para todos los endpoints, documentado en OpenAPI.
- ✅ Un único punto donde se controla qué información se expone.
- ⚠️ El formato `422` difiere del que esperan algunos frontends Laravel. Se mitiga manteniendo la clave `errors` con la misma estructura.
