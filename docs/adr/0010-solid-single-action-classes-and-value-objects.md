# ADR-0010: SOLID: clases de una sola acción y Value Objects

- **Estado:** Accepted
- **Fecha:** 2026-10-29
- **Complementa a:** [ADR-0009](./0009-pragmatic-ddd-hexagonal.md)
- **Specs relacionadas:** todas

## Contexto

Con DDD hexagonal ya definido (ADR-0009), falta fijar la **granularidad** de las clases. Los controllers resource de Laravel (`ProductController` con `index/store/show/update/destroy`) agrupan 5 razones de cambio en una clase, y lo mismo pasa con un `ProductService` con muchos métodos. Además, representar `name`, `price` o `exchange_rate` como `string` o `float` sueltos permite estados inválidos dentro del dominio (*primitive obsession*).

## Alternativas consideradas

1. **Resource controllers + un FormRequest compartido + services con varios métodos:** menos archivos, pero varias responsabilidades por clase, reglas de validación con `if ($this->isMethod('PUT'))` y diffs que mezclan casos de uso.
2. **Una clase por acción en cada capa + Value Objects:** más archivos, cada uno con una única razón de cambio.

## Decisión

Usaremos la opción 2, aplicando SOLID de forma explícita.

### S: Single Responsibility (una acción = un archivo por capa)

Cada endpoint tiene su propio **Controller invokable**, su propio **Request** y su propio **UseCase**:

| Endpoint | Controller | Request | UseCase | Command (input) |
|---|---|---|---|---|
| `GET /products` | `ListProductsController` | `ListProductsRequest` | `ListProductsUseCase` | `ListProductsQuery` |
| `POST /products` | `CreateProductController` | `CreateProductRequest` | `CreateProductUseCase` | `CreateProductCommand` |
| `GET /products/{id}` | `GetProductController` | — | `GetProductUseCase` | — (recibe `ProductId`) |
| `PUT /products/{id}` | `UpdateProductController` | `UpdateProductRequest` | `UpdateProductUseCase` | `UpdateProductCommand` |
| `DELETE /products/{id}` | `DeleteProductController` | — | `DeleteProductUseCase` | — |
| `GET /products/{id}/prices` | `ListProductPricesController` | — | `ListProductPricesUseCase` | — |
| `POST /products/{id}/prices` | `CreateProductPriceController` | `CreateProductPriceRequest` | `CreateProductPriceUseCase` | `CreateProductPriceCommand` |
| `GET /currencies` | `ListCurrenciesController` | — | `ListCurrenciesUseCase` | — |
| `POST /auth/token` | `IssueTokenController` | `IssueTokenRequest` | `IssueTokenUseCase` | `IssueTokenCommand` |
| `DELETE /auth/token` | `RevokeTokenController` | — | `RevokeTokenUseCase` | — |
| `GET /auth/me` | `GetAuthenticatedUserController` | — | — (sólo lectura del guard) | — |

- Los endpoints sin body ni query params no tienen Request propio: un Request vacío sería una clase sin responsabilidad. La autorización de esos endpoints va por middleware `abilities:` en la ruta.
- Cada UseCase tiene **un único método público** `execute(...)`.

### O: Open/Closed
- El `ProblemDetailsRenderer` no crece con cada error nuevo. Las excepciones de dominio extienden una **categoría** abstracta del Shared kernel (`NotFound`, `Conflict`, `InvalidValue`, `AuthenticationFailed`) y declaran un `errorCode()` estable. El renderer mapea categoría → status HTTP y usa `errorCode()` como `type` (`/problems/price-already-exists`). Un error nuevo es una clase nueva, sin modificar el renderer. Además, el dominio no conoce códigos HTTP. *(Enmienda 2026-10-29, spec 004: reemplaza la idea inicial de una interfaz `HasProblemDetails` con `status()`, que filtraba HTTP al dominio.)*
- Los filtros del listado son una lista de clases `ProductFilter` registradas, no `if` encadenados.

### L: Liskov
- Los adaptadores (`EloquentProductRepository`, `InMemoryProductRepository` de tests) cumplen el mismo contrato y los mismos tests de contrato.

### I: Interface Segregation
- Puertos pequeños y por uso: `ProductRepository` (escritura: `find`, `save`, `delete`) separado de `ProductSearcher` (lectura paginada). Un caso de uso de lectura no depende de métodos de escritura.

### D: Dependency Inversion
- Los UseCases dependen de interfaces del dominio/aplicación. Los bindings a Eloquent/Sanctum viven en el ServiceProvider de cada contexto.

### Value Objects

Todo concepto con reglas propias es un VO `final readonly`, validado en su constructor e inmutable:

| Contexto | Value Objects |
|---|---|
| Shared | `Amount` (decimal ≥ 0, escala 2, sobre `BigDecimal`), `Money` (`Amount` + `CurrencyId`), `CurrencyId` (identidad publicada de una divisa) |
| Catalog | `ProductId`, `ProductName` (1–255, trim), `ProductDescription` (1–2000), `TaxCost`, `ManufacturingCost` |
| Currency | `CurrencyCode` (ISO 4217, 3 letras mayúsculas), `CurrencyName`, `CurrencySymbol`, `ExchangeRate` (> 0, escala 8) |
| Auth | `Email`, `DeviceName` |

- Las entidades sólo aceptan VOs, nunca primitivos.
- El Request valida el **formato HTTP** y devuelve 422 con mensajes por campo. El VO protege el **invariante del dominio** ante cualquier entrada, sea HTTP, seeder o test. Son dos responsabilidades distintas y no se duplica lógica de negocio: el Request no conoce reglas como "no puede ser la divisa base".

## Consecuencias

- ✅ Cada archivo tiene una razón de cambio. Los diffs y las revisiones quedan acotados a un caso de uso.
- ✅ Estados inválidos irrepresentables dentro del dominio.
- ✅ Los tests unitarios de VOs y UseCases son triviales y rápidos.
- ⚠️ Más archivos (≈ 3–5 por endpoint). Se mitiga con una estructura por caso de uso predecible.
- ⚠️ Algunas reglas de formato (ej. longitud de `name`) aparecen en el Request y en el VO. Es duplicación aceptada: cada capa protege su propia frontera.
