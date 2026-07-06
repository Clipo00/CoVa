<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop old flat blueprint_tags table
        Schema::dropIfExists('blueprint_tags');

        // Create normalized pivot
        Schema::create('blueprint_tag', function (Blueprint $table) {
            $table->foreignId('blueprint_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tag_id')->constrained()->cascadeOnDelete();
            $table->primary(['blueprint_id', 'tag_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blueprint_tag');

        // Restore old flat table
        Schema::create('blueprint_tags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('blueprint_id')->constrained('blueprints')->cascadeOnDelete();
            $table->string('tag', 50);
            $table->timestamps();
            $table->index('tag');
        });
    }
};
