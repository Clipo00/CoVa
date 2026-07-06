<?php

declare(strict_types=1);

namespace App\Modules\Blueprint\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;

class BlueprintVariable extends Model
{
    protected $fillable = [
        'blueprint_id',
        'key',
        'type',
        'default_value',
        'is_interactive',
        'is_secret',
        'section',
        'section_color',
        'sort_order',
    ];

    protected $casts = [
        'is_interactive' => 'boolean',
        'is_secret' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function blueprint()
    {
        return $this->belongsTo(Blueprint::class);
    }

    /**
     * Get the decrypted default_value when is_secret is true.
     * Falls back to raw value for backward compatibility with legacy plaintext data.
     */
    protected function defaultValue(): Attribute
    {
        return Attribute::make(
            get: function (?string $value) {
                if ($value === null || $value === '') {
                    return $value;
                }

                if (!$this->is_secret) {
                    return $value;
                }

                try {
                    return Crypt::decryptString($value);
                } catch (\Exception $e) {
                    Log::warning('Failed to decrypt secret variable value', [
                        'variable_id' => $this->id,
                        'key' => $this->key,
                    ]);

                    return $value;
                }
            },
        );
    }

    /**
     * Ensure is_secret is bound to the model before default_value during mass-assignment.
     * This guarantees the encryption mutator sees the correct is_secret state.
     */
    public function fill(array $attributes): static
    {
        // Set is_secret before default_value to ensure proper encryption
        if (array_key_exists('is_secret', $attributes)) {
            $this->is_secret = (bool) $attributes['is_secret'];
        }

        return parent::fill($attributes);
    }

    /**
     * Encrypt default_value before saving when is_secret is true.
     * Uses the raw attributes to avoid infinite loops with the get accessor.
     */
    public function setDefaultValueAttribute(?string $value): void
    {
        if ($value === null || $value === '') {
            $this->attributes['default_value'] = $value;

            return;
        }

        if ($this->is_secret) {
            $this->attributes['default_value'] = Crypt::encryptString($value);
        } else {
            $this->attributes['default_value'] = $value;
        }
    }
}
