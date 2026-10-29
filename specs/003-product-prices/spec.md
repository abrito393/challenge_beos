# 003 — Product prices (precios por divisa)

- **Estado:** Done
- **Autor:** Aquiles Brito
- **Fecha:** 2026-10-29
- **Depende de:** 001, 002, 004
- **ADRs relacionados:** [0003](../../docs/adr/0003-money-precision.md), [0004](../../docs/adr/0004-exchange-rate-semantics.md)

## Contexto

El challenge pide registrar precios de un producto en diferentes divisas, además del precio en su divisa base.

## Objetivos

- Consultar todos los precios de un producto.
- Registrar un precio en otra divisa, ya sea explícito o calculado a partir de la tasa de cambio.

## Fuera de alcance

- Actualizar o eliminar precios por divisa (el challenge sólo pide GET y POST). *Extensión futura: `PUT/DELETE /products/{id}/prices/{currency}`.*
- Recalcular automáticamente los precios derivados cuando cambia el precio base o la tasa.

## Modelo de datos (del challenge)

| Campo | Tipo | Regla |
|---|---|---|
| `id` | integer | PK |
| `product_id` | integer | FK a `products`, `ON DELETE CASCADE` |
| `currency_id` | integer | FK a `currencies`, `ON DELETE RESTRICT` |
| `price` | decimal | `>= 0`, 2 decimales |

Restricción: `UNIQUE(product_id, currency_id)`.

## Reglas de negocio

- **RN-003-1:** Un producto tiene como máximo **un** precio por divisa.
- **RN-003-2:** No se registra un precio en la divisa base del producto: ese precio ya vive en `products.price` y duplicarlo crearía dos fuentes de verdad.
- **RN-003-3:** Si el request no incluye `price`, se calcula convirtiendo el precio base con las tasas vigentes (RN-001-2).

## Endpoints

| Método | Ruta | Éxito | Ability |
|---|---|---|---|
| GET | `/api/v1/products/{id}/prices` | 200 | `products:read` |
| POST | `/api/v1/products/{id}/prices` | 201 | `products:write` |

## Criterios de aceptación

- **AC-003-01:** Dado un producto con precios en EUR y MXN, cuando hago `GET /products/{id}/prices`, entonces recibo `200` con ambos precios, cada uno con su divisa embebida y `price` como string.
- **AC-003-02:** Dado un producto sin precios adicionales, entonces recibo `200` con `data: []`.
- **AC-003-03:** Dado un producto inexistente o eliminado, en GET o POST, entonces recibo `404`.
- **AC-003-04:** Dado `{"currency_id": EUR, "price": "89.90"}`, cuando hago `POST`, entonces recibo `201` con ese precio exacto.
- **AC-003-05:** Dado `{"currency_id": EUR}` sin `price` y un producto de `100.00 USD` con EUR a `0.92`, entonces recibo `201` con `price: "92.00"`.
- **AC-003-06:** Dado un producto que ya tiene precio en EUR, cuando hago `POST` con EUR, entonces recibo `409 Conflict`.
- **AC-003-07:** Dado `currency_id` igual a la divisa base del producto, entonces recibo `422`.
- **AC-003-08:** Dado `currency_id` inexistente, `price` negativo o con más de 2 decimales, entonces recibo `422`.
- **AC-003-09:** Dado dos requests concurrentes con la misma divisa, entonces sólo uno se crea; el otro recibe `409` (garantizado por el `UNIQUE` de la base de datos, no sólo por validación).
- **AC-003-10:** Un token sólo con `products:read` recibe `403` en `POST`.
