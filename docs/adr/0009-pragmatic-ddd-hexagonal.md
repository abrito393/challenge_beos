# ADR-0009: DDD pragmático con arquitectura hexagonal por bounded context

- **Estado:** Accepted
- **Fecha:** 2026-10-29
- **Reemplaza a:** [ADR-0002](./0002-layered-architecture-with-actions.md)
- **Specs relacionadas:** todas

## Contexto

El ADR-0002 eligió capas ligeras al estilo Laravel (Actions + Eloquent directo) por proporcionalidad. Al revisar el problema con más detalle:

- El dominio tiene **lógica real que merece aislarse**: aritmética monetaria exacta, conversión cruzada entre divisas, invariantes (un precio por divisa, no duplicar la divisa base, montos no negativos).
- El enunciado pide demostrar "conocimientos y estructuras que conozcas", no sólo el mínimo.
- El equipo (autor) tiene experiencia sólida en DDD (Dominio / Aplicación / Infraestructura).
- Requisito duro: **"La API debe utilizar Eloquent"**.

## Alternativas consideradas

1. **Capas Laravel (ADR-0002):** idiomático y con poco código, pero el dominio queda acoplado al framework y las reglas se reparten entre modelos y Actions.
2. **DDD estricto:** agregados puros, eventos de dominio, command/query bus, mappers dedicados. Máxima pureza, pero desproporcionado para 3 entidades (mucho *boilerplate* sin beneficio observable).
3. **DDD pragmático hexagonal:** dominio puro con lo que aporta valor (entidades, Value Objects, servicios de dominio, puertos), sin bus ni eventos que nadie consume.

## Decisión

Usaremos la opción 3.

### Bounded contexts (`src/`, namespace `Src\`)

| Contexto | Responsabilidad |
|---|---|
| `Catalog` | Productos y sus precios por divisa |
| `Currency` | Catálogo de divisas, tasas y conversión |
| `Auth` | Identidad y tokens. Es un subdominio genérico, así que vive casi todo en Infraestructura (Sanctum) |
| `Shared` | Shared kernel: `Money`, `DecimalAmount`, excepciones base, Problem Details |

### Capas por contexto

```
Domain/          PHP puro. Entidades, Value Objects, servicios de dominio,
                 interfaces de repositorio (puertos), excepciones de dominio.
Application/     Casos de uso: <Acción><Entidad>UseCase + su <Acción><Entidad>Command
                 (DTO readonly). Orquesta dominio y puertos; transacción vía puerto.
                 Granularidad y naming: ADR-0010.
Infrastructure/  Adaptadores: Persistence/ (modelos Eloquent + repositorios),
                 Http/ (controllers invokables, Requests por acción, Resources),
                 <Context>ServiceProvider (bindings puerto → adaptador).
```

### Reglas de dependencia (verificadas con `arch()` de Pest)

- `Domain` no depende de `Application`, `Infrastructure` ni `Illuminate\*`. Sólo puede depender de `Shared\Domain` y `Brick\Math`.
- `Application` no depende de `Infrastructure` ni de `Illuminate\Http`.
- Un contexto no importa el `Domain`/`Infrastructure` de otro. La comunicación entre contextos se hace por su capa `Application` (ej. `Catalog` usa `Currency\Application\ConvertAmount`) o por el `Shared` kernel.
- `app/` sólo contiene lo que exige el framework (providers, `User` de Sanctum si aplica). No contiene lógica de negocio.

### Pragmatismo explícito

- **Escritura → dominio; lectura → puerto de lectura.** Los comandos (crear/actualizar/agregar precio) pasan por entidades y `ProductRepository`. Los listados paginados y filtrados pasan por `ListProductsUseCase` → puerto `ProductSearcher`, implementado con Eloquent, que devuelve *read models* (`ProductView`) sin hidratar entidades. Es CQRS ligero sin bus.
- **Eloquent sólo en Infraestructura.** Los repositorios mapean el modelo Eloquent ↔ la entidad de dominio. Así se cumple el requisito del challenge sin acoplar el dominio.
- **Sin event bus ni eventos de dominio**: no hay consumidores. Se agregarían cuando existan.
- **Validación en dos niveles:** el FormRequest valida forma y tipos (HTTP) y las entidades/VOs protegen los invariantes. Los mensajes al cliente salen del FormRequest.

## Consecuencias

- ✅ El dominio se testea con unit tests puros (sin DB ni framework) y en milisegundos.
- ✅ Los límites se **verifican** automáticamente con tests de arquitectura, no dependen de la disciplina de cada dev.
- ✅ Eloquent podría reemplazarse por otro adaptador sin tocar Domain/Application.
- ⚠️ Más archivos y un mapeo entidad ↔ modelo Eloquent. Es un costo aceptado y acotado a 3 repositorios.
- ⚠️ Menos "Laravel idiomático" para devs sin experiencia en DDD. Se mitiga con `docs/architecture.md` y el skill `laravel-conventions`.
- 🔁 Si el dominio creciera (inventario, órdenes), se evaluaría agregar eventos de dominio y un bus.
