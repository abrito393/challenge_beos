# 001 — Currencies · Tareas

- **Plan:** [plan.md](./plan.md)

Leyenda: `[ ]` pendiente · `[~]` en curso · `[x]` hecha

| # | Tarea | AC | DoD | Estado |
|---|---|---|---|---|
| T-001-01 | Shared: VO `Amount` + `InvalidAmount` + unit tests | — | Unit verde | [x] |
| T-001-02 | Currency Domain: VOs, entidad, `CurrencyConverter`, puerto, excepciones + unit tests | AC-001-03, 04, 05 | Unit verde | [x] |
| T-001-03 | Currency Application: `ListCurrenciesUseCase`, `ConvertAmountUseCase` + unit tests in-memory | AC-001-03 | Unit verde | [x] |
| T-001-04 | Persistence: migración con `CHECK`, `EloquentCurrency`, factory, repositorio, `CurrencySeeder`, `config/currency.php` | AC-001-06, 07 | Feature verde | [x] |
| T-001-05 | Http: controller, resource, rutas, provider | AC-001-01, 02 | Feature verde | [x] |
| T-001-06 | Verificación (`/sdd-verify`), docs y estado | — | `make check` verde | [x] |

## Notas de implementación

- **Verificación:** 7/7 ACs con test; `make check` verde (139 tests).
- **Mutation check:** truncar en vez de redondear HALF_UP → 1 test falla; tasa invertida (`× from / to`) → 4 tests fallan.
- **Desvíos del plan:**
  - `brick/math` 0.14 depreca `stripTrailingZeros()` → `strippedOfTrailingZeros()`.
  - La factory genera tasas como string (`sprintf`), no con `randomFloat`: ni siquiera los datos de prueba pasan por float.
  - El `CHECK` se valida con un test que inserta directo con `DB::table()` y se salta la aplicación (AC-001-06).
