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
        $this->assertTrue(Schema::hasTable('blueprint_tag'));
        $this->assertTrue(Schema::hasTable('tags'));
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
        // Tags table
        $tagColumns = Schema::getColumnListing('tags');
        $this->assertContains('id', $tagColumns);
        $this->assertContains('name', $tagColumns);
        $this->assertContains('slug', $tagColumns);

        // Pivot table
        $pivotColumns = Schema::getColumnListing('blueprint_tag');
        $this->assertContains('blueprint_id', $pivotColumns);
        $this->assertContains('tag_id', $pivotColumns);
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
