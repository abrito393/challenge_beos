# 000 — Foundation

- **Estado:** Approved
- **Autor:** Aquiles Brito
- **Fecha:** 2026-10-29

## Contexto

Antes de cualquier feature de negocio necesitamos una base reproducible: un evaluador debe poder clonar el repositorio y tener la API corriendo, con datos de prueba y tests en verde, sin instalar nada más que Docker.

## Objetivos

- Levantar el entorno completo con un solo comando.
- Tener las herramientas de calidad (tests, análisis estático, estilo) configuradas desde el día uno, para que cada feature posterior se verifique igual.
- Contar con CI que impida integrar código que no cumpla la constitution.

## Fuera de alcance

- Despliegue a producción (cloud, Kubernetes, etc.).
- Observabilidad avanzada (APM, tracing).

## Historias de usuario

- **US-000-1:** Como evaluador, quiero ejecutar `make setup` y tener la API disponible en `http://localhost:8080`, para revisar el challenge sin fricción.
- **US-000-2:** Como desarrollador, quiero ejecutar `make check` y obtener lint + análisis estático + tests, para saber si mi cambio está terminado.
- **US-000-3:** Como revisor, quiero que cada push ejecute la misma verificación en CI.

## Criterios de aceptación

- **AC-000-01:** Dado un clon limpio con Docker instalado, cuando ejecuto `make setup`, entonces los contenedores `app`, `web` y `db` quedan saludables, las migraciones y seeders se ejecutan y `GET /up` responde `200`.
- **AC-000-02:** Dado el proyecto levantado, cuando ejecuto `make test`, entonces la suite Pest corre contra una base de datos aislada y no toca los datos de desarrollo.
- **AC-000-03:** Dado el proyecto, cuando ejecuto `make check`, entonces se ejecutan Pint (modo test), Larastan nivel máximo y Pest, y el comando falla si alguno falla.
- **AC-000-04:** Dado un push o pull request, cuando corre GitHub Actions, entonces se ejecuta el equivalente a `make check` sobre MySQL 8.
- **AC-000-05:** Dado cualquier request a `/api/*`, cuando no envía `Accept: application/json`, entonces la respuesta sigue siendo JSON.

## Requisitos no funcionales

- PHP 8.3, Laravel 12, MySQL 8.4.
- Imagen de la app basada en `php:8.3-fpm-alpine` con usuario no-root.
- Tiempo de `make setup` en máquina limpia: < 5 minutos (excluyendo descarga de imágenes).
