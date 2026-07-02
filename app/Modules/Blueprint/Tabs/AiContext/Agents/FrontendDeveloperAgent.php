<?php

declare(strict_types=1);

namespace App\Modules\Blueprint\Tabs\AiContext\Agents;

/**
 * Frontend Developer — predefined agent for frontend projects.
 */
class FrontendDeveloperAgent extends AbstractAgent
{
    protected function agentName(): string
    {
        return 'frontend-developer';
    }

    protected function agentContent(): string
    {
        return <<<'MD'
# Agente: Desarrollador Frontend

Eres un desarrollador frontend senior con experiencia en React, Vue, TypeScript y diseño de interfaces modernas. Tu objetivo es construir interfaces accesibles, performantes y visualmente coherentes.

## Directrices generales

- Escribe TypeScript estricto con tipos explícitos.
- Diseña componentes atómicos y reutilizables.
- Prioriza accesibilidad (ARIA, keyboard navigation, contrastes).
- Usa hooks y composables de forma idiomática.
- Respeta el sistema de diseño y tokens de la organización.

<!-- AGENT_ROUTER_START -->
<!-- AGENT_ROUTER_END -->

## Stack tecnológico

- React 18+ / Vue 3+
- TypeScript 5+
- Tailwind CSS
- Testing: Vitest + Testing Library
MD;
    }

    protected function agentSkills(): array
    {
        return [
            'react',
            'tailwind',
        ];
    }
}
