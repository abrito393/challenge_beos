# 004 — Auth & Security · Plan técnico

- **Spec:** [spec.md](./spec.md)
- **ADRs relacionados:** [0005 Problem Details](../../docs/adr/0005-error-format-problem-details.md), [0008 Sanctum](../../docs/adr/0008-auth-sanctum-abilities.md), [0009 DDD](../../docs/adr/0009-pragmatic-ddd-hexagonal.md), [0010 SOLID](../../docs/adr/0010-solid-single-action-classes-and-value-objects.md)

## 1. Esquema de base de datos

`users` (se edita la migración base, porque el proyecto aún no tiene datos productivos):

| Columna | Tipo | Regla |
|---|---|---|
| `id` | BIGINT PK | |
| `name` | VARCHAR(255) | |
| `email` | VARCHAR(255) | `UNIQUE` |
| `password` | VARCHAR(255) | hash bcrypt (cast `hashed`) |
| `role` | VARCHAR(20) | `admin` \| `viewer`, default `viewer` |
| `timestamps` | | |

Se eliminan `password_reset_tokens` y `sessions`, porque la API es stateless y no tiene reset de contraseña (fuera de alcance). `personal_access_tokens` viene de Sanctum.

## 2. Componentes

### Shared kernel

| Capa | Clase | Responsabilidad |
|---|---|---|
| Domain | `Security\TokenAbility` (enum) | `products:read`, `products:write`. Es contrato entre contextos, por eso vive en Shared (ver §6) |
| Domain | `Exceptions\DomainException` (abstract) | Base de errores de dominio. `errorCode(): string` (slug estable) |
| Domain | `Exceptions\NotFound`, `Conflict`, `InvalidValue`, `AuthenticationFailed` (abstract) | **Categorías**. Cada error concreto extiende una; el renderer mapea categoría → HTTP (OCP, sin `switch` por excepción) |
| Infra | `Http\Middleware\SecurityHeaders` | Headers de seguridad en todas las respuestas (AC-004-09) |
| Infra | `Http\ProblemDetails\ProblemDetailsRenderer` | Throwable → `application/problem+json` (AC-004-10/11/12) |
| Infra | `SharedServiceProvider` | Rate limiter `api` (60/min por usuario o IP) |

### Contexto Auth

| Capa | Clase | Responsabilidad |
|---|---|---|
| Domain | `UserRole` (enum) | `admin` / `viewer`; `abilities(): list<TokenAbility>` |
| Domain | `ValueObjects\UserId`, `Email`, `DeviceName` | Invariantes: id > 0; email válido y normalizado a minúsculas; device 1–255 chars |
| Domain | `User` (entidad) | id, email, role, passwordHash |
| Domain | `UserRepository` (puerto) | `findByEmail(Email): ?User` |
| Domain | `PasswordHasher` (puerto) | `verify(plain, ?hash): bool`, con tiempo constante aunque el usuario no exista |
| Domain | `Exceptions\InvalidCredentials` | extiende `AuthenticationFailed` → 401 |
| Application | `IssueToken\IssueTokenUseCase` + `IssueTokenCommand` | Verifica credenciales y emite un token con las abilities del rol |
| Application | `IssueToken\TokenIssuer` (puerto) + `IssuedToken` (DTO) | Emitir token con expiración |
| Application | `RevokeToken\RevokeTokenUseCase` + `RevokeTokenCommand` | Revoca el token actual |
| Application | `RevokeToken\TokenRevoker` (puerto) | Revocar por id |
| Infra | `Persistence\EloquentUser` | Modelo Authenticatable + `HasApiTokens` |
| Infra | `Persistence\EloquentUserRepository` | Eloquent ↔ entidad `User` |
| Infra | `Security\LaravelPasswordHasher` | `Hash::check`, con hash dummy si el usuario no existe (anti-enumeración por timing) |
| Infra | `Security\SanctumTokenIssuer` / `SanctumTokenRevoker` | Adaptadores Sanctum |
| Infra | `Http\Controllers\IssueTokenController`, `RevokeTokenController`, `GetAuthenticatedUserController` | Invokables |
| Infra | `Http\Requests\IssueTokenRequest` | `email`, `password`, `device_name` |
| Infra | `Http\Resources\IssuedTokenResource`, `AuthenticatedUserResource` | Serialización (sin password) |
| Infra | `Http\routes.php` | `auth/token` (POST, `throttle:login`), `auth/token` (DELETE) y `auth/me` (GET), estas dos con `auth:sanctum` + `throttle:api` |
| Infra | `AuthServiceProvider` | Bindings puerto → adaptador; rate limiter `login` (5/min por email+IP) |

## 3. Contratos

### `POST /api/v1/auth/token`

Validación (`IssueTokenRequest`):
```php
'email'       => ['required', 'string', 'email', 'max:255'],
'password'    => ['required', 'string', 'max:255'],
'device_name' => ['required', 'string', 'max:255'],
```
`201`:
```json
{ "data": { "token": "1|abc…", "token_type": "Bearer", "abilities": ["products:read","products:write"], "expires_at": "2026-10-30T18:00:00Z" } }
```
`401` (Problem Details, mismo mensaje para email inexistente y password incorrecta):
```json
{ "type": "/problems/invalid-credentials", "title": "Unauthorized", "status": 401, "detail": "The provided credentials are incorrect.", "instance": "/api/v1/auth/token" }
```

### `DELETE /api/v1/auth/token` → `204`

### `GET /api/v1/auth/me` → `200`
```json
{ "data": { "id": 1, "name": "Admin", "email": "admin@example.com", "role": "admin", "abilities": ["products:read","products:write"] } }
```

### Problem Details: mapeo del renderer

| Origen | status | `type` |
|---|---|---|
| `ValidationException` | 422 | `/problems/validation-error` + `errors` |
| `AuthenticationException` | 401 | `/problems/unauthenticated` |
| `Shared\Domain\Exceptions\AuthenticationFailed` | 401 | `/problems/{errorCode}` |
| `Shared\Domain\Exceptions\NotFound` | 404 | `/problems/{errorCode}` |
| `Shared\Domain\Exceptions\Conflict` | 409 | `/problems/{errorCode}` |
| `Shared\Domain\Exceptions\InvalidValue` | 422 | `/problems/{errorCode}` |
| `UniqueConstraintViolationException` | 409 | `/problems/conflict` |
| `HttpExceptionInterface` (403, 404, 405, 429…) | el suyo, conserva headers (`Retry-After`) | `/problems/{slug del status}` |
| Cualquier otro `Throwable` | 500 | `about:blank`, `detail` genérico; `debug` sólo si `APP_DEBUG=true` |

> Laravel convierte `AuthorizationException` (incluida `MissingAbilityException` de Sanctum) en `AccessDeniedHttpException` y `ModelNotFoundException` en `NotFoundHttpException` antes de los render callbacks. Por eso basta con `HttpExceptionInterface`.

## 4. Configuración

- `config/sanctum.php`: `guard => []` (sólo tokens, sin sesión) y `expiration => env('SANCTUM_EXPIRATION', 1440)`.
- `config/auth.php`: provider `users.model` → `Src\Auth\Infrastructure\Persistence\EloquentUser`.
- `bootstrap/app.php`: alias `abilities` / `ability` (Sanctum), `SecurityHeaders` global y renderer Problem Details para `api/*`.
- `bootstrap/providers.php`: `SharedServiceProvider`, `AuthServiceProvider`.
- `routes/api.php`: composition root que incluye `src/*/Infrastructure/Http/routes.php`.
- Scramble: `security_strategy = MiddlewareAuthSecurityStrategy` (Bearer en OpenAPI para rutas con `auth:sanctum`).
- `UserSeeder`: `admin@example.com` y `viewer@example.com` sólo fuera de producción.

## 5. Mapeo AC → test

| AC | Tipo | Archivo |
|---|---|---|
| AC-004-01 | Feature | `tests/Feature/Api/V1/Auth/IssueTokenTest.php` |
| AC-004-02 | Feature + Unit | `IssueTokenTest.php`, `tests/Unit/Auth/Application/IssueTokenUseCaseTest.php` |
| AC-004-03 | Feature | `IssueTokenTest.php` |
| AC-004-04 | Feature + Unit | `RevokeTokenTest.php`, `RevokeTokenUseCaseTest.php` |
| AC-004-05 | Feature | `TokenExpirationTest.php` |
| AC-004-06 | Feature + Unit | `IssueTokenTest.php`, `tests/Unit/Auth/Domain/UserRoleTest.php` |
| AC-004-07 | Feature | `tests/Feature/Security/AbilitiesTest.php` |
| AC-004-08 | Feature | `tests/Feature/Security/RateLimitTest.php` |
| AC-004-09 | Feature | `tests/Feature/Security/SecurityHeadersTest.php` |
| AC-004-10 | Feature | `tests/Feature/Security/ProblemDetailsTest.php` |
| AC-004-11 | Feature | `ProblemDetailsTest.php` (un caso por status) |
| AC-004-12 | Feature | `ProblemDetailsTest.php` |
| AC-004-13 | Feature | `GetAuthenticatedUserTest.php` |

Unit tests de VOs: `tests/Unit/Auth/Domain/{Email,DeviceName,UserId}Test.php`.

## 6. Decisiones

- **`TokenAbility` en el Shared kernel** (enmienda a ADR-0008): las rutas de `Catalog` y `Currency` necesitan las abilities, y la regla de límites prohíbe importar `Auth\Domain`. Es lenguaje publicado entre contextos, no un detalle de Auth.
- **Excepciones de dominio por categoría** (enmienda a ADR-0010, O/C): el dominio no conoce códigos HTTP. Expresa *qué tipo* de error ocurrió (`NotFound`, `Conflict`…) y la infraestructura lo traduce.
- **Anti-enumeración:** mismo mensaje y mismo costo computacional (hash dummy) para email inexistente y password incorrecta.

## Riesgos

| Riesgo | Mitigación |
|---|---|
| El guard cachea el usuario entre requests de un mismo test (falso positivo en revocación/expiración) | `app('auth')->forgetGuards()` entre requests en esos tests |
| Headers de seguridad ausentes en respuestas de error | Middleware **global** (envuelve también el router y los errores) |
