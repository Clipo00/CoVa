<?php

declare(strict_types=1);

namespace App\Modules\Blueprint\Tests\Unit\Notifications;

use App\Modules\Auth\Models\User;
use App\Modules\Blueprint\Notifications\BlueprintZipPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class BlueprintZipPasswordTest extends TestCase
{
    use RefreshDatabase;

    public function test_via_returns_mail_channel(): void
    {
        $notification = new BlueprintZipPassword('My Blueprint', 'abc123');

        $channels = $notification->via(new \stdClass);

        $this->assertEquals(['mail'], $channels);
    }

    public function test_constructor_stores_blueprint_title(): void
    {
        $notification = new BlueprintZipPassword('My Blueprint', 'abc123');

        $this->assertEquals('My Blueprint', $notification->blueprintTitle);
        $this->assertEquals('abc123', $notification->password);
    }

    public function test_notification_is_sent_via_mail_channel(): void
    {
        Notification::fake();

        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);
        $user->notify(new BlueprintZipPassword('Test BP', 'test-password-123'));

        Notification::assertSentTo(
            [$user],
            BlueprintZipPassword::class,
            function (BlueprintZipPassword $notification, array $channels): bool {
                $this->assertEquals('Test BP', $notification->blueprintTitle);
                $this->assertEquals('test-password-123', $notification->password);

                return in_array('mail', $channels, true);
            },
        );
    }

    public function test_to_mail_subject_contains_blueprint_title(): void
    {
        $notification = new BlueprintZipPassword('My Blueprint', 'abc123');
        $notifiable = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $mailMessage = $notification->toMail($notifiable);
        $rendered = (string) $mailMessage->render();

        $this->assertStringContainsString('My Blueprint', $rendered);
    }

    public function test_to_mail_contains_password(): void
    {
        $notification = new BlueprintZipPassword('My Blueprint', 'test-password-456');
        $notifiable = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $mailMessage = $notification->toMail($notifiable);
        $rendered = (string) $mailMessage->render();

        $this->assertStringContainsString('test-password-456', $rendered);
    }
}
