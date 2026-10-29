# ADR-0003: Precisión monetaria con DECIMAL, brick/math y strings en JSON

- **Estado:** Accepted
- **Fecha:** 2026-10-29
- **Specs relacionadas:** 001, 002, 003

## Contexto

La API maneja precios, costos y conversiones entre divisas. En IEEE-754, `0.1 + 0.2 = 0.30000000000000004`, y PHP convierte a `float` con facilidad (casts, `json_encode`, operadores aritméticos). Un error de un centavo en una conversión es un bug de correctitud, que es justo el primer criterio de evaluación.

## Alternativas consideradas

1. **`float` en PHP + `DECIMAL` en BD:** simple, pero introduce errores en cálculos y serialización.
2. **Enteros en centavos (minor units):** exacto y rápido, pero los decimales varían por divisa (JPY 0, KWD 3) y las tasas igual necesitan precisión arbitraria.
3. **`brick/money`:** modela Money completo con divisas, pero es más pesado de lo que necesitamos.
4. **`DECIMAL` en BD + `brick/math` (`BigDecimal`) en cálculos + strings en JSON.**

## Decisión

Usaremos la opción 4:
- Montos `DECIMAL(15,2)`, tasas `DECIMAL(18,8)`.
- Cast Eloquent `decimal:2` / `decimal:8`, que devuelve string y nunca float.
- Toda aritmética pasa por `BigDecimal` con escala intermedia alta, y se redondea `RoundingMode::HALF_UP` a 2 decimales **sólo al final**.
- En el dominio, los montos son el Value Object `Src\Shared\Domain\Money` y las tasas `Src\Currency\Domain\ExchangeRate`, que encapsulan `BigDecimal` (ADR-0009).
- JSON: los montos se serializan como string (`"19.99"`) y se documenta así en OpenAPI.
- Validación de entrada: `numeric`, `min:0`, `decimal:0,2`.

## Consecuencias

- ✅ Cero errores de redondeo acumulado. Resultados deterministas y testeables por igualdad exacta.
- ✅ Los clientes JS no pierden precisión al parsear.
- ⚠️ Los clientes deben tratar los montos como string/decimal. Se documenta.
- 🔁 Si se soportan divisas con distinta cantidad de decimales, la escala pasaría a ser un atributo de la divisa.
