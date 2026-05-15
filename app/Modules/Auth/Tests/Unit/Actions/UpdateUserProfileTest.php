<?php

declare(strict_types=1);

namespace App\Modules\Auth\Tests\Unit\Actions;

use App\Modules\Auth\Actions\UpdateUserProfile;
use App\Modules\Auth\DTOs\UpdateUserProfileData;
use App\Modules\Auth\Models\User;
use App\Modules\Shared\Models\Plan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UpdateUserProfileTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\PlanSeeder::class);
    }

    public function test_it_updates_name_and_email(): void
    {
        $plan = Plan::where('slug', 'free')->first();
        $user = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => bcrypt('password'),
            'plan_id' => $plan->id,
        ]);

        $action = new UpdateUserProfile();
        $data = new UpdateUserProfileData(
            name: 'Jane Doe',
            email: 'jane@example.com',
        );

        $updated = $action->execute($user, $data);

        $this->assertEquals('Jane Doe', $updated->name);
        $this->assertEquals('jane@example.com', $updated->email);
    }

    public function test_it_updates_avatar(): void
    {
        Storage::fake('avatars');

        $plan = Plan::where('slug', 'free')->first();
        $user = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => bcrypt('password'),
            'plan_id' => $plan->id,
        ]);

        $file = UploadedFile::fake()->create('avatar.jpg', 100, 'image/jpeg');

        $action = new UpdateUserProfile();
        $data = new UpdateUserProfileData(
            name: 'John Doe',
            email: 'john@example.com',
            avatar: $file,
        );

        $updated = $action->execute($user, $data);

        $this->assertNotNull($updated->avatar);
        Storage::disk('avatars')->assertExists($updated->avatar);
    }

    public function test_it_deletes_old_avatar_when_updating(): void
    {
        Storage::fake('avatars');

        $plan = Plan::where('slug', 'free')->first();
        $user = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => bcrypt('password'),
            'plan_id' => $plan->id,
            'avatar' => 'old-avatar.jpg',
        ]);

        Storage::disk('avatars')->put('old-avatar.jpg', 'fake content');

        $file = UploadedFile::fake()->create('new-avatar.jpg', 100, 'image/jpeg');

        $action = new UpdateUserProfile();
        $data = new UpdateUserProfileData(
            name: 'John Doe',
            email: 'john@example.com',
            avatar: $file,
        );

        $updated = $action->execute($user, $data);

        Storage::disk('avatars')->assertMissing('old-avatar.jpg');
        Storage::disk('avatars')->assertExists($updated->avatar);
    }

    public function test_it_updates_password(): void
    {
        $plan = Plan::where('slug', 'free')->first();
        $user = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => Hash::make('old-password'),
            'plan_id' => $plan->id,
        ]);

        $action = new UpdateUserProfile();
        $data = new UpdateUserProfileData(
            name: 'John Doe',
            email: 'john@example.com',
            newPassword: 'new-password',
        );

        $updated = $action->execute($user, $data);

        $this->assertTrue(Hash::check('new-password', $updated->password));
    }
}
