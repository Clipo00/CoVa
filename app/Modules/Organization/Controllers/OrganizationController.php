<?php

declare(strict_types=1);

namespace App\Modules\Organization\Controllers;

use App\Modules\Organization\Models\Organization;
use Illuminate\View\View;

class OrganizationController
{
    public function index(): View
    {
        return view('organization::index');
    }

    public function create(): View
    {
        return view('organization::create');
    }

    public function show(string $slug): View
    {
        $organization = Organization::where('slug', $slug)->firstOrFail();
        return view('organization::show', compact('organization'));
    }
}
