<?php

declare(strict_types=1);

namespace App\Modules\Auth\Tests\Unit\Actions;

use App\Modules\Auth\Actions\LogoutUser;
use App\Modules\Auth\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class LogoutUserTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_logs_out_authenticated_user(): void
    {
        $user = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => bcrypt('password123'),
        ]);

        $this->actingAs($user);
        $this->assertAuthenticated();

        $request = Request::create('/logout', 'POST');
        $request->setLaravelSession($this->app['session']->driver('array'));

        $action = new LogoutUser;
        $action->execute($request);

        $this->assertGuest();
    }
}
