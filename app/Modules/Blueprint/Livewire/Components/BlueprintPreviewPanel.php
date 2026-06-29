<?php

declare(strict_types=1);

namespace App\Modules\Blueprint\Livewire\Components;

use App\Modules\Blueprint\Actions\ResolveBlueprintPreview;
use App\Modules\Blueprint\DTOs\ResolvedTabs;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * Livewire component for the blueprint live preview panel.
 *
 * Dispatched from parent form via 'preview-refresh' event.
 * Renders resolved tabs and variables using shared partials.
 */
class BlueprintPreviewPanel extends Component
{
    public array $tabsConfig = [];

    public array $variables = [];

    public bool $canViewSecrets = false;

    private ?ResolvedTabs $cachedResolvedTabs = null;

    #[On('preview-refresh')]
    public function refreshPreview(array $tabsConfig, array $variables = []): void
    {
        $this->tabsConfig = $tabsConfig;
        $this->variables = $variables;
        $this->cachedResolvedTabs = null;
    }

    public function getResolvedTabsProperty(): ?ResolvedTabs
    {
        if ($this->cachedResolvedTabs !== null) {
            return $this->cachedResolvedTabs;
        }

        if (empty($this->tabsConfig)) {
            return null;
        }

        $action = app(ResolveBlueprintPreview::class);
        $outputs = $action->execute($this->tabsConfig);

        if (empty($outputs)) {
            return null;
        }

        $this->cachedResolvedTabs = new ResolvedTabs($outputs);

        return $this->cachedResolvedTabs;
    }

    public function render()
    {
        return view('blueprint::livewire.components.blueprint-preview-panel');
    }
}
