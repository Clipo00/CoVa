<?php

declare(strict_types=1);

namespace App\Modules\Blueprint\Actions;

use App\Modules\Blueprint\Models\Blueprint;
use Illuminate\Support\Collection;

/**
 * Generate a .env template string from a blueprint's variables.
 *
 * Outputs KEY=value lines grouped by section. Secret variables
 * always emit empty values to prevent credential leakage.
 */
class GenerateEnvTemplate
{
    /**
     * Generate .env template content.
     *
     * @return string Template content (empty string if no variables)
     */
    public function execute(Blueprint $blueprint): string
    {
        /** @var Collection $variables */
        $variables = $blueprint->variables;

        if ($variables === null || $variables->isEmpty()) {
            return '';
        }

        $lines = [];

        // Group variables by section
        $grouped = $variables->groupBy(fn ($v) => $v->section ?? 'General');

        foreach ($grouped as $section => $vars) {
            $lines[] = '# --- '.$section.' ---';

            foreach ($vars as $variable) {
                if (empty($variable->key)) {
                    continue;
                }

                $value = $variable->is_secret ? '' : ($variable->default_value ?? '');
                $lines[] = $variable->key.'='.$value;
            }
        }

        return implode("\n", $lines)."\n";
    }
}
