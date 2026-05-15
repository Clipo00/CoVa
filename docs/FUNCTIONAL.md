# CoVa — Especificación Funcional

> Documento de requisitos funcionales y comportamiento del producto.
> Audiencia: Desarrolladores, product owners, y stakeholders técnicos.
>
> Para el detalle de pantallas, componentes y decisiones de UX, ver [`UI_SPECIFICATION.md`](UI_SPECIFICATION.md).

---

## 1. Actores

| Actor | Descripción | Autenticación |
|-------|-------------|---------------|
| **Guest** | Visitante no autenticado | ❌ |
| **User** | Usuario registrado, puede crear orgs y blueprints | ✅ |
| **Developer** | Miembro de org con rol Developer | ✅ + membresía org |
| **Maintainer** | Miembro de org con rol Maintainer | ✅ + membresía org |
| **Owner** | Creador de la org, control total | ✅ + ownership org |

Los roles **Developer**, **Maintainer** y **Owner** son mutuamente excluyentes dentro de una organización. Un usuario puede tener roles distintos en organizaciones diferentes.

---

## 2. Módulo Auth

### 2.1 Registro de Usuario

**Actor**: Guest  
**Precondición**: No estar autenticado.  
**Postcondición**: Usuario creado con plan Free, sesión iniciada, redirigido al dashboard.

**Flujo**:
1. Guest visita `/register`
2. Completa formulario: nombre, email, password, confirmación
3. Sistema valida:
   - Email único (case-insensitive, normalizado a lowercase)
   - Password ≥ 8 caracteres
   - Confirmación coincide
4. Se crea usuario con `plan_id` = Free
5. Se inicia sesión automáticamente
6. Redirect a `/dashboard`

**Reglas de negocio**:
- RN-AUTH-01: Todo registro nuevo se asigna al plan Free sin excepción.
- RN-AUTH-02: El email se almacena siempre en lowercase (via VO `Email`).
- RN-AUTH-03: La password se hashea con `password_hash` (via `PasswordHasher`).

### 2.2 Login

**Actor**: Guest / User  
**Precondición**: No estar autenticado.  
**Postcondición**: Sesión iniciada, redirigido al dashboard.

**Flujo**:
1. Guest visita `/login`
2. Completa email + password
3. Sistema valida credenciales
4. Si válido: inicia sesión, regenera token CSRF, redirige a `/dashboard`
5. Si inválido: muestra error genérico (no diferenciar email no existe vs password incorrecta)

**Reglas de negocio**:
- RN-AUTH-04: No revelar si el email existe o no (protección contra enumeración).

### 2.3 Logout

**Actor**: User (cualquier rol)  
**Postcondición**: Sesión invalidada, tokens revocados.

**Flujo**:
1. User hace click en "Cerrar sesión" (POST a `/logout`)
2. Sistema invalida sesión y tokens de Sanctum
3. Redirige a `/login`

---

## 3. Módulo Organization

### 3.1 Crear Organización

**Actor**: User  
**Precondición**: Usuario autenticado.  
**Postcondición**: Organización creada con plan heredado del usuario.

**Flujo**:
1. User visita `/organizations/create`
2. Completa nombre de organización
3. Slug se genera automáticamente desde el nombre (sanitizado via VO `Slug`)
4. Sistema valida:
   - Nombre único
   - Límite de organizaciones del plan actual no alcanzado (RN-ORG-01)
5. Se crea org con `owner_id` = user.id, `plan_id` = user.plan_id
6. Se añade al Owner como miembro con rol Owner
7. Redirect a `/organizations/{slug}`

**Reglas de negocio**:
- RN-ORG-01: Un usuario no puede tener más organizaciones que el límite de su plan.
- RN-ORG-02: El Owner de una org siempre es el creador inicial. Transferencia de ownership no está implementada.
- RN-ORG-03: El plan de la org se hereda del usuario en el momento de creación.

### 3.2 Editar Organización

**Actor**: Owner  
**Precondición**: Ser Owner de la org.  
**Postcondición**: Org actualizada.

**Flujo**:
1. Owner visita `/organizations/{slug}/edit`
2. Modifica nombre (slug se recalcula automáticamente)
3. Sistema valida nombre único
4. Guarda cambios
5. Redirect a `/organizations/{slug}`

### 3.3 Eliminar Organización (Soft Delete)

**Actor**: Owner  
**Precondición**: Ser Owner de la org.  
**Postcondición**: Org marcada como eliminada. Blueprints asociados soft-deleted en cascada.

**Flujo**:
1. Owner en `/organizations/{slug}` hace click en "Eliminar"
2. Confirmación modal
3. POST a `/organizations/{slug}/delete`
4. Sistema ejecuta soft delete en org + blueprints relacionados
5. Redirect a `/dashboard` con toast de éxito

**Reglas de negocio**:
- RN-ORG-04: Soft delete en cascada de blueprints asociados.
- RN-ORG-05: Los miembros pierden acceso inmediatamente.

### 3.4 Restaurar Organización

**Actor**: Owner  
**Precondición**: Org previamente soft-deleted.  
**Postcondición**: Org y blueprints restaurados.

**Flujo**:
1. Owner en `/organizations/{slug}` (solo visible si está eliminada) hace click en "Restaurar"
2. POST a `/organizations/{slug}/restore`
3. Se restaura org + blueprints relacionados
4. Redirect a `/organizations/{slug}`

### 3.5 Force Delete (Eliminación Permanente)

**Actor**: Owner  
**Precondición**: Org soft-deleted.  
**Postcondición**: Eliminación permanente de org, blueprints, miembros e invitaciones.

**Flujo**:
1. Owner en org eliminada hace click en "Eliminar permanentemente"
2. Confirmación con texto de seguridad
3. POST a `/organizations/{slug}/force-delete`
4. Se eliminan registros de BD permanentemente
5. Redirect a `/dashboard`

**Reglas de negocio**:
- RN-ORG-06: Force delete solo disponible en la vista de org eliminada.
- RN-ORG-07: Favoritos de blueprints eliminados en cascada.

### 3.6 Gestión de Miembros

**Actor**: Owner, Maintainer  
**Precondición**: Membresía en la org con rol ≥ Maintainer.

#### 3.6.1 Añadir Miembro Directo

**Actor**: Owner  
**Flujo**:
1. Owner visita `/organizations/{slug}/members`
2. Click en "Añadir miembro"
3. Ingresa email de usuario existente + selecciona rol (Developer/Maintainer)
4. Sistema valida:
   - Usuario existe
   - No es ya miembro
   - Límite de miembros del plan no alcanzado (RN-ORG-08)
5. Se crea relación `organization_user`
6. Toast de éxito

**Reglas de negocio**:
- RN-ORG-08: Límite de miembros por org según plan.
- RN-ORG-09: Solo Owner puede añadir miembros directamente. Maintainer no puede.

#### 3.6.2 Cambiar Rol de Miembro

**Actor**: Owner  
**Flujo**:
1. Owner en `/organizations/{slug}/members`
2. Selecciona nuevo rol en dropdown del miembro
3. POST a `/organizations/{slug}/members/{user_id}/role`
4. Sistema valida que no se modifique el Owner
5. Actualiza rol

**Reglas de negocio**:
- RN-ORG-10: No se puede cambiar el rol del Owner.
- RN-ORG-11: No se puede asignar rol Owner a otro miembro (no hay transferencia de ownership).

#### 3.6.3 Invitar por Email

**Actor**: Owner, Maintainer  
**Flujo**:
1. En `/organizations/{slug}/members`, click en "Invitar"
2. Ingresa email + rol deseado
3. Sistema genera token único con expiración (48h)
4. Envía email con link de invitación (simulado en dev)
5. Invitado visita link → `/invitations/{token}/accept`
6. Si autenticado y token válido: se añade a org
7. Si no autenticado: redirige a `/register` con token en session, luego auto-acepta

**Reglas de negocio**:
- RN-ORG-12: Token expira en 48 horas.
- RN-ORG-13: Token de un solo uso (`used_at` timestamp).
- RN-ORG-14: El email de invitación no necesita coincidir con el email de registro (se usa el token).

---

## 4. Módulo Blueprint

### 4.1 Crear Blueprint

**Actor**: User (Owner, Maintainer, Developer)  
**Precondición**: Pertener a al menos una organización.  
**Postcondición**: Blueprint creado con UUID único.

**Flujo**:
1. User visita `/blueprints/create?org={id}` (o desde `/organizations/{slug}`)
2. Completa:
   - Título (requerido, slug auto-generado)
   - Descripción (opcional)
   - Categoría (select de categorías globales)
   - Organización (select, valida acceso)
3. Sección Variables:
   - Añade variables .env con: key, tipo (Fixed/Empty), default_value, flags (interactive, secret)
   - Agrupa en secciones (ej: "Database", "API Keys")
   - Reordena con drag & drop
4. Sección Tabs (opcional):
   - Añade tabs dinámicas: VSCode Extensions, MCP Servers, AI Context
   - Configura cada tab según su tipo
   - Reordena tabs
5. Sistema valida:
   - Límite de blueprints por org según plan (RN-BP-01)
   - Límite de variables por blueprint según plan (RN-BP-02)
   - Título único dentro de la org
6. Se genera UUID v4 único
7. Redirect a `/blueprints/{uuid}`

**Reglas de negocio**:
- RN-BP-01: Límite de blueprints por organización según plan.
- RN-BP-02: Límite de variables por blueprint según plan.
- RN-BP-03: Slug auto-generado desde título, sanitizado (solo minúsculas, números, guiones).
- RN-BP-04: UUID v4 generado automáticamente, inmutable.

### 4.2 Ver Blueprint (Resolución)

**Actor**: User (miembro de la org del blueprint)  
**Precondición**: Blueprint existe y user tiene acceso.

**Flujo**:
1. User visita `/blueprints/{uuid}`
2. Sistema resuelve el blueprint:
   - Muestra variables .env en tabla con tipo, default, flags
   - Si `is_secret` = true y user no es Owner: muestra `***`
   - Procesa `tabs_config` JSON via `ResolveBlueprint`
   - Genera outputs estructurados (`TabOutput[]`)
3. Muestra pestañas colapsables:
   - **Variables**: Tabla de variables .env
   - **VSCode Extensions**: Lista de extensiones con copy-to-clipboard
   - **MCP Servers**: Tabla de servidores con comandos
   - **AI Context**: Preview de `agent.md` generado con copy-to-clipboard
4. Si hay `agent.md`, muestra badge y botón de copia
5. Botón "Copiar comando de instalación" (si aplica)

**Reglas de negocio**:
- RN-BP-05: Solo Owner ve valores de variables secretas. Otros roles ven `***`.
- RN-BP-06: `ResolveBlueprint` genera `agent.md` combinando presets + skills + custom_rules del tab AI Context.

### 4.3 Editar Blueprint

**Actor**: Owner (cualquier blueprint de su org), Maintainer (cualquier blueprint), Developer (solo los suyos)  
**Precondición**: Blueprint no soft-deleted.

**Flujo**:
1. User visita `/blueprints/{uuid}/edit`
2. Formulario pre-cargado con datos actuales
3. Edita título, descripción, categoría
4. Modifica variables (add/edit/delete/reorder)
5. Modifica tabs (add/remove/reorder/config)
6. Sistema sincroniza estado de `TabManager` hijo via eventos `tabs-updated`
7. Guardar: valida límites, actualiza BD
8. Redirect a `/blueprints/{uuid}`

**Reglas de negocio**:
- RN-BP-07: Developer solo puede editar blueprints que él creó (`created_by` = su id).
- RN-BP-08: Los cambios en tabs se guardan como JSON en `tabs_config`.

### 4.4 Transferir Blueprint

**Actor**: Owner  
**Precondición**: Ser Owner de la org origen. Tener acceso a la org destino.

**Flujo**:
1. Owner en `/blueprints/{uuid}` abre modal "Transferir"
2. Selecciona org destino de sus orgs
3. Confirma transferencia
4. POST a `/blueprints/{uuid}/transfer`
5. Sistema actualiza `organization_id` del blueprint
6. Variables y tabs se mantienen intactas
7. Redirect a `/blueprints/{uuid}` con toast

**Reglas de negocio**:
- RN-BP-09: Solo Owner puede transferir.
- RN-BP-10: La org destino debe pertenecer al Owner.
- RN-BP-11: Se valida límite de blueprints de la org destino.

### 4.5 Eliminar Blueprint (Soft Delete)

**Actor**: Owner (cualquier blueprint de su org)  
**Precondición**: Blueprint no eliminado.

**Flujo**:
1. Owner en `/blueprints/{uuid}` hace click en "Eliminar"
2. Confirmación modal
3. POST a `/blueprints/{uuid}/delete`
4. Soft delete del blueprint
5. Favoritos se mantienen (referencia histórica)
6. Redirect a `/organizations/{slug}`

### 4.6 Restaurar Blueprint

**Actor**: Owner  
**Precondición**: Blueprint soft-deleted.

**Flujo**:
1. Owner visita `/blueprints/deleted` (papelera de la org)
2. Ve blueprints eliminados
3. Click en "Restaurar"
4. POST a `/blueprints/{uuid}/restore`
5. Blueprint restaurado
6. Redirect a `/blueprints/{uuid}`

### 4.7 Favoritos

**Actor**: Cualquier miembro de la org  
**Flujo**:
1. User en `/blueprints/{uuid}` hace click en ⭐ (o en lista de blueprints)
2. POST a endpoint interno (via Livewire `ToggleFavorite`)
3. Si no es favorito: se añade a `blueprint_favorites`
4. Si es favorito: se elimina
5. UI se actualiza sin recarga (Livewire)

**Reglas de negocio**:
- RN-BP-12: Un usuario solo puede marcar favoritos blueprints de sus organizaciones.
- RN-BP-13: Si un blueprint se soft-deleta, el favorito persiste pero se marca como "eliminado" en la UI.

### 4.8 Listados de Blueprints

#### 4.8.1 Todos los Blueprints
- `/blueprints` — Blueprints de todas las orgs del usuario
- Filtros: por org, categoría, búsqueda por título
- Ordenación: recientes, alfabético

#### 4.8.2 Favoritos
- `/blueprints/favorites` — Solo favoritos del usuario
- Misma estructura que listado general

#### 4.8.3 Papelera (Deleted)
- `/blueprints/deleted` — Blueprints soft-deleted de las orgs donde es Owner
- Acciones: Restaurar
- No se muestra a Developer/Maintainer

---

## 5. Dashboard

**Actor**: User autenticado  
**URL**: `/dashboard`

### 5.1 Sin Organizaciones
- CTA grande: "Crear primera organización"
- Mensaje explicativo del valor de CoVa
- Link a `/organizations/create`

### 5.2 Con Organizaciones
- Grid de tarjetas de organizaciones:
  - Nombre + slug
  - Cantidad de blueprints
  - Rol del usuario en esa org
- Botón "Nueva Organización" (deshabilitado si alcanzó límite del plan)
- Warning si alcanzó límite de orgs del plan actual
- Lista de blueprints recientes

---

## 6. Flujos de Usuario End-to-End

### 6.1 Registro → Primera Org → Primer Blueprint

```
Guest visita /register
  → Completa formulario
  → Registro con plan Free
  → Redirect a /dashboard
  → Ve CTA "Crear primera organización"
  → Click → /organizations/create
  → Completa nombre
  → Org creada (plan Free: 2 orgs max, 3 BP/org, 5 members, 20 variables/BP)
  → Redirect a /organizations/{slug}
  → Click "Nuevo Blueprint"
  → /blueprints/create?org={id}
  → Completa título, categoría, variables, tabs
  → Blueprint creado con UUID
  → Redirect a /blueprints/{uuid}
  → Ve resolución completa con agent.md
```

### 6.2 Invitación a Organización

```
Owner en /organizations/{slug}/members
  → Click "Invitar"
  → Ingresa email de invitado + rol Developer
  → Sistema genera token y envía email
  → Invitado recibe email con link
  → Invitado click link /invitations/{token}/accept
    CASO A: Invitado autenticado
      → Se añade a org automáticamente
      → Redirect a /organizations/{slug}
    CASO B: Invitado no autenticado
      → Redirect a /register
      → Se registra
      → Se auto-acepta invitación post-registro
      → Redirect a /organizations/{slug}
```

### 6.3 Colaboración en Blueprint

```
Developer en /blueprints/{uuid}
  → Ve blueprint de su org
  → Puede editar (solo si es el creador)
  → Añade variables
  → Guarda cambios
  → Maintainer ve cambios en tiempo real
  → Owner puede transferir blueprint a otra org si es necesario
```

---

## 7. Reglas de Negocio Globales

| ID | Regla | Módulo |
|----|-------|--------|
| RN-AUTH-01 | Registro nuevo → plan Free siempre | Auth |
| RN-AUTH-02 | Email lowercase automático | Auth |
| RN-AUTH-03 | Password hasheado con algoritmo seguro | Auth |
| RN-AUTH-04 | No revelar si email existe en login | Auth |
| RN-ORG-01 | Límite de orgs por plan | Organization |
| RN-ORG-02 | Owner inicial es el creador | Organization |
| RN-ORG-03 | Plan heredado del usuario en creación | Organization |
| RN-ORG-04 | Soft delete en cascada de blueprints | Organization |
| RN-ORG-05 | Miembros pierden acceso al soft-delete org | Organization |
| RN-ORG-06 | Force delete solo en vista de eliminada | Organization |
| RN-ORG-07 | Favoritos eliminados en force delete | Organization |
| RN-ORG-08 | Límite de miembros por plan | Organization |
| RN-ORG-09 | Solo Owner añade miembros directos | Organization |
| RN-ORG-10 | No cambiar rol del Owner | Organization |
| RN-ORG-11 | No asignar Owner a otro miembro | Organization |
| RN-ORG-12 | Token de invitación expira en 48h | Organization |
| RN-ORG-13 | Token de un solo uso | Organization |
| RN-BP-01 | Límite de blueprints por org | Blueprint |
| RN-BP-02 | Límite de variables por blueprint | Blueprint |
| RN-BP-03 | Slug auto-generado y sanitizado | Blueprint |
| RN-BP-04 | UUID v4 inmutable | Blueprint |
| RN-BP-05 | Solo Owner ve secretos | Blueprint |
| RN-BP-06 | agent.md generado desde AI Context tab | Blueprint |
| RN-BP-07 | Developer solo edita sus blueprints | Blueprint |
| RN-BP-08 | Tabs guardadas en JSON | Blueprint |
| RN-BP-09 | Solo Owner transfiere | Blueprint |
| RN-BP-10 | Org destino debe ser del Owner | Blueprint |
| RN-BP-11 | Validar límite blueprints org destino en transferencia | Blueprint |
| RN-BP-12 | Favoritos solo de orgs del usuario | Blueprint |
| RN-BP-13 | Favoritos persisten en soft delete | Blueprint |

---

## 8. Casos de Error y Mensajes

| Escenario | Mensaje al Usuario | Código HTTP |
|-----------|-------------------|-------------|
| Login credenciales inválidas | "Las credenciales no son válidas" | 422 |
| Registro email duplicado | "El email ya está registrado" | 422 |
| Límite de orgs alcanzado | "Has alcanzado el límite de organizaciones de tu plan" | 422 |
| Límite de blueprints alcanzado | "Has alcanzado el límite de blueprints para esta organización" | 422 |
| Límite de variables alcanzado | "Has alcanzado el límite de variables para este blueprint" | 422 |
| Límite de miembros alcanzado | "Has alcanzado el límite de miembros para esta organización" | 422 |
| Token de invitación expirado | "La invitación ha expirado" | 410 |
| Token de invitación usado | "La invitación ya fue utilizada" | 410 |
| Acceso a blueprint sin permiso | "No tienes permiso para ver este blueprint" | 403 |
| Editar blueprint sin permiso | "No tienes permiso para editar este blueprint" | 403 |
| Transferir sin ser Owner | "Solo el Owner puede transferir blueprints" | 403 |
| Blueprint no encontrado | "Blueprint no encontrado" | 404 |
| Org no encontrada | "Organización no encontrada" | 404 |

---

## 9. Estados de Carga y Feedback

| Acción | Estado de Carga | Feedback de Éxito | Feedback de Error |
|--------|----------------|-------------------|-------------------|
| Login | Spinner en botón | Redirect silencioso | Toast rojo con mensaje |
| Registro | Spinner en botón | Redirect a dashboard | Toast rojo, errores por campo |
| Crear org | Spinner en botón | Redirect a org, toast verde | Toast rojo, campo resaltado |
| Crear blueprint | Spinner en botón | Redirect a blueprint, toast verde | Toast rojo, tab resaltado con error |
| Guardar blueprint | Spinner en botón | Toast verde "Guardado" | Toast rojo con detalle |
| Eliminar | Spinner en modal | Toast, redirect | Toast rojo |
| Transferir | Spinner en modal | Toast, redirect | Toast rojo |
| Toggle favorito | Icono animado | Cambio de color de ⭐ | Toast rojo (raro) |
| Copiar al portapapeles | Icono cambia a ✓ | Toast verde "Copiado" | — |
| Invitar miembro | Spinner en botón | Toast verde "Invitación enviada" | Toast rojo |
| Aceptar invitación | Spinner en página | Redirect a org, toast verde | Toast rojo con razón |

---

## 10. Features en Progreso

### 10.1 AI Agents / Skills Configuration
- **Estado**: 🚧 En progreso
- **Descripción**: Configuración avanzada de contexto para agentes AI dentro del tab AI Context.
- **Implementado**: Presets, skills, custom_rules en tab. Generación de `agent.md`.
- **Pendiente**: Integración con LLM providers, export a formatos específicos de agentes (Claude, GPT, etc.).

### 10.2 Marketplace
- **Estado**: 🚧 Preparación
- **Descripción**: Publicación de blueprints públicos para la comunidad.
- **Implementado**: Campo `is_public` en blueprints, flag `has_marketplace_publish` en planes, org de marketplace creada en seeder.
- **Pendiente**: Landing pública, rating/reviews, búsqueda, filtrado, moderación.

---

**Documento generado**: 2026-05-15  
**Versión**: 1.0  
**Última actualización**: Fase 2 del plan de documentación
