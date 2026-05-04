<?php

declare(strict_types=1);

namespace App\Modules\Blueprint\Actions;

use App\Modules\Blueprint\Models\Blueprint;

class UpdateBlueprint
{
    public function execute(
        Blueprint $blueprint,
        array $data,
        array $variables = []
    ): Blueprint {
        // Extraer variables del data si viene ahí
        if (isset($data['variables'])) {
            $variables = $data['variables'];
            unset($data['variables']);
        }

        $blueprint->update($data);

        // Si hay variables, sincronizarlas
        if (!empty($variables)) {
            // Eliminar variables existentes
            $blueprint->variables()->delete();

            // Crear las nuevas
            foreach ($variables as $variableData) {
                if (empty($variableData['key'])) {
                    continue;
                }

                $blueprint->variables()->create([
                    'key' => $variableData['key'],
                    'type' => $variableData['type'] ?? 'fixed',
                    'default_value' => $variableData['default_value'] ?? null,
                    'is_interactive' => $variableData['is_interactive'] ?? false,
                    'is_secret' => $variableData['is_secret'] ?? false,
                    'section' => $variableData['section'] ?? null,
                    'sort_order' => 0,
                ]);
            }
        }

        return $blueprint->fresh();
    }
}