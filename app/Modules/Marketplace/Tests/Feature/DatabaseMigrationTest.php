<?php

declare(strict_types=1);

namespace App\Modules\Marketplace\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class DatabaseMigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_tables_exist_after_migrate(): void
    {
        $this->assertTrue(Schema::hasTable('blueprint_subscriptions'));
        $this->assertTrue(Schema::hasTable('blueprint_votes'));
        $this->assertTrue(Schema::hasTable('blueprint_tags'));
        $this->assertTrue(Schema::hasTable('notifications'));
    }

    public function test_blueprint_has_marketplace_counters(): void
    {
        $this->assertTrue(Schema::hasColumn('blueprints', 'votes_count'));
        $this->assertTrue(Schema::hasColumn('blueprints', 'subscribers_count'));
    }

    public function test_blueprint_subscriptions_schema(): void
    {
        $columns = Schema::getColumnListing('blueprint_subscriptions');

        $this->assertContains('id', $columns);
        $this->assertContains('user_id', $columns);
        $this->assertContains('subscribed_blueprint_id', $columns);
        $this->assertContains('copied_blueprint_id', $columns);
        $this->assertContains('notify_on_update', $columns);
        $this->assertContains('created_at', $columns);
        $this->assertContains('updated_at', $columns);
    }

    public function test_blueprint_votes_schema(): void
    {
        $columns = Schema::getColumnListing('blueprint_votes');

        $this->assertContains('id', $columns);
        $this->assertContains('user_id', $columns);
        $this->assertContains('blueprint_id', $columns);
        $this->assertContains('vote', $columns);
        $this->assertContains('created_at', $columns);
        $this->assertContains('updated_at', $columns);
    }

    public function test_blueprint_tags_schema(): void
    {
        $columns = Schema::getColumnListing('blueprint_tags');

        $this->assertContains('id', $columns);
        $this->assertContains('blueprint_id', $columns);
        $this->assertContains('tag', $columns);
        $this->assertContains('created_at', $columns);
        $this->assertContains('updated_at', $columns);
    }

    public function test_notifications_schema(): void
    {
        $columns = Schema::getColumnListing('notifications');

        $this->assertContains('id', $columns);
        $this->assertContains('user_id', $columns);
        $this->assertContains('type', $columns);
        $this->assertContains('data', $columns);
        $this->assertContains('read_at', $columns);
        $this->assertContains('created_at', $columns);
        $this->assertContains('updated_at', $columns);
    }
}
