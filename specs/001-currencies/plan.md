# 001 — Currencies · Plan técnico

- **Spec:** [spec.md](./spec.md)
- **ADRs relacionados:** [0003 Precisión monetaria](../../docs/adr/0003-money-precision.md), [0004 Tasas de cambio](../../docs/adr/0004-exchange-rate-semantics.md), [0009](../../docs/adr/0009-pragmatic-ddd-hexagonal.md), [0010](../../docs/adr/0010-solid-single-action-classes-and-value-objects.md)

## 1. Esquema de base de datos

`currencies`

| Columna | Tipo | Regla |
|---|---|---|
| `id` | BIGINT PK | |
| `code` | CHAR(3) | `UNIQUE` (ISO 4217) |
| `name` | VARCHAR(100) | `UNIQUE` (el challenge lo define como identificador único) |
| `symbol` | VARCHAR(10) | |
| `exchange_rate` | DECIMAL(18,8) | `CHECK (exchange_rate > 0)` |
| `timestamps` | | |

El `CHECK` se crea con `ALTER TABLE`, porque Blueprint no expone checks. Así la base rechaza tasas inválidas aunque se salte la aplicación (AC-001-06).

## 2. Componentes

| Contexto | Capa | Clase | Responsabilidad |
|---|---|---|---|
| Shared | Domain | `ValueObjects\Amount` | Decimal ≥ 0, máx. 2 decimales, sobre `BigDecimal`; `value(): string` (`"19.99"`) |
| Shared | Domain | `Exceptions\InvalidAmount` | `InvalidValue` |
| Currency | Domain | `ValueObjects\CurrencyId`, `CurrencyCode` (3 letras A–Z, mayúsculas), `CurrencyName` (1–100), `CurrencySymbol` (1–10), `ExchangeRate` (> 0, máx. 8 decimales) | Invariantes |
| Currency | Domain | `Currency` (entidad) | id, code, name, symbol, rate |
| Currency | Domain | `CurrencyConverter` (servicio) | `convert(Amount, Currency $from, Currency $to): Amount`. Fórmula `A × rate_to / rate_from` con un único redondeo `HALF_UP` a 2 decimales (RN-001-2/3) |
| Currency | Domain | `CurrencyRepository` (puerto) | `find(CurrencyId): ?Currency`, `all(): list<Currency>` |
| Currency | Domain | `Exceptions\CurrencyNotFound` (`NotFound`), `Invalid*` (`InvalidValue`) | |
| Currency | Application | `ListCurrencies\ListCurrenciesUseCase` | Lista ordenada por código |
| Currency | Application | `ConvertAmount\ConvertAmountUseCase` + `ConvertAmountCommand` | **API pública del contexto** para Catalog (spec 003). Entra y sale con primitivos (`string`/`int`), así ningún otro contexto importa `Currency\Domain` |
| Currency | Infra | `Persistence\EloquentCurrency`, `EloquentCurrencyRepository` | Mapeo Eloquent ↔ entidad |
| Currency | Infra | `Http\Controllers\ListCurrenciesController`, `Http\Resources\CurrencyResource`, `Http\routes.php` | `GET /currencies` con `auth:sanctum` + `abilities:products:read` + `throttle:api` |
| Currency | Infra | `CurrencyServiceProvider` | Binding del repositorio |
| — | Config | `config/currency.php` | `reference` = `env('CURRENCY_REFERENCE', 'USD')` |
| — | Seeder | `CurrencySeeder` | Idempotente (`updateOrCreate` por `code`). Verifica que la divisa de referencia tenga rate 1 (RN-001-1). Se ejecuta en todos los entornos, porque es dato de referencia |

## 3. Contrato

`GET /api/v1/currencies` → `200`
```json
{ "data": [ { "id": 2, "code": "EUR", "name": "Euro", "symbol": "€", "exchange_rate": "0.92000000" } ] }
```
Sin paginar: catálogo acotado (decenas de filas). Se documenta como decisión.

## 4. Mapeo AC → test

| AC | Tipo | Archivo |
|---|---|---|
| AC-001-01, 02 | Feature | `tests/Feature/Api/V1/Currencies/ListCurrenciesTest.php` |
| AC-001-03, 04, 05 | Unit | `tests/Unit/Currency/Domain/CurrencyConverterTest.php` (+ `ConvertAmountUseCaseTest`) |
| AC-001-06 | Feature (DB) | `tests/Feature/Currency/CurrencySchemaTest.php` |
| AC-001-07 | Feature | `tests/Feature/Currency/CurrencySeederTest.php` |

## Riesgos

| Riesgo | Mitigación |
|---|---|
| División con resultado periódico (ej. ÷ 0.92) | `dividedBy(..., scale: 2, HALF_UP)` en un único paso final; la multiplicación previa es exacta |
| `exchange_rate` llega como float desde algún driver | Cast `decimal:8` (string) en Eloquent y `ExchangeRate::fromString` |
