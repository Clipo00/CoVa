<?php

declare(strict_types=1);

namespace App\Modules\Auth\Tests\Feature;

use App\Modules\Auth\Livewire\Forms\RegisterForm;
use App\Modules\Auth\Models\User;
use Database\Seeders\PlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class EnsureOnboardingCompletedTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    // ─── Middleware: Guest access ─────────────────────────────────────

    public function test_guest_redirected_to_login_for_dashboard(): void
    {
        $this->get('/dashboard')
            ->assertRedirect(route('login'));
    }

    // ─── Middleware: Un-onboarded user blocked from dashboard ──────────

    public function test_un_onboarded_user_redirected_to_onboarding(): void
    {
        $user = User::create([
            'name' => 'Unonboarded User',
            'email' => 'unonboarded@example.com',
            'password' => bcrypt('password123'),
        ]);

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertRedirect(route('onboarding'));
    }

    // ─── Middleware: Onboarded user passes through ────────────────────

    public function test_onboarded_user_can_access_dashboard(): void
    {
        $user = User::create([
            'name' => 'Onboarded User',
            'email' => 'onboarded@example.com',
            'password' => bcrypt('password123'),
            'onboarding_completed_at' => now(),
        ]);

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertOk();
    }

    // ─── Onboarding route: Guest blocked ─────────────────────────────

    public function test_guest_redirected_to_login_for_onboarding(): void
    {
        $this->get('/onboarding')
            ->assertRedirect(route('login'));
    }

    // ─── Onboarding route: Un-onboarded user sees the page ────────────

    public function test_un_onboarded_user_sees_onboarding_page(): void
    {
        $user = User::create([
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'password' => bcrypt('password123'),
        ]);

        $this->actingAs($user)
            ->get('/onboarding')
            ->assertOk();
    }

    // ─── Onboarding route: Already-completed user redirected ──────────

    public function test_onboarded_user_redirected_from_onboarding_to_dashboard(): void
    {
        $user = User::create([
            'name' => 'Completed User',
            'email' => 'completed@example.com',
            'password' => bcrypt('password123'),
            'onboarding_completed_at' => now(),
        ]);

        $this->actingAs($user)
            ->get('/onboarding')
            ->assertRedirect(route('dashboard'));
    }

    // ─── Post-registration redirect ──────────────────────────────────

    public function test_registration_redirects_to_onboarding(): void
    {
        $this->seed(PlanSeeder::class);

        $component = Livewire::test(RegisterForm::class);

        $component->set('name', 'New User');
        $component->set('email', 'newuser@example.com');
        $component->set('password', 'password123');
        $component->set('password_confirmation', 'password123');

        $component->call('submit');

        $component->assertRedirect(route('onboarding'));
    }

    // ─── Backfill migration ──────────────────────────────────────────

    public function test_backfill_migration_sets_onboarding_completed_at_for_existing_users(): void
    {
        $user = User::create([
            'name' => 'Existing User',
            'email' => 'existing@example.com',
            'password' => bcrypt('password123'),
            'created_at' => now()->subDays(30),
        ]);

        // Simulate the backfill query from the migration
        DB::table('users')
            ->whereNull('onboarding_completed_at')
            ->update(['onboarding_completed_at' => DB::raw('created_at')]);

        $user->refresh();

        $this->assertNotNull($user->onboarding_completed_at);
        $this->assertEquals(
            $user->created_at->toDateTimeString(),
            $user->onboarding_completed_at->toDateTimeString()
        );
    }

    // ─── New user has null onboarding fields ──────────────────────────

    public function test_new_user_has_null_onboarding_fields(): void
    {
        $user = User::create([
            'name' => 'Fresh User',
            'email' => 'fresh@example.com',
            'password' => bcrypt('password123'),
        ]);

        $this->assertNull($user->onboarding_step);
        $this->assertNull($user->onboarding_completed_at);
    }
}
