# ADR-0006: Docker Compose propio (PHP-FPM + Nginx + MySQL)

- **Estado:** Accepted
- **Fecha:** 2026-10-29
- **Specs relacionadas:** 000

## Contexto

El evaluador debe poder correr la API sin instalar PHP, Composer ni MySQL, y sin depender de la versión de PHP que tenga en su máquina.

## Alternativas consideradas

1. **Sin Docker + SQLite:** lo más simple, pero depende del PHP local y SQLite no valida igual que MySQL (tipos, `CHECK`, concurrencia).
2. **Laravel Sail:** oficial y rápido, pero es una imagen de desarrollo genérica (pesada, corre como root, no se parece a producción).
3. **Compose propio:** PHP-FPM alpine + Nginx + MySQL 8.4, con una topología parecida a la de producción.

## Decisión

Compose propio, con 3 servicios (`app`, `web`, `db`), healthchecks, usuario no-root, base de datos de testing separada y un `Makefile` como interfaz única (`make setup`, `make check`).

## Consecuencias

- ✅ Reproducible, liviano y con la misma topología que un despliegue real (proxy + FPM).
- ✅ Los tests corren sobre el mismo motor que producción (MySQL), así que `UNIQUE`, FK y `CHECK` se prueban de verdad.
- ⚠️ Más archivos de infraestructura que mantener que con Sail.
