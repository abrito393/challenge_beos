# 000 — Foundation · Tareas

- **Plan:** [plan.md](./plan.md)

Leyenda: `[ ]` pendiente · `[~]` en curso · `[x]` hecha

| # | Tarea | AC | DoD (Definition of Done) | Estado |
|---|---|---|---|---|
| T-000-01 | Crear proyecto Laravel 12 en la raíz del repo, sin romper `specs/`, `docs/` ni `.claude/` | — | `php artisan --version` muestra 12.x | [x] |
| T-000-02 | `docker/php/Dockerfile`, `docker/nginx/default.conf`, `docker/mysql/init/01-testing-db.sql`, `docker-compose.yml` | AC-000-01 | `docker compose up -d` deja los 3 servicios healthy | [x] |
| T-000-03 | `Makefile` con targets del plan | AC-000-01, 03 | `make setup` desde cero funciona | [x] |
| T-000-04 | Instalar y configurar Pest; `phpunit.xml` apuntando a `products_api_testing` | AC-000-02 | `make test` verde con test de humo | [x] |
| T-000-05 | Instalar Larastan (`phpstan.neon`, level max) y Pint (`pint.json`) | AC-000-03 | `make check` verde | [x] |
| T-000-06 | `bootstrap/app.php`: prefijo `api/v1`, middleware `ForceJsonResponse` + test | AC-000-05 | Test `AC-000-05` verde | [x] |
| T-000-06b | Estructura `src/` (`Catalog`, `Currency`, `Auth`, `Shared` × `Domain/Application/Infrastructure`), autoload `"Src\\": "src/"`, Larastan y Pint incluyendo `src/` | — | `composer dump-autoload` ok; Larastan analiza `src/` | [x] |
| T-000-06c | Tests de arquitectura en `tests/Architecture/` (dominio puro, capas, límites entre contextos, `final` + `strict_types`, controllers invokables, UseCases con un solo `execute`, VOs `final readonly`) | — | `make test` ejecuta las reglas `arch()` en verde | [x] |
| T-000-07 | `AppServiceProvider`: `Model::shouldBeStrict`, `DB::prohibitDestructiveCommands` en producción. Se mantiene el wrapper `data` de los Resources | — | Larastan verde | [x] |
| T-000-08 | Instalar `brick/math`, `dedoc/scramble`, `laravel/sanctum` (`install:api`) | — | `composer validate` ok | [x] |
| T-000-09 | GitHub Actions `ci.yml` | AC-000-04 | Workflow válido (`act` o primer push) | [~] |
| T-000-10 | README: setup, comandos, estructura; actualizar estado en `specs/README.md` | — | Un tercero puede levantar el proyecto sólo con el README | [x] |
| T-000-11 | Restaurar `--coverage --min=90` en `ci.yml` al cerrar la feature 004 | AC-000-04 | CI verde con el umbral activo | [x] |

## Notas de implementación

- **Verificación de las reglas `arch()`:** se plantaron violaciones a propósito (dominio usando `Illuminate`, VO no `final`, Catalog usando `Currency\Domain`, UseCase con dos métodos públicos) y las 4 reglas fallaron como se esperaba. Después se eliminaron.
- **Aislamiento de DB (AC-000-02):** tras correr la suite, `products_api` conserva sus datos y las tablas de test viven en `products_api_testing`.
- **T-000-09:** el primer push falló porque `tests/Unit` estaba vacía y git no versiona carpetas vacías; se agregó `tests/Unit/.gitkeep`. El umbral `--min=90` se desactivó hasta la feature 004 (T-000-11). Hoy la cobertura es 33%, porque `ForceJsonResponse` aún no tiene rutas que lo ejerciten y `User` se moverá al contexto Auth.
- **Desvíos del plan** (ya reflejados en el plan):
  - Se eliminaron las rutas web, la vista `welcome`, el controller base y el tooling de frontend (Vite/npm): es una API pura.
  - `shouldRenderJsonWhen` en el exception handler cubre las rutas `/api/*` inexistentes, que no pasan por el middleware.
  - En macOS el GID 20 colisiona con un grupo de Alpine: `usermod/groupmod -o`.
  - PCOV necesita `pcov.directory` explícito para medir cobertura.
