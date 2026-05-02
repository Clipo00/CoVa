<?php

declare(strict_types=1);

namespace App\Modules\Auth\Controllers;

use App\Modules\Auth\Actions\LogoutUser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuthController
{
    public function showLogin(): View
    {
        return view('auth::login');
    }

    public function showRegister(): View
    {
        return view('auth::register');
    }

    public function logout(Request $request, LogoutUser $logoutUser): RedirectResponse
    {
        $logoutUser->execute($request);

        return redirect()->route('login');
    }
}
