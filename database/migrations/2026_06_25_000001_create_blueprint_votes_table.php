<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blueprint_votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('blueprint_id')->constrained()->cascadeOnDelete();
            $table->string('vote_type'); // 'up' or 'down'
            $table->timestamps();

            $table->unique(['user_id', 'blueprint_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blueprint_votes');
    }
};
