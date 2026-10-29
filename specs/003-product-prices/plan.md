# 003 — Product prices · Plan técnico

- **Spec:** [spec.md](./spec.md)
- **ADRs relacionados:** [0003](../../docs/adr/0003-money-precision.md), [0004](../../docs/adr/0004-exchange-rate-semantics.md), [0009](../../docs/adr/0009-pragmatic-ddd-hexagonal.md), [0010](../../docs/adr/0010-solid-single-action-classes-and-value-objects.md)

## 1. Esquema de base de datos

`product_prices`

| Columna | Tipo | Regla |
|---|---|---|
| `id` | BIGINT PK | |
| `product_id` | FK → `products.id` | `ON DELETE CASCADE` |
| `currency_id` | FK → `currencies.id` | `ON DELETE RESTRICT` |
| `price` | DECIMAL(15,2) | `CHECK >= 0` |
| `timestamps` | | |
| | | `UNIQUE(product_id, currency_id)`: garantía final ante concurrencia (AC-003-09) |

## 2. Componentes

| Capa | Clase | Responsabilidad |
|---|---|---|
| Domain | `ProductPrice` (entidad del agregado `Product`) + `ValueObjects\ProductPriceId` | Precio (`Money`) en otra divisa |
| Domain | `Product::addPriceIn(Money): ProductPrice` | Invariantes RN-003-1 (uno por divisa → `PriceAlreadyExists`, `Conflict`/409) y RN-003-2 (no en la divisa base → `PriceInBaseCurrency`, `InvalidValue`/422) |
| Domain | `Product::prices()` | El agregado carga sus precios (son pocos por producto) |
| Application | `CreateProductPrice\CreateProductPriceUseCase` + `Command` | `find` o 404 → precio explícito o `PriceConverter` → `addPriceIn` → `save` → `ProductPriceView` |
| Application | `CreateProductPrice\PriceConverter` (puerto) | `convert(Money $from, CurrencyId $to): Amount`. Catalog no conoce cómo se convierte |
| Application | `ListProductPrices\ListProductPricesUseCase` + `Query` | Valida que el producto exista y lista sus precios |
| Application | `ProductPriceFinder` (puerto lectura) + `ProductPriceView` | `forProduct(ProductId)`, `find(ProductId, CurrencyId)` |
| Infra | `Currency\CurrencyContextPriceConverter` | **Anti-corruption layer**: adapta el puerto a `Currency\Application\ConvertAmountUseCase` (primitivos) |
| Infra | `Persistence\EloquentProductPrice`, relación `EloquentProduct::prices()` | |
| Infra | `EloquentProductRepository` | `find` con `with('prices')`; `save` en transacción e inserta precios nuevos. Traduce la violación de `UNIQUE` a `PriceAlreadyExists` (misma respuesta 409 en carrera que en caso normal) |
| Infra | `Persistence\EloquentProductPriceFinder` | `JOIN currencies`, orden por código |
| Infra | `Http\Controllers\ListProductPricesController`, `CreateProductPriceController`, `Http\Requests\CreateProductPriceRequest`, `Http\Resources\ProductPriceResource` | |

## 3. Contratos

`POST /api/v1/products/{id}/prices`
```php
'currency_id' => ['required', 'integer', 'exists:currencies,id'],
'price'       => ['sometimes', 'nullable', 'numeric', 'min:0', 'decimal:0,2', 'max:9999999999999.99'],
```
`201`:
```json
{ "data": { "id": 7, "product_id": 1, "price": "92.00", "currency": { "id": 2, "code": "EUR", "name": "Euro", "symbol": "€" }, "created_at": "…", "updated_at": "…" } }
```
- `409 /problems/price-already-exists`, `422 /problems/price-in-base-currency`, `404 /problems/product-not-found`.

`GET /api/v1/products/{id}/prices` → `200 { "data": [ … ] }`, ordenado por código de divisa, sin paginar (máximo un precio por divisa).

## 4. Mapeo AC → test

| AC | Archivo |
|---|---|
| 01, 02, 03 | `tests/Feature/Api/V1/ProductPrices/ListProductPricesTest.php` |
| 03–08, 10 | `.../CreateProductPriceTest.php` |
| 09 | `tests/Feature/Catalog/ProductPriceConcurrencyTest.php` (carrera simulada a nivel repositorio) |
| Invariantes | `tests/Unit/Catalog/Domain/ProductPricesTest.php`, `tests/Unit/Catalog/Application/ProductPriceUseCasesTest.php` |
