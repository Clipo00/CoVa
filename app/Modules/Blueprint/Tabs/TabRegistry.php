<?php

declare(strict_types=1);

namespace App\Modules\Blueprint\Tabs;

use App\Modules\Blueprint\Contracts\TabInterface;
use App\Modules\Blueprint\Exceptions\UnknownTabTypeException;

/**
 * Dynamic registry for tab implementations.
 *
 * Follows OCP: register new tabs without modifying existing code.
 */
class TabRegistry
{
    /**
     * @var array<string, TabInterface>
     */
    private array $tabs = [];

    /**
     * Register a tab implementation.
     */
    public function register(TabInterface $tab): void
    {
        $this->tabs[$tab->type()] = $tab;
    }

    /**
     * Get a tab by its type.
     *
     * @throws UnknownTabTypeException
     */
    public function get(string $type): TabInterface
    {
        if (! isset($this->tabs[$type])) {
            throw new UnknownTabTypeException($type);
        }

        return $this->tabs[$type];
    }

    /**
     * Check if a tab type is registered.
     */
    public function has(string $type): bool
    {
        return isset($this->tabs[$type]);
    }

    /**
     * Get all registered tab types.
     *
     * @return string[]
     */
    public function types(): array
    {
        return array_keys($this->tabs);
    }

    /**
     * Get all registered tabs.
     *
     * @return TabInterface[]
     */
    public function all(): array
    {
        return $this->tabs;
    }
}
