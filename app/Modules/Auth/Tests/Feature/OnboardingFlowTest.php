<?php

declare(strict_types=1);

namespace App\Modules\Auth\Tests\Feature;

use App\Modules\Auth\Livewire\Forms\OnboardingWizard;
use App\Modules\Auth\Models\User;
use App\Modules\Organization\Models\Organization;
use Database\Seeders\PlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class OnboardingFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    // ─── Full integration: registration → wizard → dashboard ──────────

    public function test_full_registration_to_dashboard_flow(): void
    {
        $this->seed(PlanSeeder::class);

        $user = User::create([
            'name' => 'Flow User',
            'email' => 'flow@example.com',
            'password' => bcrypt('password'),
            'plan_id' => 1,
        ]);

        // Onboarding page renders for un-onboarded user
        $this->actingAs($user)
            ->get(route('onboarding'))
            ->assertOk();

        // Navigate through all wizard steps
        Livewire::actingAs($user)
            ->test(OnboardingWizard::class)
            ->assertSet('step', 1)
            ->call('goToStep', 2)
            ->set('orgName', 'Flow Organization')
            ->call('submitOrg')
            ->assertSet('step', 3)
            ->call('skipStep')
            ->assertSet('step', 4)
            ->call('skipStep')
            ->assertSet('step', 5)
            ->call('complete')
            ->assertRedirect(route('dashboard'));

        // User is now onboarded
        $user->refresh();
        $this->assertNotNull($user->onboarding_completed_at);
        $this->assertNull($user->onboarding_step);

        // Dashboard is now accessible
        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk();
    }

    // ─── Skip-all flow: skip both step 3 and 4 ────────────────────────

    public function test_skip_all_flow_skipping_both_steps_advances_to_done(): void
    {
        $this->seed(PlanSeeder::class);

        $user = User::create([
            'name' => 'Skip All User',
            'email' => 'skipall@example.com',
            'password' => bcrypt('password'),
            'plan_id' => 1,
        ]);

        Livewire::actingAs($user)
            ->test(OnboardingWizard::class)
            ->call('goToStep', 2)
            ->set('orgName', 'My Organization')
            ->call('submitOrg')
            ->assertSet('step', 3)
            ->assertSee(__('onboarding.skip_button'))
            ->call('skipStep')
            ->assertSet('step', 4)
            ->assertSee(__('onboarding.skip_button'))
            ->call('skipStep')
            ->assertSet('step', 5)
            ->assertSee(__('onboarding.step_done'))
            ->call('complete')
            ->assertRedirect(route('dashboard'));

        $user->refresh();
        $this->assertNotNull($user->onboarding_completed_at);
        $this->assertNull($user->onboarding_step);
    }

    // ─── Email verification banner ────────────────────────────────────

    public function test_email_verification_banner_shown_when_email_not_verified(): void
    {
        $user = User::create([
            'name' => 'Unverified User',
            'email' => 'unverified@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->assertFalse($user->hasVerifiedEmail());

        // Banner should show on step 1
        Livewire::actingAs($user)
            ->test(OnboardingWizard::class)
            ->assertSee(__('onboarding.verify_email_notice'));

        // Banner should also show on step 2 (all steps)
        Livewire::actingAs($user)
            ->test(OnboardingWizard::class)
            ->call('goToStep', 2)
            ->assertSee(__('onboarding.verify_email_notice'));
    }

    public function test_email_verification_banner_hidden_when_email_verified(): void
    {
        $user = User::create([
            'name' => 'Verified User',
            'email' => 'verified@example.com',
            'password' => bcrypt('password'),
        ]);

        // email_verified_at is not mass-assignable, use the trait method
        $user->markEmailAsVerified();
        $user->refresh();

        $this->assertTrue($user->hasVerifiedEmail());

        Livewire::actingAs($user)
            ->test(OnboardingWizard::class)
            ->assertDontSee(__('onboarding.verify_email_notice'));
    }

    // ─── Plan limit exception ─────────────────────────────────────────

    public function test_plan_limit_exception_caught_and_shows_error_on_step_2(): void
    {
        $this->seed(PlanSeeder::class);

        $user = User::create([
            'name' => 'Plan Limit User',
            'email' => 'planlimit@example.com',
            'password' => bcrypt('password'),
            'plan_id' => 1, // Free plan: max 2 organizations
        ]);

        // Create 2 organizations to hit the limit
        Organization::create(['name' => 'Existing 1', 'slug' => 'existing-1', 'owner_id' => $user->id]);
        Organization::create(['name' => 'Existing 2', 'slug' => 'existing-2', 'owner_id' => $user->id]);

        Livewire::actingAs($user)
            ->test(OnboardingWizard::class)
            ->call('goToStep', 2)
            ->set('orgName', 'Over Limit Org')
            ->call('submitOrg')
            ->assertHasErrors('orgName')
            ->assertSet('step', 2); // Stays on step 2
    }

    // ─── Browser refresh resilience ───────────────────────────────────

    public function test_browser_refresh_preserves_current_step_from_database(): void
    {
        $user = User::create([
            'name' => 'Refresh User',
            'email' => 'refresh@example.com',
            'password' => bcrypt('password'),
        ]);

        // Simulate being on step 4
        $user->onboarding_step = 4;
        $user->save();

        // Remount should restore step from DB
        Livewire::actingAs($user)
            ->test(OnboardingWizard::class)
            ->assertSet('step', 4);
    }

    // ─── Already-completed guard ──────────────────────────────────────

    public function test_onboarded_user_redirected_from_onboarding_to_dashboard(): void
    {
        $user = User::create([
            'name' => 'Completed User',
            'email' => 'completed@example.com',
            'password' => bcrypt('password'),
            'onboarding_completed_at' => now(),
        ]);

        $this->actingAs($user)
            ->get(route('onboarding'))
            ->assertRedirect(route('dashboard'));
    }
}
