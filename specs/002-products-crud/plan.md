# 002 — Products CRUD · Plan técnico

- **Spec:** [spec.md](./spec.md)
- **ADRs relacionados:** [0003](../../docs/adr/0003-money-precision.md), [0009](../../docs/adr/0009-pragmatic-ddd-hexagonal.md), [0010](../../docs/adr/0010-solid-single-action-classes-and-value-objects.md)

## 1. Esquema de base de datos

`products`

| Columna | Tipo | Regla |
|---|---|---|
| `id` | BIGINT PK | |
| `name` | VARCHAR(255) | índice (filtro y orden) |
| `description` | TEXT | |
| `price` | DECIMAL(15,2) | `CHECK >= 0`, índice |
| `currency_id` | FK → `currencies.id` | `ON DELETE RESTRICT`, índice |
| `tax_cost` | DECIMAL(15,2) | `CHECK >= 0` |
| `manufacturing_cost` | DECIMAL(15,2) | `CHECK >= 0` |
| `created_at`, `updated_at` | | índice en `created_at` (orden) |
| `deleted_at` | | soft delete |

## 2. Componentes

### Shared kernel (movimientos)

- `CurrencyId` pasa de `Currency\Domain\ValueObjects` a `Shared\Domain\ValueObjects`, y `InvalidCurrencyId` a `Shared\Domain\Exceptions`. Es la identidad publicada de una divisa: Catalog la referencia sin depender del contexto Currency (DDD: referenciar otros agregados por id).
- Nuevo `Shared\Domain\ValueObjects\Money` (`Amount` + `CurrencyId`).
- Nuevo `Shared\Application\Page<T>`: resultado paginado sin depender de Illuminate.

### Catalog

| Capa | Clase | Responsabilidad |
|---|---|---|
| Domain | `ValueObjects\ProductId`, `ProductName` (1–255, trim), `ProductDescription` (1–2000, trim), `TaxCost`, `ManufacturingCost` (envuelven `Amount`) | Invariantes |
| Domain | `Product` (raíz de agregado) | `create(...)` (sin id), `fromPersistence(...)`, `update(...)` |
| Domain | `ProductRepository` (puerto escritura) | `find(ProductId): ?Product`, `save(Product): ProductId`, `delete(ProductId)` |
| Domain | `Exceptions\ProductNotFound` (`NotFound`), `InvalidProductId`, `InvalidProductName`, `InvalidProductDescription` (`InvalidValue`) | |
| Application | `ProductView`, `CurrencySummary` | Read model (primitivos, montos como string) |
| Application | `ProductFinder` (puerto lectura) | `find(int $id): ?ProductView` |
| Application | `ListProducts\ProductSearcher` (puerto lectura) + `ListProductsQuery` + `ProductSort` | Búsqueda paginada |
| Application | `CreateProduct\CreateProductUseCase` + `Command` | Construye VOs → `Product::create` → `save` → devuelve `ProductView` |
| Application | `UpdateProduct\UpdateProductUseCase` + `Command` | `find` o `ProductNotFound` → `update` → `save` |
| Application | `DeleteProduct\DeleteProductUseCase` + `Command` | `find` o `ProductNotFound` → `delete` (soft) |
| Application | `GetProduct\GetProductUseCase` + `Query` | `ProductFinder` o `ProductNotFound` |
| Application | `ListProducts\ListProductsUseCase` | Delegación al `ProductSearcher` |
| Infra | `Persistence\EloquentProduct` (SoftDeletes, casts `decimal:2`) + `ProductFactory` | |
| Infra | `Persistence\EloquentProductRepository` | Mapeo agregado ↔ Eloquent |
| Infra | `Persistence\EloquentProductFinder`, `EloquentProductSearcher`, `ProductViewMapper` | Una sola consulta con `JOIN currencies` (sin N+1, AC-002-09). Sin importar clases de Currency: el join es por tabla |
| Infra | `Persistence\Filters\ProductFilter` + `NameFilter`, `CurrencyFilter`, `MinPriceFilter`, `MaxPriceFilter` | Filtros como estrategias (O/C) |
| Infra | `Http\Controllers\{List,Create,Get,Update,Delete}ProductController` | Invokables |
| Infra | `Http\Requests\{ListProducts,CreateProduct,UpdateProduct}Request` | Uno por acción |
| Infra | `Http\Resources\ProductResource` | Serializa `ProductView` |
| Infra | `CatalogServiceProvider` | Bindings |

## 3. Contratos

### Validación de escritura (`CreateProductRequest` = `UpdateProductRequest`; PUT es reemplazo completo, AC-002-13)
```php
'name'               => ['required', 'string', 'max:255'],
'description'        => ['required', 'string', 'max:2000'],
'price'              => ['required', 'numeric', 'min:0', 'decimal:0,2', 'max:9999999999999.99'],
'currency_id'        => ['required', 'integer', 'exists:currencies,id'],
'tax_cost'           => ['required', 'numeric', 'min:0', 'decimal:0,2', 'max:9999999999999.99'],
'manufacturing_cost' => ['required', 'numeric', 'min:0', 'decimal:0,2', 'max:9999999999999.99'],
```
Son dos clases separadas aunque hoy tengan las mismas reglas (ADR-0010): evolucionan por separado.

### Listado (`ListProductsRequest`)
```php
'filter'              => ['sometimes', 'array:name,currency_id,min_price,max_price'], // claves desconocidas → 422
'filter.name'         => ['sometimes', 'string', 'max:255'],
'filter.currency_id'  => ['sometimes', 'integer', 'min:1'],
'filter.min_price'    => ['sometimes', 'numeric', 'min:0'],
'filter.max_price'    => ['sometimes', 'numeric', 'min:0', 'gte:filter.min_price'],
'sort'                => ['sometimes', Rule::in(ProductSort::values())], // name, -name, price, -price, created_at, -created_at
'page'                => ['sometimes', 'integer', 'min:1'],
'per_page'            => ['sometimes', 'integer', 'min:1', 'max:100'],
```
Orden por defecto: `-created_at`, con desempate por `id`. Los filtros de precio comparan `products.price` en la divisa base de cada producto (documentado en OpenAPI).

### Respuestas
- `POST` → `201` + `Location: /api/v1/products/{id}`. `GET`/`PUT` → `200`. `DELETE` → `204`.
- Listado → `data` + `links` + `meta` (paginador de Laravel construido en el controller a partir de `Page`).
- `{product}` restringido a numérico (`whereNumber`). Inexistente o eliminado → `404 /problems/product-not-found`.

## 4. Mapeo AC → test

| AC | Archivo |
|---|---|
| 01, 02, 03, 16 | `tests/Feature/Api/V1/Products/CreateProductTest.php` |
| 04–09 | `.../ListProductsTest.php` |
| 10, 11 | `.../GetProductTest.php` |
| 12, 13, 16 | `.../UpdateProductTest.php` |
| 14, 15, 16 | `.../DeleteProductTest.php` |
| Invariantes | `tests/Unit/Catalog/Domain/*`, `tests/Unit/Catalog/Application/*` |

## Riesgos

| Riesgo | Mitigación |
|---|---|
| El read model hace join con una tabla de otro contexto | Es una proyección de lectura en Infraestructura, por nombre de tabla y sin importar clases de Currency. La regla `arch()` lo verifica |
| Desfase entre `ProductView` y lo persistido tras escribir | Los use cases de escritura releen vía `ProductFinder` (una consulta extra; aceptable) |
