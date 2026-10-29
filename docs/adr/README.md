# Architecture Decision Records

Registro de decisiones con alternativas reales. Formato basado en [Michael Nygard](https://cognitect.com/blog/2011/11/15/documenting-architecture-decisions). Plantilla: [`template.md`](./template.md). Se crean con el skill `/adr`.

| # | Decisión | Estado |
|---|---|---|
| [0001](./0001-spec-driven-development.md) | Spec-Driven Development como proceso de trabajo | Accepted |
| [0002](./0002-layered-architecture-with-actions.md) | Arquitectura en capas con Actions y DTOs (sin repositorios) | Superseded by 0009 |
| [0003](./0003-money-precision.md) | Precisión monetaria: DECIMAL + brick/math + strings en JSON | Accepted |
| [0004](./0004-exchange-rate-semantics.md) | Semántica de tasas de cambio respecto a una divisa de referencia | Accepted |
| [0005](./0005-error-format-problem-details.md) | Errores en formato RFC 9457 Problem Details | Accepted |
| [0006](./0006-docker-compose-infrastructure.md) | Docker Compose propio (PHP-FPM + Nginx + MySQL) | Accepted |
| [0007](./0007-api-documentation-scramble.md) | Documentación OpenAPI con Scramble + colección Postman | Accepted |
| [0008](./0008-auth-sanctum-abilities.md) | Autenticación con Sanctum y autorización por abilities | Accepted |
| [0009](./0009-pragmatic-ddd-hexagonal.md) | DDD pragmático con arquitectura hexagonal por bounded context | Accepted |
| [0010](./0010-solid-single-action-classes-and-value-objects.md) | SOLID: clases de una sola acción y Value Objects | Accepted |
