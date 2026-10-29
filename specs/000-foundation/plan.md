# 000 — Foundation · Plan técnico

- **Spec:** [spec.md](./spec.md)
- **ADRs relacionados:** [0009 DDD hexagonal](../../docs/adr/0009-pragmatic-ddd-hexagonal.md), [0006 Docker](../../docs/adr/0006-docker-compose-infrastructure.md), [0007 API docs](../../docs/adr/0007-api-documentation-scramble.md)

## 1. Infraestructura (Docker Compose)

| Servicio | Imagen | Rol |
|---|---|---|
| `app` | build `docker/php/Dockerfile` (php:8.3-fpm-alpine) | PHP-FPM, extensiones `pdo_mysql`, `bcmath`, `intl`, `opcache`; usuario `www-data` |
| `web` | `nginx:1.27-alpine` | Reverse proxy → `app:9000`, expone `8080`, headers de seguridad base |
| `db` | `mysql:8.4` | Base de datos `products_api`; healthcheck `mysqladmin ping` |

- Una segunda base `products_api_testing` se crea con un script en `docker/mysql/init/`. Los tests nunca usan la base de desarrollo (AC-000-02).
- `app` depende de `db` con `condition: service_healthy`.

## 2. Makefile (interfaz única del proyecto)

| Target | Acción |
|---|---|
| `setup` | build, up, `composer install`, `.env`, `key:generate`, `migrate --seed` |
| `up` / `down` | levantar / detener |
| `test` | `php artisan test` (Pest) |
| `lint` | `pint --test` |
| `stan` | `phpstan analyse` |
| `check` | `lint` + `stan` + `test` |
| `docs` | exportar OpenAPI (`scramble:export`) a `docs/api/openapi.json` |
| `shell` | shell dentro de `app` |

## 3. Paquetes

| Paquete | Uso |
|---|---|
| `laravel/sanctum` | Autenticación por token (feature 004) |
| `brick/math` | Aritmética decimal exacta (ADR-0003) |
| `dedoc/scramble` | OpenAPI + Swagger UI (ADR-0007) |
| `pestphp/pest` + `pest-plugin-laravel` (dev) | Tests |
| `larastan/larastan` (dev) | Análisis estático nivel max |
| `laravel/pint` (dev) | Estilo (preset `laravel` + `declare_strict_types`, `final_class`) |

## 4. Estructura DDD (ADR-0009)

- `composer.json` → `"autoload": {"psr-4": {"App\\": "app/", "Src\\": "src/"}}`.
- Un `ServiceProvider` por contexto en `src/<Ctx>/Infrastructure/`, registrado en `bootstrap/providers.php`. Cada uno registra sus bindings puerto → adaptador. Las rutas de cada contexto (`src/<Ctx>/Infrastructure/Http/routes.php`) se incluyen desde `routes/api.php`, que actúa como composition root y hereda el grupo `api` y el prefijo `api/v1`.
- `phpstan.neon` y `pint.json` incluyen `src/`.
- `tests/Architecture/*.php` con `arch()` de Pest para las reglas de dependencia.

## 5. Bootstrap de la aplicación (`bootstrap/app.php`)

- Rutas API con prefijo `api/v1` (`apiPrefix`).
- Middleware `Src\Shared\Infrastructure\Http\Middleware\ForceJsonResponse` antepuesto al grupo `api`, más `shouldRenderJsonWhen(api/*)` en excepciones para las rutas inexistentes, que no pasan por el grupo (AC-000-05).
- Sin rutas web ni frontend: se eliminan `routes/web.php`, las vistas, el controller base y Vite/npm.
- Registro del renderer de excepciones Problem Details (detalle en feature 004 / ADR-0005).
- `Model::shouldBeStrict(! app()->isProduction())` y `Model::unguard(false)` en `AppServiceProvider`.

## 6. CI (`.github/workflows/ci.yml`)

- Job `quality`: setup PHP 8.3, cache de Composer, Pint, Larastan.
- Job `tests`: servicio MySQL 8.4, migraciones, Pest con `--coverage --min=90` (si Xdebug/PCOV disponible).

## Riesgos

| Riesgo | Mitigación |
|---|---|
| Permisos de archivos entre host y contenedor | UID/GID configurables por build args |
| Larastan nivel max genera ruido con Eloquent | Usar generics (`@use HasFactory<ProductFactory>`) y `@property` en modelos |
