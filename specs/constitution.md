# Constitution — Products API

> Principios **no negociables** del proyecto. Toda spec, plan, tarea y línea de código debe cumplirlos.
> Si una decisión necesita violar un principio, primero se registra un ADR que lo justifique y se enmienda este documento.

Versión: 1.2.0 · Última enmienda: 2026-10-29 (IV: SOLID, clases de una acción y Value Objects, ADR-0010)

---

## I. Spec-Driven Development

1. **Nada se implementa sin spec aprobada.** Flujo obligatorio por feature:
   `spec.md` (qué y por qué) → `plan.md` (cómo) → `tasks.md` (pasos atómicos) → implementación → verificación.
2. Cada criterio de aceptación de una spec tiene **al menos un test automatizado** que lo cubre y lo referencia (`AC-xxx`).
3. Las specs se escriben en lenguaje de negocio; los planes en lenguaje técnico. No mezclar.
4. Los cambios de alcance se reflejan **primero** en la spec y luego en el código.

## II. Correctitud del dominio

1. **El dinero nunca es `float`.** Persistencia en `DECIMAL`, aritmética con `brick/math` (`BigDecimal`), serialización JSON como `string`. (ADR-0003)
2. Montos: `DECIMAL(15,2)`. Tasas de cambio: `DECIMAL(18,8)`. Redondeo `HALF_UP` sólo al final del cálculo.
3. La integridad se garantiza en **dos capas**: validación en la aplicación **y** restricciones en base de datos (FK, `UNIQUE`, `NOT NULL`, `CHECK` cuando aplique).
4. Las operaciones que escriben más de una fila se ejecutan dentro de una transacción.

## III. Seguridad por defecto (secure by default)

1. Toda ruta de negocio requiere autenticación (Sanctum) y autorización por abilities del token, declaradas en cada ruta (`abilities:`). Una ruta pública es una excepción documentada.
2. Toda entrada se valida con el `FormRequest` propio de su acción (`<Acción><Entidad>Request`). Nunca se usa `$request->all()` para persistir.
3. Los modelos declaran `$fillable` explícito; se activa `Model::shouldBeStrict()` fuera de producción.
4. Rate limiting en todas las rutas; límite más estricto en autenticación.
5. Los errores nunca exponen stack traces, SQL ni rutas internas cuando `APP_DEBUG=false`.
6. No hay secretos en el repositorio. `.env.example` sólo contiene valores ficticios.

## IV. Arquitectura y organización (ADR-0009)

1. **DDD hexagonal por bounded context** en `src/` (`Catalog`, `Currency`, `Auth`, `Shared`), cada uno con `Domain` / `Application` / `Infrastructure`.
2. **El dominio es PHP puro:** no depende de Laravel ni de Eloquent. Las reglas de negocio e invariantes viven en entidades, Value Objects y servicios de dominio.
3. Los casos de uso (`Application`) reciben **Commands inmutables** (`readonly`), nunca un `Request` ni arrays.
4. Eloquent existe **sólo en Infraestructura**, detrás de interfaces de repositorio definidas en el dominio.
5. **SOLID, con responsabilidad única estricta (ADR-0010):** una acción = un archivo por capa. Cada endpoint tiene su **Controller invokable** (`CreateProductController`), su **Request** de validación (`CreateProductRequest`) cuando recibe datos, y su **UseCase** (`CreateProductUseCase`) con un único método público `execute()`. Prohibidos los resource controllers, los Requests compartidos entre acciones y los "services" con varios casos de uso.
6. **Value Objects** para todo concepto con reglas (`ProductName`, `Amount`, `Money`, `CurrencyCode`, `ExchangeRate`…). Las entidades nunca reciben primitivos.
7. Puertos pequeños y segregados por uso (escritura ≠ lectura).
8. Los límites entre capas y contextos, y las convenciones de naming, se **verifican con tests de arquitectura** (`arch()` de Pest); una violación rompe el build.
9. API versionada (`/api/v1`). Los contratos públicos no se rompen dentro de una versión.
10. **Proporcionalidad:** no se agrega bus, eventos ni CQRS completo sin un consumidor concreto. Lecturas complejas pueden ir por query services de Infraestructura.

## V. Calidad verificable

1. Tests con **Pest**: feature tests por endpoint (camino feliz + errores), unit tests para lógica de dominio.
2. **Larastan nivel máximo** sin baseline. **Pint** sin diferencias.
3. Un cambio está terminado sólo si `make check` (lint + análisis estático + tests) pasa en local y en CI.
4. `declare(strict_types=1);` en todos los archivos PHP de `src/` y `app/`, clases `final` por defecto.

## VI. Contratos de API consistentes

1. Todas las respuestas son JSON, incluidos los errores (middleware `ForceJsonResponse`).
2. Errores en formato **RFC 9457 Problem Details** (`application/problem+json`). (ADR-0005)
3. Colecciones siempre paginadas; filtros y ordenamiento sólo por campos en whitelist.
4. Códigos HTTP semánticos: `201` al crear, `204` al eliminar, `404`, `409`, `422`, `401`, `403`, `429`.

## VII. Documentación viva

1. La documentación OpenAPI se genera desde el código (Scramble). Si el código cambia, la doc cambia.
2. Toda decisión con alternativas reales se registra como ADR en `docs/adr/`.
3. Idioma: **documentación en español, código e identificadores en inglés.**
4. El README permite levantar el proyecto con **un solo comando** (`make setup`).
