# Plan: Landing Page — Nueva Home de CoVa

> **Objetivo**: Reemplazar la landing default de Laravel por una página de alto impacto que comunique los valores clave de CoVa: **ahorro de tiempo** y **seguridad**.
> **Estado**: ✅ Implementado — en uso productivo  
> **Creado**: 2026-05-23  
> **Última actualización**: 2026-07-01

---

## 1. Mensaje Clave y Proposición de Valor

**Mensaje principal**: *"Configurá entornos de desarrollo en segundos, no en horas. Seguro, privado y reproducible."*

**Pilares comunicativos**:
1. **Tiempo**: El caos de configurar proyectos desde cero es real. CoVa lo elimina.
2. **Seguridad**: No más `.env` en Slack. Las variables viven en un vault cifrado.
3. **Reusabilidad**: Creá una vez, usá siempre. Compartí en tu equipo o con la comunidad.

---

## 2. Estructura de la Landing

### 2.1 Hero Section

**Posición**: Full viewport height (`min-h-screen` o `100vh`), centrado.

**Layout**:
```
+--------------------------------------------------+
|  [Nav: Logo | Login | Register | Theme Toggle]   |
+--------------------------------------------------+
|                                                  |
|   TÍTULO PRINCIPAL                        +------------------+
|   Subtítulo explicativo                   |  Terminal        |
|                                           |  Animada         |
|   [CTA Primario] [CTA Secundario]         |  (cova vault:fetch) |
|                                           +------------------+
|                                                  |
+--------------------------------------------------+
```

**Contenido sugerido**:
- **Título**: "Tu entorno, en un comando."
- **Subtítulo**: "CoVa es tu vault de configuraciones. Definí, publicá y ejecutá `cova vault:fetch`. Tu entorno listo en 3 segundos."
- **CTA Primario**: "Comenzá Gratis →" (lleva a `/register`)
- **CTA Secundario**: "Ver cómo funciona" (scroll suave a #how-it-works)

**Terminal Animada**:
- Componente visual que simula una terminal oscura (estilo iTerm2 / VS Code terminal).
- Debe ejecutar una animación de typing que muestre:
  ```
  $ cova vault:fetch laravel-inertia
  > Descargando blueprint...
  > Variables cargadas: 12
  > Archivos generados: .env, .agent.md, .vscode/extensions.json, .vscode/mcp.json
  ✅ Entorno listo en 2.4s
  ```
- La animación debe ser en loop o reproducirse al entrar en viewport (IntersectionObserver).
- Efecto: cursor parpadeante, colores de sintaxis (verde para éxito, cyan para comandos).

---

### 2.2 The "Pain" Point

**Posición**: Debajo del Hero, fondo alternativo (gris muy claro en light, gris oscuro en dark).

**Propósito**: Crear empatía. El usuario debe sentir: *"sí, eso me pasa"*.

**Layout**:
- Grid de 3 cards con iconos.
- Título de sección: "¿Te suena familiar?"

**Cards**:
1. **"El caos del .env"**
   - Icono: 🔄 o un símbolo de archivo
   - Texto: *"Compartir variables por Slack, perder el historial, no saber cuál es la versión correcta..."*

2. **"Configurar desde cero"**
   - Icono: 🏗️
   - Texto: *"Cada nuevo proyecto = horas configurando el mismo agent.md, las mismas reglas de Cursor, los mismos archivos base."*

3. **"Sin estandarizar"**
   - Icono: 🌀
   - Texto: *"Cada developer en tu equipo tiene su propia forma de configurar las cosas. Nada es reproducible."*

**Animación**: Cards aparecen con fade-in + translate-y al hacer scroll (stagger de 150ms entre cada una).

---

### 2.3 How it Works (3 Pasos)

**Posición**: Sección siguiente, fondo blanco/base.

**Título**: "De la idea al entorno en 3 pasos"

**Layout**: 3 columnas horizontales en desktop, apiladas en mobile.

**Pasos**:

#### Paso 1: Define
- **Icono**: 🎨 o un ícono de editar/crear
- **Título**: "Crea tu Blueprint"
- **Descripción**: *"Definí variables, archivos de configuración y reglas de tu entorno visualmente en el dashboard. Sin tocar la terminal."*
- **Visual**: Mockup pequeño del formulario de creación de blueprint (screenshot o representación simplificada).

#### Paso 2: Publish
- **Icono**: 🚀
- **Título**: "Publicá o Guardalo"
- **Descripción**: *"Mantenlo privado para tu organización o compartilo en el CoVa Marketplace para que la comunidad lo use."*
- **Visual**: Badge/toggle animado que muestra "Privado → Público".

#### Paso 3: Fetch
- **Icono**: ⚡
   - **Título**: "Ejecutá `cova vault:fetch`"
- **Descripción**: *"Un solo comando y tu entorno está listo. Variables cargadas, archivos generados, todo en su lugar."*
- **Visual**: Repetición corta del bloque de terminal del Hero.

**Conectores**: Líneas o flechas entre los pasos (en desktop) que indiquen progresión.

**Animación**: Cada paso aparece al hacer scroll. Los iconos pueden tener un micro-animación (bounce sutil al aparecer).

---

### 2.4 Marketplace Preview

**Posición**: Siguiente sección, fondo alternativo.

**Título**: "Empezá con una plantilla lista para usar"

**Layout**: Grid de cards (3 columnas en desktop, 1 en mobile).

**Cards de ejemplo** (mockup, no requiere datos reales de BD para la landing):
1. **"Laravel + Inertia + Tailwind"**
   - Badge: "Popular"
   - Descripción corta: "Configuración completa para un stack moderno de Laravel."
   - Stats: "1.2k descargas" (mock)
   - Autor: "CoVa Team"

2. **"React + TypeScript + Vite"**
   - Badge: "Nuevo"
   - Descripción corta: "Entorno frontend con reglas de Cursor y MCP listos."
   - Stats: "850 descargas" (mock)
   - Autor: "CoVa Team"

3. **"Node.js API + PostgreSQL"**
   - Badge: "Backend"
   - Descripción corta: "Variables de conexión, seeds y configuración de Docker."
   - Stats: "600 descargas" (mock)
   - Autor: "CoVa Team"

4. **"Python + FastAPI + SQLModel"**
   - Badge: "Backend"
   - Descripción corta: "Stack Python moderno con configuración de entorno lista."
   - Stats: "430 descargas" (mock)
   - Autor: "CoVa Team"

5. **"Vue 3 + Nuxt + Supabase"**
   - Badge: "Fullstack"
   - Descripción corta: "Configuración completa para apps Vue con backend serverless."
   - Stats: "720 descargas" (mock)
   - Autor: "CoVa Team"

6. **"Go + Gin + PostgreSQL"**
   - Badge: "Backend"
   - Descripción corta: "API en Go con configuración de entorno y Docker."
   - Stats: "310 descargas" (mock)
   - Autor: "CoVa Team"

**CTA debajo del grid**: "Explorar el Marketplace →" (lleva a `/blueprints` o una ruta de marketplace futura).

**Animación**: Cards aparecen con stagger al hacer scroll. Hover: elevación sutil (`translateY(-4px)` + sombra).

---

### 2.5 CTA Final

**Posición**: Última sección antes del footer.

**Layout**: Centrado, fondo con gradiente sutil o color de acento (usar el color primario del tema de CoVa).

**Contenido**:
- **Título**: "Empezá a ahorrar tiempo hoy"
- **Subtítulo**: "Registrate gratis y creá tu primer blueprint en menos de 5 minutos."
- **CTA**: "Crear cuenta gratis →" (botón grande, estilo primario)
- **Texto secundario**: "No requiere tarjeta de crédito. Plan gratuito disponible."

---

### 2.6 Footer

**Contenido mínimo**:
- Logo + tagline: "CoVa — Configuraciones que viajan contigo."
- Links rápidos: Login, Register, Marketplace (si existe), Docs (futuro).
- Crédito: "© 2026 CoVa. Todos los derechos reservados."

---

## 3. Animaciones y Efectos Visuales

### 3.1 Terminal Animada (Hero)

**Tecnología**: Vanilla JS + CSS. No se requiere librería externa.

**Comportamiento**:
- Al cargar la página, la terminal ejecuta una secuencia de typing.
- Cada línea aparece con un retraso progresivo.
- El cursor (`_`) parpadea al final de la línea actual.
- Al finalizar, se mantiene estática por 3 segundos y luego se reinicia.
- **Accesibilidad**: Respetar `prefers-reduced-motion`. Si está activo, mostrar el texto completo sin animación.

**Paleta de colores de la terminal**:
- Fondo: `#1a1a1a` (dark) / `#f5f5f5` (light)
- Texto: `#e0e0e0` (dark) / `#333333` (light)
- Prompt (`$`): `#63c5da` (cyan)
- Éxito (`✅`): `#4ade80` (verde)
- Info (`>`): `#a5b4fc` (lila)

### 3.2 Scroll Reveal

**Tecnología**: IntersectionObserver nativo + clases CSS.

**Comportamiento**:
- Elementos con clase `.reveal` inician con `opacity: 0; transform: translateY(20px);`
- Al entrar en viewport (threshold 0.1), se agrega clase `.revealed` con transición a `opacity: 1; transform: translateY(0);`
- Stagger: se logra con `transition-delay` progresivo en CSS (`--delay` custom property).

### 3.3 Hover Effects

- **Cards**: `transform: translateY(-4px); box-shadow: ...` (transición suave 200ms).
- **Botones**: Escala sutil (`scale(1.02)`) o cambio de brillo.
- **Links**: Subrayado animado de izquierda a derecha.

---

## 4. Archivos a Crear / Modificar

### Archivos Nuevos

| Ruta | Descripción |
|------|-------------|
| `resources/views/landing/index.blade.php` | Vista principal de la landing (reemplaza a `welcome.blade.php`) |
| `resources/views/landing/partials/hero.blade.php` | Sección Hero (reutilizable) |
| `resources/views/landing/partials/pain-point.blade.php` | Sección de dolor |
| `resources/views/landing/partials/how-it-works.blade.php` | 3 pasos |
| `resources/views/landing/partials/marketplace-preview.blade.php` | Preview del marketplace |
| `resources/views/landing/partials/cta-final.blade.php` | CTA final |
| `resources/views/landing/partials/footer.blade.php` | Footer de la landing |
| `resources/views/components/animated-terminal.blade.php` | Componente Blade reutilizable de terminal |
| `resources/js/landing.js` | JS específico de la landing (terminal, scroll reveal) |
| `lang/es/landing.php` | Traducciones en español |
| `lang/en/landing.php` | Traducciones en inglés |

### Archivos a Modificar

| Ruta | Cambio |
|------|--------|
| `routes/web.php` | La ruta `/` debe apuntar a la nueva landing |
| `resources/views/welcome.blade.php` | Eliminar o renombrar a `welcome-old.blade.php` |
| `vite.config.js` (si existe) | Agregar entry point `resources/js/landing.js` si es necesario, o incluir en `app.js` |
| `lang/es/welcome.php` | Eliminar o migrar claves a `landing.php` |
| `lang/en/welcome.php` | Eliminar o migrar claves a `landing.php` |

---

## 5. Dependencias Técnicas

### Sin Dependencias Nuevas

La landing no requiere librerías externas. Todo se resuelve con:
- Tailwind CSS v4 (ya instalado)
- Alpine.js (ya disponible vía Livewire)
- Vanilla JS nativo
- Blade components

### Opcionales (discutir)

- **Lottie**: Si se quiere una animación más elaborada en el Hero (ej. un ícono animado). No recomendado para MVP de la landing.
- **GSAP**: Si se quieren animaciones de scroll muy complejas. Overkill para este caso. IntersectionObserver es suficiente.

---

## 6. Accesibilidad (A11y)

- **Motion**: Respetar `prefers-reduced-motion` en TODAS las animaciones.
- **Contraste**: Asegurar ratio mínimo 4.5:1 para todo el texto.
- **Terminal**: El contenido de la terminal debe ser legible por screen readers (usar `aria-label` o texto alternativo).
- **Navegación**: Todos los CTAs deben ser alcanzables por teclado (`tabindex`, `:focus-visible`).
- **Semántica**: Usar `<header>`, `<main>`, `<section>`, `<footer>` correctamente.
- **Skip Link**: Considerar agregar un "Saltar al contenido" para screen readers.

---

## 7. Performance

- **Lazy loading**: Las imágenes/screenshots de la sección "How it Works" deben usar `loading="lazy"`.
- **CSS**: No crear un CSS separado; todo va vía Tailwind + `@source` en `app.css`.
- **JS**: El script de `landing.js` debe ser `<script defer>` o incluirse en el bundle de Vite.
- **Font**: Ya se usa `Instrument Sans` desde Bunny Fonts (preconnect ya configurado en `app.blade.php`).
- **Bundle**: El JS de la landing debe ser mínimo (< 5KB sin comprimir).

---

## 8. Responsive Breakpoints

| Breakpoint | Comportamiento |
|-----------|---------------|
| **Mobile** (< 640px) | Todo apilado, terminal debajo del texto del Hero, cards en 1 columna |
| **Tablet** (640px - 1024px) | Hero en 2 columnas (60/40), cards en 2 columnas, pasos en 1 columna apilados |
| **Desktop** (> 1024px) | Hero 2 columnas (50/50), cards en 3 columnas, pasos en 3 columnas con conectores |

---

## 9. SEO y Meta Tags

Agregar en el `<head>` de la landing:

```html
<meta name="description" content="CoVa: Configurá entornos de desarrollo en segundos. Vault seguro para variables de entorno, blueprints reutilizables y marketplace de plantillas.">
<meta name="keywords" content="vault, environment variables, developer tools, devops, blueprints, laravel, env">
<meta property="og:title" content="CoVa — Configuraciones que viajan contigo">
<meta property="og:description" content="Tu entorno, en un comando. Definí, publicá y ejecutá cova vault:fetch.">
<meta property="og:type" content="website">
<meta property="og:url" content="https://cova.dev"> <!-- ajustar URL real -->
<meta property="og:image" content="https://cova.dev/og-image.png"> <!-- crear imagen OG -->
<meta name="twitter:card" content="summary_large_image">
```

---

## 10. Plan de Traducciones

Todas las strings visibles deben estar en `lang/{es,en}/landing.php`.

Ejemplo de estructura:

```php
<?php

return [
    // Hero
    'hero_title' => 'Tu entorno, en un comando.',
    'hero_subtitle' => 'CoVa es tu vault de configuraciones...',
    'cta_primary' => 'Comenzá Gratis',
    'cta_secondary' => 'Ver cómo funciona',

    // Pain Point
    'pain_title' => '¿Te suena familiar?',
    'pain_env_title' => 'El caos del .env',
    'pain_env_desc' => 'Compartir variables por Slack...',
    'pain_config_title' => 'Configurar desde cero',
    'pain_config_desc' => 'Cada nuevo proyecto = horas configurando...',
    'pain_standards_title' => 'Sin estandarizar',
    'pain_standards_desc' => 'Cada developer tiene su propia forma...',

    // How it Works
    'how_title' => 'De la idea al entorno en 3 pasos',
    'step1_title' => 'Crea tu Blueprint',
    'step1_desc' => 'Definí variables, archivos...',
    'step2_title' => 'Publicá o Guardalo',
    'step2_desc' => 'Mantenlo privado...',
    'step3_title' => 'Ejecutá cova vault:fetch',
    'step3_desc' => 'Un solo comando y tu entorno está listo...',

    // Marketplace
    'marketplace_title' => 'Empezá con una plantilla lista para usar',
    'marketplace_cta' => 'Explorar el Marketplace',

    // CTA Final
    'cta_final_title' => 'Empezá a ahorrar tiempo hoy',
    'cta_final_subtitle' => 'Registrate gratis...',
    'cta_final_button' => 'Crear cuenta gratis',
    'cta_final_note' => 'No requiere tarjeta de crédito...',

    // Footer
    'footer_tagline' => 'Configuraciones que viajan contigo.',
    'footer_copyright' => '© 2026 CoVa. Todos los derechos reservados.',
];
```

---

## 11. Criterios de Aceptación para Implementación

- [x] La ruta `/` muestra la nueva landing page, no la de Laravel default.
- [x] La terminal animada se reproduce correctamente al cargar la página.
- [x] Todos los textos están traducidos en `es` y `en`.
- [x] Las animaciones respetan `prefers-reduced-motion`.
- [x] El layout es responsive en mobile, tablet y desktop.
- [x] Los CTAs llevan a las rutas correctas (`/register`, `/login`, `#how-it-works`).
- [x] El dark mode funciona correctamente en todas las secciones.
- [x] No hay errores de accesibilidad críticos (contrastes, navegación por teclado).
- [x] El bundle JS de la landing es menor a 5KB. (0.31KB gzipped)
- [ ] El Lighthouse score es > 90 en Performance y Accessibility. (pendiente de auditoría)

---

## 12. Notas de Implementación

### Estructura de la Vista

La vista principal (`landing/index.blade.php`) debe extender un layout ligero, NO el `app.blade.php` completo (que tiene nav de dashboard). Se propone crear un layout dedicado:

```
resources/views/layouts/landing.blade.php  <-- layout mínimo con nav de landing
resources/views/landing/index.blade.php    <-- incluye partials
```

El `landing.blade.php` incluye:
- `<head>` con meta tags SEO
- Nav minimalista (solo logo, login, register, theme toggle)
- `@yield('content')`
- Footer
- Script de `landing.js`

### Terminal: Enfoque Técnico

Opción A: **CSS-only typing animation** (recomendada)
- Usar `@keyframes` para `width` de un `span` con `overflow: hidden` y `white-space: nowrap`.
- Pro: no requiere JS.
- Contra: menos flexible para múltiples líneas con tiempos distintos.

Opción B: **JS typing animation** (recomendada para este caso)
- Crear un componente Alpine.js o vanilla JS que itere un array de líneas y las "escriba" carácter por carácter.
- Pro: control total de tiempos, colores por línea, reinicio.
- Contra: requiere JS (~50 líneas).

**Recomendación final**: Opción B con Alpine.js (ya cargado) para mantener la reactividad y control.

### Paleta de Colores Sugerida

Usar los colores existentes del tema de CoVa (indigo/slate), pero para la landing se puede enfatizar:

- **Primario**: Indigo 600 (`#4f46e5`) — botones principales
- **Acento**: Emerald 500 (`#10b981`) — éxito, terminal
- **Fondo Hero**: Blanco (light) / Slate 950 (dark)
- **Fondo Alternativo**: Slate 50 (light) / Slate 900 (dark)

---

## 13. Próximos Pasos

1. **✅ Landing implementada**: Todos los items del checklist completados.
2. **Refinamientos posteriores**:
   - ~~Simplificar logo~~ ✅ (2026-05-28): Sin recuadro, dial más grande
   - ~~Agregar favicon~~ ✅ (2026-05-28): Logo SVG en pestañas del navegador
   - ~~Fix i18n demo + terminal~~ ✅ (2026-05-28): Textos hardcodeados extraídos a traducciones
   - Auditoría Lighthouse Performance/Accessibility
   - Imagen OG para social sharing
   - Analytics en CTAs (futuro)

---

**Plan creado por**: Arquitecto Senior  
**Fecha**: 2026-05-23  
**Versión**: 1.0
