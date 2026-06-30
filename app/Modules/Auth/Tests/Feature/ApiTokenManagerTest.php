<?php

declare(strict_types=1);

namespace App\Modules\Auth\Tests\Feature;

use App\Modules\Auth\Livewire\ApiTokenManager;
use App\Modules\Auth\Models\User;
use App\Modules\Shared\Models\Plan;
use Carbon\Carbon;
use Database\Seeders\PlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class ApiTokenManagerTest extends TestCase
{
    use RefreshDatabase;

    private User $proUser;
    private User $freeUser;
    private Plan $proPlan;
    private Plan $freePlan;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        $this->seed(PlanSeeder::class);

        $this->proPlan = Plan::where('slug', 'pro')->first();
        $this->freePlan = Plan::where('slug', 'free')->first();

        $this->proUser = User::create([
            'name' => 'Pro User',
            'email' => 'pro@example.com',
            'password' => Hash::make('password123'),
            'plan_id' => $this->proPlan->id,
        ]);

        $this->freeUser = User::create([
            'name' => 'Free User',
            'email' => 'free@example.com',
            'password' => Hash::make('password123'),
            'plan_id' => $this->freePlan->id,
        ]);
    }

    public function test_seguridad_tab_shows_token_list_when_tokens_exist(): void
    {
        $this->actingAs($this->proUser);

        $this->proUser->createToken('CLI Token', ['*'], Carbon::now()->addMonths(6));
        $this->proUser->createToken('CI/CD Token', ['*'], Carbon::now()->addYear());

        Livewire::test(ApiTokenManager::class)
            ->assertSee('CLI Token')
            ->assertSee('CI/CD Token');
    }

    public function test_seguridad_tab_shows_empty_state_when_no_tokens(): void
    {
        $this->actingAs($this->proUser);

        Livewire::test(ApiTokenManager::class)
            ->assertSee(__('auth.token_empty'));
    }

    public function test_create_form_toggle_expands_and_collapses(): void
    {
        $this->actingAs($this->proUser);

        $component = Livewire::test(ApiTokenManager::class)
            ->assertSet('showCreateForm', false);

        $component->set('showCreateForm', true)
            ->assertSet('showCreateForm', true);

        $component->set('showCreateForm', false)
            ->assertSet('showCreateForm', false);
    }

    public function test_valid_token_creation_shows_one_time_display_with_copy_button_and_warning(): void
    {
        $this->actingAs($this->proUser);

        Livewire::test(ApiTokenManager::class)
            ->set('tokenName', 'My CLI Token')
            ->set('expiresAt', Carbon::now()->addMonths(6)->format('Y-m-d'))
            ->set('password', 'password123')
            ->call('createToken')
            ->assertSet('newPlainTextToken', fn ($value) => is_string($value) && str_contains($value, '|'))
            ->assertSee(__('auth.token_one_time_warning'))
            ->assertSee(__('auth.token_copy'))
            ->assertSee(__('auth.token_dismiss'));
    }

    public function test_after_dismissing_new_token_it_appears_in_list(): void
    {
        $this->actingAs($this->proUser);

        Livewire::test(ApiTokenManager::class)
            ->set('tokenName', 'My CLI Token')
            ->set('expiresAt', Carbon::now()->addMonths(6)->format('Y-m-d'))
            ->set('password', 'password123')
            ->call('createToken')
            ->assertSet('newPlainTextToken', fn ($value) => is_string($value))
            ->call('dismissNewToken')
            ->assertSet('newPlainTextToken', null)
            ->assertSee('My CLI Token');
    }

    public function test_revoke_token_with_correct_password_removes_token_and_shows_toast(): void
    {
        $this->actingAs($this->proUser);

        $token = $this->proUser->createToken('To Revoke', ['*'], Carbon::now()->addMonths(6));

        Livewire::test(ApiTokenManager::class)
            ->call('confirmRevoke', $token->accessToken->id)
            ->assertSet('revokeTokenId', $token->accessToken->id)
            ->set('revokePassword', 'password123')
            ->call('revokeToken')
            ->assertSet('revokeTokenId', null)
            ->assertSet('revokePassword', '')
            ->assertDispatched('notify')
            ->assertDontSee('To Revoke');
    }

    public function test_revoke_token_with_wrong_password_shows_error_and_token_stays(): void
    {
        $this->actingAs($this->proUser);

        $token = $this->proUser->createToken('Keep Me', ['*'], Carbon::now()->addMonths(6));

        Livewire::test(ApiTokenManager::class)
            ->call('confirmRevoke', $token->accessToken->id)
            ->set('revokePassword', 'wrong-password')
            ->call('revokeToken')
            ->assertHasErrors('password')
            ->assertSet('revokeTokenId', $token->accessToken->id)
            ->assertSee('Keep Me');
    }

    public function test_free_plan_user_sees_upgrade_cta_instead_of_create_form(): void
    {
        $this->actingAs($this->freeUser);

        Livewire::test(ApiTokenManager::class)
            ->assertSet('isFreePlan', true)
            ->assertSee(__('auth.token_plan_cta'));
    }

    public function test_pro_plan_user_sees_create_form(): void
    {
        $this->actingAs($this->proUser);

        Livewire::test(ApiTokenManager::class)
            ->assertSet('isFreePlan', false)
            ->assertSee(__('auth.token_create'));
    }

    public function test_expiration_beyond_one_year_shows_validation_error(): void
    {
        $this->actingAs($this->proUser);

        Livewire::test(ApiTokenManager::class)
            ->set('tokenName', 'Long Lived Token')
            ->set('expiresAt', Carbon::now()->addYears(2)->format('Y-m-d'))
            ->set('password', 'password123')
            ->call('createToken')
            ->assertHasErrors('expiresAt');
    }

    public function test_create_token_without_name_shows_validation_error(): void
    {
        $this->actingAs($this->proUser);

        Livewire::test(ApiTokenManager::class)
            ->set('tokenName', '')
            ->set('expiresAt', Carbon::now()->addMonths(6)->format('Y-m-d'))
            ->set('password', 'password123')
            ->call('createToken')
            ->assertHasErrors('tokenName');
    }

    public function test_create_token_without_password_shows_validation_error(): void
    {
        $this->actingAs($this->proUser);

        Livewire::test(ApiTokenManager::class)
            ->set('tokenName', 'No Password')
            ->set('expiresAt', Carbon::now()->addMonths(6)->format('Y-m-d'))
            ->set('password', '')
            ->call('createToken')
            ->assertHasErrors('password');
    }

    public function test_cancel_revoke_clears_revoke_state(): void
    {
        $this->actingAs($this->proUser);

        $token = $this->proUser->createToken('Cancel Me', ['*'], Carbon::now()->addMonths(6));

        Livewire::test(ApiTokenManager::class)
            ->call('confirmRevoke', $token->accessToken->id)
            ->assertSet('revokeTokenId', $token->accessToken->id)
            ->call('cancelRevoke')
            ->assertSet('revokeTokenId', null)
            ->assertSet('revokePassword', '');
    }

    public function test_rate_limit_blocks_excessive_token_creation(): void
    {
        $this->actingAs($this->proUser);

        $component = Livewire::test(ApiTokenManager::class);

        // Fire 11 create attempts within a short window
        for ($i = 0; $i < 11; $i++) {
            $component->set('tokenName', "Token {$i}")
                ->set('expiresAt', Carbon::now()->addMonths(6)->format('Y-m-d'))
                ->set('password', 'password123')
                ->call('createToken');
        }

        $component->assertHasErrors('tokenName');
    }
}
