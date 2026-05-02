<?php

declare(strict_types=1);

namespace App\Modules\Organization\Controllers;

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
        return view('organization::show', compact('slug'));
    }
}
