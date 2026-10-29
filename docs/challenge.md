# Enunciado original — Prueba Backend

> Transcripción fiel del PDF recibido. Es la fuente de verdad de los requisitos; cualquier extensión se marca como tal en las specs.

**IMPORTANTE:** El propósito de la siguiente prueba es que puedas plasmar los conocimientos que tienes en Laravel. Como es una prueba sencilla con una estructura pequeña se presta para hacerse rápido; ejecute las funcionalidades descritas. Lo que se busca es que puedas implementar los conocimientos y estructuras que conozcas de Laravel. **No solo hacerlo con lo mínimo.**

## Prueba

Se requiere desarrollar una API RESTful utilizando Laravel que gestione productos.

### Requisitos
- La API debe permitir crear, leer, actualizar y eliminar productos.
- Cada producto debe tener un nombre, descripción, precio, costo de impuestos y costo de fabricación.
- La API debe permitir registrar precios de los productos en diferentes divisas.
- La API debe devolver los datos en formato JSON.
- La API debe utilizar Eloquent para interactuar con la base de datos.

### Modelo de datos

**Productos**

| Campo | Tipo | Descripción |
|---|---|---|
| id | integer | Identificador único del producto |
| name | string | Nombre del producto |
| description | string | Descripción del producto |
| price | decimal | Precio del producto en la divisa base |
| currency_id | integer | Identificador de la divisa base |
| tax_cost | decimal | Costo de impuestos del producto |
| manufacturing_cost | decimal | Costo de fabricación del producto |

**Divisas**

| Campo | Tipo | Descripción |
|---|---|---|
| id | integer | Identificador único de la divisa |
| name | string | Identificador único de la divisa |
| symbol | string | Símbolo de la divisa |
| exchange_rate | decimal | Tasa de cambio de la divisa |

**Precios de productos**

| Campo | Tipo | Descripción |
|---|---|---|
| id | integer | Identificador único |
| product_id | integer | Identificador del producto |
| currency_id | integer | Identificador de la divisa |
| price | decimal | Precio del producto en la divisa especificada |

### Endpoints
- `GET /products`: obtener lista de productos
- `POST /products`: crear un nuevo producto
- `GET /products/{id}`: obtener un producto por ID
- `PUT /products/{id}`: actualizar un producto
- `DELETE /products/{id}`: eliminar un producto
- `GET /products/{id}/prices`: obtener lista de precios de un producto
- `POST /products/{id}/prices`: crear un nuevo precio para un producto

### Notas
- La API debe utilizar Laravel 8 o superior.
- La API debe utilizar Eloquent para interactuar con la base de datos.
- La API debe devolver los datos en formato JSON.
- La API debe tener una documentación clara y concisa.

## Evaluación
- **Correctitud:** la API debe funcionar correctamente y cumplir con los requisitos especificados.
- **Claridad:** la documentación de la API debe ser clara y concisa.
- **Organización:** la estructura del código debe ser organizada y fácil de entender.
- **Seguridad:** la API debe tener medidas de seguridad adecuadas para proteger los datos.

## Entrega
Repositorio de preferencia + documentación en Swagger, JSON Postman, JSON Insomnia o documento.

## Matriz de cumplimiento

| Requisito | Dónde se resuelve |
|---|---|
| CRUD de productos | Spec 002 |
| Campos de producto | Spec 002 · Modelo de datos |
| Precios en diferentes divisas | Specs 001 + 003 |
| Respuestas JSON | Spec 000 (AC-000-05) · ADR-0005 |
| Eloquent | ADR-0002 |
| Laravel ≥ 8 | Laravel 12 (Spec 000) |
| Documentación | ADR-0007 · `docs/api/` |
| Seguridad | Spec 004 · ADR-0008 · Constitution III |
| Organización | ADR-0002 · `docs/architecture.md` |
