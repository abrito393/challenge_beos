# ADR-0008: Autenticación con Sanctum y autorización por abilities

- **Estado:** Accepted
- **Fecha:** 2026-10-29
- **Specs relacionadas:** 004

## Contexto

El criterio "Seguridad: medidas adecuadas para proteger los datos" no se cumple sólo con validar entradas: una API de gestión sin autenticación permite que cualquiera modifique o borre el catálogo.

## Alternativas consideradas

1. **Sin autenticación:** sólo validación y rate limit. Insuficiente.
2. **Passport (OAuth2):** completo, pero desproporcionado: no hay terceros que deleguen acceso.
3. **JWT (paquete externo):** sin estado, pero revocar tokens es complejo y suma una dependencia no oficial.
4. **Sanctum personal access tokens + abilities:** oficial, tokens revocables, scopes simples.

## Decisión

- Sanctum con tokens Bearer y expiración configurable (`SANCTUM_EXPIRATION`, 24 h por defecto).
- Abilities `products:read` y `products:write`, derivadas del rol del usuario (`Src\Auth\Domain\UserRole`) en el servidor.
- Rutas protegidas con `auth:sanctum` + middleware `abilities:` (`CheckAbilities`) declarado en cada ruta. No se usan Policies: no hay reglas por registro (ownership). Si aparecieran, se agregaría una Policy por agregado.
- Login con rate limit estricto (5/min por email+IP) y mensaje de error genérico.
- Ability values en un enum `Src\Auth\Domain\TokenAbility`, sin strings mágicos.

## Consecuencias

- ✅ Separación lectura/escritura demostrable con tests (viewer recibe 403).
- ✅ Tokens revocables (logout) y con expiración.
- ⚠️ Los tokens se guardan en BD (hasheados), lo que agrega una consulta por request. Es aceptable a esta escala.
- 🔁 Si se necesitara delegar acceso a apps de terceros, se migraría a Passport.
