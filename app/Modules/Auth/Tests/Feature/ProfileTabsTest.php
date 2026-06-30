<?php

declare(strict_types=1);

namespace App\Modules\Auth\Tests\Feature;

use App\Modules\Auth\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileTabsTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();

        $this->user = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => bcrypt('password123'),
        ]);
    }

    public function test_default_tab_is_datos_on_first_visit(): void
    {
        $this->actingAs($this->user)
            ->get('/profile')
            ->assertOk()
            ->assertSee('x-data')
            ->assertSee('activeTab')
            ->assertSee('datos')
            ->assertSee(__('auth.profile_tab_datos'))
            ->assertSee(__('auth.profile_tab_cuenta'))
            ->assertSee(__('auth.profile_tab_seguridad'));
    }

    public function test_seguridad_tab_has_hash_sync_handler(): void
    {
        $response = $this->actingAs($this->user)
            ->get('/profile')
            ->assertOk();

        $content = $response->getContent();

        $this->assertStringContainsString('activeTab', $content);
        $this->assertStringContainsString('location.hash', $content);
        $this->assertStringContainsString('seguridad', $content);
        $this->assertStringContainsString(__('auth.profile_tab_seguridad'), $content);
    }

    public function test_direct_navigation_with_hash_restores_active_tab(): void
    {
        $response = $this->actingAs($this->user)
            ->get('/profile')
            ->assertOk();

        $content = $response->getContent();

        // The Alpine x-init or equivalent should read location.hash on page load
        // to restore the active tab state
        $this->assertStringContainsString('location.hash', $content);
    }
}
