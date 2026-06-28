<?php

declare(strict_types=1);

return [
    // Nav
    'login' => 'Iniciar sesión',
    'go_to_dashboard' => 'Ir al panel',
    'register' => 'Registrarse',

    // Hero
    'hero_title' => 'Olvídate de copiar tus configuraciones de un lado a otro',
    'site_title' => 'CoVa, tu propio espacio',
    'hero_subtitle' => 'CoVa es tu vault de configuraciones. Define, publica y ejecuta <strong>vault fetch</strong>. Tu entorno listo en segundos, sin importar el stack.',
    'cta_primary' => 'Empieza gratis',
    'cta_secondary' => 'Ver cómo funciona',

    // Pain Point
    'pain_title' => '¿Te suena familiar?',
    'pain_env_title' => 'Configuraciones dispersas',
    'pain_env_desc' => 'Compartir configs por Slack, perder el historial, no saber cuál es la versión correcta…',
    'pain_config_title' => 'Configurar desde cero',
    'pain_config_desc' => 'Cada nuevo proyecto son horas configurando el mismo agent.md, las mismas reglas de Cursor, los mismos archivos base.',
    'pain_standards_title' => 'Sin estandarizar',
    'pain_standards_desc' => 'Cada developer en tu equipo tiene su propia forma de configurar las cosas. Nada es reproducible.',

    // How it Works
    'how_title' => 'De la idea al entorno en 3 pasos',
    'step1_title' => 'Crea tu Blueprint',
    'step1_desc' => 'Define variables, archivos de configuración y reglas de tu entorno visualmente en el dashboard. Sin tocar la terminal.',
    'step2_title' => 'Publícalo o guárdalo',
    'step2_desc' => 'Mantenlo privado para tu organización o compártelo en el Marketplace para que la comunidad lo use.',
    'step3_title' => 'Ejecuta vault fetch',
    'step3_desc' => 'Un solo comando y tu entorno está listo. Variables cargadas, archivos generados, todo en su lugar.',
    'step3_note' => 'Incluye reglas de contexto IA, scripts post-instalación, extensiones VS Code y servidores MCP.',

    // Demo
    'demo_title' => 'Mira cómo funciona',
    'demo_subtitle' => 'Crea tu primera organización, define tus blueprints y ejecutalos con un solo comando.',
    'demo_dashboard' => 'Dashboard',
    'demo_org' => 'Crear Organización',
    'demo_blueprint' => 'Crear Blueprint',
    'demo_ai_context' => 'Contexto IA',
    'demo_prev' => 'Anterior',
    'demo_next' => 'Siguiente',

    // Demo slides - Dashboard
    'demo_dash_title' => 'Dashboard',
    'demo_dash_orgs' => 'Organizaciones',
    'demo_dash_blueprints' => 'Blueprints',
    'demo_dash_vars' => 'Variables',
    'demo_dash_org_name' => 'Mi Empresa',
    'demo_dash_org_blueprints' => ':count blueprints',
    'demo_dash_personal' => 'Proyecto Personal',
    'demo_plan_pro' => 'Pro',
    'demo_plan_free' => 'Free',

    // Demo slides - Crear Org
    'demo_org_title' => 'Crear Organización',
    'demo_org_name_label' => 'Nombre de la organización',
    'demo_org_name_placeholder' => 'Mi Empresa',
    'demo_org_slug_label' => 'Slug (URL)',
    'demo_org_slug_placeholder' => 'mi-empresa',
    'demo_org_slug_help' => 'Se genera automáticamente desde el nombre',
    'demo_org_plan_label' => 'Plan',
    'demo_org_plan_pro_desc' => '25 blueprints · 50 miembros',
    'demo_org_plan_pro_price' => '19 €/mes',
    'demo_org_plan_free_desc' => '3 blueprints · 5 miembros',
    'demo_org_plan_free_price' => 'Gratis',
    'demo_org_submit' => 'Crear Organización',

    // Demo slides - Crear Blueprint
    'demo_bp_title' => 'Crear Blueprint',
    'demo_bp_title_label' => 'Título',
    'demo_bp_title_placeholder' => 'Laravel + Inertia + Tailwind',
    'demo_bp_desc_label' => 'Descripción',
    'demo_bp_desc_placeholder' => 'Stack completo para proyectos modernos...',
    'demo_bp_cat_label' => 'Categoría',
    'demo_bp_cat_placeholder' => 'Fullstack',
    'demo_bp_submit' => 'Crear Blueprint',
    'demo_bp_vars_title' => 'Variables de Entorno',
    'demo_bp_file_env' => '.env',
    'demo_bp_file_testing' => '.env.testing',
    'demo_bp_file_config' => 'config/app.php',
    'demo_bp_var_host' => 'DB_HOST',
    'demo_bp_var_db' => 'DB_DATABASE',
    'demo_bp_var_app_name' => 'APP_NAME',
    'demo_bp_var_app_key' => 'APP_KEY',
    'demo_bp_var_value_localhost' => 'localhost',
    'demo_bp_var_value_myapp' => 'myapp',
    'demo_bp_var_value_test' => 'myapp_test',
    'demo_bp_var_value_app' => 'MyApp',
    'demo_bp_var_value_hidden' => '••••••••',
    'demo_bp_vars_count' => '5 variables en 3 ficheros',

    // Demo slides - AI Context
    'demo_ai_title' => 'Configuración de Contexto IA',
    'demo_ai_desc' => 'Selecciona presets y skills de código para generar un archivo agent.md personalizado para tus herramientas de IA.',
    'demo_ai_presets' => 'Presets de código',
    'demo_ai_skills' => 'Skills',
    'demo_ai_preset_psr12' => 'PSR-12',
    'demo_ai_preset_solid' => 'SOLID',
    'demo_ai_preset_clean' => 'Clean Arch.',
    'demo_ai_preset_laravel' => 'Laravel Conv.',
    'demo_ai_preset_ts' => 'TypeScript Strict',
    'demo_ai_skill_stripe' => 'Stripe',
    'demo_ai_skill_tailwind' => 'Tailwind',
    'demo_ai_skill_react' => 'React Expert',
    'demo_ai_skill_vue' => 'Vue Expert',
    'demo_ai_output' => '→ Genera agent.md con 2 presets + 2 skills',
    'demo_ai_count' => '5 presets · 4 skills disponibles',

    // Marketplace
    'marketplace_title' => 'Empieza con una plantilla lista para usar',
    'marketplace_cta' => 'Explorar el Marketplace →',

    // Marketplace cards (mock data)
    'blueprint_1_title' => 'Laravel + Inertia + Tailwind',
    'blueprint_1_desc' => 'Configuración completa para un stack moderno de Laravel.',
    'blueprint_1_badge' => 'Popular',
    'blueprint_1_downloads' => '1.2k descargas',

    'blueprint_2_title' => 'React + TypeScript + Vite',
    'blueprint_2_desc' => 'Entorno frontend con reglas de Cursor y MCP listos.',
    'blueprint_2_badge' => 'Nuevo',
    'blueprint_2_downloads' => '850 descargas',

    'blueprint_3_title' => 'Node.js API + PostgreSQL',
    'blueprint_3_desc' => 'Variables de conexión, seeds y configuración de Docker.',
    'blueprint_3_badge' => 'Backend',
    'blueprint_3_downloads' => '600 descargas',

    'blueprint_4_title' => 'Python + FastAPI + SQLModel',
    'blueprint_4_desc' => 'Stack Python moderno con configuración de entorno lista.',
    'blueprint_4_badge' => 'Backend',
    'blueprint_4_downloads' => '430 descargas',

    'blueprint_5_title' => 'Vue 3 + Nuxt + Supabase',
    'blueprint_5_desc' => 'Configuración completa para apps Vue con backend serverless.',
    'blueprint_5_badge' => 'Fullstack',
    'blueprint_5_downloads' => '720 descargas',

    'blueprint_6_title' => 'Go + Gin + PostgreSQL',
    'blueprint_6_desc' => 'API en Go con configuración de entorno y Docker.',
    'blueprint_6_badge' => 'Backend',
    'blueprint_6_downloads' => '310 descargas',

    'badge_cova_team' => 'CoVa Team',

    // CTA Final
    'cta_final_title' => 'Empieza a ahorrar tiempo hoy',
    'cta_final_subtitle' => 'Regístrate gratis y crea tu primer blueprint en menos de 5 minutos.',
    'cta_final_button' => 'Crear cuenta gratis',
    'cta_final_note' => 'No requiere tarjeta de crédito. Plan gratuito disponible.',

    // Terminal
    'terminal_aria_label' => 'Demostración de terminal ejecutando vault fetch',
    'terminal_title' => 'Terminal',
    'terminal_caption' => 'un solo comando, todo listo',
    'terminal_cmd_fetch' => '$ vault fetch cova-marketplace/laravel-inertia',
    'terminal_downloading' => '> Descargando blueprint...',
    'terminal_variables' => '> Variables cargadas: 12',
    'terminal_files' => '> Archivos generados: .env, agent.md, .cursorrules',
    'terminal_presets' => '> Contexto IA: PSR-12, Clean Architecture, React',
    'terminal_scripts' => '> Scripts post-instalación: composer install, npm run build',
    'terminal_ready' => '✅ Entorno listo en 2.4s',

    // Hero trust
    'hero_trust' => 'Tus datos siempre cifrados. Privacidad desde el diseño.',

    // Marketplace extra
    'marketplace_more' => 'y muchas plantillas más creadas por la comunidad',
    'coming_soon' => 'Próximamente',
    'coming_soon_badge' => '🔜 Próximamente',
    'marketplace_coming_soon' => 'El marketplace estará disponible próximamente. ¡Mantente atento!',

    // Pricing
    'nav_pricing' => 'Precios',
    'pricing_title' => 'Precios simples y transparentes',
    'pricing_subtitle' => 'Empieza gratis y escala cuando tu equipo crezca. Sin sorpresas, sin tarjetas de crédito.',
    'pricing_note' => 'Todos los precios en euros (EUR). El plan Enterprise requiere contacto previo.',
    'pricing_coming_soon_note' => '* Funcionalidades del marketplace disponibles próximamente',

    'plan_free_desc' => 'Para empezar con CoVa',
    'plan_pro_desc' => 'Para equipos en crecimiento',
    'plan_enterprise_desc' => 'Para organizaciones grandes',
    'plan_price_free' => 'gratis',
    'plan_price_month' => '/mes',
    'plan_price_custom' => 'Contactar',

    'plan_orgs' => ':count organizaciones',
    'plan_orgs_unlimited' => 'Organizaciones ilimitadas',
    'plan_blueprints' => ':count blueprints/org',
    'plan_blueprints_unlimited' => 'Blueprints ilimitados',
    'plan_members' => ':count miembros/org',
    'plan_members_unlimited' => 'Miembros ilimitados',
    'plan_variables' => ':count variables/blueprint',
    'plan_variables_unlimited' => 'Variables ilimitadas',

    'plan_marketplace_browse' => 'Explorar Marketplace',
    'plan_coming_soon' => 'Próximamente',
    'plan_api_access' => 'Acceso API',
    'plan_marketplace_publish' => 'Publicar en Marketplace',
    'plan_priority_support' => 'Soporte prioritario',
    'plan_dedicated_support' => 'Soporte dedicado',
    'plan_sso' => 'SSO / SAML',

    'plan_name_free' => 'Free',
    'plan_name_pro' => 'Pro',
    'plan_name_enterprise' => 'Enterprise',

    'plan_cta_free' => 'Empezar gratis',
    'plan_cta_pro' => 'Prueba Pro 14 días',
    'plan_cta_enterprise' => 'Contactar ventas',
    'plan_popular' => 'Más popular',

    // Footer
    'footer_tagline' => 'Configuraciones que viajan contigo.',
    'footer_product' => 'Producto',
    'footer_account' => 'Cuenta',
    'footer_links_login' => 'Iniciar sesión',
    'footer_links_register' => 'Registrarse',
    'footer_links_marketplace' => 'Marketplace',
    'footer_copyright' => '© 2026 CoVa. Todos los derechos reservados.',
];
