# CoVa — Especificación de Interfaz de Usuario

> Detalle de pantallas, componentes, estados y decisiones de UX.
> Audiencia: Desarrolladores frontend, diseñadores, y devs implementando nuevas pantallas.

---

## 1. Inventario de Páginas

### 1.1 Páginas Públicas (Guest)

| Ruta | Nombre | Descripción | Layout |
|------|--------|-------------|--------|
| `/` | Home | Landing page (futuro, actualmente redirect a login) | Guest |
| `/login` | Login | Formulario de inicio de sesión | Guest |
| `/register` | Register | Formulario de registro | Guest |
| `/invitations/{token}/accept` | Accept Invitation | Aceptar invitación a org | Guest |

### 1.2 Páginas Autenticadas (Auth Layout)

| Ruta | Nombre | Componentes Livewire | Descripción |
|------|--------|---------------------|-------------|
| `/dashboard` | Dashboard | — | Centro de control, orgs recientes, stats |
| `/organizations` | Organizations Index | `OrganizationList` | Listado de orgs del usuario |
| `/organizations/create` | Create Organization | `CreateOrganizationForm` | Formulario de nueva org |
| `/organizations/{slug}` | Organization Show | — | Detalle de org, blueprints, miembros |
| `/organizations/{slug}/edit` | Edit Organization | — | Editar nombre/slug de org |
| `/organizations/{slug}/members` | Organization Members | — | Gestión de miembros e invitaciones |
| `/blueprints` | Blueprints Index | `BlueprintList` | Todos los blueprints del usuario |
| `/blueprints/create` | Create Blueprint | `BlueprintCreateForm`, `VariableManager`, `TabManager` | Wizard de creación |
| `/blueprints/favorites` | Favorites | `BlueprintList` | Blueprints favoritos |
| `/blueprints/deleted` | Deleted Blueprints | `BlueprintList` | Papelera de blueprints |
| `/blueprints/{uuid}` | Blueprint Show | — | Detalle con resolución y tabs |
| `/blueprints/{uuid}/edit` | Edit Blueprint | `BlueprintEditForm`, `VariableManager`, `TabManager` | Edición completa |

---

## 2. Layouts

### 2.1 Guest Layout
- Sin navegación lateral
- Header mínimo con logo CoVa
- Footer opcional
- Fondo claro, centrado verticalmente
- Responsive: mismo diseño en mobile

### 2.2 Auth Layout
- **Sidebar** (desktop): Navegación fija izquierda
  - Logo CoVa (arriba)
  - Links: Dashboard, Organizations, Blueprints, Favorites
  - Separador
  - User dropdown (abajo): Perfil, Settings, Logout
- **Topbar** (mobile): Hamburger menu + logo
- **Main Content Area**: Contenido dinámico con padding consistente
- **Toast Container**: Esquina inferior derecha (desktop), inferior centrado (mobile)
- Responsive:
  - Desktop (>1024px): Sidebar visible
  - Tablet (768-1024px): Sidebar colapsable
  - Mobile (<768px): Sidebar como drawer overlay

---

## 3. Componentes Livewire Principales

### 3.1 Auth

#### `LoginForm`
- **Props**: Ninguno (usa estado local)
- **Estado**: `email`, `password`, `remember`
- **Validación en tiempo real**: Email formato, password requerido
- **UI**:
  - Inputs con labels flotantes (Tailwind)
  - Botón "Iniciar sesión" con spinner durante submit
  - Link "¿No tienes cuenta? Regístrate"
- **Error handling**: Toast rojo con mensaje genérico

#### `RegisterForm`
- **Estado**: `name`, `email`, `password`, `password_confirmation`
- **Validación en tiempo real**:
  - Name: requerido, min 2 chars
  - Email: formato válido, único (debounce 500ms)
  - Password: min 8 chars
  - Confirmación: coincide con password
- **UI**:
  - Indicador de fuerza de password (opcional)
  - Checkmarks en validación inline
  - Botón "Crear cuenta" con spinner

### 3.2 Organization

#### `CreateOrganizationForm`
- **Estado**: `name`, `slug` (computed from name)
- **Validación**:
  - Name: requerido, min 3, único
  - Slug: auto-generado, editable manualmente
- **UI**:
  - Input name con preview de slug en tiempo real
  - Warning si alcanzó límite de orgs (deshabilita form, muestra CTA de upgrade)
  - Botón "Crear organización"
- **Success**: Redirect a `/organizations/{slug}`

#### `OrganizationList`
- **Estado**: `organizations` (colección), `search`, `sort`
- **UI**:
  - Tabla responsive:
    - Desktop: columnas Nombre, Slug, Blueprints, Miembros, Rol, Acciones
    - Mobile: tarjetas apiladas
  - Acciones por fila: Ver, Editar (solo Owner), Eliminar (solo Owner)
  - Empty state: "No tienes organizaciones" + CTA crear
  - Paginación si > 10 orgs

### 3.3 Blueprint

#### `BlueprintCreateForm` (Wizard)
- **Estado**: `title`, `description`, `category_id`, `organization_id`, `variables[]`, `tabsConfig[]`
- **Pasos** (wizard implícito en scroll):
  1. **Info básica**: Título, descripción, categoría, org
  2. **Variables**: `VariableManager` embebido
  3. **Tabs**: `TabManager` embebido
- **Validación por paso**:
  - Paso 1: Título requerido, org válida y con acceso
  - Paso 2: Variables dentro de límite de plan
  - Paso 3: Tabs válidas (type reconocido por enum)
- **UI**:
  - Barra de progreso (opcional)
  - Secciones colapsables para Variables y Tabs
  - Preview de slug en tiempo real
  - Contador de variables usadas / límite
  - Botón "Crear blueprint" al final

#### `BlueprintEditForm`
- **Estado**: Igual que CreateForm + `blueprint_id`
- **Diferencias con Create**:
  - Precarga datos desde modelo
  - Slug editable (con validación de unicidad en org)
  - Botón "Guardar cambios" en lugar de "Crear"
- **Sincronización con TabManager**:
  - Listener: `'tabs-updated' => 'onTabsUpdated'`
  - Recibe array de tabs y actualiza `tabsConfig`
  - Normaliza antes de guardar en BD

#### `VariableManager`
- **Estado**: `variables[]` (cada variable: key, type, default_value, is_interactive, is_secret, section, sort_order)
- **Operaciones**:
  - `addVariable()`: Añade variable vacía al final
  - `removeVariable(index)`: Elimina variable
  - `updateVariable(index, field, value)`: Actualiza campo
  - `reorderVariables(from, to)`: Drag & drop
- **UI**:
  - Tabla de variables:
    - Columnas: Key, Tipo, Default, Interactive ☐, Secret ☐, Section, Acciones
    - Filas editables inline
    - Dropdown de tipo: Fixed / Empty
  - Secciones: Variables agrupadas visualmente por `section`
  - Drag handle (⋮⋮) para reordenar
  - Botón "+ Añadir variable"
  - Contador: "X de Y variables usadas"
  - Warning al acercarse al límite del plan

#### `TabManager`
- **Estado**: `tabs[]`, `availableTabTypes`
- **Operaciones**:
  - `addTab(type)`: Añade tab con config default según tipo
  - `removeTab(index)`: Elimina tab
  - `moveTab(index, direction)`: Reordenar (-1 arriba, +1 abajo)
  - `updateVscodeExtensions(index, text)`: Parsea texto a array
  - `addMcpServer(index)`, `removeMcpServer(index, serverIndex)`, `updateMcpServerField(...)`
  - `togglePreset(index, preset)`, `toggleSkill(index, skill)`, `updateCustomRules(index, rules)`
- **Sincronización**:
  - Cada cambio dispara `$this->dispatch('tabs-updated', tabs: $this->tabs)`
  - Padre (`BlueprintCreateForm`/`BlueprintEditForm`) escucha y persiste
- **UI**:
  - Lista de tabs con header colapsable:
    - Título del tipo + ícono
    - Botones: ▲/▼ colapsar, ↑↓ reordenar, ✕ eliminar
  - Contenido expandido según tipo:
    - **VSCode Extensions**: Textarea (una extensión por línea) + preview de lista
    - **MCP Servers**: Tabla de servidores con inputs name/command/args + botón añadir/quitar
    - **AI Context**:
      - Presets: Checkboxes de presets predefinidos
      - Skills: Checkboxes de skills disponibles
      - Custom Rules: Textarea libre
  - Dropdown "+ Añadir tab" (solo tipos disponibles en `availableTabTypes`)
  - Máximo de tabs: no hardcodeado, pero UI scrollable

#### `BlueprintList`
- **Estado**: `blueprints`, `search`, `filters`, `sort`, `favoritesOnly`
- **Props**: `favoritesOnly` (boolean, default false), `deletedOnly` (boolean, default false)
- **UI**:
  - Barra de búsqueda con debounce
  - Filtros: Org (dropdown), Categoría (dropdown)
  - Grid de tarjetas (desktop 3-col, tablet 2-col, mobile 1-col)
  - Cada tarjeta:
    - Título + UUID truncado
    - Badge de categoría
    - Badge de org
    - Cantidad de variables
    - ⭐ favorito (toggle)
    - Estado: Activo / Eliminado (badge gris)
  - Empty state:
    - General: "No tienes blueprints. Crea uno."
    - Favorites: "No tienes favoritos. Marca algunos."
    - Deleted: "Papelera vacía."
  - Paginación: 12 por página

#### `CopyToClipboard`
- **Props**: `text` (string), `successMessage` (string, default "Copiado"), `label` (string)
- **Estado**: `copied` (boolean, timeout 2s)
- **UI**:
  - Botón con ícono 📋
  - Al click: copia al portapapeles, cambia ícono a ✓, muestra toast
  - Después de 2s: vuelve a ícono 📋
- **Uso**: En blueprint show (agent.md, install command, extensions list)

---

## 4. Páginas Detalladas

### 4.1 Dashboard (`/dashboard`)

#### Estado: Sin Organizaciones
- **Layout**: Centrado, ilustración + texto
- **Contenido**:
  - Título: "Bienvenido a CoVa"
  - Subtítulo: "Crea tu primera organización para empezar a gestionar blueprints"
  - CTA grande: "Crear organización" → `/organizations/create`
  - Link secundario: "¿Qué es un blueprint?" (tooltip o modal)

#### Estado: Con Organizaciones
- **Layout**: Grid de tarjetas + sidebar stats
- **Contenido**:
  - Sección "Tus Organizaciones":
    - Grid de tarjetas (max 3 visibles, "Ver todas" link)
    - Cada tarjeta: Nombre, cantidad de blueprints, rol, link
  - Botón "Nueva Organización":
    - Habilitado si < límite de plan
    - Deshabilitado con tooltip "Límite alcanzado" si no
  - Warning banner: "Has alcanzado el límite de X organizaciones" (condicional)
  - Sección "Blueprints Recientes":
    - Lista horizontal scrollable de últimos 5 blueprints editados
    - Empty: "No hay blueprints recientes"

### 4.2 Organization Show (`/organizations/{slug}`)

- **Header**:
  - Nombre de org + slug (badge)
  - Rol del usuario actual (badge color: Owner=rojo, Maintainer=amarillo, Developer=azul)
  - Acciones:
    - Owner: Editar, Eliminar, Gestionar miembros
    - Maintainer: Gestionar miembros
    - Developer: Ninguna
- **Stats row**:
  - Blueprints: X / límite
  - Miembros: X / límite
  - Plan actual (badge)
- **Tabs** (en página):
  - **Blueprints**: Grid de blueprints de esta org + botón "Nuevo Blueprint"
  - **Miembros**: Tabla de miembros (solo Owner/Maintainer ven acciones)
- **Empty state de blueprints**: "Esta organización no tiene blueprints. Crea el primero."

### 4.3 Blueprint Show (`/blueprints/{uuid}`)

- **Header**:
  - Título + UUID (copiable)
  - Badge categoría
  - Badge org
  - ⭐ Favorito (toggle)
  - Acciones:
    - Editar (según policy)
    - Transferir (Owner)
    - Eliminar (Owner)
- **Secciones colapsables** (acordeón, todas colapsadas por defecto excepto Variables):
  - **Variables**:
    - Tabla: Key | Tipo | Default | Interactive | Secret
    - Secret: `***` para no-Owner
    - Empty: "Este blueprint no tiene variables"
  - **VSCode Extensions** (si existe tab):
    - Lista de extensiones
    - Botón "Copiar lista" (CopyToClipboard)
    - Empty tab: No se muestra sección
  - **MCP Servers** (si existe tab):
    - Tabla: Name | Command | Args
    - Botón "Copiar configuración"
  - **AI Context** (si existe tab):
    - Presets activos (badges)
    - Skills activos (badges)
    - Custom rules (blockquote)
    - **agent.md preview**:
      - Badge "agent.md"
      - Bloque de código con syntax highlighting
      - Botón "Copiar agent.md" (CopyToClipboard)
- **Install Command** (si aplica):
  - Bloque de código: `cova fetch {uuid}`
  - Botón copiar

### 4.4 Members Page (`/organizations/{slug}/members`)

- **Header**: "Miembros de {org_name}"
- **Stats**: X de Y miembros usados (según plan)
- **Tabs**:
  - **Miembros Activos**:
    - Tabla: Usuario | Email | Rol | Desde | Acciones
    - Acciones Owner: Cambiar rol, Eliminar
    - Acciones Maintainer: Ninguna (solo ver)
    - Empty: "No hay miembros. Invita a alguien."
  - **Invitaciones Pendientes**:
    - Tabla: Email | Rol | Expira | Acciones
    - Acciones: Reenviar, Cancelar
    - Empty: "No hay invitaciones pendientes"
- **Acciones**:
  - "Añadir miembro" (Owner): Modal con email + rol
  - "Invitar por email" (Owner/Maintainer): Modal con email + rol

---

## 5. Estados de UI

### 5.1 Estados de Carga

| Componente | Estado Loading | Visual |
|------------|---------------|--------|
| Formularios | Submit en progreso | Botón con spinner, campos deshabilitados |
| Tablas/Listas | Carga inicial | Skeleton rows (3-5 filas grises pulsantes) |
| Blueprint Show | Resolución | Spinner en área de tabs |
| Modal | Acción en progreso | Overlay semi-transparente + spinner centrado |
| Page transition | Entre páginas | Fade out/in de 150ms |

### 5.2 Empty States

| Contexto | Mensaje | Icono | Acción |
|----------|---------|-------|--------|
| Sin orgs | "Crea tu primera organización" | 🏢 | CTA a `/organizations/create` |
| Sin blueprints | "No hay blueprints" | 📋 | CTA a `/blueprints/create` |
| Sin favoritos | "No tienes favoritos" | ⭐ | CTA a `/blueprints` |
| Papelera vacía | "Papelera vacía" | 🗑️ | — |
| Sin miembros | "Invita a tu equipo" | 👥 | CTA a modal invitar |
| Sin invitaciones | "No hay invitaciones pendientes" | 📧 | — |
| Sin variables | "Este blueprint no tiene variables" | ⚙️ | CTA a editar |
| Sin tabs | "No hay tabs configuradas" | 📑 | CTA a editar |

### 5.3 Estados de Error

| Contexto | Visual | Comportamiento |
|----------|--------|----------------|
| Validación form | Borde rojo en campo + texto error debajo | Auto-focus en primer error |
| Error 403 | Ilustración + "No tienes permiso" | Link a dashboard |
| Error 404 | Ilustración + "No encontrado" | Link a dashboard |
| Error 500 | Ilustración + "Algo salió mal" | Botón "Reintentar" |
| Toast error | Fondo rojo, ícono ⚠️, auto-dismiss 5s | Click para cerrar antes |
| Toast éxito | Fondo verde, ícono ✓, auto-dismiss 3s | Click para cerrar antes |
| Toast warning | Fondo amarillo, ícono ⚡, auto-dismiss 4s | Click para cerrar antes |

---

## 6. Decisiones de UX

### 6.1 Tabs Colapsables en Blueprint Show
**Decisión**: Las secciones de un blueprint (Variables, VSCode, MCP, AI Context) se presentan como acordeón colapsable.

**Razón**:
- Un blueprint puede tener muchas variables + múltiples tabs. Pantalla larga = scroll infinito.
- Colapsando, el usuario ve el "índice" del blueprint de un vistazo.
- Variables se expande por defecto porque es el contenido principal.

**Alternativa considerada**: Tabs horizontales (como navegación). Rechazada porque las tabs dinámicas ya usan el concepto "tab" y sería confuso tener tabs dentro de tabs.

### 6.2 Wizard Implícito en Create/Edit
**Decisión**: El formulario de blueprint es un solo scroll largo con secciones, no pasos explícitos.

**Razón**:
- MVP: Complejidad de wizard multi-step es alta. Scroll único es más rápido de implementar.
- Livewire maneja bien el estado en un solo componente.
- Validación por sección se puede agregar luego sin cambiar la arquitectura.

**Futuro**: Fase 2 contempla refinar a wizard de pasos guiados.

### 6.3 Slug Auto-generado pero Editable
**Decisión**: Al escribir el título, el slug se genera automáticamente. El usuario puede editarlo manualmente.

**Razón**:
- Reduce fricción: 90% de usuarios no quieren pensar en slugs.
- Permite override: El 10% que quiere un slug específico puede editarlo.
- Validación en tiempo real previene slugs duplicados.

### 6.4 Variables en Tabla Inline
**Decisión**: Las variables se editan en tabla con campos inline, no en modal.

**Razón**:
- Ver todas las variables de un vistazo es crítico para la UX de blueprints.
- Modal por variable = muchos clicks para setups complejos.
- Inline editing permite reordenar drag & drop natural.

### 6.5 Favoritos con Toggle Inmediato
**Decisión**: El ⭐ en listados y detalle es un toggle inmediato sin confirmación.

**Razón**:
- Acción reversible y de bajo riesgo.
- Feedback inmediato (cambio de color) recompensa la acción.
- Confirmación sería fricción innecesaria.

### 6.6 Soft Delete con Papelera Separada
**Decisión**: Los blueprints eliminados no se muestran en listados normales. Hay una página `/blueprints/deleted` para restaurar.

**Razón**:
- Previene eliminaciones accidentales (recuperable).
- Mantiene listados limpios (no mostrar borrados).
- Papelera separada = modelo mental claro (como OS).

### 6.7 Copy-to-Clipboard Component Reutilizable
**Decisión**: Cada bloque copiable (agent.md, extensions, install command) usa el mismo componente `CopyToClipboard`.

**Razón**:
- Consistencia visual y de comportamiento.
- Un solo lugar para ajustar timing, iconos, y accesibilidad.
- Facilita tests: un solo componente que testear.

### 6.8 Invitación con Token en URL
**Decisión**: Las invitaciones usan token UUID en la URL. El receptor no necesita estar autenticado para clickear.

**Razón**:
- Flujo de onboarding simple: recibe email → click → registra/acepta → ya está en la org.
- Token en URL = shareable (puede enviarse por Slack, WhatsApp, etc. sin perder funcionalidad).
- Expiración de 48h mitiga riesgo de tokens expuestos.

---

## 7. Responsive Breakpoints

| Breakpoint | Ancho | Cambios principales |
|------------|-------|---------------------|
| Mobile | < 640px | Single column, sidebar como drawer, tablas como tarjetas |
| Tablet | 640-1024px | 2-column grid, sidebar colapsable |
| Desktop | > 1024px | Full layout, sidebar fija, 3-column grids |

### 7.1 Mobile-Specific
- Tablas se convierten en tarjetas apiladas
- Formularios: inputs full-width, labels arriba
- Modales: full-screen overlay
- Toast: ancho completo, bottom-fixed

---

## 8. Accesibilidad (A11y)

| Elemento | Requisito |
|----------|-----------|
| Todos los inputs | Label asociado (`for` attribute) |
| Botones | `aria-label` si solo ícono |
| Modales | Focus trap, `role="dialog"`, cerrar con ESC |
| Toasts | `role="status"`, `aria-live="polite"` |
| Tabs colapsables | `aria-expanded`, `aria-controls` |
| Drag & drop | Keyboard alternative (botones ↑↓) |
| Color | Contraste mínimo 4.5:1 para texto |

---

## 9. Iconografía

| Icono | Uso | Librería |
|-------|-----|----------|
| 🏢 | Organizaciones | Heroicons |
| 📋 | Blueprints | Heroicons |
| ⭐ | Favoritos | Heroicons |
| 👥 | Miembros | Heroicons |
| ⚙️ | Variables | Heroicons |
| 📑 | Tabs | Heroicons |
| 📋/✓ | Copy to clipboard | Heroicons |
| ⚠️ | Error | Heroicons |
| ✓ | Éxito | Heroicons |
| ⚡ | Warning | Heroicons |
| 🗑️ | Papelera | Heroicons |
| ↑↓ | Reordenar | Heroicons |
| ✕ | Eliminar | Heroicons |

---

**Documento generado**: 2026-05-15  
**Versión**: 1.0  
**Última actualización**: Fase 2 del plan de documentación
