<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blueprint_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('subscribed_blueprint_id')->constrained('blueprints')->cascadeOnDelete();
            $table->foreignId('copied_blueprint_id')->constrained('blueprints')->cascadeOnDelete();
            $table->boolean('notify_on_update')->default(true);
            $table->timestamps();

            $table->unique(['user_id', 'subscribed_blueprint_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blueprint_subscriptions');
    }
};
