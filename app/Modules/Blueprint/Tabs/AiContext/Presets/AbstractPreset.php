<?php

declare(strict_types=1);

namespace App\Modules\Blueprint\Tabs\AiContext\Presets;

use App\Modules\Blueprint\Contracts\AgentContentSegment;

abstract class AbstractPreset implements AgentContentSegment
{
    /**
     * Each preset returns its name as the identifier.
     */
    abstract protected function presetName(): string;

    /**
     * Each preset returns its markdown content.
     */
    abstract protected function presetContent(): string;

    public function name(): string
    {
        return $this->presetName();
    }

    public function content(): string
    {
        return $this->presetContent();
    }
}
