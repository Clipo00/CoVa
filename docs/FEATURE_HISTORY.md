# CoVa — Historial de Features

> Narrativa de evolución del producto: por qué se construyó cada feature, qué problemas resolvió, y qué se aprendió en el camino.
> Audiencia: Stakeholders técnicos, nuevos devs buscando contexto histórico, y revisiones de producto.

---

## Fase 0: Fundamentos (Mayo 2026)

### El Problema

Configurar entornos de desarrollo es repetitivo, propenso a errores, y cada equipo reinventa la rueda. Los `.env.example` se quedan desactualizados, la documentación de setup vive en Notions olvidados, y onboarding de nuevos devs toma días.

### La Hipótesis

Si centralizamos la configuración de entornos en "plantillas ejecutables" (Blueprints), podemos reducir el setup de un proyecto de horas a segundos. Cada blueprint contiene: variables .env, extensiones de VSCode, servidores MCP, y contexto para agentes AI.

### Decisiones Iniciales

**¿Por qué Laravel y no Node.js/Express?**
- El equipo tenía experiencia en PHP/Laravel
- Eloquent + migrations aceleran el desarrollo de CRUDs
- Livewire permite reactividad sin construir un SPA

**¿Por qué monolito modular?**
- Velocidad de desarrollo MVP
- Cada módulo puede extraerse a microservicio más adelante
- Límites claros entre dominios (Auth, Org, Blueprint)

---

## Fase 1: MVP Foundation (3 May 2026)

### Auth + Shared Infrastructure

**Qué se construyó**: Login, registro, logout, planes configurables, categorías, Value Objects (Email, Uuid, Slug).

**Por qué**: Sin autenticación no hay tenancy. Sin planes no hay monetización. Sin categorías no hay organización.

**Aprendizaje clave**: Los Value Objects validan en constructor (fail fast). `Email` normaliza a lowercase automáticamente — evitó bugs de "usuario registró con mayúsculas y no puede loguear" desde el día 1.

### Organizaciones

**Qué se construyó**: CRUD de organizaciones con slug auto-generado, herencia de plan, soft deletes.

**Por qué**: Los blueprints necesitan pertenecer a un contexto (org). El plan heredado permite que cambios de suscripción del usuario se propaguen automáticamente.

**Decisión técnica**: Soft delete desde el inicio. Recuperación accidental es más común de lo que parece cuando hay múltiples usuarios con permisos.

### Blueprints CRUD

**Qué se construyó**: Crear, listar, ver blueprints. UUID v4 como identificador público. Favoritos.

**Por qué**: El core del producto. Sin blueprints no hay CoVa.

**Decisión técnica**: UUID en lugar de ID autoincremental. URLs como `/blueprints/abc-123` son más seguras (no revelan cantidad de registros) y shareables.

---

## Fase 2: MVP Core (3 May 2026)

### Dashboard

**Qué se construyó**: Centro de control con grid de organizaciones, stats de blueprints, CTA contextual.

**Por qué**: El primer login de un usuario debe orientarlo. Dashboard vacío con CTA grande = onboarding implícito.

**Decisión de UX**: Sin organizaciones → CTA "Crear primera org". Con orgs → grid + botón condicional (deshabilitado si límite alcanzado).

### Roles y Autorización

**Qué se construyó**: Owner, Maintainer, Developer. Policies para cada recurso. Middleware `EnsureOrganizationAccess` y `EnsureRole`.

**Por qué**: Un producto de equipos necesita permisos diferenciados. No todos los miembros deberían poder eliminar la org.

**Matriz de decisión**:
| Rol | Poder | Riesgo |
|-----|-------|--------|
| Owner | Todo | Máximo |
| Maintainer | CRUD blueprints + miembros | Medio |
| Developer | CRUD blueprints (solo suyos) | Mínimo |

**Aprendizaje clave**: La regla "Developer solo puede editar sus propios blueprints" surgió de un escenario real: en equipos grandes, no querés que un dev nuevo sobrescriba la config de producción de otro.

### Variable Manager

**Qué se construyó**: CRUD inline de variables .env con tipos (Fixed, Empty), flags (interactive, secret), secciones, y drag & drop.

**Por qué**: Un blueprint sin variables es solo una descripción. Las variables son el valor real — automatizan el `.env`.

**Decisión técnica**: Variables en tabla relacional (`blueprint_variables`) en lugar de JSON. ¿Por qué? Necesitamos filtrar por tipo ("mostrar solo interactivas"), buscar por key, y ordenar.

**Decisión de UX**: Tabla inline en lugar de modales. Ver 20 variables de un vistazo > 20 modales.

### Plan Limits

**Qué se construyó**: Validación de límites en creación de orgs, blueprints, variables, y miembros.

**Por qué**: Sin límites no hay planes de pago. Sin planes de pago no hay negocio.

**Decisión técnica**: Planes en BD, no hardcodeados. Permite A/B testing de límites y añadir nuevos planes sin deploy.

---

## Fase 3: Colaboración (4 May 2026)

### Invitaciones por Token

**Qué se construyó**: Sistema de invitación con token UUID expirable (48h) de un solo uso.

**Por qué**: Añadir miembros directo por email requiere que el usuario ya exista. Las invitaciones permiten onboarding de nuevos usuarios.

**Flujo diseñado**:
1. Owner invita a `nuevo@dev.com`
2. Sistema genera token → envía email
3. Nuevo dev recibe email → click link
4. Si ya tiene cuenta: se une automáticamente
5. Si no: se registra → se une automáticamente post-registro

**Aprendizaje clave**: El token en URL permite compartir por cualquier canal (Slack, WhatsApp). La expiración de 48h es un balance entre usabilidad y seguridad.

### Soft Delete y Papelera

**Qué se construyó**: Soft delete para blueprints y orgs. Papelera separada (`/blueprints/deleted`). Restauración.

**Por qué**: En un producto de equipos, la eliminación accidental por un miembro es un riesgo real. Sin soft delete, perderías todo el trabajo.

**Decisión técnica**: Force delete disponible solo para Owner en la vista de eliminado. Doble confirmación para eliminación permanente.

### Transferencia de Blueprints

**Qué se construyó**: Transferir un blueprint de una org a otra.

**Por qué**: Escenario real — un equipo crea un blueprint en su org personal y quiere moverlo a la org de la empresa.

**Restricciones**: Solo Owner, solo a orgs propias, valida límite de destino.

### Secciones en Variables

**Qué se construyó**: Campo `section` para agrupar variables visualmente ("Database", "API Keys", etc.).

**Por qué**: Un blueprint con 30 variables sin agrupar es ilegible. Las secciones crean jerarquía visual.

---

## Fase 4: Tabs Dinámicas y AI (5 May 2026)

### El Problema

Un blueprint no es solo variables .env. Para un setup completo necesitás:
- Extensiones de VSCode recomendadas
- Servidores MCP configurados
- Contexto para agentes AI (presets, skills, reglas)

Cada uno de estos tiene una estructura diferente. ¿Cómo los modelamos sin crear 10 tablas nuevas?

### La Solución: Plugin Architecture

**Qué se construyó**: `TabType` enum + `TabManager` genérico. Cada tab se guarda en `tabs_config` JSON con `type` + `config`.

**Tipos implementados**:
| Tipo | Estructura | Caso de uso |
|------|-----------|-------------|
| VSCode Extensions | `extensions: string[]` | Lista de extensiones recomendadas |
| MCP Servers | `servers: {name, command, args[]}[]` | Configuración de servidores MCP |
| AI Context | `presets[], skills[], custom_rules` | Contexto para agentes AI |

**Decisión técnica**: JSON en lugar de tablas relacionales. Cada tipo tiene estructura libre. Para agregar un nuevo tipo: añadir caso al enum + config default en `TabManager::addTab()`. Sin migraciones.

**Decisión de UX**: Tabs colapsables en acordeón. El blueprint show puede tener mucho contenido — el acordeón permite ver el "índice" de un vistazo.

### AI Context y agent.md

**Qué se construyó**: Tab AI Context con presets, skills, y custom rules. Generación de `agent.md` via `ResolveBlueprint`.

**Por qué**: Los agentes AI (Claude Code, Cursor, GitHub Copilot) usan archivos de contexto (`agent.md`, `.cursorrules`). CoVa puede generar estos archivos automáticamente desde la config del blueprint.

**Ejemplo de salida**:
```markdown
# Agent Context — Laravel Project

## Presets
- Laravel 11
- Pest Testing

## Skills
- CoVa Blueprints
- Laravel Actions

## Custom Rules
- Usar strict_types en todo archivo PHP
- Preferir Actions sobre lógica en Controllers
```

**Aprendizaje clave**: La generación de `agent.md` es un diferenciador. Ninguna otra herramienta de config management genera contexto para AI agents.

### Agents y Skills Configurables

**Qué se construyó**: Estructura de directorios `app/Modules/Blueprint/Tabs/AiContext/Presets` y `Skills`. Tests para `AgentGenerator`.

**Por qué**: Queremos que la comunidad pueda añadir presets y skills. Es un paso hacia el marketplace de contexto.

**Estado actual**: Estructura lista, contenido preliminar. En progreso.

---

## Fase 5: Marketplace Preparation (4-5 May 2026)

### Qué se construyó
- Campo `is_public` en blueprints
- Flag `has_marketplace_publish` en planes (solo Pro/Enterprise)
- Org de marketplace creada en seeder

### Por qué

El valor de CoVa se multiplica si los blueprints pueden compartirse públicamente. Un marketplace permite:
- Templates oficiales de la comunidad
- Discovery de blueprints para stacks populares
- Network effects (más usuarios → más blueprints → más usuarios)

### Modelo de negocio

| Plan | Marketplace |
|------|-------------|
| Free | Solo consumir (si se publican blueprints gratuitos) |
| Pro | Publicar blueprints propios |
| Enterprise | Publicar + features de branding |

**Decisión técnica**: Marketplace como org especial. Los blueprints públicos pertenecen a la org "Marketplace" pero mantienen referencia al autor original.

---

## Features Descartadas o Postergadas

| Feature | Razón del descarte | Alternativa implementada |
|---------|-------------------|-------------------------|
| Wizard de 4 pasos explícito | Complejidad de UI alta para MVP | Formulario único con secciones colapsables |
| API REST en MVP | No crítico para validación del producto | Sanctum instalado, API en Fase 3 |
| CLI Node.js/Python | Depende de API REST | Preparado para Fase 3 |
| Billing/Stripe | MVP necesita validación de uso primero | Planes en BD listos para integración |
| SSO/SAML | Overkill para MVP | Auth básica con registro email |
| Real-time colaboración | WebSockets añaden complejidad | Livewire polling suficiente por ahora |
| Blueprint versioning | Schema lock-in | Tabs en JSON permiten evolución sin migraciones |

---

## Métricas de Evolución

| Métrica | 3 May (v0.1) | 4 May (v0.3) | 5 May (v0.4) | Actual |
|---------|-------------|-------------|-------------|--------|
| Tests | 60 | 90 | 117 | 117 |
| Assertions | 100 | 160 | 219 | 219 |
| Módulos | 4 | 4 | 4 | 4 |
| Actions | 12 | 20 | 26 | 26 |
| Livewire Components | 6 | 10 | 14 | 14 |
| Policies | 2 | 4 | 4 | 4 |
| Rutas | 15 | 25 | 30 | 30 |
| Features completas | 8 | 14 | 20 | 20 |
| Features en progreso | 2 | 2 | 2 | 2 |

---

## Lecciones Aprendidas

### 1. Actions sobre lógica en Controllers
Al refactorizar lógica de controllers a Actions, los tests se volvieron 3x más rápidos (no simulan HTTP). Y descubrimos 2 bugs que solo aparecían en tests de controller por el overhead de requests.

### 2. JSON para Tabs fue la decisión correcta
En 2 días agregamos 3 tipos de tabs sin una sola migración. Si hubiéramos usado tablas relacionales, cada nuevo tipo habría requerido: nueva tabla + migración + modelo + relación + factory.

### 3. Livewire tiene curva de aprendizaje
La sincronización entre `TabManager` (hijo) y `BlueprintEditForm` (padre) tomó varias iteraciones. Eventos `tabs-updated` + listeners funcionan bien, pero requieren disciplina en naming.

### 4. Soft deletes son obligatorios en SaaS
Un test de integración borró accidentalmente una org con 5 blueprints. Gracias al soft delete, la recuperación fue 1 línea de código (`$org->restore()`). Sin soft delete, habría requerido restore de backup.

### 5. Conventional commits valen la pena
Generar este changelog tomó 30 minutos gracias a conventional commits. Sin ellos, habría sido imposible reconstruir la historia.

---

## Fase 6: Seguridad — OWASP Top 10:2025 (21 May 2026)

### Contexto
Se identificaron gaps de seguridad durante la creación de la skill `covar-security`. Los 4 gaps críticos se corrigieron en este sprint.

### Bugs Corregidos

1. **A10 — Sin manejo de excepciones**: `bootstrap/app.php` tenía el bloque `withExceptions` completamente vacío. Cualquier error no capturado mostraba stack trace en dev y podía filtrar información interna.
2. **A02 — Sin CSP ni headers de seguridad**: No existía Content-Security-Policy, HSTS, ni Referrer-Policy. La aplicación era vulnerable a XSS, clickjacking, y ataques MITM.
3. **A06 — Sin rate limiting**: Solo Livewire upload tenía throttle. Las rutas POST de Blueprint y Organization no tenían límites, permitiendo abuso de API.
4. **A04 — SESSION_ENCRYPT=false**: Los datos de sesión se almacenaban en texto plano en la DB.

### Decisiones Técnicas

| Decisión | Alternativa | Por qué |
|----------|-------------|---------|
| CSP con `'unsafe-inline'` y `'unsafe-eval'` | CSP estricto con nonces | Alpine.js requiere inline scripts y Livewire requiere eval. Nonces son más seguros pero inviables con el stack actual |
| Una skill de seguridad integral | 10 skills separadas por OWASP | La seguridad es transversal — un archivo puede tener A01 + A05 + A09. 10 skills sería insostenible |
| `EnsureSecurityHeaders` como middleware GLOBAL | Por ruta | Los headers de seguridad deben aplicarse a TODAS las respuestas, incluyendo errores |
| Rate limiting: 30/min CRUD, 5/min sensible | Un solo rate para todos | Operaciones destructivas (force-delete, invite, role change) merecen límite más restrictivo |

### Aprendizajes Clave

1. **El route cache miente**: Después de modificar rutas, siempre hacer `php artisan optimize:clear` antes de verificar. El route cache mantenía referencias viejas con throttles duplicados.
2. **Error pages sin Livewire**: Las páginas de error deben funcionar SIN Livewire porque los errores ocurren antes de que los componentes booteen. Usar `@vite()` directo, no layouts que dependan de `@livewireScripts`.
3. **CSP y Alpine.js**: Alpine.js usa `eval()` internamente para evaluar expresiones en atributos `x-*`. Sin `'unsafe-eval'` en CSP, las expresiones de Alpine no funcionan.
4. **Slug vs ID en URLs**: Los IDs auto-incrementales NUNCA deben aparecer en URLs GET. El slug resuelve la org, el ID se usa internamente en POST (protegido por CSRF + server-side validation).

### Próximos Pasos (Postergados)
- Deploy config: `config:cache`, `APP_KEY` generation, verificar `APP_DEBUG=false`
- CSP monitoring: ajustar directivas según reportes de producción
- Audit logging: canal separado para operaciones sensibles
- Signed URLs para invitaciones
- MFA para organizations Enterprise

---

**Documento generado**: 2026-05-15  
**Versión**: 1.0  
**Última actualización**: Fase 4 del plan de documentación
