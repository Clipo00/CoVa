<?php

declare(strict_types=1);

namespace App\Modules\Blueprint\Tests\Unit\Models;

use App\Modules\Auth\Models\User;
use App\Modules\Blueprint\Models\Blueprint;
use App\Modules\Blueprint\Models\BlueprintVariable;
use App\Modules\Organization\Actions\CreateOrganization;
use App\Modules\Shared\Models\Plan;
use Database\Seeders\PlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class BlueprintVariableEncryptionTest extends TestCase
{
    use RefreshDatabase;

    private Blueprint $blueprint;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PlanSeeder::class);

        $plan = Plan::where('slug', 'free')->first();
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'plan_id' => $plan->id,
        ]);

        $createOrg = new CreateOrganization;
        $organization = $createOrg->execute($user, 'Test Org', 'test-org');

        $this->actingAs($user);

        $this->blueprint = Blueprint::create([
            'uuid' => Str::uuid(),
            'slug' => 'test-blueprint',
            'title' => 'Test Blueprint',
            'is_public' => false,
            'organization_id' => $organization->id,
            'created_by' => $user->id,
            'tabs_config' => [],
        ]);
    }

    public function test_encrypts_default_value_when_is_secret(): void
    {
        $variable = $this->blueprint->variables()->create([
            'key' => 'DB_PASSWORD',
            'type' => 'fixed',
            'default_value' => 'super-secret-123',
            'is_secret' => true,
        ]);

        $fresh = BlueprintVariable::find($variable->id);

        // The raw DB value should be encrypted (not plaintext)
        $raw = $fresh->getRawOriginal('default_value');
        $this->assertNotEquals('super-secret-123', $raw);
        $this->assertNotNull($raw);
    }

    public function test_decrypts_default_value_when_is_secret(): void
    {
        $this->blueprint->variables()->create([
            'key' => 'DB_PASSWORD',
            'type' => 'fixed',
            'default_value' => 'super-secret-123',
            'is_secret' => true,
        ]);

        $fresh = BlueprintVariable::first();

        $this->assertEquals('super-secret-123', $fresh->default_value);
    }

    public function test_non_secret_variable_remains_plaintext(): void
    {
        $this->blueprint->variables()->create([
            'key' => 'DB_HOST',
            'type' => 'fixed',
            'default_value' => 'localhost',
            'is_secret' => false,
        ]);

        $fresh = BlueprintVariable::first();

        $raw = $fresh->getRawOriginal('default_value');
        $this->assertEquals('localhost', $raw);
        $this->assertEquals('localhost', $fresh->default_value);
    }

    public function test_handles_null_default_value(): void
    {
        $this->blueprint->variables()->create([
            'key' => 'OPTIONAL_KEY',
            'type' => 'empty',
            'default_value' => null,
            'is_secret' => true,
        ]);

        $fresh = BlueprintVariable::first();

        $this->assertNull($fresh->default_value);
    }

    public function test_handles_empty_string_default_value(): void
    {
        $this->blueprint->variables()->create([
            'key' => 'EMPTY_KEY',
            'type' => 'empty',
            'default_value' => '',
            'is_secret' => true,
        ]);

        $fresh = BlueprintVariable::first();

        $this->assertEquals('', $fresh->default_value);
    }

    public function test_backward_compatibility_with_legacy_plaintext(): void
    {
        // Simulate a legacy variable with is_secret=true but plaintext default_value
        $variable = $this->blueprint->variables()->create([
            'key' => 'LEGACY_SECRET',
            'type' => 'fixed',
            'is_secret' => true,
        ]);

        // Directly set raw value in DB to simulate legacy data
        BlueprintVariable::withoutEvents(function () use ($variable) {
            BlueprintVariable::where('id', $variable->id)
                ->update(['default_value' => 'legacy-plaintext']);
        });

        // Clear model from identity map
        $fresh = BlueprintVariable::find($variable->id);

        // Should return the raw value without throwing
        $this->assertEquals('legacy-plaintext', $fresh->default_value);
    }

    public function test_encrypts_updated_value_when_is_secret(): void
    {
        $variable = $this->blueprint->variables()->create([
            'key' => 'API_KEY',
            'type' => 'fixed',
            'default_value' => 'original',
            'is_secret' => true,
        ]);

        $variable->update(['default_value' => 'updated-secret']);

        $fresh = BlueprintVariable::find($variable->id);
        $this->assertEquals('updated-secret', $fresh->default_value);

        $raw = $fresh->getRawOriginal('default_value');
        $this->assertNotEquals('updated-secret', $raw);
    }

    public function test_does_not_encrypt_non_secret_variable_update(): void
    {
        $variable = $this->blueprint->variables()->create([
            'key' => 'DB_HOST',
            'type' => 'fixed',
            'default_value' => 'localhost',
            'is_secret' => false,
        ]);

        $variable->update(['default_value' => 'db.example.com']);

        $fresh = BlueprintVariable::find($variable->id);
        $raw = $fresh->getRawOriginal('default_value');
        $this->assertEquals('db.example.com', $raw);
    }
}
