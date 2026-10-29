# ADR-0004: Semántica de tasas de cambio respecto a una divisa de referencia

- **Estado:** Accepted
- **Fecha:** 2026-10-29
- **Specs relacionadas:** 001, 003

## Contexto

El challenge define `currencies.exchange_rate` sin indicar respecto a qué divisa se expresa. Además, cada producto tiene su propia divisa base (`products.currency_id`), así que hace falta convertir entre **dos divisas cualesquiera**, no sólo desde una divisa global.

## Alternativas consideradas

1. **Tabla de pares (`from`, `to`, `rate`):** flexible, pero crece en O(n²) y puede ser inconsistente (A→B→C ≠ A→C).
2. **Tasa relativa a una divisa de referencia única:** O(n), siempre consistente, y la conversión cruzada sale de una fórmula.

## Decisión

- `exchange_rate` = **unidades de esa divisa por 1 unidad de la divisa de referencia**. La referencia (`USD` por defecto, `config('currency.reference')`) tiene `exchange_rate = 1`.
- Conversión X→Y: `amount × rate_Y / rate_X`, implementada en el servicio de dominio `Src\Currency\Domain\CurrencyConverter` y expuesta a otros contextos vía `Src\Currency\Application\ConvertAmount` (ver ADR-0003 para precisión).
- Se agrega la columna `code` (ISO 4217, `CHAR(3)`, única) como extensión del modelo del challenge. Es el identificador que esperan los clientes y facilita los seeders. Los campos del challenge se mantienen intactos (`name` sigue siendo único).

## Consecuencias

- ✅ Conversión entre cualquier par de divisas, consistente por construcción.
- ✅ Actualizar una tasa es tocar una sola fila.
- ⚠️ Los precios por divisa calculados (feature 003) son una "foto" de la tasa al momento de crearlos. No se recalculan solos. Queda documentado como fuera de alcance.
- 🔁 Si se necesitara historial de tasas, se agregaría una tabla `exchange_rates` con vigencia.
