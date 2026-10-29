# Products API

API RESTful en **Laravel 12** para gestionar productos y sus precios en múltiples divisas. Construida con **Spec-Driven Development**: cada funcionalidad tiene especificación, plan técnico, tareas y tests trazables a sus criterios de aceptación.

> 🚧 **Estado:** 000, 004 y 001 listas · siguiente: Products CRUD (002). Ver [`specs/README.md`](./specs/README.md).

## Inicio rápido

Requisitos: Docker + Docker Compose y `make`.

```bash
git clone <repo> && cd challenge_beos
make setup
```

- API: `http://localhost:8080/api/v1`
- Swagger UI: `http://localhost:8080/docs/api`
- Usuarios de prueba: `admin@example.com` / `password` (lectura + escritura) · `viewer@example.com` / `password` (sólo lectura)

```bash
# Obtener token
curl -s -X POST http://localhost:8080/api/v1/auth/token \
  -H 'Content-Type: application/json' \
  -d '{"email":"admin@example.com","password":"password","device_name":"cli"}'

# Listar productos
curl -s http://localhost:8080/api/v1/products -H "Authorization: Bearer <token>"
```

## Endpoints

| Método | Ruta | Descripción |
|---|---|---|
| POST | `/api/v1/auth/token` | Obtener token |
| DELETE | `/api/v1/auth/token` | Revocar token |
| GET | `/api/v1/currencies` | Listar divisas |
| GET | `/api/v1/products` | Listar productos (paginado, filtros, orden) |
| POST | `/api/v1/products` | Crear producto |
| GET | `/api/v1/products/{id}` | Ver producto |
| PUT | `/api/v1/products/{id}` | Actualizar producto |
| DELETE | `/api/v1/products/{id}` | Eliminar producto |
| GET | `/api/v1/products/{id}/prices` | Listar precios por divisa |
| POST | `/api/v1/products/{id}/prices` | Registrar precio en otra divisa |

Contratos completos en Swagger UI, en [`docs/api/openapi.json`](./docs/api/) y en la colección Postman [`docs/api/postman_collection.json`](./docs/api/).

## Documentación del proyecto

| Documento | Contenido |
|---|---|
| [`docs/challenge.md`](./docs/challenge.md) | Enunciado original + matriz de cumplimiento |
| [`specs/constitution.md`](./specs/constitution.md) | Principios no negociables |
| [`specs/`](./specs/README.md) | Specs, planes y tareas por feature |
| [`docs/architecture.md`](./docs/architecture.md) | Capas, modelo de datos, flujo de un request |
| [`docs/adr/`](./docs/adr/README.md) | Decisiones de arquitectura y sus alternativas |

## Highlights

- **DDD hexagonal** por bounded context (`Catalog`, `Currency`, `Auth`, `Shared`), con un dominio puro y límites verificados por tests de arquitectura ([ADR-0009](./docs/adr/0009-pragmatic-ddd-hexagonal.md)).
- **SOLID estricto:** una acción = un archivo por capa (`CreateProductController` + `CreateProductRequest` + `CreateProductUseCase`) y Value Objects para cada concepto del dominio ([ADR-0010](./docs/adr/0010-solid-single-action-classes-and-value-objects.md)).
- **Dinero exacto:** `DECIMAL` + `brick/math`, montos como string en JSON ([ADR-0003](./docs/adr/0003-money-precision.md)).
- **Conversión entre cualquier par de divisas** vía divisa de referencia ([ADR-0004](./docs/adr/0004-exchange-rate-semantics.md)).
- **Seguridad:** Sanctum + abilities por ruta, rate limiting, security headers, errores sin fugas ([ADR-0008](./docs/adr/0008-auth-sanctum-abilities.md)).
- **Errores estándar** RFC 9457 Problem Details ([ADR-0005](./docs/adr/0005-error-format-problem-details.md)).
- **Calidad:** Pest, Larastan nivel máximo, Pint y CI en GitHub Actions.

## Comandos

```bash
make check   # lint + análisis estático + tests
make test    # tests
make docs    # regenerar OpenAPI
```
