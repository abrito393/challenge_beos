---
name: adr
description: Registra una Architecture Decision Record en docs/adr/. Usar cuando se tome una decisión técnica con alternativas reales (librería, patrón, formato, modelo de datos, seguridad), cuando se cambie una decisión previa, o con /adr "título".
---

# /adr — Architecture Decision Record

## Cuándo crear un ADR
- Hay al menos **dos alternativas viables** y elegimos una.
- La decisión es costosa de revertir o afecta a varias features.
- Nos desviamos del enunciado del challenge o de una convención de Laravel.

No crear ADRs para cosas triviales o sin alternativa real.

## Pasos
1. Siguiente número: `ls docs/adr | grep -E '^[0-9]{4}' | sort | tail -1`, +1, con 4 dígitos.
2. Archivo: `docs/adr/NNNN-kebab-title.md`, copiado de `docs/adr/template.md`.
3. Completar: Contexto (hechos) → Alternativas (pros/contras honestos) → Decisión (voz activa) → Consecuencias (incluir las negativas y qué haría reconsiderarla).
4. Si reemplaza a otro ADR: marcar el anterior como `Superseded by ADR-NNNN`. **Nunca** borrar ADRs.
5. Agregar la fila en `docs/adr/README.md`.
6. Enlazar el ADR desde la spec/plan que lo originó y, si cambia un principio, enmendar `specs/constitution.md` (subir versión).
