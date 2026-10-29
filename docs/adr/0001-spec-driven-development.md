# ADR-0001: Spec-Driven Development como proceso de trabajo

- **Estado:** Accepted
- **Fecha:** 2026-10-29

## Contexto

El challenge evalúa correctitud, claridad, organización y seguridad. Además pide "no hacerlo con lo mínimo". El evaluador sólo ve el resultado final, así que el razonamiento detrás de cada decisión se pierde si no queda escrito. Además, el desarrollo se apoya en un agente de IA (Claude Code), y sin un contrato explícito un agente tiende a improvisar alcance.

## Alternativas consideradas

1. **Código directo + README al final:** rápido, pero las decisiones quedan implícitas y no hay trazabilidad requisito → test.
2. **TDD puro sin specs:** buena calidad local, pero sin visión de alcance ni criterios de negocio explícitos.
3. **SDD (spec → plan → tasks → implement → verify):** más documentación inicial, a cambio de trazabilidad completa.

## Decisión

Usaremos SDD. Cada feature tiene `spec.md` (negocio), `plan.md` (técnico) y `tasks.md` (pasos atómicos) en `specs/NNN-*`, gobernados por `specs/constitution.md`. Cada criterio de aceptación (`AC-xxx`) se cubre con al menos un test que lo nombra. El proceso se codifica en skills de Claude Code (`.claude/skills/sdd-*`) para que se aplique de forma consistente.

## Consecuencias

- ✅ El evaluador puede auditar el *por qué* de cada pieza y verificar que cada requisito tiene test.
- ✅ El agente trabaja contra un contrato y no improvisa alcance.
- ⚠️ Más tiempo inicial de documentación. Es aceptable porque el alcance es pequeño.
- 🔁 En un equipo con producto ya maduro, las specs podrían vivir en la herramienta de tickets en vez del repo.
