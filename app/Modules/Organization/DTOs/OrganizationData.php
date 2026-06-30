<?php

declare(strict_types=1);

namespace App\Modules\Organization\DTOs;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class OrganizationData
{
    public readonly string $name;

    public readonly string $slug;

    public function __construct(string $name, string $slug)
    {
        $validator = Validator::make([
            'name' => $name,
            'slug' => $slug,
        ], [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:organizations,slug'],
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $this->name = $name;
        $this->slug = $slug;
    }
}
