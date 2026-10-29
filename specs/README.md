# Specs — Índice y estado

Este proyecto se construye con **Spec-Driven Development (SDD)**. Cada feature vive en su propia carpeta numerada y pasa por estas fases:

```
 spec.md  ──►  plan.md  ──►  tasks.md  ──►  implementación  ──►  verificación
 (qué/por qué)  (cómo)      (pasos atómicos)  (test primero)      (ACs ↔ tests)
   /sdd-spec    /sdd-plan    /sdd-tasks       /sdd-implement      /sdd-verify
```

Las reglas que gobiernan todas las fases están en [`constitution.md`](./constitution.md).

## Estados

| Estado | Significado |
|---|---|
| `Draft` | En redacción, puede cambiar libremente |
| `Approved` | Revisada; es el contrato contra el que se implementa |
| `In Progress` | Tiene tareas en ejecución |
| `Done` | Todas las tareas completas y todos los ACs cubiertos por tests en verde |

## Features

| ID | Feature | Spec | Plan | Tasks | Estado |
|---|---|---|---|---|---|
| 000 | [Foundation](./000-foundation/) — scaffolding, Docker, calidad, CI | ✅ | ✅ | ✅ | In Progress |
| 001 | [Currencies](./001-currencies/) — divisas y conversión | ✅ | ✅ | ✅ | Done |
| 002 | [Products CRUD](./002-products-crud/) | ✅ | ✅ | ✅ | Done |
| 003 | [Product prices](./003-product-prices/) — precios por divisa | ✅ | ✅ | ✅ | Done |
| 004 | [Auth & Security](./004-auth-security/) — Sanctum, abilities, hardening | ✅ | ✅ | ✅ | Done |

## Orden de implementación

`000 → 004 → 001 → 002 → 003`

La autenticación (004) se implementa justo después de la foundation para que **todos** los endpoints nazcan protegidos (Constitution III.1), en vez de protegerlos al final.

## Trazabilidad

- Criterios de aceptación: `AC-<feature>-<n>` (ej. `AC-002-03`).
- Cada test referencia su AC: `it('AC-002-03: returns 404 for a missing product', ...)`.
- `/sdd-verify` cruza los ACs de la spec con los tests y reporta los que no tienen cobertura.
