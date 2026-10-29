---
name: sdd-spec
description: Redacta o actualiza la especificación funcional (spec.md) de una feature siguiendo SDD. Usar cuando se pida una nueva feature, cambiar alcance o requisitos, o cuando el usuario invoque /sdd-spec. Produce historias, reglas de negocio y criterios de aceptación AC-xxx; nunca código.
---

# /sdd-spec — Especificación funcional

Primera fase de SDD: define **qué** se construye y **por qué**, nunca el cómo.

## Entradas
- Descripción de la feature (del usuario o del challenge en `docs/challenge.md`).
- `specs/constitution.md` (leer siempre).
- Specs existentes en `specs/` para detectar dependencias y numeración.

## Pasos
1. Determinar el ID: siguiente número libre `NNN` en `specs/`. La carpeta es `specs/NNN-kebab-name/`.
2. Si la spec ya existe, **editarla** en lugar de crear otra. Si está `Approved`, anotar el cambio en una sección `## Historial de cambios`.
3. Redactar `spec.md` con esta estructura exacta:
   - Cabecera: Estado (`Draft`), Autor, Fecha (absoluta), Depende de, ADRs relacionados
   - `## Contexto` · `## Objetivos` · `## Fuera de alcance` (obligatorio, explícito)
   - `## Modelo de datos` (si aplica): marcar qué viene del challenge y qué es extensión
   - `## Reglas de negocio`: `RN-NNN-n`
   - `## Endpoints` (si aplica): método, ruta, código de éxito, ability
   - `## Historias de usuario`: `US-NNN-n` en formato "Como… quiero… para…"
   - `## Criterios de aceptación`: `AC-NNN-nn` en **Dado / cuando / entonces**, verificables y con valores concretos
   - `## Preguntas abiertas`: con la decisión tomada, o marcadas como bloqueantes
4. Cubrir para cada endpoint: camino feliz, validación (422), no encontrado (404), auth (401/403) y conflictos (409) cuando aplique.
5. Si surge una decisión con alternativas reales, invocar `/adr` y enlazarla.
6. Actualizar la fila de la feature en `specs/README.md`.

## Reglas
- Lenguaje de negocio. Prohibido nombrar clases, tablas internas o paquetes (excepto el modelo de datos del challenge).
- Cada AC debe poder convertirse en **un test** sin interpretación adicional.
- No inventar requisitos: lo que no está en el challenge es una **extensión** y se marca como tal, con su justificación.
- Al terminar, pedir aprobación al usuario antes de pasar a `/sdd-plan`.
