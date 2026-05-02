<?php

declare(strict_types=1);

namespace App\Modules\Auth\Actions;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LogoutUser
{
    public function execute(Request $request): void
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }
}
