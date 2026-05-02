<?php

declare(strict_types=1);

namespace App\Modules\Organization\Actions;

use App\Modules\Organization\Models\Organization;
use App\Modules\Shared\ValueObjects\Slug;

class UpdateOrganization
{
    public function execute(Organization $organization, string $name, ?string $slug = null): Organization
    {
        $data = ['name' => $name];

        if ($slug !== null) {
            $data['slug'] = (string) new Slug($slug);
        }

        $organization->update($data);

        return $organization->fresh();
    }
}
