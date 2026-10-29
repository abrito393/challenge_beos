# 002 — Products CRUD · Tareas

- **Plan:** [plan.md](./plan.md)

Leyenda: `[ ]` pendiente · `[~]` en curso · `[x]` hecha

| # | Tarea | AC | DoD | Estado |
|---|---|---|---|---|
| T-002-01 | Shared: mover `CurrencyId`/`InvalidCurrencyId`, crear `Money` y `Page` | — | Suite verde tras el movimiento | [x] |
| T-002-02 | Catalog Domain: VOs, `Product`, `ProductRepository`, excepciones + unit tests | AC-002-02 | Unit verde | [x] |
| T-002-03 | Catalog Application: read model, puertos de lectura, 5 use cases + unit tests | AC-002-11, 15 | Unit verde | [x] |
| T-002-04 | Persistence: migración con `CHECK`s e índices, `EloquentProduct`, factory, repositorio, finder, searcher, filtros | AC-002-08, 09 | Feature verde | [x] |
| T-002-05 | Http: 3 requests, 5 controllers, resource, rutas, provider | AC-002-01..16 | Feature verde | [x] |
| T-002-06 | Verificación, seeder de productos demo, docs y estado | — | `make check` verde | [x] |

## Notas de implementación

- **Verificación:** 16/16 ACs con test. `make check` verde (200 tests).
- **Mutation check:** sin escapar `%`/`_` en el filtro de nombre → 1 falla; hard delete en vez de soft → 1 falla; join a la columna equivocada → 12 fallan.
- **Smoke test real:** listado con orden y paginación, alta con `Location`, viewer → 403 al borrar, PUT cambia la divisa, DELETE → 204 y luego GET → 404 Problem Details.
- **Desvíos del plan:**
  - `CurrencyId` se movió al Shared kernel (`git mv`), junto con `InvalidCurrencyId`, para que Catalog referencie divisas sin importar `Currency\Domain`.
  - Los filtros de precio se agruparon en `PriceRangeFilter` (min y max son una sola regla de negocio).
  - Un fake de test que implementaba `ProductFinder` y `ProductRepository` a la vez provocó un fatal silencioso (exit 255): ambas interfaces declaran `find()` con retornos distintos. Que PHP lo rechace confirma que separar los puertos (ISP) era correcto.
  - `ProductSeeder` con 5 productos demo en distintas divisas (sólo fuera de producción).
