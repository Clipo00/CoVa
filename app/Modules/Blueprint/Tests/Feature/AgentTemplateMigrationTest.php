<?php

declare(strict_types=1);

namespace App\Modules\Blueprint\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AgentTemplateMigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_agent_templates_table_exists(): void
    {
        $this->assertTrue(Schema::hasTable('agent_templates'));
    }

    public function test_agent_templates_table_has_expected_columns(): void
    {
        $columns = Schema::getColumnListing('agent_templates');

        $this->assertContains('id', $columns);
        $this->assertContains('name', $columns);
        $this->assertContains('display_name', $columns);
        $this->assertContains('content', $columns);
        $this->assertContains('skills', $columns);
        $this->assertContains('created_at', $columns);
        $this->assertContains('updated_at', $columns);
    }

    public function test_name_column_is_unique(): void
    {
        DB::table('agent_templates')->insert([
            'name' => 'duplicate-name',
            'display_name' => 'First',
            'content' => '# First',
            'skills' => '[]',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->expectException(\Illuminate\Database\QueryException::class);

        DB::table('agent_templates')->insert([
            'name' => 'duplicate-name',
            'display_name' => 'Second',
            'content' => '# Second',
            'skills' => '[]',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
