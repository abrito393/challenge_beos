---
name: sdd-tasks
description: Descompone el plan.md de una feature en tareas atómicas y ordenadas (tasks.md) con AC y Definition of Done. Usar tras aprobar un plan, o con /sdd-tasks NNN.
---

# /sdd-tasks — Descomposición en tareas

Tercera fase de SDD.

## Precondiciones
- `specs/NNN-*/plan.md` existe.

## Formato de `tasks.md`

```markdown
# NNN — Nombre · Tareas

- **Plan:** [plan.md](./plan.md)

Leyenda: `[ ]` pendiente · `[~]` en curso · `[x]` hecha

| # | Tarea | AC | DoD | Estado |
|---|---|---|---|---|
| T-NNN-01 | ... | AC-NNN-01 | criterio verificable | [ ] |
```

## Reglas de descomposición
1. **Atómicas:** cada tarea cabe en un commit (≈ < 200 líneas de diff) y deja el proyecto en verde.
2. **Ordenadas de adentro hacia afuera:** Domain (VOs, entidades, puertos; unit tests puros) → Application (UseCase + Command con repo in-memory) → Infrastructure/Persistence (migración, Eloquent, repositorio, factory) → Infrastructure/Http (request, resource, controller, rutas; feature tests) → docs.
3. **Test primero:** las tareas de comportamiento incluyen escribir el test del AC antes que el código.
4. **Trazables:** cada AC de la spec aparece en al menos una tarea. Verificarlo al final con una lista de ACs sin asignar (debe quedar vacía).
5. Siempre cerrar con una tarea de documentación (OpenAPI/Postman/README/estado de la spec).

Al terminar, cambiar el estado de la feature a `In Progress` en `specs/README.md` cuando se empiece a implementar.
