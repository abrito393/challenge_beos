---
name: sdd-implement
description: Implementa la siguiente tarea pendiente (o una indicada, ej. T-002-03) de un tasks.md siguiendo test-first y las convenciones Laravel del proyecto. Usar con /sdd-implement NNN o /sdd-implement T-NNN-nn, o cuando se pida implementar una feature con spec aprobada.
---

# /sdd-implement — Implementación de una tarea

Cuarta fase de SDD. **Una tarea por invocación.**

## Precondiciones
- La tarea existe en `specs/NNN-*/tasks.md` y está `[ ]` o `[~]`.
- Leer: la tarea, su sección en `plan.md`, los ACs en `spec.md`, `specs/constitution.md` y el skill `laravel-conventions`.

## Ciclo
1. Marcar la tarea `[~]` en `tasks.md`.
2. **Rojo:** escribir el/los test(s) Pest que nombran el AC (`it('AC-002-03: …')`). Ejecutarlos y confirmar que fallan por la razón correcta.
3. **Verde:** escribir el código mínimo que siga el plan y las convenciones.
4. **Refactor:** limpiar sin cambiar comportamiento.
5. Ejecutar `make check` (Pint + Larastan max + Pest). Si algo falla, corregir; nunca desactivar reglas ni agregar baseline.
6. Marcar la tarea `[x]`.
7. Proponer un commit con Conventional Commits que referencie la tarea:
   `feat(products): add product creation endpoint (T-002-04)`

## Reglas
- No implementar nada fuera de la tarea. Si se detecta un hueco, detenerse y volver a `/sdd-spec` o `/sdd-plan`.
- Si el código diverge del plan por una buena razón, **actualizar el plan** en el mismo cambio.
- No commitear sin que el usuario lo pida.
- Si `make` no está disponible (Docker apagado), usar `docker compose exec app …` o informar al usuario. No ejecutar tests contra la base de desarrollo.
