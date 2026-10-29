# ADR-0007: Documentación OpenAPI con Scramble + colección Postman

- **Estado:** Accepted
- **Fecha:** 2026-10-29
- **Specs relacionadas:** 000, todas

## Contexto

El challenge pide documentación "clara y concisa" en Swagger, Postman, Insomnia u otra herramienta. La documentación escrita a mano se desincroniza del código con el tiempo.

## Alternativas consideradas

1. **L5-Swagger (anotaciones/atributos):** explícito, pero muy verboso y duplica lo que ya dicen los FormRequests y Resources. Además puede mentir si no se actualiza.
2. **Scribe:** bueno, pero depende de docblocks y de llamadas reales para inferir respuestas.
3. **Scramble:** genera OpenAPI 3.1 por análisis estático de rutas, FormRequests, Resources y tipos. La documentación sale del código.

## Decisión

- **Scramble** como fuente de verdad: Swagger UI en `/docs/api` (sólo entornos no productivos) y export a `docs/api/openapi.json` con `make docs`.
- Se complementa con docblocks/atributos sólo donde el análisis no alcanza (descripciones, ejemplos, esquema Problem Details, `securityScheme` Bearer).
- **Colección Postman** en `docs/api/postman_collection.json`, con una variable `{{token}}` que se setea automáticamente con un script post-response del login.

## Consecuencias

- ✅ La documentación no puede desincronizarse de la validación real.
- ✅ El evaluador elige: Swagger UI interactivo, OpenAPI importable o Postman listo para usar.
- ⚠️ Algunas respuestas (errores Problem Details) requieren extensión manual del documento.
