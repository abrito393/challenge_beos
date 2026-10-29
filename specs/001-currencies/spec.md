# 001 — Currencies

- **Estado:** Done
- **Autor:** Aquiles Brito
- **Fecha:** 2026-10-29
- **ADRs relacionados:** [0003 Precisión monetaria](../../docs/adr/0003-money-precision.md), [0004 Semántica de tasas de cambio](../../docs/adr/0004-exchange-rate-semantics.md)

## Contexto

Los productos tienen un precio en una **divisa base** y pueden tener precios adicionales en otras divisas. Para eso el sistema necesita un catálogo de divisas con su tasa de cambio. El challenge define el modelo, pero no los endpoints de divisas.

## Objetivos

- Disponer de un catálogo de divisas consistente, sembrado con valores realistas.
- Exponer un endpoint de sólo lectura para que un cliente pueda saber qué `currency_id` usar al crear productos y precios.
- Ofrecer un servicio de conversión exacto y reutilizable entre divisas.

## Fuera de alcance

- CRUD de divisas por API (se gestionan por seeder/migración). *Posible extensión futura.*
- Sincronización automática de tasas con un proveedor externo.
- Historial de tasas de cambio.

## Modelo de datos (del challenge)

| Campo | Tipo | Regla |
|---|---|---|
| `id` | integer | PK |
| `name` | string | Único (el challenge lo define como identificador único) |
| `symbol` | string | Obligatorio |
| `exchange_rate` | decimal | > 0. Unidades de esta divisa equivalentes a **1 unidad de la divisa de referencia** |

**Extensión (ADR-0004):** se agrega `code` (ISO 4217, 3 letras, único) porque es el identificador estándar que esperan los clientes. No altera los campos del challenge.

## Reglas de negocio

- **RN-001-1:** Existe exactamente una divisa de referencia, la que tiene `exchange_rate = 1` y está configurada en `config('currency.reference')` (por defecto `USD`).
- **RN-001-2:** Conversión de un monto `A` de la divisa `X` a la divisa `Y`: `A × (rate_Y / rate_X)`, con redondeo `HALF_UP` a 2 decimales **sólo en el resultado final**.
- **RN-001-3:** Convertir a la misma divisa devuelve el monto sin cambios.

## Historias de usuario

- **US-001-1:** Como cliente de la API, quiero listar las divisas disponibles, para saber qué `currency_id` usar.
- **US-001-2:** Como sistema, quiero convertir montos entre divisas de forma exacta, para calcular precios en otras divisas sin errores de punto flotante.

## Criterios de aceptación

- **AC-001-01:** Dado un usuario autenticado con ability `products:read`, cuando hace `GET /api/v1/currencies`, entonces recibe `200` con la lista de divisas (`id`, `code`, `name`, `symbol`, `exchange_rate` como string).
- **AC-001-02:** Dado un request sin token, cuando hace `GET /api/v1/currencies`, entonces recibe `401`.
- **AC-001-03:** Dado `100.00 USD` y `EUR` con rate `0.92`, cuando convierto USD→EUR, entonces obtengo `"92.00"`.
- **AC-001-04:** Dado `92.00 EUR` (rate `0.92`) y `MXN` (rate `18.50`), cuando convierto EUR→MXN, entonces obtengo `"1850.00"` (cálculo cruzado vía referencia, sin pérdida de precisión).
- **AC-001-05:** Dado un monto y la misma divisa de origen y destino, cuando convierto, entonces obtengo el mismo monto.
- **AC-001-06:** Dado un intento de persistir una divisa con `exchange_rate <= 0` o `code` duplicado, entonces la base de datos lo rechaza.
- **AC-001-07:** Dado `make setup`, entonces existen al menos USD, EUR, MXN, COP, VES sembradas.

## Requisitos no funcionales

- `exchange_rate`: `DECIMAL(18,8)`.
- El listado de divisas puede cachearse; si se cachea, se invalida al cambiar una divisa.
