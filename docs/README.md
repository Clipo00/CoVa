# Documentación de CoVaR

> Centro de navegación de toda la documentación del proyecto.
> Si llegaste acá sin saber qué leer, empezá por la sección **"¿Quién sos?"**.

---

## ¿Quién sos?

### Soy stakeholder / product owner / nuevo en el proyecto
→ Empezá por acá:
1. [`PROJECT_SUMMARY.md`](PROJECT_SUMMARY.md) — Qué es CoVaR, arquitectura general, estado actual
2. [`FUNCTIONAL.md`](FUNCTIONAL.md) — Qué hace el producto, flujos de usuario, reglas de negocio
3. [`CHANGELOG.md`](../CHANGELOG.md) — Qué se entregó y cuándo

### Soy desarrollador y me sumo al equipo
→ Empezá por acá:
1. [`README.md`](../README.md) — Quickstart, stack, overview
2. [`CONTRIBUTING.md`](CONTRIBUTING.md) — Setup del entorno, convenciones, cómo agregar features
3. [`ARCHITECTURE.md`](ARCHITECTURE.md) — Cómo está construido, patrones, flujo de request
4. [`TESTING.md`](TESTING.md) — Cómo testear, fixtures, cobertura

### Soy desarrollador y necesito entender una parte específica
→ Buscá por módulo:

| Necesito entender... | Documento | Sección |
|---------------------|-----------|---------|
| Auth (login/register/logout) | [`FUNCTIONAL.md`](FUNCTIONAL.md#2-módulo-auth) | Sección 2 |
| Organizaciones (CRUD, roles, miembros) | [`FUNCTIONAL.md`](FUNCTIONAL.md#3-módulo-organization) | Sección 3 |
| Blueprints (CRUD, variables, tabs) | [`FUNCTIONAL.md`](FUNCTIONAL.md#4-módulo-blueprint) | Sección 4 |
| Permisos (Owner/Maintainer/Developer) | [`FUNCTIONAL.md`](FUNCTIONAL.md#4-módulo-blueprint) | Policies + RN |
| Tabs dinámicas (VSCode, MCP, AI) | [`ARCHITECTURE.md`](ARCHITECTURE.md#55-plugin-architecture-tabs) | Patrón Plugin |
| Cómo agregar un módulo nuevo | [`ARCHITECTURE.md`](ARCHITECTURE.md#34-agregar-un-nuevo-módulo-paso-a-paso) | Paso a paso |
| Cómo testear Actions | [`TESTING.md`](TESTING.md#3-tests-unitarios) | Sección 3.1 |
| Cómo testear Policies | [`TESTING.md`](TESTING.md#3-tests-unitarios) | Sección 3.2 |
| Decisiones técnicas del proyecto | [`PROJECT_SUMMARY.md`](PROJECT_SUMMARY.md#decisiones-técnicas-clave) | Decisiones |
| Historia y evolución del producto | [`FEATURE_HISTORY.md`](FEATURE_HISTORY.md) | Narrativa completa |

### Soy QA / necesito entender flujos de usuario
→ [`FUNCTIONAL.md`](FUNCTIONAL.md#6-flujos-de-usuario-end-to-end) — Flujos End-to-End con paso a paso

### Soy designer / UX
→ [`UI_SPECIFICATION.md`](UI_SPECIFICATION.md) — Pantallas, componentes, decisiones de UX

---

## Mapa de Documentos

### Documentos de Producto

| Documento | Qué contiene | Estado | Última actualización |
|-----------|-------------|--------|---------------------|
| [`PROJECT_SUMMARY.md`](PROJECT_SUMMARY.md) | Visión general, arquitectura, módulos, estado actual | ✅ Actualizado | 2026-05-15 |
| [`FUNCTIONAL.md`](FUNCTIONAL.md) | Requisitos funcionales, actores, user stories, reglas de negocio | ✅ Generado | 2026-05-15 |
| [`UI_SPECIFICATION.md`](UI_SPECIFICATION.md) | Especificación de interfaz, componentes, estados, decisiones UX | ✅ Generado | 2026-05-15 |
| [`CHANGELOG.md`](../CHANGELOG.md) | Historial de cambios por versión (Keep a Changelog) | ✅ Generado | 2026-05-15 |
| [`FEATURE_HISTORY.md`](FEATURE_HISTORY.md) | Narrativa de evolución, decisiones, lecciones aprendidas | ✅ Generado | 2026-05-15 |

### Documentos Técnicos

| Documento | Qué contiene | Estado | Última actualización |
|-----------|-------------|--------|---------------------|
| [`ARCHITECTURE.md`](ARCHITECTURE.md) | Arquitectura modular, patrones, flujo de request, decisiones | ✅ Generado | 2026-05-15 |
| [`CONTRIBUTING.md`](CONTRIBUTING.md) | Setup, convenciones, flujo de trabajo, troubleshooting | ✅ Generado | 2026-05-15 |
| [`TESTING.md`](TESTING.md) | Pirámide de tests, patrones, cobertura, anti-patrones | ✅ Generado | 2026-05-15 |

### Otros

| Documento | Qué contiene |
|-----------|-------------|
| [`README.md`](../README.md) | Overview del proyecto, quickstart, stack, links a docs |
| [`.agents/skills/`](../.agents/skills/) | Skills de AI para el proyecto (CoVaR-specific) |

---

## Convenciones de la Documentación

- **Fechas**: Formato ISO 8601 (`YYYY-MM-DD`)
- **Estados**:
  - ✅ Actualizado / Generado — Documento refleja el estado actual del código
  - 🚧 En progreso — Documento refleja estado parcial, hay secciones pendientes
  - 📋 Legacy — Documento desactualizado, en proceso de migración
- **Links**: Todos los links son relativos al repo (funcionan en GitHub, GitLab, o wiki)
- **Versionado**: Los documentos incluyen fecha de última actualización en el footer

---

## Mantenimiento

Esta documentación se mantiene como código:
- Vive en el repo (`docs/`)
- Se versiona con git
- Se actualiza en los checkpoints de cada fase
- Si cambia el código, se actualiza el doc correspondiente

**Regla de oro**: Si implementás una feature nueva, actualizá el doc que corresponda antes de cerrar el PR.

---

**Documento generado**: 2026-05-15  
**Versión**: 1.0  
**Última actualización**: Fase 5 del plan de documentación
