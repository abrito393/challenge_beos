# CLAUDE.md

Products API: challenge backend en Laravel 12, construido con **Spec-Driven Development**.

## Antes de tocar código
1. Leer `specs/constitution.md`. Sus principios no son negociables.
2. Revisar el estado en `specs/README.md`.
3. Cargar el skill `laravel-conventions` antes de escribir PHP.

## Flujo obligatorio (SDD)
`/sdd-spec` → `/sdd-plan` → `/sdd-tasks` → `/sdd-implement` (una tarea por vez) → `/sdd-verify`

- Sin spec `Approved` no hay implementación.
- Decisiones con alternativas reales → `/adr`.
- Si el código diverge del plan, se actualiza el plan en el mismo cambio.

## Comandos
```bash
make setup   # primera vez: build + install + migrate --seed
make up      # levantar contenedores
make check   # pint --test + larastan max + pest  (definición de "terminado")
make test    # sólo tests
make docs    # exportar OpenAPI a docs/api/openapi.json
make shell   # shell en el contenedor app
```
Nunca ejecutar tests contra la base de desarrollo (`products_api`). Los tests usan `products_api_testing`.

## Arquitectura (ADR-0009)
DDD hexagonal: `src/<Context>/{Domain,Application,Infrastructure}` con contextos `Catalog`, `Currency`, `Auth`, `Shared`.
El dominio es PHP puro: nada de `Illuminate\*`. Eloquent sólo en `Infrastructure/Persistence`. Los tests `arch()` lo hacen cumplir.

## Mapa del repo
- `src/`: código de negocio por bounded context (namespace `Src\`)
- `app/`: sólo bootstrap del framework
- `specs/`: constitution + una carpeta por feature (`spec.md`, `plan.md`, `tasks.md`)
- `docs/challenge.md`: enunciado original (fuente de verdad de requisitos)
- `docs/architecture.md`: capas, modelo de datos, endpoints
- `docs/adr/`: decisiones de arquitectura
- `docs/api/`: OpenAPI exportado + colección Postman
- `docker/`: Dockerfile PHP-FPM, Nginx e init de MySQL
- `.claude/skills/`: skills del proceso SDD y convenciones

## Idioma
Documentación en español. Código, identificadores, commits y nombres de tests en inglés.

## Commits
Conventional Commits con referencia a la tarea, ej. `feat(products): add index filters (T-002-06)`. No commitear sin que el usuario lo pida.
