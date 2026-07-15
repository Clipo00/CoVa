# CoVaR — Historial de Features

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

**Por qué**: El core del producto. Sin blueprints no hay CoVaR.

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

**Por qué**: Los agentes AI (Claude Code, Cursor, GitHub Copilot) usan archivos de contexto (`agent.md`, `.cursorrules`). CoVaR puede generar estos archivos automáticamente desde la config del blueprint.

**Ejemplo de salida**:
```markdown
# Agent Context — Laravel Project

## Presets
- Laravel 11
- Pest Testing

## Skills
- CoVaR Blueprints
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

El valor de CoVaR se multiplica si los blueprints pueden compartirse públicamente. Un marketplace permite:
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
| API REST en MVP | No crítico para validación del producto | Sanctum instalado, API completada en Fase 3 ✅ |
| CLI Node.js/Python | Depende de API REST | CLI Laravel Zero completado en Fase 13 ✅ |
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

## Fase 7: Landing Page (Mayo 2026)

### El Problema

La ruta raíz (`/`) mostraba la landing default de Laravel 13: un SVG del logo de Laravel, links a Laracasts y documentación, y botón "Deploy now". Esto era confuso para visitantes que llegaban a CoVaR sin saber qué hace el producto. No comunicaba el valor diferencial ni tenía CTAs hacia registro.

Además, visitantes autenticados veían la misma landing genérica en lugar de ser redirigidos al dashboard.

### La Solución

Una landing page de alto impacto estructurada en 6 secciones:

1. **Hero**: Terminal animada ejecutando `covar vault:fetch` para demostrar el core loop del producto en segundos
2. **The Pain**: Tres cards que crean empatía con problemas reales (caos del .env, configuración manual, falta de estandarización)
3. **How it Works**: Define → Publish → Fetch, el flujo completo explicado en 30 segundos
4. **Marketplace Preview**: Grid con 6 plantillas populares (mock data) que muestran el valor inmediato
5. **CTA Final**: Botón grande "Crear cuenta gratis" con copy de bajo riesgo
6. **Footer**: Links a login, registro, marketplace

### Decisiones Técnicas

| Decisión | Alternativa | Por qué |
|----------|-------------|---------|
| Layout landing separado de app.blade.php | Usar app.blade.php con @guest | app.blade.php tiene nav de dashboard con links a orgs/blueprints que no existen para guests |
| Alpine.js para terminal typing | CSS-only, Lottie, GSAP | Alpine ya está cargado vía Livewire. 0 dependencias nuevas. Control total del timing |
| IntersectionObserver nativo para scroll reveal | Librería externa (AOS, ScrollReveal) | < 50 líneas de JS, sin dependencias, respeta prefers-reduced-motion |
| Mock data para marketplace | DB queries reales | La landing es pública; los blueprints públicos no existen todavía como feature completa |
| Terminal en Hero como componente Blade | Inline en la vista | Reutilizable para futuras páginas (docs, blog) |

### Arquitectura de Archivos

```
resources/
├── views/
│   ├── layouts/
│   │   └── landing.blade.php    ← Layout limpio (nav minimalista, SEO, footer)
│   ├── landing/
│   │   ├── index.blade.php      ← Vista principal (incluye partials)
│   │   └── partials/
│   │       ├── hero.blade.php
│   │       ├── pain-point.blade.php
│   │       ├── how-it-works.blade.php
│   │       ├── marketplace-preview.blade.php
│   │       ├── demo.blade.php
│   │       ├── pricing.blade.php
│   │       ├── cta-final.blade.php
│   │       └── footer.blade.php
│   └── components/
│       └── animated-terminal.blade.php
├── js/
│   └── landing.js               ← Entry point Vite (0.31KB)
lang/
├── es/landing.php
└── en/landing.php
```

### Aprendizajes Clave

1. **El layout app.blade.php NO sirve para landing**: Tiene nav de dashboard con links a blueprints, orgs y trash — conceptos que no existen para usuarios no autenticados. Un layout separado es obligatorio.

2. **Alpine.js para animaciones es perfecto para este caso**: La terminal typing animation con Alpine.data() es declarativa, reactiva, y reutilizable. Sin dependencias externas, ~80 líneas de JS.

3. **`prefers-reduced-motion` no es opcional**: Muchos devs usan `prefers-reduced-motion: reduce`. Ignorarlo es una mala experiencia de usuario. La landing lo respeta en terminal typing, scroll reveal, y hover effects.

4. **Las landing pages son el punto ciego de seguridad más común**: Como no tienen auth, suelen no auditarlas. La landing de CoVaR no expone datos sensibles, no tiene formularios, y todos los CTAs apuntan a rutas internas protegidas.

5. **Mock data > DB queries para landing**: Las landing pages son públicas y deben cargar rápido. Consultar la BD para mostrar plantillas del marketplace introduce latencia innecesaria y complejidad. Mock data es la decisión correcta hasta que el marketplace sea una feature real.

### Refinamientos Post-Implementación (2026-05-28)

| Refinamiento | Motivación | Decisión |
|--------------|-----------|----------|
| **Simplificar logo** | El recuadro de caja fuerte + rueda era visualmente ruidoso a tamaños pequeños | Solo el dial de combinación sobre fondo azul. Más limpio, más legible en 32×32px |
| **Favicon SVG** | Pestañas del navegador mostraban el favicon genérico de Laravel | Data URI SVG inline en ambos layouts. Sin requests extra, siempre actualizado con el logo |
| **Fix i18n terminal** | Textos hardcodeados en español dentro del JS de Alpine.js ignoraban el idioma del usuario | Pasar las traducciones desde Blade como prop `:lines` al componente. Alpine recibe array traducido vía `json_encode()` |
| **Fix i18n demo** | Las 3 slides de la demo tenían textos hardcodeados en español | Extraer 37 nuevas keys de traducción (`demo_dash_*`, `demo_org_*`, `demo_bp_*`) |

### Aprendizajes Clave (Refinamientos)

6. **Los SVG como data URI son perfectos para favicons**: No requieren archivos estáticos, se cachean con el HTML, y escalan a cualquier tamaño sin pérdida. Un `<link rel="icon" type="image/svg+xml" href="data:image/svg+xml,...">` es más mantenible que un `.ico` o `.png`.

7. **Nunca hardcodear strings visibles en JavaScript del cliente**: Alpine.js corre en el browser, después de que Laravel renderizó. Si el JS tiene strings en español, los usuarios en inglés verán español. La solución: pasar las traducciones desde Blade como props, no como literales en JS.

8. **La consistencia del logo importa en todos los touchpoints**: Nav, footer, favicon, apple-touch-icon. Usar el mismo SVG en todos lados refuerza la marca. El favicon no es un detalle menor — es lo primero que ve el usuario en la pestaña.

**Documento generado**: 2026-05-23  
**Versión**: 1.2  
**Última actualización**: Security Validation Audit (Junio 2026)

---

## Fase 2: Security Hardening (Junio 2026)

### El Problema

Una auditoría de seguridad reveló 6 gaps de autorización y validación que comprometían el modelo de seguridad de CoVaR. Los roles no se respetaban correctamente (maintainers podían cambiar roles), las invitaciones no verificaban identidad, no había protección contra emails desechables, y no existía MFA.

### Decisiones Clave

**¿Por qué restringir cambio de roles a solo owner?**
- El modelo de seguridad estándar en SaaS multi-tenant requiere que solo el owner pueda escalar privilegios. Permitir que un maintainer cambie roles abre la puerta a escalación horizontal: un maintainer podría promocionar a otro maintainer a owner-equivalente.

**¿Por qué `propaganistas/laravel-disposable-email` y no una lista manual?**
- El paquete tiene ~3M de descargas, 601 estrellas, cero issues abiertos, y recibe actualizaciones semanales de la blacklist `disposable/disposable`. Una lista manual se desactualiza en días. El comando `disposable:update` programable en el scheduler garantiza mantenimiento cero.

**¿Por qué MFA por email y no TOTP (Google Authenticator)?**
- El MVP prioriza simplicidad de implementación y UX. Email MFA no requiere que el usuario instale apps externas. La migración a TOTP/Webauthn se puede hacer más adelante sin romper la infraestructura existente (la tabla `mfa_codes` es genérica).

**¿Por qué feature-branch-chain para los PRs del Track B?**
- El Track B (~630 líneas) excedía el budget de revisión de 400 líneas. Partirlo en 3 PRs encadenados (PR1 → PR2 → PR3) mantiene cada diff bajo 300 líneas, y el feature-branch-chain evita conflictos de merge acumulativos.

### Lo Que Se Hizo

| Gap | Severidad | Solución | Archivos |
|-----|-----------|----------|----------|
| Maintainer cambia roles | HIGH | `UpdateOrganizationUserRole` restringido a `isOwner()`, nuevo gate `updateMemberRole` en `OrganizationPolicy` | Action, Policy, Controller |
| Developer borra blueprints | MEDIUM | `BlueprintPolicy::delete()` ahora solo owner (alineado con SKILL.md) | Policy |
| Invitación sin verificación de email | HIGH | `AcceptInvitation` verifica match de email + límite de miembros del plan | Action, Exception |
| Tabs duplicadas en blueprint | MEDIUM | Dedup en `TabManager::addTab()` + validación en `BlueprintCreateForm`/`BlueprintEditForm` | Livewire, Forms, Blade |
| Transfer sin check de límite | HIGH | `TransferBlueprint` chequea `max_blueprints_per_org` en org destino | Action |
| Registro sin verificación de email | CRITICAL | `MustVerifyEmail` en User, signed URL verification, bloqueo de disposable emails | Model, Controller, Action, Rule, Routes |
| Sin MFA | CRITICAL | Infraestructura completa: `mfa_codes` table, `SendMfaCode`/`VerifyMfaCode` actions, Livewire challenge UI, toggle en perfil, rate limiting OWASP A07 | Migration, Model, Actions, Livewire, Views, Routes |

### Aprendizajes Clave

9. **Rate limiting en MFA no es opcional**: Un código de 6 dígitos tiene 1/1,000,000 de probabilidad por intento. Sin throttle, un atacante con 100 requests/segundo lo rompe en ~2.7 horas. Con `throttle:5,1` (5 intentos/minuto), se necesitan ~139 días. La diferencia es pasar de "vulnerable" a "computacionalmente inviable".

10. **El paquete de disposable email se actualiza solo**: `propaganistas/laravel-disposable-email` publica releases semanales con la última blacklist. Programar `disposable:update` en el scheduler de Laravel garantiza que la lista esté siempre fresca sin intervención manual.

11. **`MustVerifyEmail` de Laravel ya hace el 80% del trabajo**: La interfaz `MustVerifyEmail` en el modelo User + el evento `Registered` disparan automáticamente el envío del email de verificación. Solo hizo falta crear el controller de verificación con signed URLs. No reinventar la rueda.

---

## Fase 8: Marketplace v1 (Junio 2026)

### El Problema

CoVaR tenía la infraestructura básica para publicar blueprints (`is_public`, toggle de publish), pero no era un marketplace real. No había listado público, ni búsqueda, ni forma de que los usuarios descubrieran plantillas de la comunidad. El "marketplace preview" en la landing page usaba mock data hardcodeado.

### La Hipótesis

Un marketplace real con descubrimiento, suscripciones (fork), votación y notificaciones crearía network effects: más blueprints públicos → más usuarios → más blueprints. Las suscripciones como "forks" (copias independientes) permitirían a los usuarios empezar desde una plantilla sin depender del original.

### Decisiones Clave

**¿Por qué un módulo separado y no extender Blueprint?**
- Marketplace es un dominio distinto: descubrimiento, suscripción, votación. Meterlo en Blueprint acoplaría conceptos que no son core del CRUD de blueprints.
- El módulo Marketplace consume Blueprint vía relaciones, no modifica su lógica interna.
- Si mañana queremos extraer Marketplace a un microservicio, el límite ya está definido.

**¿Por qué suscripciones ilimitadas pero edición capeada por plan?**
- La suscripción es un acto de "guardar para después". No cuesta recursos significativos.
- La edición (crear/modificar blueprints) es lo que consume recursos del plan (variables, tabs, storage).
- Un usuario Free puede suscribirse a 50 plantillas pero solo editar 3. Es como "guardar en favoritos con copia".

**¿Por qué notificaciones in-app y no email?**
- El MVP prioriza simplicidad. Email requiere templates, colas de mail, manejo de bounces.
- El buzón in-app es inmediato, no depende de deliverability, y no satura la bandeja del usuario.
- Email se puede agregar después como canal adicional sin romper la infraestructura existente.

**¿Por qué votos anónimos al usuario pero trazables?**
- La transparencia total de votos (quién votó qué) puede inhibir votos negativos honestos por miedo a represalias.
- La trazabilidad interna permite auditoría anti-abuso (detección de granjas de votos) sin exponer al usuario.
- Es el mismo modelo que usa Reddit: los votos son privados, pero el sistema sabe quién votó.

**¿Por qué desvincular en vez de borrar suscripciones al eliminar un blueprint?**
- Borrar la suscripción pierde la trazabilidad. El usuario ya no sabe que estuvo suscrito.
- Poner `subscribed_blueprint_id = null` mantiene el registro histórico y la copia del usuario sigue funcionando.
- La copia es completamente independiente: tiene su propio UUID, su propia org, y no depende del original.

### Lo Que Se Hizo

| Feature | Archivos clave |
|---------|---------------|
| Listado público | `MarketplaceList` Livewire, `/marketplace`, búsqueda, tags, sort, paginación |
| Vista detalle | `marketplace/show.blade.php`, reusa partials de Blueprint |
| Suscripción/Fork | `SubscribeToBlueprint` Action, `blueprint_subscriptions` table |
| Votación | `VoteOnBlueprint` Action, `blueprint_votes` table, Alpine.js optimistic UI |
| Notificaciones | `NotificationBell` Livewire, `NotifySubscribers` Job (batched), buzón `/notifications` |
| Delete flow | `DeleteBlueprint` modificado: notifica → desvincula → soft-delete |

### Features Pre-CLI (misma sesión)

Antes del marketplace, se cerraron gaps para dejar la app lista para la Fase 3 (API + CLI):

| Gap | Solución |
|-----|----------|
| Templates de tabs | 3 stacks (Laravel, Node.js, Python) con IDs reales |
| Publicar blueprint UI | Toggle `is_public` + policy + badges + delete warning |
| Presets/Skills | 7 presets + 5 skills dinámicos, Blade refactorizado |
| Live Preview | Panel colapsable en create/edit con debounce 300ms |
| Password toggle | Ojito en 6 campos (login, register, profile) |
| Planes en orgs | `plan_id` eliminado de organizations, plan delegado al owner |

### Aprendizajes Clave

12. **Un módulo nuevo es menos disruptivo que extender uno existente**: Marketplace toca Blueprint solo en 2 puntos (UpdateBlueprint y DeleteBlueprint para disparar notificaciones). Todo lo demás es autocontenido. Si hubiéramos metido subscriptions y votes en Blueprint, el modelo Blueprint tendría 10 relaciones más.

13. **Las notificaciones por lotes son obligatorias desde el día 1**: Un blueprint con 500 suscriptores no puede disparar 500 inserts en el request del owner. El job `NotifySubscribers` con `chunk(100)` y database queue lo resuelve sin complejidad de infraestructura (Redis, SQS).

14. **Los cached counters evitan N+1 en listados**: `votes_count` y `subscribers_count` como columnas en `blueprints` permiten ordenar por rating sin subconsultas. Actualizarlos atómicamente (`increment`/`decrement`) es más barato que recalcular con `COUNT` en cada carga.

15. **Desvincular > Borrar**: Cuando un blueprint del marketplace se elimina, los suscriptores pierden la relación con el original pero conservan su copia. Si borráramos la copia, el usuario perdería trabajo. Si borráramos la suscripción, perderíamos auditoría. `subscribed_blueprint_id = null` es el punto medio.

---

## Fase 9: Friendly URLs & Downloads (Junio 2026)

**El Problema**: Las URLs de blueprints usaban UUIDs (`/blueprints/550e8400-e29b-...`) que son ilegibles, difíciles de compartir, y perjudican el SEO. Los usuarios no tenían forma de descargar el contenido del blueprint (agent.md, .env template) como archivos para usar en sus proyectos.

**Decisiones Clave**: Slugs en lugar de UUIDs para legibilidad. 301 redirects de UUID→slug mantienen links viejos. Descargas client-side con Alpine.js Blob sin nuevas rutas. Mutation routes (POST/PUT/DELETE) mantienen UUID por seguridad.

**Lo Que Se Hizo**: Friendly URLs `/b/{slug}` con route model binding. Legacy 301 redirects. Vault fetch CLI card en show page. Descargas de agent.md, .env template, y per-segment .md con Alpine.js Blob. `GenerateEnvTemplate` Action. Auth loading spinners en login/register.

**Aprendizajes Clave**:
16. **Descargas client-side evitan complejidad**: Blob + URL.createObjectURL() + `<a download>` permite descargas sin endpoints nuevos. Los datos ya están en el DOM tras autorización. Sin riesgo de exposición.
17. **Slugs en GET, UUIDs en POST**: Separar identificadores por verbo HTTP es más seguro que usar uno solo para todo. Slugs son legibles, UUIDs son inmutables.

---

## Fase 10: Segment CRUD & Dashboard Polish (Junio 2026)

**El Problema**: El AI Context tab usaba toggles que inyectaban marcadores HTML (`<!-- BEGIN:preset:... -->`) en un textarea. Esto era frágil (regex para quitar bloques), no escalaba, y los usuarios no podían reordenar ni editar contenido individualmente. El dashboard carecía de estadísticas y empty states.

**La Solución: Segmentos modulares**: Cada preset/skill se convierte en un "segmento" — card colapsable con nombre, tipo, contenido y orden. Son DTOs (`AiContextSegment`), no texto escapado en textarea.

**Decisiones Clave**: Segmentos como DTOs con validación en construcción. Tipos: preset, skill, custom. Consumen slots de variables del plan. agent.md como router de segmentos. Sin backward compat (proyecto no publicado).

**Lo Que Se Hizo**: `AiContextSegment` DTO + `AiContextConfig` refactor. TabManager segment CRUD (add, remove, move, update). Blade rewrite con dropdowns + cards colapsables. AgentGenerator itera segments. Templates en formato segments. Dashboard polish con 5 UI deliverables (stats row, org cards, marketplace empty, blueprint badge, org show count). 463 tests.

**Aprendizajes Clave**:
18. **DTOs previenen bugs de forma**: `AiContextSegment` valida `type` contra enum en construcción. Imposible crear segmento con tipo inválido.
19. **Contenido del registry no se precarga en UI al seleccionar template**: Los tabs se crean correctamente pero los textareas aparecen vacíos. Livewire no serializa propiedades privadas; requiere hidratación adicional.

---

## Fase 11: Onboarding Wizard (Junio 2026)

**El Problema**: Post-registro, usuarios llegaban a dashboard vacío sin guía. Sin camino claro, la tasa de abandono era alta.

**La Solución**: Wizard de 4 pasos: Bienvenida → Crear Org → Invitar → Completar. Skip-all para usuarios que prefieren explorar solos.

**Decisiones Clave**: Livewire wizard sin recarga entre pasos. `onboarding_step` en BD para persistencia. Middleware `EnsureOnboardingCompleted` redirige al wizard. Skip-all marca `onboarding_completed_at` inmediatamente. Email banner no bloqueante. 3 chained PRs (~630 loc).

**Lo Que Se Hizo**: `OnboardingWizard` Livewire. `EnsureOnboardingCompleted` middleware. Columnas `onboarding_step` + `onboarding_completed_at` en users. RegisterForm redirect a `/onboarding`. i18n `onboarding.php`. `OnboardingFlowTest.php` con 7 tests de integración. 463 tests, 1029 assertions.

**Aprendizajes Clave**:
20. **Flash message y orden de limpieza**: El bug C1 fue limpiar `inviteEmail` ANTES del flash message. Guardar en variable local primero.
21. **Middleware de onboarding no debe bloquear rutas de utilidad**: Whitelist debe incluir logout, locale switch, y cualquier ruta necesaria para salir del wizard.

---

## Fase 12: API Token Management (Junio 2026)

**El Problema**: CoVaR necesitaba tokens de API para que el futuro CLI (`covar fetch`) pudiera autenticarse. Sanctum estaba instalado pero completamente sin usar: no había `HasApiTokens` en User, no existía la migración `personal_access_tokens`, y no había ninguna UI para gestionar tokens.

**La Solución**: Integrar Sanctum en el módulo Auth existente y rediseñar el perfil de usuario en tabs para acomodar los tokens junto a las settings de seguridad existentes (password, MFA).

**Decisiones Clave**: 
- **Perfil en tabs**: Datos, Cuenta, Seguridad — los API tokens viven en Seguridad junto con el concepto de "acceso externo"
- **Token de un solo uso**: el plain-text token se muestra UNA vez con botón copiar y advertencia — patrón estándar de GitHub, GitLab, npm
- **Expiración obligatoria**: máximo 1 año, requerido en creación. Sin tokens eternos.
- **Plan-gating**: solo Pro/Enterprise — Free ve CTA de upgrade
- **Password confirmation para crear Y revocar**: OWASP A07 — previene session riding
- **RateLimiter en componente**: no depende de rutas HTTP, Livewire maneja el throttle internamente

**Lo Que Se Hizo**: `HasApiTokens` en User. Migración Sanctum. Prefijo `covar_` en config. `ApiTokenManager` Livewire con CRUD completo. `CreateApiToken` y `RevokeApiToken` Actions con `VerifiesPassword` trait. Perfil con 3 tabs Alpine.js + URL hash sync. 24 tests nuevos (7 unit + 14 feature + 3 tabs). 487 tests, 0 regresiones.

**Aprendizajes Clave**:
22. **RateLimiter de Laravel en Livewire > throttle en rutas**: Al poner el rate limit en el componente Livewire con `RateLimiter::attempt()`, el límite aplica sin necesidad de rutas HTTP dedicadas. Esto evita crear métodos vacíos en controllers solo para que exista un endpoint rate-limited.

23. **El trait `VerifiesPassword` evita duplicación**: Tanto `CreateApiToken` como `RevokeApiToken` necesitan verificar la contraseña del usuario. Extraerlo a un trait (`VerifiesPassword`) mantiene DRY sin herencia forzada. En un futuro, `LoginUser` también podría usarlo.

---

## Fase 13: CLI (`covar`) + API REST (Julio 2026)

**El Problema**: Los usuarios solo podían interactuar con CoVaR vía web. Para hacer scaffolding de un blueprint necesitaban copiar archivos manualmente desde la UI. El `git clone → entorno productivo` en segundos era el pitch central del producto pero no existía sin CLI.

**La Solución**: CLI autocontenido basado en Laravel Zero 2.0 compilado como PHAR (~11.5 MB). API REST JSON con Sanctum como backing layer.

**Decisiones Clave**:
- **Laravel Zero sobre Node.js/Python**: Mantener el stack PHP. Sin dependencias de runtime externas.
- **PHAR autocontenido**: Sin requisitos de instalación — descargar, chmod, ejecutar.
- **Secret double-auth flow**: Variables secretas se desencriptan con contraseña adicional, no se exponen en el token de API.
- **API rate-limited con plan-gating**: Free bloqueado, Pro/Enterprise con 60 req/min. RFC 7807 errors.

**Lo Que Se Hizo**:
- Comandos CLI: `config:set-key`, `vault:list`, `vault:fetch <slug>`
- `vault:fetch` genera `.env` con variables resueltas, `.vscode/extensions.json`, y `.agent.md`
- API endpoints: `GET /api/blueprints`, `GET /api/blueprints/{slug}`, `GET /api/me`, `POST /api/fetch/{slug}/verify`
- Autenticación Sanctum `auth:sanctum` con prefijo `covar_`
- Tests CLI: 3+ suites de comandos
- Builds compilados en `cli/builds/covar`
- Script `railway-build.sh` genera el PHAR en cada deploy

**Aprendizajes Clave**:
27. **PHAR > binario nativo para PHP**: Compilar a PHAR con `box` permite distribución cross-platform sin compilación por SO. El overhead de ~11.5 MB es aceptable para una tool de desarrollo.
28. **Secret double-auth es necesario**: Las variables marcadas como secretas no pueden viajar en el token de API. Un segundo factor (contraseña del usuario) desencripta los valores en el momento del fetch, no antes.

---

## 2026-07-03 — Refactor: Presets → Skills, Categories → Tags

### El Problema

El módulo Blueprint mantenía dos conceptos redundantes: **Presets** (como segmentos predefinidos de AI Context) y **Categories** (como taxonomía de blueprints). Los Presets duplicaban la funcionalidad de Skills con una interfaz diferente. Las Categories eran una tabla separada con 8 valores fijos que podían modelarse como Tags polimórficos.

### Lo Que Se Hizo

1. **Presets → Skills**: Se eliminaron 7 clases Preset y se reemplazaron por clases Skill. El segment type `preset` ya no existe — solo `skill`, `custom`, `agent`.
2. **Categories → Tags**: Se eliminó la tabla `categories` y `category_id` de blueprints. Se creó el modelo `App\Models\Tag` con relación `belongsToMany` via `blueprint_tag` pivot.
3. **BlueprintTag model eliminado**: Código muerto removido.

### Métricas del Cambio

- **35 archivos modificados**, +208/-471 líneas
- **Judgment Day Round 2** aprobado sin issues
- Commit: `37f1861`

### Aprendizajes Clave

24. **Eliminar código muerto es tan importante como escribir código nuevo**: El modelo `BlueprintTag` había quedado huérfano después del refactor inicial. Su eliminación redujo la superficie de posibles bugs.
25. **Tags polimórficos > tablas de categorías fijas**: Con 8 categorías predefinidas, cada nuevo tipo de blueprint requería evaluar si agregar una categoría nueva. Tags resuelven esto con una taxonomía flexible y escalable.
26. **La consistencia del modelo de datos simplifica la UI**: Al unificar Presets y Skills bajo un mismo modelo de segmentos, la UI del AI Context tab pasó de tener dos dropdowns ("Add preset", "Add skill") a uno solo ("Add skill"), reduciendo la carga cognitiva del usuario.

---

**Documento generado**: 2026-05-23  
**Versión**: 1.7  
**Última actualización**: 2026-07-08 — Fase 13 CLI + API REST documentada, de-scoping actualizado  
**Última actualización**: 2026-07-03 — Presets→Skills, Categories→Tags
