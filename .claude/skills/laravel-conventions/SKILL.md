---
name: laravel-conventions
description: Convenciones de código de este proyecto Laravel con DDD hexagonal (bounded contexts en src/, capas Domain/Application/Infrastructure, dinero, validación, errores, tests). Cargar antes de escribir o revisar cualquier código PHP en src/, app/, routes/, database/ o tests/.
---

# Convenciones del proyecto (Laravel 12 + DDD hexagonal)

Stack: PHP 8.3 · Laravel 12 · MySQL 8.4 · Pest · Larastan max · Pint. Arquitectura: **ADR-0009** y `docs/architecture.md`.

## Reglas generales
- `declare(strict_types=1);` en todos los archivos. Clases `final` salvo necesidad de herencia.
- Tipos explícitos en todo. Generics de Larastan en relaciones y factories.
- Namespace `Src\<Context>\<Layer>\...` mapeado en `composer.json` (`"Src\\": "src/"`).
- Sin strings mágicos: abilities, roles y tipos de problema en Enums.

## SOLID: una acción = un archivo por capa (ADR-0010)

Para cada endpoint se crean, con el mismo prefijo `<Acción><Entidad>`:

```
Infrastructure/Http/Controllers/CreateProductController.php   final, sólo __invoke()
Infrastructure/Http/Requests/CreateProductRequest.php         sólo si el endpoint recibe body/query
Application/CreateProduct/CreateProductUseCase.php            un único método público execute()
Application/CreateProduct/CreateProductCommand.php            final readonly, input del caso de uso
```

**Prohibido:** `Route::apiResource` / resource controllers, un Request compartido entre acciones (`ProductRequest` con `if ($this->isMethod(...))`), clases `*Service` con varios casos de uso y métodos públicos extra en UseCases.

```php
// Controller: sólo traduce HTTP ↔ aplicación
final class CreateProductController
{
    public function __invoke(CreateProductRequest $request, CreateProductUseCase $useCase): JsonResponse
    {
        $product = $useCase->execute($request->toCommand());

        return ProductResource::make($product)
            ->response()
            ->setStatusCode(Response::HTTP_CREATED)
            ->header('Location', route('products.show', $product->id()->value()));
    }
}
```

## Value Objects (ADR-0010)
- Todo concepto con reglas es un VO `final readonly` en `Domain/ValueObjects/`: se valida en el constructor (lanza excepción de dominio) y expone `value()` y `equals()`.
- Las entidades reciben **sólo VOs**. El UseCase construye los VOs a partir del Command.
- Catálogo: `Amount`, `Money`, `CurrencyId` (Shared) · `ProductId`, `ProductName`, `ProductDescription`, `TaxCost`, `ManufacturingCost` (Catalog) · `CurrencyCode`, `CurrencyName`, `CurrencySymbol`, `ExchangeRate` (Currency) · `Email`, `DeviceName` (Auth).

## ¿Dónde va cada cosa?

| Qué | Dónde | Reglas |
|---|---|---|
| Entidad / raíz de agregado | `src/<Ctx>/Domain/` | Constructor privado + named constructors (`create`, `fromPrimitives`). Protege invariantes y lanza excepciones de dominio. Sin setters públicos: métodos con intención (`changePrice`, `addPriceIn`) |
| Value Object | `src/<Ctx>/Domain/ValueObjects/` o `src/Shared/Domain/ValueObjects/` | `final readonly`, se valida en el constructor, `value()` + `equals()` |
| Servicio de dominio | `src/<Ctx>/Domain/` | Sin estado, sin framework (ej. `CurrencyConverter`) |
| Puerto (repositorio) | `src/<Ctx>/Domain/<X>Repository.php` | Interface. Firma en términos de dominio (`save(Product)`, `find(ProductId): ?Product`) |
| Excepción de dominio | `src/<Ctx>/Domain/Exceptions/` | Extiende `Src\Shared\Domain\DomainException`. El renderer la mapea a Problem Details |
| Caso de uso | `src/<Ctx>/Application/<Acción><Entidad>/` | `<Acción><Entidad>UseCase` (único método público `execute()`) + `<Acción><Entidad>Command` (`final readonly`, primitivos/strings) |
| Puerto de lectura + read model | `src/<Ctx>/Application/List<X>/` | `<X>Searcher` (interface) + `<X>View` (`final readonly`) |
| Comunicación entre contextos | `src/<Ctx>/Application/` del contexto proveedor | Ej. `Src\Currency\Application\ConvertAmount`. Nunca importar Domain/Infra de otro contexto |
| Modelo Eloquent | `src/<Ctx>/Infrastructure/Persistence/Eloquent<X>.php` | `$fillable` explícito, `casts()`, relaciones tipadas. **Sin** lógica de negocio |
| Repositorio | `src/<Ctx>/Infrastructure/Persistence/Eloquent<X>Repository.php` | Mapea Eloquent ↔ entidad (`toDomain()` / `fromDomain()`) |
| Searcher (lecturas) | `src/<Ctx>/Infrastructure/Persistence/Eloquent<X>Searcher.php` | Implementa el puerto de lectura: paginación, filtros y orden con whitelist → `<X>View` |
| Controller | `src/<Ctx>/Infrastructure/Http/Controllers/<Acción><Entidad>Controller.php` | Invokable, sólo `__invoke()`: `$request->toCommand()` → `$useCase->execute()` → Resource |
| FormRequest | `src/<Ctx>/Infrastructure/Http/Requests/<Acción><Entidad>Request.php` | **Uno por acción.** `rules()`, `authorize()` (ability) y `toCommand(): <Acción><Entidad>Command` |
| Resource | `src/<Ctx>/Infrastructure/Http/Resources/` | Montos como string, fechas ISO-8601 UTC |
| Rutas | `src/<Ctx>/Infrastructure/Http/routes.php` | Una línea por controller invokable: `Route::post('products', CreateProductController::class)->name('products.store')->middleware('abilities:products:write')`. `auth:sanctum` + `throttle:api` en el grupo |
| Bindings | `src/<Ctx>/Infrastructure/<Ctx>ServiceProvider.php` | `$this->app->bind(Port::class, Adapter::class)`. Registrado en `bootstrap/providers.php` |

## Prohibido en `Domain/`
`Illuminate\*`, facades, helpers globales de Laravel (`now()`, `config()`, `collect()`), Eloquent y `Carbon` de Laravel. Si el dominio necesita algo externo (reloj, config), se define un puerto. Lo verifica `tests/Architecture`.

## Transacciones
Puerto `Src\Shared\Domain\TransactionManager` con implementación Laravel en `Shared/Infrastructure`. Los handlers que escriben varias filas lo usan.

## Dinero (ADR-0003)
- VO `Src\Shared\Domain\Money` (`BigDecimal` + scale 2) para montos. `ExchangeRate` (scale 8) en Currency.
- Validación HTTP: `['required', 'numeric', 'min:0', 'decimal:0,2']`.
- Eloquent casts: `'price' => 'decimal:2'`, `'exchange_rate' => 'decimal:8'`.
- Prohibido `(float)`, `floatval`, `round()` y operadores `* /` sobre montos.

## Errores
Nunca construir respuestas de error en controllers. Cada error de dominio es una clase `final` que extiende una **categoría** de `Src\Shared\Domain\Exceptions` y define `errorCode()` (kebab-case), con un named constructor:

| Categoría | HTTP | Ejemplo |
|---|---|---|
| `NotFound` | 404 | `ProductNotFound::withId($id)` |
| `Conflict` | 409 | `PriceAlreadyExists::forCurrency($code)` |
| `InvalidValue` | 422 | `InvalidEmail::because($value)` |
| `AuthenticationFailed` | 401 | `InvalidCredentials::create()` |

El `ProblemDetailsRenderer` las traduce sin conocerlas una por una (ADR-0005, ADR-0010). Los mensajes de dominio se escriben para el cliente, porque se exponen en `detail`. La violación de `UNIQUE` en la DB también se mapea a 409 (concurrencia).

## Abilities
En las rutas, nunca strings sueltos: `'abilities:'.TokenAbility::ProductsWrite->value` (`Src\Shared\Domain\Security\TokenAbility`).

## Base de datos
- Migraciones en `database/migrations` con FKs explícitas (`restrictOnDelete` / `cascadeOnDelete`), índices y `UNIQUE`.
- Factories apuntan a los modelos Eloquent de Infraestructura. Seeders idempotentes (`updateOrCreate`).
- `Model::shouldBeStrict()` fuera de producción (detecta N+1 y atributos inexistentes).

## Tests (Pest)
| Tipo | Ubicación | Qué prueba |
|---|---|---|
| Architecture | `tests/Architecture/` | Reglas de dependencia (ADR-0009) |
| Unit | `tests/Unit/<Ctx>/Domain`, `.../Application` | Entidades, VOs, UseCases con repos **in-memory**. Sin DB ni framework |
| Feature | `tests/Feature/Api/V1/<Recurso>/` | HTTP end-to-end sobre MySQL de testing |

- Nombre: `it('AC-002-03: returns 404 for a missing product')`.
- Helpers en `tests/Pest.php`: `actingAsAdmin()`, `actingAsViewer()`.
- Errores: `assertHeader('Content-Type', 'application/problem+json')`.

## Commits
Conventional Commits con contexto y tarea: `feat(catalog): add product price in another currency (T-003-04)`.
