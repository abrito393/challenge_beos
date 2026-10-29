# 003 — Product prices · Tareas

- **Plan:** [plan.md](./plan.md)

Leyenda: `[ ]` pendiente · `[~]` en curso · `[x]` hecha

| # | Tarea | AC | DoD | Estado |
|---|---|---|---|---|
| T-003-01 | Domain: `ProductPrice`, `ProductPriceId`, `Product::addPriceIn`/`prices`, `PriceAlreadyExists`, `PriceInBaseCurrency` + unit tests | AC-003-06, 07 | Unit verde | [x] |
| T-003-02 | Application: puertos `PriceConverter`/`ProductPriceFinder`, `ProductPriceView`, 2 use cases + unit tests con fakes | AC-003-04, 05 | Unit verde | [x] |
| T-003-03 | Persistence: migración, `EloquentProductPrice`, repositorio con precios + traducción de `UNIQUE`, finder | AC-003-09 | Feature verde | [x] |
| T-003-04 | ACL `CurrencyContextPriceConverter` + Http (request, 2 controllers, resource, rutas) | AC-003-01..10 | Feature verde | [x] |
| T-003-05 | Verificación, seeder demo, docs, estado | — | `make check` verde | [x] |

## Notas de implementación

- **Verificación:** 10/10 ACs con test. `make check` verde (227 tests), cobertura total 98,2%.
- **Mutation check:** permitir la divisa base → 3 fallan; no traducir la violación `UNIQUE` en carrera → 1 falla (AC-003-09).
- **Concurrencia (AC-003-09):** el test carga el agregado (la regla de dominio pasa), inserta el mismo precio por SQL simulando otra request y verifica que `save()` lanza `PriceAlreadyExists` y que sólo queda el precio ganador.
- **Smoke test real:** precio calculado COP `1299.99 × 4100 = 5329959.00` exacto, duplicado → 409, divisa base → 422.
- **Desvíos del plan:** ninguno de diseño. `ProductSeeder` agrega precios EUR y MXN al producto demo para que el endpoint de precios tenga datos.
