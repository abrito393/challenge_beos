# 004 — Auth & Security

- **Estado:** Approved
- **Autor:** Aquiles Brito
- **Fecha:** 2026-10-29
- **ADRs relacionados:** [0005 Problem Details](../../docs/adr/0005-error-format-problem-details.md), [0008 Sanctum + abilities](../../docs/adr/0008-auth-sanctum-abilities.md)

## Contexto

El challenge evalúa explícitamente la **seguridad** ("medidas de seguridad adecuadas para proteger los datos"). Esta feature define autenticación, autorización y hardening transversal, y se implementa **antes** que los endpoints de negocio para que todos nazcan protegidos.

## Objetivos

- Sólo clientes autenticados acceden a la API.
- Separar permisos de lectura y escritura.
- Mitigar abuso (fuerza bruta, scraping) y fugas de información.

## Fuera de alcance

- Registro público de usuarios, recuperación de contraseña, OAuth2/SSO.
- Multi-tenancy.

## Roles y abilities

| Rol | Abilities del token |
|---|---|
| `admin` | `products:read`, `products:write` |
| `viewer` | `products:read` |

Las abilities se asignan **en el servidor** según el rol; el cliente no puede pedir abilities que su rol no tiene.

## Endpoints

| Método | Ruta | Éxito | Auth |
|---|---|---|---|
| POST | `/api/v1/auth/token` | 201 | Pública (rate limit estricto) |
| DELETE | `/api/v1/auth/token` | 204 | Token |
| GET | `/api/v1/auth/me` | 200 | Token |

## Criterios de aceptación

### Autenticación
- **AC-004-01:** Dadas credenciales válidas y un `device_name`, cuando hago `POST /auth/token`, entonces recibo `201` con `token`, `token_type: "Bearer"`, `abilities` y `expires_at`.
- **AC-004-02:** Dadas credenciales inválidas, entonces recibo `401` con un mensaje genérico (sin revelar si el email existe).
- **AC-004-03:** Dado más de 5 intentos de login por minuto para el mismo email + IP, entonces recibo `429` con header `Retry-After`.
- **AC-004-04:** Dado un token válido, cuando hago `DELETE /auth/token`, entonces el token se revoca y su siguiente uso recibe `401`.
- **AC-004-05:** Dado un token expirado (configurable, por defecto 24 h), entonces recibo `401`.

### Autorización
- **AC-004-06:** Un usuario `viewer` obtiene un token sólo con `products:read`.
- **AC-004-07:** Un token sin la ability requerida recibe `403` Problem Details.

### Hardening
- **AC-004-08:** Toda ruta autenticada tiene rate limit de 60 req/min por usuario; al excederlo recibe `429`.
- **AC-004-09:** Todas las respuestas incluyen `X-Content-Type-Options: nosniff`, `X-Frame-Options: DENY`, `Referrer-Policy: no-referrer` y no incluyen `X-Powered-By`.
- **AC-004-10:** Con `APP_DEBUG=false`, un error 500 devuelve Problem Details genérico sin stack trace, SQL ni rutas de archivos.
- **AC-004-11:** Todos los errores (`401`, `403`, `404`, `405`, `409`, `422`, `429`, `500`) usan `Content-Type: application/problem+json` con `type`, `title`, `status`, `detail`; `422` agrega `errors`.
- **AC-004-12:** Una ruta inexistente bajo `/api` devuelve `404` Problem Details (no HTML).
- **AC-004-13:** Las contraseñas se almacenan con hash (bcrypt/argon) y nunca se serializan en respuestas.

## Usuarios de prueba (seeder, sólo entornos no productivos)

| Email | Password | Rol |
|---|---|---|
| `admin@example.com` | `password` | admin |
| `viewer@example.com` | `password` | viewer |
