<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('max_organizations_per_user')->nullable();
            $table->integer('max_blueprints_per_org')->nullable();
            $table->integer('max_members_per_org')->nullable();
            $table->integer('max_variables_per_blueprint')->nullable();
            $table->boolean('has_api_access')->default(false);
            $table->boolean('has_marketplace_publish')->default(false);
            $table->decimal('price_monthly', 8, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};
