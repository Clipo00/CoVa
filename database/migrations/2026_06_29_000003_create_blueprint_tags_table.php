<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blueprint_tags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('blueprint_id')->constrained('blueprints')->cascadeOnDelete();
            $table->string('tag', 50);
            $table->timestamps();

            $table->index('tag');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blueprint_tags');
    }
};
