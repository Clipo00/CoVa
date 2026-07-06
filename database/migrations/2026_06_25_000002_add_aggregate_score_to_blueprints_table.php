<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('blueprints', function (Blueprint $table) {
            $table->integer('aggregate_score')->default(0)->after('is_public');
            $table->index('aggregate_score');
        });
    }

    public function down(): void
    {
        Schema::table('blueprints', function (Blueprint $table) {
            $table->dropIndex(['aggregate_score']);
            $table->dropColumn('aggregate_score');
        });
    }
};
