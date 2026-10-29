# 002 — Products CRUD

- **Estado:** Approved
- **Autor:** Aquiles Brito
- **Fecha:** 2026-10-29
- **Depende de:** 001 (divisas), 004 (auth)

## Contexto

Núcleo del challenge: gestionar productos con nombre, descripción, precio en una divisa base, costo de impuestos y costo de fabricación.

## Objetivos

- CRUD completo de productos vía API REST JSON.
- Listado usable en la práctica: paginado, filtrable y ordenable.
- Eliminación segura y recuperable (soft delete).

## Fuera de alcance

- Restaurar productos eliminados por API (el dato se conserva; endpoint de restore es extensión futura).
- Categorías, stock, imágenes.

## Modelo de datos (del challenge)

| Campo | Tipo | Regla |
|---|---|---|
| `id` | integer | PK |
| `name` | string | Obligatorio, máx. 255 |
| `description` | string | Obligatorio, máx. 2000 (se persiste como `TEXT`) |
| `price` | decimal | Obligatorio, `>= 0`, máx. 2 decimales. Precio en la divisa base |
| `currency_id` | integer | Obligatorio, FK a `currencies` existente |
| `tax_cost` | decimal | Obligatorio, `>= 0`, máx. 2 decimales |
| `manufacturing_cost` | decimal | Obligatorio, `>= 0`, máx. 2 decimales |

Extensiones: `created_at`, `updated_at`, `deleted_at` (soft delete).

## Endpoints

| Método | Ruta | Éxito | Ability |
|---|---|---|---|
| GET | `/api/v1/products` | 200 | `products:read` |
| POST | `/api/v1/products` | 201 + `Location` | `products:write` |
| GET | `/api/v1/products/{id}` | 200 | `products:read` |
| PUT | `/api/v1/products/{id}` | 200 | `products:write` |
| DELETE | `/api/v1/products/{id}` | 204 | `products:write` |

## Historias de usuario

- **US-002-1:** Como administrador, quiero crear un producto con su precio base y costos, para registrarlo en el catálogo.
- **US-002-2:** Como cliente, quiero listar productos paginados, filtrar por nombre/divisa/rango de precio y ordenar, para encontrar lo que busco.
- **US-002-3:** Como cliente, quiero ver el detalle de un producto, incluyendo su divisa base.
- **US-002-4:** Como administrador, quiero actualizar un producto.
- **US-002-5:** Como administrador, quiero eliminar un producto sin perder el histórico.

## Criterios de aceptación

### Crear
- **AC-002-01:** Dado un payload válido y ability `products:write`, cuando hago `POST /products`, entonces recibo `201`, header `Location` y el producto con montos como string (`"19.99"`) y la divisa embebida.
- **AC-002-02:** Dado un payload sin campos obligatorios, montos negativos, más de 2 decimales o `currency_id` inexistente, entonces recibo `422` en formato Problem Details con `errors` por campo.
- **AC-002-03:** Dado un payload con campos no permitidos (ej. `id`, `deleted_at`), entonces esos campos se ignoran (no mass assignment).

### Listar
- **AC-002-04:** Dado N productos, cuando hago `GET /products`, entonces recibo `200` con `data`, `links` y `meta` de paginación; `per_page` por defecto 15, máximo 100.
- **AC-002-05:** Dado `?filter[name]=lap`, entonces sólo recibo productos cuyo nombre contiene "lap" (case-insensitive).
- **AC-002-06:** Dado `?filter[currency_id]=2&filter[min_price]=10&filter[max_price]=50`, entonces los filtros se combinan con AND.
- **AC-002-07:** Dado `?sort=-price`, entonces el orden es por precio descendente. Campos permitidos: `name`, `price`, `created_at`. Un campo no permitido devuelve `422`.
- **AC-002-08:** Los productos eliminados no aparecen en el listado.
- **AC-002-09:** El listado no ejecuta consultas N+1 (divisa precargada).

### Ver
- **AC-002-10:** Dado un id existente, cuando hago `GET /products/{id}`, entonces recibo `200` con el producto y su divisa.
- **AC-002-11:** Dado un id inexistente o eliminado, entonces recibo `404` Problem Details.

### Actualizar
- **AC-002-12:** Dado un payload válido completo, cuando hago `PUT /products/{id}`, entonces recibo `200` con el producto actualizado.
- **AC-002-13:** `PUT` es reemplazo completo: si falta un campo obligatorio, recibo `422`.

### Eliminar
- **AC-002-14:** Dado un producto existente, cuando hago `DELETE /products/{id}`, entonces recibo `204` y el registro queda con `deleted_at` (soft delete).
- **AC-002-15:** Dado un producto ya eliminado, cuando repito el `DELETE`, entonces recibo `404`.

### Autorización
- **AC-002-16:** Un token sólo con `products:read` recibe `403` en POST/PUT/DELETE.

## Ejemplo de respuesta

```json
{
  "data": {
    "id": 1,
    "name": "Laptop Pro 14",
    "description": "Laptop de 14 pulgadas",
    "price": "1299.99",
    "currency": { "id": 1, "code": "USD", "name": "US Dollar", "symbol": "$" },
    "tax_cost": "207.99",
    "manufacturing_cost": "650.00",
    "created_at": "2026-10-29T18:00:00Z",
    "updated_at": "2026-10-29T18:00:00Z"
  }
}
```

## Preguntas abiertas

- ¿Validar que `price >= tax_cost + manufacturing_cost`? **Decisión:** no. El challenge no lo pide y vender bajo costo puede ser legítimo (promociones). Se documenta como posible regla futura.
