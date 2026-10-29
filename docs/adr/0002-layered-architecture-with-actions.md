# ADR-0002: Arquitectura en capas con Actions y DTOs (sin repositorios)

- **Estado:** Superseded by [ADR-0009](./0009-pragmatic-ddd-hexagonal.md)
- **Fecha:** 2026-10-29

> Se conserva como registro histórico. Al revisarla se priorizó aislar el dominio monetario y demostrar límites verificables; ver ADR-0009.

## Contexto

El challenge pide código "organizado y fácil de entender" y mostrar conocimientos de Laravel más allá del mínimo. Hay que elegir dónde vive la lógica de negocio. El dominio es pequeño: 3 entidades y 1 regla de conversión.

## Alternativas consideradas

1. **MVC clásico (lógica en controllers):** mínimo código, pero mezcla HTTP con negocio y es difícil de testear aislado.
2. **Hexagonal / DDD completo (puertos, adaptadores, repositorios con interfaces):** desacople máximo, pero sobre-ingeniería para 3 entidades. Envolver Eloquent en repositorios duplica una abstracción que Eloquent ya provee.
3. **Capas ligeras: Controller → FormRequest → DTO → Action → Eloquent → Resource:** separa responsabilidades usando las piezas idiomáticas de Laravel.

## Decisión

Usaremos la opción 3:

```
HTTP     Controller (delgado) · FormRequest (validación + authorize) · Resource (serialización)
App      Action (un caso de uso, un método __invoke/handle) · DTO readonly
Domain   Model Eloquent · Service de dominio (CurrencyConverter) · Enum
Infra    Migrations · Policies · Middleware · Exception renderer
```

- Las Actions se inyectan por contenedor y son testeables sin HTTP.
- Las consultas complejas (filtros del listado) viven en un **query object** / scopes del modelo, no en el controller.
- **No** usaremos repositorios ni interfaces mientras exista una sola implementación.

## Consecuencias

- ✅ Cada clase tiene una responsabilidad y un lugar obvio.
- ✅ Es idiomático: cualquier dev Laravel lo entiende sin aprender un framework propio.
- ⚠️ El dominio queda acoplado a Eloquent. Es aceptable a esta escala.
- 🔁 Si apareciera una segunda fuente de datos o reglas de dominio ricas, se evaluaría introducir repositorios/agregados.
