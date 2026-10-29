# 004 — Auth & Security · Tareas

- **Plan:** [plan.md](./plan.md)

Leyenda: `[ ]` pendiente · `[~]` en curso · `[x]` hecha

| # | Tarea | AC | DoD | Estado |
|---|---|---|---|---|
| T-004-01 | Shared Domain: `TokenAbility`, `DomainException` y categorías (`NotFound`, `Conflict`, `InvalidValue`, `AuthenticationFailed`) | — | Larastan verde; arch verde | [x] |
| T-004-02 | Auth Domain: `UserRole`, VOs `UserId`/`Email`/`DeviceName`, entidad `User`, puertos `UserRepository`/`PasswordHasher`, `InvalidCredentials` + unit tests | AC-004-06 | Unit tests verdes | [x] |
| T-004-03 | Auth Application: `IssueTokenUseCase`, `RevokeTokenUseCase`, puertos `TokenIssuer`/`TokenRevoker`, `IssuedToken` + unit tests con fakes in-memory | AC-004-02, 04, 06 | Unit tests verdes | [x] |
| T-004-04 | Persistence: migración `users` con `role`, `EloquentUser`, factory con estados `admin()`/`viewer()`, repositorio, `UserSeeder`; config `auth`/`sanctum` | AC-004-13 | `make fresh` ok | [x] |
| T-004-05 | Adaptadores: `LaravelPasswordHasher`, `SanctumTokenIssuer`, `SanctumTokenRevoker`, `AuthServiceProvider` (bindings + limiter `login`) | AC-004-03, 05 | Larastan verde | [x] |
| T-004-06 | Shared Infra: `ProblemDetailsRenderer`, `SecurityHeaders`, `SharedServiceProvider` (limiter `api`), wiring en `bootstrap/app.php` | AC-004-09..12 | Feature tests de seguridad verdes | [x] |
| T-004-07 | Http Auth: Request, controllers, resources, rutas; composition root en `routes/api.php` | AC-004-01..08, 13 | Feature tests de auth verdes | [x] |
| T-004-08 | Helpers de test `actingAsAdmin()`/`actingAsViewer()`; Scramble con Bearer; enmiendas a ADR-0008/0010 y docs | — | `make check` verde; `/docs/api` muestra auth | [x] |

## Notas de implementación

- **Verificación:** 13/13 ACs con test. `make check` verde (92 tests, Larastan max sin errores), cobertura 97,1%.
- **Mutation check manual:** se introdujeron 3 bugs a propósito (viewer con permiso de escritura, sin security headers, revocación que no borra) y la suite los detectó (4, 4 y 1 tests fallidos).
- **Smoke test contra el stack real** (Nginx + FPM + MySQL) con curl: login, `/auth/me`, 401 en Problem Details con headers de seguridad, logout y reuso del token revocado (401). OpenAPI con Bearer y el login marcado público.
- **Desvíos del plan:**
  - `ProblemDetailsRenderer` reemplaza los mensajes de las excepciones HTTP del framework por textos genéricos por status. Ejemplo: el 404 de `ModelNotFoundException` incluye el nombre de la clase del modelo.
  - Login con email mal formado → `InvalidCredentials` (401), no 422 de dominio, para no dar pistas. El FormRequest ya rechaza formatos inválidos con 422 antes de llegar al dominio.
