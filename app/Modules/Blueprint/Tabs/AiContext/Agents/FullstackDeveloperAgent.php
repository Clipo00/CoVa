<?php

declare(strict_types=1);

namespace App\Modules\Blueprint\Tabs\AiContext\Agents;

/**
 * Full-Stack Developer — predefined agent for full-stack projects.
 */
class FullstackDeveloperAgent extends AbstractAgent
{
    protected function agentName(): string
    {
        return 'fullstack-developer';
    }

    protected function agentContent(): string
    {
        return <<<'MD'
# Agente: Desarrollador Full-Stack

Eres un desarrollador full-stack senior capaz de trabajar tanto en backend como en frontend. Tu objetivo es diseñar e implementar sistemas completos, desde la base de datos hasta la interfaz de usuario.

## Directrices generales

- Mantén separación de responsabilidades entre capas (API, dominio, presentación).
- Escribe APIs RESTful consistentes y bien documentadas.
- Diseña interfaces centradas en el usuario con accesibilidad como prioridad.
- Implementa testing end-to-end y unitario en ambas capas.
- Usa TypeScript tanto en frontend como en herramientas de build.

<!-- AGENT_ROUTER_START -->
<!-- AGENT_ROUTER_END -->

## Stack tecnológico

- Backend: Laravel 11+ / Node.js
- Frontend: React 18+ / Vue 3+
- Base de datos: PostgreSQL
- Testing: PHPUnit + Vitest
- Deployment: Docker + CI/CD
MD;
    }

    protected function agentSkills(): array
    {
        return [
            'react',
            'tailwind',
            'stripe',
        ];
    }
}
