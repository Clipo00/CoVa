<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TFM CoVaR — Presentación Oficial</title>
    <!-- Tailwind CSS para el renderizado exacto de estilos del proyecto -->
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <!-- Google Fonts: Fuentes especificadas en UI_SPECIFICATION.md -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css2?family=JetBrains+Mono:ital,wght=0,100..800;1,100..800&family=Plus+Jakarta+Sans:ital,wght=0,200..800;1,200..800&family=Poppins:ital,wght=0,100..900;1,100..900&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #0f172a;
            color: #f8fafc;
        }
        h1, h2, h3 {
            font-family: 'Poppins', sans-serif;
        }
        code, pre, .terminal-text {
            font-family: 'JetBrains Mono', monospace;
        }
        .slide {
            display: none;
            height: 100vh;
            width: 100vw;
        }
        .slide.active {
            display: flex;
        }
        .badge-tech {
            background: rgba(79, 70, 229, 0.15);
            border: 1px solid rgba(79, 70, 229, 0.4);
            color: #818cf8;
        }
    </style>
</head>
<body class="overflow-hidden select-none">

    <!-- Diapositiva 1: Portada Real CoVaR -->
    <div class="slide active flex-col justify-between p-16 relative bg-gradient-to-br from-slate-950 via-slate-900 to-indigo-950/40">
        <div class="absolute top-12 right-16 flex items-center gap-2">
            <span class="text-indigo-500 font-black tracking-wider text-xl">CoVaR</span>
            <div class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></div>
        </div>
        <div class="my-auto max-w-4xl space-y-6">
            <span class="badge-tech px-4 py-1.5 rounded-full text-xs font-semibold tracking-widest uppercase">Trabajo Fin de Máster</span>
            <h1 class="text-6xl font-extrabold tracking-tight text-white sm:text-7xl">
                CoVaR <span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-400 to-cyan-400">— The Config Vault Recovery</span>
            </h1>
            <p class="text-xl text-slate-400 max-w-2xl font-light">
                Zero-latency environment setup for modern developers.
            </p>
        </div>
        <div class="flex justify-between items-end border-t border-slate-800/80 pt-8">
            <div>
                <p class="text-sm font-medium text-slate-400">Máster en Desarrollo con IA</p>
                <p class="text-xs text-slate-500 mt-1">Convocatoria: Julio 2026</p>
            </div>
            <div class="text-right">
                <p class="text-sm font-semibold text-indigo-400">Andrés Moreno Domínguez</p>
                <p class="text-xs text-slate-500">covarapp.com</p>
            </div>
        </div>
    </div>

    <!-- Diapositiva 2: El Problema -->
    <div class="slide flex-col justify-between p-16 bg-slate-950">
        <div class="flex justify-between items-center">
            <h2 class="text-2xl font-bold text-slate-400"><span class="text-indigo-400">01.</span> El Contexto y la Fricción</h2>
            <span class="text-xs tracking-widest text-slate-600 font-mono">CoVaR v1.0</span>
        </div>
        <div class="grid grid-cols-1 lg:grid-cols-5 gap-12 my-auto items-center">
            <div class="lg:col-span-3 space-y-6">
                <h3 class="text-4xl font-bold text-white">¿Por qué seguimos perdiendo tiempo en el Setup?</h3>
                <ul class="space-y-4 text-lg text-slate-300">
                    <li class="flex items-start gap-3">
                        <span class="text-indigo-400 font-bold">→</span> Configurar entornos locales es un proceso manual, fragmentado y propenso a errores humanos.
                    </li>
                    <li class="flex items-start gap-3">
                        <span class="text-indigo-400 font-bold">→</span> Los repositorios sufren de archivos <code class="text-indigo-300 bg-slate-900 px-1.5 py-0.5 rounded text-sm">.env.example</code> obsoletos o desactualizados.
                    </li>
                    <li class="flex items-start gap-3">
                        <span class="text-indigo-400 font-bold">→</span> El onboarding de nuevos desarrolladores se mide en **días de fricción**, en lugar de minutos productivos.
                    </li>
                </ul>
            </div>
            <div class="lg:col-span-2 bg-gradient-to-b from-indigo-950/30 to-slate-900 border border-indigo-500/20 rounded-2xl p-8 text-center shadow-2xl">
                <p class="text-7xl font-black text-indigo-400">67%</p>
                <p class="text-md text-slate-300 font-semibold mt-4">De los desarrolladores encuestados</p>
                <p class="text-sm text-slate-400 mt-2 font-light">Pierde más de **4 horas críticas** configurando su primer entorno de trabajo local.</p>
            </div>
        </div>
        <div class="text-xs text-slate-600 font-mono">Estudio de métricas de Developer Experience (DX) — 2026</div>
    </div>

    <!-- Diapositiva 3: La Solución -->
    <div class="slide flex-col justify-between p-16 bg-slate-950">
        <div class="flex justify-between items-center">
            <h2 class="text-2xl font-bold text-slate-400"><span class="text-indigo-400">02.</span> La Propuesta de Valor</h2>
            <span class="text-xs tracking-widest text-slate-600 font-mono">CoVaR v1.0</span>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 my-auto">
            <div class="p-8 bg-slate-900/60 border border-slate-800 rounded-xl space-y-4">
                <div class="h-10 w-10 rounded-lg bg-indigo-600/20 flex items-center justify-center text-indigo-400 font-bold">01</div>
                <h4 class="text-xl font-bold text-white">Plataforma SaaS Centralizada</h4>
                <p class="text-slate-400 text-sm leading-relaxed">Gestión unificada y segura de configuraciones organizacionales sin exponer credenciales críticas en texto plano.</p>
            </div>
            <div class="p-8 bg-slate-900/60 border border-slate-800 rounded-xl space-y-4">
                <div class="h-10 w-10 rounded-lg bg-cyan-600/20 flex items-center justify-center text-cyan-400 font-bold">02</div>
                <h4 class="text-xl font-bold text-white">Blueprints Ejecutables</h4>
                <p class="text-slate-400 text-sm leading-relaxed">Plantillas vivas que inyectan de forma dinámica variables de entorno, extensiones recomendadas y contexto optimizado para IA (<code class="text-xs font-mono text-cyan-300">.agent.md</code>).</p>
            </div>
            <div class="p-8 bg-indigo-950/40 border border-indigo-500/30 rounded-xl space-y-4">
                <div class="h-10 w-10 rounded-lg bg-emerald-600/20 flex items-center justify-center text-emerald-400 font-bold">03</div>
                <h4 class="text-xl font-bold text-white">CLI de un Solo Comando</h4>
                <p class="text-slate-400 text-sm leading-relaxed">Pasa de un <code class="text-xs font-mono text-slate-300">git clone</code> a un entorno listo y productivo en segundos mediante comandos nativos optimizados.</p>
            </div>
        </div>
        <div class="text-center font-mono text-indigo-400 text-sm bg-indigo-950/20 py-2 rounded-lg border border-indigo-900/40">
            $ covar vault:fetch laravel-api-starter
        </div>
    </div>

    <!-- Diapositiva 4: Stack Tecnológico Real -->
    <div class="slide flex-col justify-between p-16 bg-slate-950">
        <div class="flex justify-between items-center">
            <h2 class="text-2xl font-bold text-slate-400"><span class="text-indigo-400">03.</span> Ecosistema de Desarrollo</h2>
            <span class="text-xs tracking-widest text-slate-600 font-mono">Ecosistema Técnico</span>
        </div>
        <div class="my-auto space-y-8">
            <h3 class="text-3xl font-bold text-white text-center">Arquitectura de Stack Avanzado e Innovador</h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 max-w-5xl mx-auto">
                <div class="p-4 bg-slate-900 border border-slate-800 rounded-lg text-center">
                    <p class="text-xs text-indigo-400 font-mono mb-1">CORE BACKEND</p>
                    <p class="text-lg font-bold text-white">Laravel 13</p>
                    <p class="text-xs text-slate-500">PHP 8.4 Strict Types</p>
                </div>
                <div class="p-4 bg-slate-900 border border-slate-800 rounded-lg text-center">
                    <p class="text-xs text-cyan-400 font-mono mb-1">FRONTEND REACTIVO</p>
                    <p class="text-lg font-bold text-white">Livewire 4</p>
                    <p class="text-xs text-slate-500">Tailwind 4 + Vite 8</p>
                </div>
                <div class="p-4 bg-slate-900 border border-slate-800 rounded-lg text-center">
                    <p class="text-xs text-emerald-400 font-mono mb-1">INTERFAZ CLI</p>
                    <p class="text-lg font-bold text-white">Laravel Zero</p>
                    <p class="text-xs text-slate-500">Distribución PHAR compilada</p>
                </div>
                <div class="p-4 bg-slate-900 border border-slate-800 rounded-lg text-center">
                    <p class="text-xs text-amber-400 font-mono mb-1">INFRAESTRUCTURA</p>
                    <p class="text-lg font-bold text-white">Railway</p>
                    <p class="text-xs text-slate-500">Automated Railpacks</p>
                </div>
            </div>
            <p class="text-center text-sm text-slate-400 max-w-xl mx-auto font-light">
                 Robustez corporativa mediante un monolito altamente tipado con interfaces de consola ultrarrápidas y bases de datos relacionales (SQLite/MySQL).
            </p>
        </div>
        <div class="text-xs text-slate-600 text-right font-mono">Producción real: https://covarapp.com</div>
    </div>

    <!-- Diapositiva 5: Monolito Modular -->
    <div class="slide flex-col justify-between p-16 bg-slate-950">
        <div class="flex justify-between items-center">
            <h2 class="text-2xl font-bold text-slate-400"><span class="text-indigo-400">04.</span> Arquitectura de Software</h2>
            <span class="text-xs tracking-widest text-slate-600 font-mono">Diseño de Sistema</span>
        </div>
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 my-auto items-center">
            <div class="space-y-6">
                <h3 class="text-4xl font-bold text-white">Monolito Modular</h3>
                <p class="text-slate-300 text-md leading-relaxed">
                    Evitamos la complejidad y latencia de microservicios prematuros implementando una estructura de módulos fuertemente desacoplados por dominios de negocio claros.
                </p>
                <div class="space-y-3 font-mono text-sm">
                    <div class="flex gap-2 items-center text-slate-300"><span class="text-emerald-400">✓</span> <span>Encapsulación estricta por dominio</span></div>
                    <div class="flex gap-2 items-center text-slate-300"><span class="text-emerald-400">✓</span> <span>Estructura interna homogénea (Actions, DTOs, Policies)</span></div>
                    <div class="flex gap-2 items-center text-slate-300"><span class="text-emerald-400">✓</span> <span>Migración a microservicios con refactorización cero</span></div>
                </div>
            </div>
            <div class="p-6 bg-slate-900 border border-slate-800 rounded-xl space-y-4">
                <p class="text-xs text-slate-500 font-mono">MAPA DE MÓDULOS DEL PROYECTO</p>
                <div class="grid grid-cols-2 gap-3 text-xs font-mono">
                    <div class="p-3 bg-indigo-950/40 border border-indigo-500/20 text-indigo-300 rounded text-center font-bold">Auth Module</div>
                    <div class="p-3 bg-slate-950 border border-slate-800 text-slate-300 rounded text-center">Organization</div>
                    <div class="p-3 bg-slate-950 border border-slate-800 text-slate-300 rounded text-center">Blueprint Core</div>
                    <div class="p-3 bg-slate-950 border border-slate-800 text-slate-300 rounded text-center">Marketplace Engine</div>
                </div>
                <div class="p-3 bg-slate-800/40 border border-slate-700/50 text-slate-400 rounded text-center text-xs font-mono">
                    Shared / Kernel (Common Core Data & Contracts)
                </div>
            </div>
        </div>
        <div class="text-xs text-slate-600 font-mono">Principio rector: "Un módulo estructurado por cada dominio exclusivo"</div>
    </div>

    <!-- Diapositiva 6: Patrones de Diseño -->
    <div class="slide flex-col justify-between p-16 bg-slate-950">
        <div class="flex justify-between items-center">
            <h2 class="text-2xl font-bold text-slate-400"><span class="text-indigo-400">05.</span> Patrones de Diseño Limpio</h2>
            <span class="text-xs tracking-widest text-slate-600 font-mono">Patrones Utilizados</span>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 my-auto max-w-6xl mx-auto">
            <div class="p-6 bg-slate-900 border border-slate-800 rounded-xl">
                <h4 class="text-md font-bold text-white flex items-center gap-2">
                    <span class="h-2 w-2 rounded-full bg-indigo-500"></span> Action Pattern
                </h4>
                <p class="text-xs text-slate-400 mt-2">Casos de uso atómicos, con una sola responsabilidad pública ejecutable. Totalmente aislados del contexto y peticiones HTTP de la aplicación.</p>
            </div>
            <div class="p-6 bg-slate-900 border border-slate-800 rounded-xl">
                <h4 class="text-md font-bold text-white flex items-center gap-2">
                    <span class="h-2 w-2 rounded-full bg-cyan-500"></span> DTO Pattern (Data Transfer Objects)
                </h4>
                <p class="text-xs text-slate-400 mt-2">Uso de clases nativas <code class="text-xs font-mono text-cyan-300">final readonly</code> para el transporte inmutable de datos seguros entre las capas lógicas.</p>
            </div>
            <div class="p-6 bg-slate-900 border border-slate-800 rounded-xl">
                <h4 class="text-md font-bold text-white flex items-center gap-2">
                    <span class="h-2 w-2 rounded-full bg-emerald-500"></span> Value Objects Autoverificables
                </h4>
                <p class="text-xs text-slate-400 mt-2">Encapsulación de lógica semántica compleja como <code class="text-xs font-mono text-emerald-300">Email</code>, <code class="text-xs font-mono text-emerald-300">Uuid</code> o <code class="text-xs font-mono text-emerald-300">Slug</code>, garantizando validez en la fase de construcción.</p>
            </div>
            <div class="p-6 bg-slate-900 border border-slate-800 rounded-xl">
                <h4 class="text-md font-bold text-white flex items-center gap-2">
                    <span class="h-2 w-2 rounded-full bg-amber-500"></span> Arquitectura de Plugins Extensible
                </h4>
                <p class="text-xs text-slate-400 mt-2">Manejo modular de pestañas dinámicas en la plataforma web mediante interfaces polimórficas libres de costosas migraciones de datos SQL.</p>
            </div>
        </div>
        <div class="text-xs text-slate-600 font-mono">Seguridad: Autorización granular basada en Policies nativas (Owner, Maintainer, Developer)</div>
    </div>

    <!-- Diapositiva 7: Demo Plataforma Web -->
    <div class="slide flex-col justify-between p-16 bg-slate-950">
        <div class="flex justify-between items-center">
            <h2 class="text-2xl font-bold text-slate-400"><span class="text-indigo-400">06.</span> Plataforma Web SaaS</h2>
            <span class="text-xs tracking-widest text-slate-600 font-mono">Demo Visual</span>
        </div>
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 my-auto items-center">
            <div class="lg:col-span-4 space-y-4">
                <span class="text-xs uppercase font-mono text-indigo-400 tracking-widest">Interfaz de Usuario Real</span>
                <h3 class="text-3xl font-bold text-white">Dashboard Reactivo</h3>
                <p class="text-sm text-slate-400 leading-relaxed">
                    Flujo de usuario optimizado de punta a punta: Registro ágil, creación inmediata de organizaciones empresariales y publicación de plantillas en la nube.
                </p>
                <div class="p-4 bg-slate-900/80 border border-slate-800 rounded-lg text-xs space-y-2 text-slate-400">
                    <p class="text-white font-semibold">Secciones Destacadas:</p>
                    <p>• Gestión visual avanzada de variables de entorno <code class="text-xs text-indigo-300 bg-slate-950 p-0.5 rounded">.env</code></p>
                    <p>• Marketplace público con votaciones integradas en tiempo real</p>
                    <p>• Integración directa del manifiesto de IA <code class="text-xs text-indigo-300 bg-slate-950 p-0.5 rounded">agent.md</code></p>
                </div>
            </div>
            <!-- Mockup basado en la demo del landing page — modo oscuro -->
            <div class="lg:col-span-8 bg-gray-800 rounded-xl shadow-2xl overflow-hidden aspect-video flex flex-col border border-gray-700">
                <div class="flex items-center gap-2 px-4 py-3 bg-gray-700 border-b border-gray-600">
                    <div class="flex gap-1.5">
                        <div class="w-3 h-3 rounded-full bg-red-400"></div>
                        <div class="w-3 h-3 rounded-full bg-yellow-400"></div>
                        <div class="w-3 h-3 rounded-full bg-green-400"></div>
                    </div>
                    <div class="flex-1 mx-4">
                        <div class="bg-gray-600 rounded px-3 py-1 text-xs text-gray-400 text-center font-mono">
                            covarapp.com/dashboard
                        </div>
                    </div>
                </div>
                <div class="flex-1 p-6 overflow-hidden">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-lg font-bold text-gray-100">Panel de Control</h3>
                        <div class="w-8 h-8 rounded-full bg-indigo-900/40 flex items-center justify-center">
                            <span class="text-xs font-bold text-indigo-400">AM</span>
                        </div>
                    </div>
                    <div class="grid grid-cols-3 gap-4 mb-6">
                        <div class="bg-gray-700/50 rounded-lg p-3 border border-gray-700">
                            <p class="text-2xl font-bold text-gray-100">3</p>
                            <p class="text-xs text-gray-400">Organizaciones</p>
                        </div>
                        <div class="bg-gray-700/50 rounded-lg p-3 border border-gray-700">
                            <p class="text-2xl font-bold text-gray-100">12</p>
                            <p class="text-xs text-gray-400">Blueprints</p>
                        </div>
                        <div class="bg-gray-700/50 rounded-lg p-3 border border-gray-700">
                            <p class="text-2xl font-bold text-gray-100">5</p>
                            <p class="text-xs text-gray-400">Favoritos</p>
                        </div>
                    </div>
                    <div class="space-y-2">
                        <div class="flex items-center justify-between p-3 bg-gray-700 rounded-lg border border-gray-600">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-indigo-900/40 flex items-center justify-center text-sm font-bold text-indigo-400">M</div>
                                <div>
                                    <p class="text-sm font-medium text-gray-100">Mercury Labs</p>
                                    <p class="text-xs text-gray-400">8 blueprints</p>
                                </div>
                            </div>
                            <span class="px-2 py-0.5 text-xs rounded-full bg-purple-900/40 text-purple-300 font-medium">Pro</span>
                        </div>
                        <div class="flex items-center justify-between p-3 bg-gray-700 rounded-lg border border-gray-600">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-emerald-900/40 flex items-center justify-center text-sm font-bold text-emerald-400">P</div>
                                <div>
                                    <p class="text-sm font-medium text-gray-100">Personal</p>
                                    <p class="text-xs text-gray-400">4 blueprints</p>
                                </div>
                            </div>
                            <span class="px-2 py-0.5 text-xs rounded-full bg-gray-600 text-gray-300 font-medium">Free</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="text-xs text-slate-600 font-mono">Reactividad nativa mediante Livewire 4 sin sobrecarga de frameworks JS pesados</div>
    </div>

    <!-- Diapositiva 8: Interfaz de Consola CLI -->
    <div class="slide flex-col justify-between p-16 bg-slate-950">
        <div class="flex justify-between items-center">
            <h2 class="text-2xl font-bold text-slate-400"><span class="text-indigo-400">07.</span> Interfaz de Consola CLI</h2>
            <span class="text-xs tracking-widest text-slate-600 font-mono">Consola Terminal</span>
        </div>
        <div class="grid grid-cols-1 lg:grid-cols-5 gap-12 my-auto items-center">
            <div class="lg:col-span-2 space-y-4">
                <h3 class="text-3xl font-bold text-white">Consola de Comandos Unificada</h3>
                <p class="text-sm text-slate-400 leading-relaxed">
                     Estructurado bajo Laravel Zero para proveer ejecuciones instantáneas desde entornos de desarrollo locales remotos.
                </p>
                <div class="space-y-2 text-xs font-mono text-slate-300">
                    <p><code class="text-indigo-400">config:set-key</code> — Enlaza credenciales locales de API de forma segura.</p>
                    <p><code class="text-indigo-400">vault:list</code> — Listado visual de Blueprints disponibles.</p>
                    <p><code class="text-indigo-400 font-bold">vault:fetch</code> — Descarga el andamiaje completo del entorno en milisegundos.</p>
                </div>
            </div>
            <!-- Consola al estilo de la landing page -->
            <div class="lg:col-span-3 bg-gray-900 rounded-xl border border-gray-700/50 shadow-2xl overflow-hidden font-mono text-sm">
                <div class="flex items-center gap-1.5 px-4 py-3 bg-gray-800/50 border-b border-gray-700/50">
                    <span class="w-3 h-3 rounded-full bg-red-500 inline-block"></span>
                    <span class="w-3 h-3 rounded-full bg-yellow-500 inline-block"></span>
                    <span class="w-3 h-3 rounded-full bg-green-500 inline-block"></span>
                    <span class="ml-2 text-xs text-gray-400">Terminal — covar</span>
                </div>
                <div class="p-4 space-y-2.5 text-sm leading-relaxed">
                    <p style="color: #63c5da;">$ covar vault:fetch laravel-api-starter</p>
                    <p style="color: #a5b4fc;">> Conectando con covarapp.com...</p>
                    <p style="color: #a5b4fc;">> Autenticación correcta. Descargando Blueprint.</p>
                    <p style="color: #a5b4fc;">> Variables cargadas: 12</p>
                    <p style="color: #a5b4fc;">> Archivos generados: .env, .vscode/extensions.json, .vscode/mcp.json</p>
                    <p style="color: #a5b4fc;">> Skills configuradas: 3</p>
                    <p style="color: #4ade80; font-weight: 600;">✓ Entorno listo en 0.42s</p>
                </div>
            </div>
        </div>
         <div class="text-xs text-slate-600 font-mono">Compilación nativa empaquetada como ejecutable universal PHAR</div>
    </div>

    <!-- Diapositiva 9: Funcionalidades Enterprise -->
    <div class="slide flex-col justify-between p-16 bg-slate-950">
        <div class="flex justify-between items-center">
            <h2 class="text-2xl font-bold text-slate-400"><span class="text-indigo-400">08.</span> Funcionalidades Clave</h2>
            <span class="text-xs tracking-widest text-slate-600 font-mono">Características</span>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6 my-auto">
            <div class="p-5 bg-slate-900 border border-slate-800 rounded-lg space-y-2">
                <p class="text-lg font-bold text-white">🔒 Seguridad OWASP</p>
                <p class="text-xs text-slate-400">Estándar OWASP Top 10:2025 aplicado. Cabeceras CSP estrictas, protección contra inyecciones y Rate Limiting avanzado.</p>
            </div>
            <div class="p-5 bg-slate-900 border border-slate-800 rounded-lg space-y-2">
                <p class="text-lg font-bold text-white">👥 Colaboración B2B</p>
                <p class="text-xs text-slate-400">Invitaciones por Token seguro, roles avanzados y lógicas transaccionales limpias con Soft Deletes.</p>
            </div>
            <div class="p-5 bg-slate-900 border border-slate-800 rounded-lg space-y-2">
                <p class="text-lg font-bold text-white">🌍 Soporte i18n</p>
                <p class="text-xs text-slate-400">Traducción completa Nativa (Español/Inglés) con más de 339 claves lingüísticas y persistencia en sesión.</p>
            </div>
            <div class="p-5 bg-slate-900 border border-slate-800 rounded-lg space-y-2">
                <p class="text-lg font-bold text-white">🎨 UX / UI Premium</p>
                <p class="text-xs text-slate-400">Control total del Modo Oscuro nativo, transiciones animadas fluidas, Toasts dinámicos y diseño Responsive.</p>
            </div>
        </div>
        <div class="text-xs text-slate-600 font-mono">Infraestructura robusta construida pensando en entornos listos para producción</div>
    </div>

    <!-- Diapositiva 10: Estrategia de Calidad y Testing -->
    <div class="slide flex-col justify-between p-16 bg-slate-950">
        <div class="flex justify-between items-center">
            <h2 class="text-2xl font-bold text-slate-400"><span class="text-indigo-400">09.</span> Aseguramiento de Calidad</h2>
            <span class="text-xs tracking-widest text-slate-600 font-mono">Testing Suite</span>
        </div>
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 my-auto items-center">
            <div class="p-6 bg-slate-900 border border-slate-800 rounded-xl text-center space-y-2">
                <p class="text-6xl font-black text-emerald-400">487</p>
                <p class="text-sm font-mono text-slate-400">Tests Automatizados Ejecutados</p>
                <div class="h-1.5 w-full bg-slate-950 rounded-full overflow-hidden mt-4">
                    <div class="h-full bg-emerald-400 w-full"></div>
                </div>
            </div>
            <div class="p-6 bg-slate-900 border border-slate-800 rounded-xl text-center space-y-2">
                <p class="text-6xl font-black text-indigo-400">1,096</p>
                <p class="text-sm font-mono text-slate-400">Aseveraciones Totales</p>
                <div class="h-1.5 w-full bg-slate-950 rounded-full overflow-hidden mt-4">
                    <div class="h-full bg-indigo-400 w-full"></div>
                </div>
            </div>
            <div class="space-y-4 text-sm text-slate-300">
                <p class="flex gap-2"><b class="text-indigo-400">🧪 Unit Testing (~78%):</b> Cobertura al 100% en Actions críticas, Reglas de Validación, Value Objects y políticas.</p>
                <p class="flex gap-2"><b class="text-indigo-400">🌐 Feature & E2E (~22%):</b> Pruebas integradas HTTP y automatización completa de interfaz web mediante Playwright.</p>
            </div>
        </div>
        <div class="text-xs text-slate-600 font-mono">Entorno de ejecución de pruebas: PHPUnit Avanzado sobre base de datos en memoria</div>
    </div>

    <!-- Diapositiva 11: Despliegue en Producción -->
    <div class="slide flex-col justify-between p-16 bg-slate-950">
        <div class="flex justify-between items-center">
            <h2 class="text-2xl font-bold text-slate-400"><span class="text-indigo-400">10.</span> DevOps e Infraestructura</h2>
            <span class="text-xs tracking-widest text-slate-600 font-mono">Despliegue Continuo</span>
        </div>
        <div class="my-auto max-w-4xl mx-auto space-y-8">
            <h3 class="text-3xl font-bold text-white text-center">Estrategia de Lanzamiento e Integración en la Nube</h3>
            <div class="flex flex-col md:flex-row items-center justify-between gap-6 font-mono text-xs text-center">
                <div class="p-4 bg-slate-900 border border-slate-800 rounded-lg w-full md:w-1/4">
                    <p class="text-indigo-400 font-bold mb-1">Git Push</p>
                    <p class="text-slate-400 text-[11px]">Control de Versiones</p>
                </div>
                <span class="text-slate-600 text-xl hidden md:inline">➔</span>
                <div class="p-4 bg-slate-900 border border-indigo-500/30 rounded-lg w-full md:w-1/4">
                    <p class="text-white font-bold mb-1">Railway Build</p>
                    <p class="text-slate-400 text-[11px]">Auto-detect Nixpacks</p>
                </div>
                <span class="text-slate-600 text-xl hidden md:inline">➔</span>
                <div class="p-4 bg-slate-900 border border-slate-800 rounded-lg w-full md:w-1/4">
                    <p class="text-emerald-400 font-bold mb-1">Build Scripts</p>
                    <p class="text-slate-400 text-[11px]">Optimizaciones de Caché</p>
                </div>
            </div>
            <div class="p-5 bg-slate-900/40 border border-slate-800 rounded-lg text-sm text-slate-400 text-center max-w-2xl mx-auto">
                <p>La plataforma está actualmente desplegada y plenamente operativa de forma pública bajo una arquitectura de servidores estables en la URL oficial:</p>
                <p class="text-indigo-400 font-bold mt-2 text-lg font-mono">https://covarapp.com</p>
            </div>
        </div>
        <div class="text-xs text-slate-600 font-mono">Base de Datos: MySQL Gestionado en la nube con réplicas seguras</div>
    </div>

    <!-- Diapositiva 12: Lecciones Aprendidas -->
    <div class="slide flex-col justify-between p-16 bg-slate-950">
        <div class="flex justify-between items-center">
            <h2 class="text-2xl font-bold text-slate-400"><span class="text-indigo-400">11.</span> Conclusiones de Ingeniería</h2>
            <span class="text-xs tracking-widest text-slate-600 font-mono">Lecciones Aprendidas</span>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 my-auto max-w-5xl mx-auto text-sm">
            <div class="space-y-2">
                <p class="font-bold text-white text-base">🏗️ Monolitos Modulares como Acierto</p>
                <p class="text-slate-400">Permitió un desarrollo ágil y sin fricciones de red durante las fases iniciales del MVP sin comprometer una futura separación de servicios.</p>
            </div>
            <div class="space-y-2">
                <p class="font-bold text-white text-base">⚡ Potencia de Livewire 4</p>
                <p class="text-slate-400">Logró dotar al sistema de una interfaz altamente reactiva y moderna sin necesidad de duplicar esfuerzos construyendo un ecosistema SPA complejo.</p>
            </div>
            <div class="space-y-2">
                <p class="font-bold text-white text-base">🛡️ El valor del Tipado Estricto</p>
                <p class="text-slate-400">El uso exhaustivo de la directiva <code class="text-xs font-mono text-indigo-300">declare(strict_types=1)</code> junto a PHP 8.4 mitigó la aparición de errores en tiempo de ejecución de manera drástica.</p>
            </div>
            <div class="space-y-2">
                <p class="font-bold text-white text-base">📈 Testing desde el Día 1</p>
                <p class="text-slate-400">Garantizó la estabilidad del software a medida que los módulos crecían, evitando acumular deuda técnica crítica al momento de realizar despliegues automáticos.</p>
            </div>
        </div>
         <div class="text-xs text-slate-600 font-mono">TFM — Máster en Desarrollo con IA</div>
    </div>

    <!-- Diapositiva 13: Futuro de la Plataforma -->
    <div class="slide flex-col justify-between p-16 bg-slate-950">
        <div class="flex justify-between items-center">
            <h2 class="text-2xl font-bold text-slate-400"><span class="text-indigo-400">12.</span> Próximos Pasos</h2>
            <span class="text-xs tracking-widest text-slate-600 font-mono">Roadmap</span>
        </div>
        <div class="my-auto max-w-3xl mx-auto space-y-6">
            <h3 class="text-3xl font-bold text-white">Evolución y Escalabilidad del Producto</h3>
            <div class="space-y-4 text-sm">
                <div class="p-4 bg-slate-900 border-l-4 border-indigo-500 rounded-r-lg">
                    <p class="font-bold text-white">💳 Planificación de Monetización (Próximamente)</p>
                    <p class="text-slate-400 text-xs mt-1">Línea estratégica de trabajo futuro para la integración completa de pasarelas de pago con Stripe, habilitando planes Pro y Enterprise para organizaciones.</p>
                </div>
                <div class="p-4 bg-slate-900 border-l-4 border-cyan-500 rounded-r-lg">
                    <p class="font-bold text-white">🔗 API Pública y Webhooks Avanzados</p>
                    <p class="text-slate-400 text-xs mt-1">Apertura de la plataforma a través de REST para integraciones con plataformas de CI/CD de terceros y notificaciones en tiempo real.</p>
                </div>
                <div class="p-4 bg-slate-900 border-l-4 border-emerald-500 rounded-r-lg">
                    <p class="font-bold text-white">🤖 Ampliación de Comandos en el CLI Core</p>
                    <p class="text-slate-400 text-xs mt-1">Evolución del binario actual (basado en <code class="text-xs font-mono text-emerald-300">vault:fetch</code>) hacia un catálogo interactivo que permita comparar y sincronizar diferencias locales.</p>
                </div>
            </div>
        </div>
        <div class="text-xs text-slate-600 font-mono">Próximos lanzamientos planificados para Q4 2026</div>
    </div>

    <!-- Diapositiva 14: Cierre Oficial -->
    <div class="slide flex-col justify-between p-16 bg-gradient-to-tr from-slate-950 via-slate-900 to-indigo-950">
        <div class="w-full flex justify-end">
            <span class="text-indigo-400 font-mono text-xs uppercase tracking-widest">Fin de la Presentación</span>
        </div>
        <div class="my-auto text-center space-y-6">
            <h2 class="text-6xl font-black text-white tracking-tight">Muchas Gracias</h2>
            <p class="text-slate-400 text-lg max-w-md mx-auto font-light">Quedo a su entera disposición para cualquier pregunta o aclaración por parte del tribunal.</p>
            <div class="pt-6 flex flex-col sm:flex-row justify-center gap-4 text-xs font-mono">
                <a href="https://covarapp.com" target="_blank" rel="noopener noreferrer" class="px-4 py-2 bg-slate-900 border border-slate-800 rounded text-slate-300 hover:bg-indigo-950 hover:border-indigo-500/40 hover:text-indigo-300 transition-colors cursor-pointer">🌐 Web: covarapp.com</a>
                <a href="https://github.com/Clipo00/CoVa" target="_blank" rel="noopener noreferrer" class="px-4 py-2 bg-slate-900 border border-slate-800 rounded text-slate-300 hover:bg-indigo-950 hover:border-indigo-500/40 hover:text-indigo-300 transition-colors cursor-pointer">📦 Código: github.com/Clipo00/CoVa</a>
            </div>
        </div>
        <div class="flex justify-between text-xs text-slate-600 font-mono">
            <p>Tribunal de Evaluación de TFM</p>
            <p>Julio 2026</p>
        </div>
    </div>

    <!-- Barra de Navegación inferior fija -->
    <div class="fixed bottom-4 left-1/2 -translate-x-1/2 bg-slate-900/90 backdrop-blur border border-slate-800 px-6 py-3 rounded-full flex items-center gap-6 shadow-xl z-50">
        <button onclick="prevSlide()" class="text-slate-400 hover:text-white font-bold text-sm cursor-pointer transition-colors">◀ Anterior</button>
        <span id="slide-indicator" class="text-xs font-mono text-indigo-400 font-semibold min-w-16 text-center">1 / 14</span>
        <button onclick="nextSlide()" class="text-slate-400 hover:text-white font-bold text-sm cursor-pointer transition-colors">Siguiente ▶</button>
    </div>

    <!-- Script de control interactivo nativo -->
    <script>
        let currentSlide = 0;
        const slides = document.querySelectorAll('.slide');
        const indicator = document.getElementById('slide-indicator');

        function updateSlides() {
            slides.forEach((slide, index) => {
                if(index === currentSlide) {
                    slide.classList.add('active');
                } else {
                    slide.classList.remove('active');
                }
            });
            indicator.textContent = `${currentSlide + 1} / ${slides.length}`;
        }

        function nextSlide() {
            if (currentSlide < slides.length - 1) {
                currentSlide++;
                updateSlides();
            }
        }

        function prevSlide() {
            if (currentSlide > 0) {
                currentSlide--;
                updateSlides();
            }
        }

        // Soporte nativo para control con las flechas del teclado
        window.addEventListener('keydown', (e) => {
            if (e.key === 'ArrowRight' || e.key === ' ') {
                nextSlide();
            } else if (e.key === 'ArrowLeft') {
                prevSlide();
            }
        });
    </script>
</body>
</html>