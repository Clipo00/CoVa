<?php

declare(strict_types=1);

namespace App\Modules\Blueprint\Controllers;

use App\Modules\Blueprint\Models\Blueprint;
use Illuminate\View\View;

class BlueprintController
{
    public function index(): View
    {
        return view('blueprint::index');
    }

    public function create(): View
    {
        return view('blueprint::create');
    }

    public function show(string $uuid): View
    {
        $blueprint = Blueprint::where('uuid', $uuid)->firstOrFail();
        return view('blueprint::show', compact('blueprint'));
    }

    public function edit(string $uuid): View
    {
        $blueprint = Blueprint::where('uuid', $uuid)->firstOrFail();
        return view('blueprint::edit', compact('blueprint'));
    }

    public function favorites(): View
    {
        return view('blueprint::favorites');
    }
}
