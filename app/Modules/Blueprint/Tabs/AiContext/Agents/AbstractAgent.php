<?php

declare(strict_types=1);

namespace App\Modules\Blueprint\Tabs\AiContext\Agents;

use App\Modules\Blueprint\Contracts\AgentContentSegment;

/**
 * Base class for predefined agent templates.
 *
 * An agent has its own markdown content (persona, role, instructions)
 * and a list of skill names that should be inserted into its content
 * at a router position marked by delimiters.
 */
abstract class AbstractAgent implements AgentContentSegment
{
    /**
     * Start delimiter for the skill router.
     */
    public const ROUTER_START = '<!-- AGENT_ROUTER_START -->';

    /**
     * End delimiter for the skill router.
     */
    public const ROUTER_END = '<!-- AGENT_ROUTER_END -->';

    /**
     * Unique agent identifier.
     */
    abstract protected function agentName(): string;

    /**
     * Markdown content for the agent persona.
     *
     * Should include ROUTER_START and ROUTER_END delimiters
     * where referenced skills will be inserted.
     */
    abstract protected function agentContent(): string;

    /**
     * Skill names from the skills registry that this agent uses.
     *
     * @return string[]
     */
    abstract protected function agentSkills(): array;

    public function name(): string
    {
        return $this->agentName();
    }

    public function content(): string
    {
        return $this->agentContent();
    }

    /**
     * Get the list of skill names this agent references.
     *
     * @return string[]
     */
    public function skills(): array
    {
        return $this->agentSkills();
    }

    /**
     * Whether the content contains router delimiters.
     */
    public function hasRouter(): bool
    {
        $content = $this->agentContent();

        return str_contains($content, self::ROUTER_START) && str_contains($content, self::ROUTER_END);
    }

    /**
     * Insert skill contents into the agent content.
     *
     * If delimiters exist, skills are inserted between them.
     * If delimiters are missing, skills are appended at the end.
     */
    public function resolveWithSkills(array $skillContents): string
    {
        $content = $this->agentContent();

        if ($this->hasRouter()) {
            $skillsBlock = implode("\n\n", $skillContents);

            return str_replace(
                self::ROUTER_START."\n".self::ROUTER_END,
                self::ROUTER_START."\n\n".$skillsBlock."\n\n".self::ROUTER_END,
                $content
            );
        }

        // No router delimiters — append at the end
        $skillsBlock = implode("\n\n", $skillContents);

        return $content."\n\n".$skillsBlock;
    }
}
