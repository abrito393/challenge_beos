---
name: sdd-plan
description: Genera el plan técnico (plan.md) de una feature a partir de su spec.md aprobada. Usar cuando una spec pasa a Approved, cuando se pida diseñar la implementación, o con /sdd-plan NNN. Define migraciones, clases, contratos y riesgos respetando la constitution y los ADRs.
---

# /sdd-plan — Plan técnico

Segunda fase de SDD: traduce la spec a un diseño concreto en Laravel.

## Precondiciones
- `specs/NNN-*/spec.md` existe y su estado es `Approved`. Si está `Draft`, detenerse y pedir aprobación.
- Leer `specs/constitution.md`, `docs/architecture.md`, `docs/adr/*` y el skill `laravel-conventions`.

## Estructura de `plan.md`
1. Cabecera: enlace a la spec y ADRs relacionados.
2. `## Esquema de base de datos`: migraciones con tipos exactos, índices, FKs (con `ON DELETE`), `UNIQUE` y `CHECK`.
3. `## Componentes`: tabla `Contexto | Capa | Clase | Responsabilidad`, siguiendo ADR-0009. Incluir **Domain** (entidades, VOs, invariantes, puertos, excepciones), **Application** (`<Acción><Entidad>UseCase` + `Command`) e **Infrastructure** (Eloquent, repositorio, searcher, y por endpoint su `<Acción><Entidad>Controller` invokable + `<Acción><Entidad>Request`, bindings). Listar los Value Objects con sus invariantes. Respetar ADR-0010 (una acción = un archivo por capa). Indicar cualquier dependencia entre contextos y por qué servicio de Application pasa.
4. `## Contratos`: por endpoint, el request (campos y reglas de validación Laravel exactas) y la respuesta (JSON de ejemplo, códigos de estado).
5. `## Mapeo AC → test`: tabla `AC | Tipo (Feature/Unit) | Archivo de test`. **Todos** los ACs de la spec deben aparecer.
6. `## Riesgos y mitigaciones`.
7. `## Decisiones`: cualquier decisión nueva con alternativas → crear ADR con `/adr` y enlazarlo aquí.

## Reglas
- No escribir código de implementación. Sólo firmas, reglas de validación y ejemplos de payload.
- Respetar la proporcionalidad (Constitution IV.5): justificar cada clase nueva.
- Si el plan revela un hueco en la spec, volver a `/sdd-spec` y corregir allí primero.
- Al terminar, actualizar `specs/README.md` (columna Plan) y proponer `/sdd-tasks`.
