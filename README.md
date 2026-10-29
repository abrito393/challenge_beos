# Products API

API RESTful en **Laravel 12** para gestionar productos y sus precios en múltiples divisas.

Construida con **Spec-Driven Development**: cada funcionalidad tiene especificación, plan técnico, tareas y tests trazables a sus criterios de aceptación. El código sigue **DDD hexagonal** y **SOLID**, con límites verificados automáticamente.

[![CI](https://github.com/abrito393/challenge_beos/actions/workflows/ci.yml/badge.svg)](https://github.com/abrito393/challenge_beos/actions/workflows/ci.yml)

| | |
|---|---|
| Tests | **231** (arquitectura + unit + feature) · cobertura **98 %** · 45 aserciones end-to-end (Postman/Newman) |
| Calidad | Larastan **nivel máximo** sin baseline · Pint · CI en GitHub Actions |
| Trazabilidad | 51 criterios de aceptación: 49 con test automatizado (`AC-xxx` en el nombre del test) y 2 de proceso (`make check` y CI) |

---

## Inicio rápido

Requisitos: **Docker** y `make`. Nada más (ni PHP, ni Composer, ni MySQL locales).

```bash
git clone git@github.com:abrito393/challenge_beos.git && cd challenge_beos
make setup        # build + composer install + migraciones + datos demo
```

| | |
|---|---|
| API | `http://localhost:8080/api/v1` |
| Swagger UI | `http://localhost:8080/docs/api` |
| Usuario admin (lectura + escritura) | `admin@example.com` / `password` |
| Usuario viewer (sólo lectura) | `viewer@example.com` / `password` |

```bash
# 1. Obtener un token
TOKEN=$(curl -s -X POST http://localhost:8080/api/v1/auth/token \
  -H 'Content-Type: application/json' \
  -d '{"email":"admin@example.com","password":"password","device_name":"cli"}' | jq -r .data.token)

# 2. Usarlo
curl -s http://localhost:8080/api/v1/products -H "Authorization: Bearer $TOKEN" | jq
```

## Documentación de la API

| Formato | Dónde | Para qué |
|---|---|---|
| **Swagger UI** | `http://localhost:8080/docs/api` | Explorar y probar la API en el navegador |
| **OpenAPI 3.1** | [`docs/api/openapi.json`](./docs/api/openapi.json) | Importar en cualquier herramienta. Se genera desde el código (Scramble) y el CI falla si queda desactualizado |
| **Postman** | [`docs/api/postman_collection.json`](./docs/api/postman_collection.json) | Colección **ejecutable**: 21 requests en orden, con tests en cada uno. `make e2e` la corre con Newman |

## Endpoints

| Método | Ruta | Permiso | Descripción |
|---|---|---|---|
| POST | `/auth/token` | público | Login: devuelve token Bearer (5 intentos/min) |
| DELETE | `/auth/token` | token | Revoca el token actual |
| GET | `/auth/me` | token | Usuario autenticado y sus permisos |
| GET | `/currencies` | `products:read` | Divisas y tasas de cambio |
| GET | `/products` | `products:read` | Lista paginada con filtros y orden |
| POST | `/products` | `products:write` | Crea un producto |
| GET | `/products/{id}` | `products:read` | Detalle con su divisa base |
| PUT | `/products/{id}` | `products:write` | Reemplazo completo |
| DELETE | `/products/{id}` | `products:write` | Eliminación lógica (soft delete) |
| GET | `/products/{id}/prices` | `products:read` | Precios en otras divisas |
| POST | `/products/{id}/prices` | `products:write` | Registra un precio; si se omite `price`, se calcula con la tasa de cambio |

**Listado:** `filter[name]`, `filter[currency_id]`, `filter[min_price]`, `filter[max_price]`, `sort=name|price|created_at` (prefijo `-` = descendente), `page`, `per_page` (máx. 100).

**Dinero:** los montos viajan como string decimal (`"1299.99"`), nunca como float.

**Errores:** siempre [RFC 9457 Problem Details](https://www.rfc-editor.org/rfc/rfc9457), con un `type` estable:

```json
{
  "type": "/problems/price-already-exists",
  "title": "Conflict",
  "status": 409,
  "detail": "The product already has a price in currency 2.",
  "instance": "/api/v1/products/1/prices"
}
```

## Cómo responde a los criterios de evaluación

### Correctitud
- Los 7 endpoints pedidos y el modelo de datos del enunciado completo ([matriz de cumplimiento](./docs/challenge.md#matriz-de-cumplimiento)).
- **Dinero exacto:** `DECIMAL` en BD, `brick/math` en los cálculos, strings en JSON ([ADR-0003](./docs/adr/0003-money-precision.md)). Ejemplo: `1299.99 USD → COP = 5329959.00` exacto.
- **Conversión entre cualquier par de divisas** vía divisa de referencia, con un solo redondeo al final ([ADR-0004](./docs/adr/0004-exchange-rate-semantics.md)).
- **Invariantes en dos capas:** dominio (Value Objects, agregado) y base de datos (`FK`, `UNIQUE`, `CHECK`). Un precio duplicado por concurrencia también responde 409.
- **Verificación más allá de "los tests pasan":** se introdujeron bugs a propósito (mutation testing manual) para confirmar que la suite los detecta. Resultados en cada `specs/*/tasks.md`.

### Claridad
- OpenAPI generado desde el código, más Swagger UI y una colección Postman ejecutable.
- Cada decisión con alternativas reales tiene su **ADR**: [`docs/adr/`](./docs/adr/README.md) (10 decisiones).
- Cada feature tiene su spec con criterios **Dado / cuando / entonces**: [`specs/`](./specs/README.md).

### Organización
- **DDD hexagonal** por bounded context: `Catalog`, `Currency`, `Auth` y `Shared` ([ADR-0009](./docs/adr/0009-pragmatic-ddd-hexagonal.md)).
- **SOLID:** una clase por acción en cada capa, por ejemplo `CreateProductController` + `CreateProductRequest` + `CreateProductUseCase` ([ADR-0010](./docs/adr/0010-solid-single-action-classes-and-value-objects.md)).
- **Límites verificados por tests de arquitectura** (`tests/Architecture`). El build falla si el dominio usa Laravel, si un contexto toca el dominio de otro, si un controller tiene más de una acción o si un Value Object es mutable.

```
src/
├── Catalog/     Product (agregado), ProductPrice, casos de uso, Eloquent, HTTP
├── Currency/    Currency, CurrencyConverter, ConvertAmountUseCase (API pública del contexto)
├── Auth/        User, roles, emisión/revocación de tokens (Sanctum)
└── Shared/      Amount, Money, CurrencyId, TokenAbility, Problem Details, middleware
   cada uno con  Domain/ (PHP puro) → Application/ (casos de uso) → Infrastructure/ (Laravel)
```

Más detalle en [`docs/architecture.md`](./docs/architecture.md).

### Seguridad
- **Sanctum** con permisos por rol: el viewer recibe 403 al intentar escribir. Tokens con expiración y revocables ([ADR-0008](./docs/adr/0008-auth-sanctum-abilities.md)).
- **Anti fuerza bruta:** 5 logins/min por email + IP. **Anti enumeración:** mismo mensaje y mismo costo de hash para un email inexistente y para una contraseña incorrecta.
- **Rate limiting:** 60 req/min por usuario.
- **Errores sin fugas:** nunca exponen stack traces, SQL ni nombres de clases. Headers de seguridad en todas las respuestas.
- **Validación estricta** con un FormRequest por acción, sin mass assignment, filtros y ordenamiento sólo por whitelist, y comodines de `LIKE` escapados.
- Contenedor PHP sin root y Nginx sin versión expuesta.

## Desarrollo

```bash
make check   # Pint + Larastan max + Pest: definición de "terminado"
make test    # sólo tests (sobre una base MySQL aislada)
make e2e     # colección Postman contra el stack real (Newman en Docker)
make docs    # regenera docs/api/openapi.json
make fresh   # reinicia la base con datos demo
make help    # todos los comandos
```

### Proceso: Spec-Driven Development

```
spec.md  →  plan.md  →  tasks.md  →  implementación (test primero)  →  verificación
```

Las reglas no negociables están en [`specs/constitution.md`](./specs/constitution.md). El proceso está codificado como skills de Claude Code en [`.claude/skills/`](./.claude/skills/) (`/sdd-spec`, `/sdd-plan`, `/sdd-tasks`, `/sdd-implement`, `/sdd-verify`, `/adr`). El historial de git muestra las specs commiteadas antes que el código.

| Feature | Spec | Estado |
|---|---|---|
| 000 Foundation | [specs/000-foundation](./specs/000-foundation/) | ✅ |
| 001 Currencies | [specs/001-currencies](./specs/001-currencies/) | ✅ |
| 002 Products CRUD | [specs/002-products-crud](./specs/002-products-crud/) | ✅ |
| 003 Product prices | [specs/003-product-prices](./specs/003-product-prices/) | ✅ |
| 004 Auth & Security | [specs/004-auth-security](./specs/004-auth-security/) | ✅ |

## Decisiones y fuera de alcance

Decisiones tomadas donde el enunciado no especificaba (todas documentadas en specs y ADRs):
- Las rutas viven bajo `/api/v1` (prefijo de Laravel más versionado).
- Se agrega `currencies.code` (ISO 4217) como extensión del modelo del challenge.
- `exchange_rate` se expresa como unidades por 1 USD.
- No se registra un precio en la divisa base del producto (422), porque duplicaría la fuente de verdad.

Fuera de alcance, como extensiones naturales:
- CRUD de divisas.
- `PUT`/`DELETE` de precios por divisa.
- Recálculo automático de precios derivados cuando cambia una tasa.
- Restaurar productos eliminados.
- Historial de tasas de cambio.
