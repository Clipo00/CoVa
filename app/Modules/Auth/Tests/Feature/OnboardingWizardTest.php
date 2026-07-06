<?php

declare(strict_types=1);

namespace App\Modules\Auth\Tests\Feature;

use App\Modules\Auth\Livewire\Forms\OnboardingWizard;
use App\Modules\Auth\Models\User;
use Database\Seeders\PlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class OnboardingWizardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    // ─── Step 1: Welcome ──────────────────────────────────────────────

    public function test_mount_renders_step_1(): void
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        Livewire::actingAs($user)
            ->test(OnboardingWizard::class)
            ->assertSet('step', 1)
            ->assertSee(__('onboarding.welcome_heading', ['name' => $user->name]));
    }

    // ─── Step progression ─────────────────────────────────────────────

    public function test_step_progression_can_advance_forward(): void
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        Livewire::actingAs($user)
            ->test(OnboardingWizard::class)
            ->call('goToStep', 2)
            ->assertSet('step', 2)
            ->assertSee(__('onboarding.step_organization'));
    }

    public function test_backward_navigation_allowed(): void
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        Livewire::actingAs($user)
            ->test(OnboardingWizard::class)
            ->call('goToStep', 3)
            ->call('goToStep', 2)
            ->assertSet('step', 2);
    }

    // ─── Step 2: Org creation (skippable, jumps to Done) ──────────────

    public function test_step_2_has_skip_button(): void
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        Livewire::actingAs($user)
            ->test(OnboardingWizard::class)
            ->call('goToStep', 2)
            ->assertSee(__('onboarding.skip_button'));
    }

    public function test_skip_org_jumps_to_done(): void
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'skiporg@example.com',
            'password' => bcrypt('password'),
        ]);

        Livewire::actingAs($user)
            ->test(OnboardingWizard::class)
            ->call('goToStep', 2)
            ->call('skipStep')
            ->assertSet('step', 5);
    }

    public function test_org_validation_stays_on_step_2(): void
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        Livewire::actingAs($user)
            ->test(OnboardingWizard::class)
            ->call('goToStep', 2)
            ->call('submitOrg')
            ->assertHasErrors('orgName')
            ->assertSet('step', 2);
    }

    // ─── Step 3: Blueprint (skippable) ────────────────────────────────

    public function test_step_3_can_skip(): void
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        Livewire::actingAs($user)
            ->test(OnboardingWizard::class)
            ->call('goToStep', 3)
            ->assertSee(__('onboarding.step_blueprint'))
            ->call('skipStep')
            ->assertSet('step', 4);
    }

    // ─── Step 4: Invite members (skippable) ───────────────────────────

    public function test_step_4_can_skip(): void
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        Livewire::actingAs($user)
            ->test(OnboardingWizard::class)
            ->call('goToStep', 4)
            ->assertSee(__('onboarding.step_invite'))
            ->call('skipStep')
            ->assertSet('step', 5);
    }

    // ─── Org creation with plan ───────────────────────────────────────

    public function test_org_creation_advances_to_step_3(): void
    {
        $this->seed(PlanSeeder::class);

        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'plan_id' => 1, // Free plan
        ]);

        Livewire::actingAs($user)
            ->test(OnboardingWizard::class)
            ->call('goToStep', 2)
            ->set('orgName', 'My Organization')
            ->call('submitOrg')
            ->assertHasNoErrors()
            ->assertSet('step', 3)
            ->assertSet('createdOrgId', fn (mixed $id): bool => $id !== null);
    }

    // ─── Completion ──────────────────────────────────────────────────

    public function test_completion_sets_onboarding_completed_at(): void
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        Livewire::actingAs($user)
            ->test(OnboardingWizard::class)
            ->call('goToStep', 5)
            ->assertSee(__('onboarding.step_done'))
            ->call('complete')
            ->assertRedirect(route('dashboard'));

        $user->refresh();

        $this->assertNotNull($user->onboarding_completed_at);
        $this->assertNull($user->onboarding_step);
    }

    // ─── Persistence ──────────────────────────────────────────────────

    public function test_step_persists_to_db_on_transition(): void
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        Livewire::actingAs($user)
            ->test(OnboardingWizard::class)
            ->call('goToStep', 2);

        $user->refresh();
        $this->assertEquals(2, $user->onboarding_step);
    }

    public function test_refresh_restores_step_from_db(): void
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        // Set step directly (onboarding_step is not fillable)
        $user->onboarding_step = 3;
        $user->save();

        Livewire::actingAs($user)
            ->test(OnboardingWizard::class)
            ->assertSet('step', 3);
    }

    // ─── Welcome step display ─────────────────────────────────────────

    public function test_welcome_step_shows_start_button(): void
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        Livewire::actingAs($user)
            ->test(OnboardingWizard::class)
            ->assertSee(__('onboarding.start_button'));
    }
}
