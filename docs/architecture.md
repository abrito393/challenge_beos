# Arquitectura

**DDD pragmático con arquitectura hexagonal por bounded context** ([ADR-0009](./adr/0009-pragmatic-ddd-hexagonal.md)). Las decisiones detrás de cada elección están en [`docs/adr/`](./adr/).

## Bounded contexts

```
┌──────────────────────────┐   Application    ┌──────────────────────────┐
│         Catalog          │ ───────────────► │         Currency         │
│ Product, ProductPrice    │  ConvertAmount   │ Currency, ExchangeRate,  │
│                          │                  │ CurrencyConverter        │
└────────────┬─────────────┘                  └────────────┬─────────────┘
             │                                             │
             └──────────────┐            ┌─────────────────┘
                            ▼            ▼
                  ┌────────────────────────────┐        ┌──────────────┐
                  │   Shared kernel            │        │     Auth     │
                  │ Money, DecimalAmount,      │        │ (Sanctum —   │
                  │ DomainException, Problem…  │        │  genérico)   │
                  └────────────────────────────┘        └──────────────┘
```

- `Catalog` consume `Currency` **sólo** a través de su capa `Application` (`ConvertAmount`), nunca de su `Domain`/`Infrastructure`.
- `Auth` es un subdominio genérico: casi todo es Infraestructura sobre Sanctum.

## Capas y regla de dependencia

```
        Infrastructure  ──►  Application  ──►  Domain
   (Eloquent, Http, Sanctum)   (UseCases,       (PHP puro: entidades, VOs,
                                Commands)        servicios, puertos)
```

Las dependencias apuntan **hacia adentro**. Se verifica en `tests/Architecture/` con `arch()` de Pest:

| Regla | Test |
|---|---|
| `Src\*\Domain` no usa `Illuminate`, `Application` ni `Infrastructure` | `DomainIsPureTest` |
| `Src\*\Application` no usa `Infrastructure` ni `Illuminate\Http` | `ApplicationLayerTest` |
| Ningún contexto usa `Domain`/`Infrastructure` de otro contexto | `ContextBoundariesTest` |
| Clases `final` y `strict_types` en `src/` | `CodeStyleTest` |
| Controllers invokables (sólo `__invoke`) con sufijo `Controller`; UseCases con un único método público `execute` | `SingleResponsibilityTest` |
| Value Objects `final readonly` | `ValueObjectsTest` |

**SOLID (ADR-0010):** una acción = un archivo por capa → `CreateProductController` + `CreateProductRequest` + `CreateProductUseCase` + `CreateProductCommand`.

## Flujo de un request (escritura)

```
Cliente ── Bearer token ──► Nginx ──► PHP-FPM
  │
  ├─ Middleware: ForceJsonResponse · SecurityHeaders · auth:sanctum · throttle
  │
  ├─ [Infra/Http]   CreateProductRequest      valida formato HTTP + authorize() (ability)
  ├─ [Infra/Http]   CreateProductController   __invoke: Request → CreateProductCommand
  ├─ [Application]  CreateProductUseCase      execute(Command)
  │     ├─ [Domain]   ProductName, ProductDescription, Money, TaxCost… (VOs)
  │     ├─ [Domain]   Product::create(...)    invariantes
  │     └─ [Domain]   ProductRepository (puerto)
  │            └─ [Infra/Persistence] EloquentProductRepository → EloquentProduct
  ├─ [Infra/Http]   ProductResource           serializa (montos como string)
  │
  └─ [Shared/Infra] ProblemDetailsRenderer    excepciones → RFC 9457
```

**Lecturas** (listado paginado con filtros): `ListProductsController → ListProductsUseCase → ProductSearcher (puerto) → EloquentProductSearcher → ProductView (read model) → Resource`. No se hidratan entidades de dominio (CQRS ligero, ADR-0009).

## Estructura de directorios

```
src/                                        namespace Src\
├── Catalog/
│   ├── Domain/
│   │   ├── Product.php                     entidad (raíz de agregado)
│   │   ├── ProductPrice.php                entidad del agregado Product
│   │   ├── ValueObjects/                   ProductId, ProductName, ProductDescription,
│   │   │                                   TaxCost, ManufacturingCost
│   │   ├── ProductRepository.php           puerto de escritura
│   │   └── Exceptions/                     ProductNotFound, PriceAlreadyExists,
│   │                                       PriceInBaseCurrency
│   ├── Application/
│   │   ├── CreateProduct/                  CreateProductUseCase + CreateProductCommand
│   │   ├── UpdateProduct/                  UpdateProductUseCase + UpdateProductCommand
│   │   ├── DeleteProduct/                  DeleteProductUseCase
│   │   ├── GetProduct/                     GetProductUseCase
│   │   ├── ListProducts/                   ListProductsUseCase + ListProductsQuery
│   │   │                                   + ProductSearcher (puerto lectura) + ProductView
│   │   ├── CreateProductPrice/             CreateProductPriceUseCase + CreateProductPriceCommand
│   │   └── ListProductPrices/              ListProductPricesUseCase
│   └── Infrastructure/
│       ├── Persistence/                    EloquentProduct, EloquentProductPrice,
│       │                                   EloquentProductRepository, EloquentProductSearcher
│       ├── Http/
│       │   ├── Controllers/                CreateProductController, UpdateProductController,
│       │   │                               DeleteProductController, GetProductController,
│       │   │                               ListProductsController, CreateProductPriceController,
│       │   │                               ListProductPricesController
│       │   ├── Requests/                   CreateProductRequest, UpdateProductRequest,
│       │   │                               ListProductsRequest, CreateProductPriceRequest
│       │   ├── Resources/                  ProductResource, ProductPriceResource
│       │   └── routes.php
│       └── CatalogServiceProvider.php      bindings puerto → adaptador
├── Currency/
│   ├── Domain/                             Currency, ValueObjects/ (CurrencyId, CurrencyCode,
│   │                                       CurrencyName, CurrencySymbol, ExchangeRate),
│   │                                       CurrencyConverter, CurrencyRepository
│   ├── Application/                        ConvertAmount/ConvertAmountUseCase,
│   │                                       ListCurrencies/ListCurrenciesUseCase
│   └── Infrastructure/                     EloquentCurrency, EloquentCurrencyRepository,
│                                           Http/ (ListCurrenciesController, CurrencyResource)
├── Auth/
│   ├── Domain/                             UserRole, TokenAbility (enums), ValueObjects/ (Email, DeviceName)
│   ├── Application/                        IssueToken/ (IssueTokenUseCase + IssueTokenCommand),
│   │                                       RevokeToken/RevokeTokenUseCase
│   └── Infrastructure/                     EloquentUser (Sanctum), Http/ (IssueTokenController,
│                                           IssueTokenRequest, RevokeTokenController,
│                                           GetAuthenticatedUserController)
└── Shared/
    ├── Domain/                             ValueObjects/ (Amount, Money), TransactionManager (puerto),
    │                                       Exceptions/ (DomainException, HasProblemDetails)
    └── Infrastructure/                     ProblemDetailsRenderer, ForceJsonResponse,
                                            SecurityHeaders, LaravelTransactionManager
app/                                        sólo bootstrap del framework (providers)
database/                                   migrations, factories, seeders
tests/
├── Architecture/                           reglas arch()
├── Unit/<Context>/                         dominio y aplicación, sin DB
└── Feature/Api/V1/<Recurso>/               endpoints end-to-end
```

## Modelo de datos

```
currencies                 products                         product_prices
──────────                 ────────                         ──────────────
id            PK           id                 PK            id           PK
code          CHAR(3) UQ   name               VARCHAR(255)  product_id   FK → products  CASCADE
name          UQ           description        TEXT          currency_id  FK → currencies RESTRICT
symbol                     price              DEC(15,2)     price        DEC(15,2)
exchange_rate DEC(18,8)    currency_id        FK → currencies RESTRICT
timestamps                 tax_cost           DEC(15,2)     UQ(product_id, currency_id)
                           manufacturing_cost DEC(15,2)     timestamps
                           timestamps, deleted_at
```

Índices adicionales: `products(currency_id)`, `products(price)`, `products(name)` para filtros y orden.

## Mapa de endpoints

| Método | Ruta | Ability | Contexto | Spec |
|---|---|---|---|---|
| POST | `/api/v1/auth/token` | pública | Auth | 004 |
| DELETE | `/api/v1/auth/token` | token | Auth | 004 |
| GET | `/api/v1/auth/me` | token | Auth | 004 |
| GET | `/api/v1/currencies` | `products:read` | Currency | 001 |
| GET | `/api/v1/products` | `products:read` | Catalog | 002 |
| POST | `/api/v1/products` | `products:write` | Catalog | 002 |
| GET | `/api/v1/products/{id}` | `products:read` | Catalog | 002 |
| PUT | `/api/v1/products/{id}` | `products:write` | Catalog | 002 |
| DELETE | `/api/v1/products/{id}` | `products:write` | Catalog | 002 |
| GET | `/api/v1/products/{id}/prices` | `products:read` | Catalog | 003 |
| POST | `/api/v1/products/{id}/prices` | `products:write` | Catalog | 003 |

> El challenge define las rutas como `/products`. Se exponen bajo `/api/v1` (prefijo estándar de Laravel + versionado) sin alterar su forma.
