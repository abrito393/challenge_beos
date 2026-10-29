---
name: sdd-verify
description: Verifica que una feature cumple su spec — cruza cada AC-xxx con los tests que lo cubren, ejecuta la suite y revisa la constitution. Usar al terminar todas las tareas de una feature, antes de marcarla Done, o con /sdd-verify NNN.
---

# /sdd-verify — Verificación de una feature

Quinta fase de SDD.

## Pasos
1. Extraer todos los IDs `AC-NNN-nn` de `specs/NNN-*/spec.md`:
   `grep -oE 'AC-NNN-[0-9]+' specs/NNN-*/spec.md | sort -u`
2. Buscar cada AC en `tests/`:
   `grep -rhoE 'AC-NNN-[0-9]+' tests | sort -u`
3. Reportar la diferencia: **ACs sin test** (bloqueante) y tests que referencian ACs inexistentes (inconsistencia).
4. Ejecutar `make check` y reportar el resultado real (no resumir un fallo como éxito).
5. Checklist de la constitution:
   - [ ] Tests de arquitectura (`tests/Architecture`) en verde: el dominio no depende de Laravel
   - [ ] Sin `float` en cálculos monetarios (`grep -rn "(float)\|floatval" src/`)
   - [ ] Todos los endpoints con `auth:sanctum` + ability (`php artisan route:list --path=api`)
   - [ ] Sin `$request->all()` en `src/`
   - [ ] Modelos con `$fillable` explícito
   - [ ] Errores en Problem Details (test por cada código de estado de la spec)
   - [ ] OpenAPI regenerado (`make docs`) y Postman actualizado si cambiaron contratos
6. Si todo pasa: marcar la feature `Done` en `specs/README.md` y en la cabecera de la spec.

## Salida
Una tabla `AC | Test | Estado` y la lista de pendientes, si los hay.
